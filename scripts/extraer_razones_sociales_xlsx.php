<?php

declare(strict_types=1);

$filePath = __DIR__.'/../Facturas/MASTER INVERSIONISTAS FEBRERO 2026 3.xlsx';
$sheetName = 'BDD FEBRERO 2026';
$targetHeader = 'RAZON SOCIAL';

if (! file_exists($filePath)) {
    fwrite(STDERR, "No se encontro el archivo: {$filePath}".PHP_EOL);
    exit(1);
}

$zip = new ZipArchive;
if ($zip->open($filePath) !== true) {
    fwrite(STDERR, 'No se pudo abrir el archivo XLSX.'.PHP_EOL);
    exit(1);
}

$readZipEntry = static function (ZipArchive $zipFile, string $entry): string {
    $content = $zipFile->getFromName($entry);
    if ($content === false) {
        throw new RuntimeException("No se encontro la entrada ZIP: {$entry}");
    }

    return $content;
};

try {
    $workbookXml = simplexml_load_string($readZipEntry($zip, 'xl/workbook.xml'));
    if ($workbookXml === false) {
        throw new RuntimeException('No se pudo leer xl/workbook.xml');
    }

    $workbookNamespaces = $workbookXml->getNamespaces(true);
    $workbookXml->registerXPathNamespace('m', $workbookNamespaces[''] ?? '');
    if (isset($workbookNamespaces['r'])) {
        $workbookXml->registerXPathNamespace('r', $workbookNamespaces['r']);
    }

    $sheetRelId = null;
    foreach ($workbookXml->xpath('//m:sheets/m:sheet') ?: [] as $sheet) {
        if ((string) $sheet['name'] === $sheetName) {
            $rAttributes = $sheet->attributes($workbookNamespaces['r'] ?? null);
            $sheetRelId = (string) ($rAttributes['id'] ?? '');
            break;
        }
    }

    if ($sheetRelId === null || $sheetRelId === '') {
        throw new RuntimeException("No se encontro la hoja '{$sheetName}'.");
    }

    $relsXml = simplexml_load_string($readZipEntry($zip, 'xl/_rels/workbook.xml.rels'));
    if ($relsXml === false) {
        throw new RuntimeException('No se pudo leer xl/_rels/workbook.xml.rels');
    }

    $relsNamespaces = $relsXml->getNamespaces(true);
    $relsXml->registerXPathNamespace('r', $relsNamespaces[''] ?? '');

    $sheetPath = null;
    foreach ($relsXml->xpath('//r:Relationship') ?: [] as $relationship) {
        if ((string) $relationship['Id'] === $sheetRelId) {
            $target = (string) $relationship['Target'];
            $target = ltrim($target, '/');
            $sheetPath = str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            break;
        }
    }

    if ($sheetPath === null) {
        throw new RuntimeException("No se pudo resolver la ruta de la hoja para relId {$sheetRelId}.");
    }

    $sharedStrings = [];
    if ($zip->locateName('xl/sharedStrings.xml') !== false) {
        $sharedStringsXml = simplexml_load_string($readZipEntry($zip, 'xl/sharedStrings.xml'));
        if ($sharedStringsXml !== false) {
            $sharedStringsNamespaces = $sharedStringsXml->getNamespaces(true);
            $sharedStringsXml->registerXPathNamespace('m', $sharedStringsNamespaces[''] ?? '');

            foreach ($sharedStringsXml->xpath('//*[local-name()="si"]') ?: [] as $si) {
                $parts = [];
                foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $textNode) {
                    $parts[] = (string) $textNode;
                }
                $sharedStrings[] = implode('', $parts);
            }
        }
    }

    $sheetXml = $readZipEntry($zip, $sheetPath);
    $xmlReader = new XMLReader;
    $xmlReader->XML($sheetXml);

    $headerRow = null;
    $targetColumn = null;
    $values = [];

    while ($xmlReader->read()) {
        if ($xmlReader->nodeType !== XMLReader::ELEMENT || $xmlReader->name !== 'row') {
            continue;
        }

        $rowNumber = (int) $xmlReader->getAttribute('r');
        $rowXml = simplexml_load_string($xmlReader->readOuterXml());
        if ($rowXml === false) {
            continue;
        }

        $cells = $rowXml->xpath('.//*[local-name()="c"]') ?: [];
        foreach ($cells as $cell) {
            $cellRef = (string) ($cell['r'] ?? '');
            if ($cellRef === '') {
                continue;
            }

            preg_match('/^[A-Z]+/', $cellRef, $matches);
            $column = $matches[0] ?? null;
            if ($column === null) {
                continue;
            }

            $cellType = (string) ($cell['t'] ?? '');
            $valueNode = $cell->xpath('./*[local-name()="v"]');
            $rawValue = isset($valueNode[0]) ? (string) $valueNode[0] : '';

            $cellValue = $rawValue;
            if ($cellType === 's') {
                $sharedIndex = (int) $rawValue;
                $cellValue = $sharedStrings[$sharedIndex] ?? '';
            }

            $cellValue = trim($cellValue);
            if ($cellValue === '') {
                continue;
            }

            if ($targetColumn === null && mb_strtoupper($cellValue) === $targetHeader) {
                $targetColumn = $column;
                $headerRow = $rowNumber;

                continue;
            }

            if ($targetColumn !== null && $column === $targetColumn && $rowNumber > (int) $headerRow) {
                $values[] = $cellValue;
            }
        }
    }

    $xmlReader->close();

    if ($targetColumn === null) {
        throw new RuntimeException("No se encontro la columna '{$targetHeader}' en la hoja '{$sheetName}'.");
    }

    $clean = [];
    $seen = [];
    foreach ($values as $value) {
        $normalized = preg_replace('/\s+/u', ' ', trim($value));
        if ($normalized === null || $normalized === '') {
            continue;
        }

        $key = mb_strtoupper($normalized);
        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $clean[] = $normalized;
    }

    sort($clean, SORT_NATURAL | SORT_FLAG_CASE);

    foreach ($clean as $name) {
        echo $name.PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit(1);
} finally {
    $zip->close();
}

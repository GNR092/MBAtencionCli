<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class PdfReaderService
{
    public function extractImportesPorDepartamento($filePath)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        // Divide por líneas y limpia espacios
        $lines = preg_split('/\r\n|\r|\n/', trim($text));

        $foundSection = false;
        $departamentos = [];
        $importes = [];
        $result = [];

        foreach ($lines as $line) {
            // Detecta los departamentos
            if (preg_match('/DEPARTAMENTO\(S\)\s+N[ÚU]MERO\(S\):/i', $line)) {
                if (preg_match_all('/\b\d{3,4}\b/', $line, $matches)) {
                    $departamentos = $matches[0];
                }
            }

            // Detecta inicio de sección de importes
            if (preg_match('/IMPORTE\s+BRUTO\s+DE\s+RENTA\s+MENSUAL/i', $line)) {
                $foundSection = true;
                continue;
            }

            // Si estamos en la sección de importes, buscamos montos
            if ($foundSection) {
                if (preg_match_all('/\$?\s*([\d]{1,3}(?:[.,]\d{3})*(?:[.,]\d{2}))/u', $line, $matches)) {
                    foreach ($matches[1] as $monto) {
                        $importes[] = $this->normalizeAmount($monto);
                    }
                }

                // Salir cuando lleguemos a la siguiente sección
                if (preg_match('/CORREO\s+PARA|FECHA\s+L[IÍ]MITE|VIGENCIA/i', $line)) {
                    break;
                }
            }
        }

        // Relacionar departamentos con importes si coinciden en cantidad
        foreach ($departamentos as $index => $dep) {
            $result[$dep] = $importes[$index] ?? null;
        }

        return $result;
    }

    private function normalizeAmount($value)
    {
        $value = str_replace(['$', ' '], '', $value);

        if (preg_match('/\d{1,3}(,\d{3})*\.\d{2}/', $value)) {
            return str_replace(',', '', $value);
        }

        if (preg_match('/\d{1,3}(\.\d{3})*,\d{2}/', $value)) {
            $value = str_replace('.', '', $value);
            return str_replace(',', '.', $value);
        }

        return $value;
    }
}

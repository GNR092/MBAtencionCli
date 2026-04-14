<?php

namespace App\Services;

use SimpleXMLElement;

class XmlValidationService
{
    private function normalize($text)
    {
        $text = strtolower(trim((string) $text));
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        return $ascii !== false ? $ascii : $text;
    }

    /**
     * Valida y extrae datos de un XML CFDI 4.0
     */
    public function validateXml($filePath, $filename)
    {
        $result = [
            'valid' => false,
            'errors' => [],
            'filename' => $filename,

            'comprobante' => [],
            'emisor' => [],
            'receptor' => [],
            'conceptos' => [],
            'conceptosAgrupados' => [],
            'impuestos' => null,
            'timbreFiscalDigital' => [],

            'emisor_name' => '',
            'receptor_name' => '',
            'uuid' => null,
            'proyectos' => '',
            'departamento' => null,
            'mes' => null,
            'anio' => null,
            'periodo_pago' => null,
            'isr' => 0.0,
            'iva' => 0.0,
            'valorUnitario' => null,
            'TipoFactor' => null,
            'tasaCuota' => null,
            'regimenFiscal' => null,
            'cuenta_predial' => null,
            'retroactivo' => false,
            'periodosDetectados' => [],
        ];

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);

        if (! $xml) {
            $result['errors'][] = ['Campo' => 'Archivo', 'Error' => 'XML corrupto', 'Sug' => 'Verificar archivo'];

            return $result;
        }

        $ns = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('cfdi', $ns['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4');
        $xml->registerXPathNamespace('tfd', $ns['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital');

        $tfdNode = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;
        if ($tfdNode) {
            $result['timbreFiscalDigital'] = $this->getAttributesAsArray($tfdNode);
            $result['uuid'] = strtolower($result['timbreFiscalDigital']['UUID'] ?? '');
        }

        $result['comprobante'] = $this->getAttributesAsArray($xml);

        $emisor = $xml->xpath('//cfdi:Comprobante/cfdi:Emisor')[0] ?? null;
        if ($emisor) {
            $result['emisor'] = $this->getAttributesAsArray($emisor);
            $result['emisor_name'] = $result['emisor']['Nombre'] ?? '';
            $result['regimenFiscal'] = $result['emisor']['RegimenFiscal'] ?? null;
        }

        $receptor = $xml->xpath('//cfdi:Comprobante/cfdi:Receptor')[0] ?? null;
        if ($receptor) {
            $result['receptor'] = $this->getAttributesAsArray($receptor);
            $result['receptor_name'] = $result['receptor']['Nombre'] ?? '';
        }

        $conceptosNodes = $xml->xpath('//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');
        $foundPeriod = false;
        $periodosDetectados = [];

        $meses = [
            'ENERO' => '01',
            'FEBRERO' => '02',
            'MARZO' => '03',
            'ABRIL' => '04',
            'MAYO' => '05',
            'JUNIO' => '06',
            'JULIO' => '07',
            'AGOSTO' => '08',
            'SEPTIEMBRE' => '09',
            'SETIEMBRE' => '09',
            'OCTUBRE' => '10',
            'NOVIEMBRE' => '11',
            'DICIEMBRE' => '12',
        ];
        $mesesPorNumero = array_flip($meses);
        $fechaComprobante = $result['comprobante']['Fecha'] ?? null;
        $fallbackYear = null;
        if ($fechaComprobante) {
            $fallbackYear = substr((string) $fechaComprobante, 0, 4);
            if (! preg_match('/^20\d{2}$/', (string) $fallbackYear)) {
                $fallbackYear = null;
            }
        }

        foreach ($conceptosNodes as $i => $node) {
            $data = $this->getAttributesAsArray($node);
            $descripcion = $data['Descripcion'] ?? '';

            $descClean = preg_replace('/\s+/', ' ', $descripcion);
            $descClean = trim((string) $descClean);

            $conceptoMes = null;
            $conceptoAnio = null;
            $conceptoPeriodo = null;
            $conceptoDepartamento = null;
            $conceptoPeriodos = $this->extractPeriodsFromText($descClean, $meses, $fallbackYear);

            if (! empty($conceptoPeriodos)) {
                $conceptoPeriodo = $conceptoPeriodos[0];
                [$conceptoAnio, $mesNum] = explode('-', $conceptoPeriodo);
                $conceptoMes = strtolower($mesesPorNumero[$mesNum] ?? '');
            }

            foreach ($conceptoPeriodos as $periodoDetectado) {
                $periodosDetectados[$periodoDetectado] = true;
            }

            $conceptoDepartamento = $this->extractDepartamentoFromText($descClean);

            if (! $result['mes'] && $conceptoMes) {
                $result['mes'] = $conceptoMes;
            }

            if (! $result['anio'] && $conceptoAnio) {
                $result['anio'] = $conceptoAnio;
            }

            if (! $result['departamento'] && $conceptoDepartamento) {
                $result['departamento'] = $conceptoDepartamento;
            }

            $data['Impuestos'] = ['Traslados' => [], 'Retenciones' => []];
            $data['concepto_mes'] = $conceptoMes;
            $data['concepto_anio'] = $conceptoAnio;
            $data['concepto_periodo'] = $conceptoPeriodo;
            $data['concepto_periodos'] = $conceptoPeriodos;
            $data['concepto_departamento'] = $conceptoDepartamento;

            $trasladosNodes = $node->xpath('cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
            if (! empty($trasladosNodes)) {
                foreach ($trasladosNodes as $tras) {
                    $data['Impuestos']['Traslados'][] = $this->getAttributesAsArray($tras);
                }
            }

            $retencionesNodes = $node->xpath('cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion');
            if (! empty($retencionesNodes)) {
                foreach ($retencionesNodes as $ret) {
                    $data['Impuestos']['Retenciones'][] = $this->getAttributesAsArray($ret);
                }
            }

            $predialNode = $node->xpath('cfdi:CuentaPredial')[0] ?? null;
            if ($predialNode) {
                $attr = $this->getAttributesAsArray($predialNode);
                $data['CuentaPredial'] = $attr;
                if (! $result['cuenta_predial']) {
                    $result['cuenta_predial'] = $attr['Numero'] ?? null;
                }
            } else {
                $predialEnTexto = $this->extractPredialFromText($descClean);
                if ($predialEnTexto) {
                    $data['CuentaPredial'] = ['Numero' => $predialEnTexto, 'Origen' => 'Texto'];
                    if (! $result['cuenta_predial']) {
                        $result['cuenta_predial'] = $predialEnTexto;
                    }
                }
            }

            if ($i === 0) {
                $result['valorUnitario'] = $data['ValorUnitario'] ?? null;
                $foundTax = false;

                if (! empty($data['Impuestos']['Retenciones'])) {
                    foreach ($data['Impuestos']['Retenciones'] as $ret) {
                        if (isset($ret['TasaOCuota'])) {
                            $result['TipoFactor'] = $ret['TipoFactor'];
                            $result['tasaCuota'] = $ret['TasaOCuota'];
                            $foundTax = true;
                            break;
                        }
                    }
                }

                if (! $foundTax && ! empty($data['Impuestos']['Traslados'])) {
                    foreach ($data['Impuestos']['Traslados'] as $tras) {
                        $tipo = $tras['TipoFactor'] ?? '';
                        if ($tipo === 'Tasa') {
                            $result['TipoFactor'] = 'Tasa';
                            $result['tasaCuota'] = $tras['TasaOCuota'];
                            $foundTax = true;
                            break;
                        }
                        if ($tipo === 'Exento' && ! $result['TipoFactor']) {
                            $result['TipoFactor'] = 'Exento';
                            $result['tasaCuota'] = '0.000000';
                        }
                    }
                }
            }

            $result['conceptos'][] = $data;
        }

        $result['periodosDetectados'] = array_keys($periodosDetectados);

        $impuestosGlobales = $xml->xpath('//cfdi:Comprobante/cfdi:Impuestos')[0] ?? null;
        if ($impuestosGlobales) {
            $result['impuestos'] = $this->getAttributesAsArray($impuestosGlobales);
            $result['isr'] = floatval($result['impuestos']['TotalImpuestosRetenidos'] ?? 0);
            $result['iva'] = floatval($result['impuestos']['TotalImpuestosTrasladados'] ?? 0);
        } else {

        }

        if ($result['anio'] && $result['mes']) {
            $mesNum = $meses[$result['mes']] ?? null;
            if ($mesNum) {
                $result['periodo_pago'] = $result['anio'].'-'.$mesNum;
            }
        }

        $currentPeriod = date('Y-m');
        if (! empty($result['periodosDetectados'])) {
            foreach ($result['periodosDetectados'] as $periodo) {
                if ($periodo !== $currentPeriod) {
                    $result['retroactivo'] = true;
                    break;
                }
            }
        }

        $critical_errors = [];
        $warnings = [];

        if (! $result['uuid']) {
            $critical_errors[] = ['Campo' => 'UUID', 'Error' => 'Falta Timbre Fiscal', 'Sug' => 'XML incompleto o sin timbrar'];
        }
        if (floatval($result['comprobante']['Total'] ?? 0) <= 0) {
            $critical_errors[] = ['Campo' => 'Total', 'Error' => 'Total en 0', 'Sug' => 'Verificar montos del XML'];
        }
        if (empty($result['receptor']['Rfc'])) {
            $critical_errors[] = ['Campo' => 'RFC Receptor', 'Error' => 'Falta RFC', 'Sug' => 'El nodo Receptor es obligatorio'];
        }

        foreach ($result['conceptos'] as $idx => $concepto) {
            if (! $concepto['concepto_periodo']) {
                $warnings[] = [
                    'Campo' => 'Concepto '.($idx + 1),
                    'Error' => 'No se detectó el mes/año en la descripción',
                    'Sug' => 'Revisar formato de descripción o asignar periodo manual antes de confirmar',
                ];
            }
        }

        if (! $result['mes']) {
            $warnings[] = ['Campo' => 'Mes', 'Error' => 'No detectado automát.', 'Sug' => 'Seleccionar mes manualmente'];
        }
        if (! $result['anio']) {
            $warnings[] = ['Campo' => 'Año', 'Error' => 'No detectado automát.', 'Sug' => 'Ingresar año manualmente'];
        }
        if (! $result['departamento']) {
            $warnings[] = ['Campo' => 'Departamento', 'Error' => 'No detectado', 'Sug' => 'Asignar departamento manualmente'];
        }

        if (! $result['cuenta_predial']) {
            $warnings[] = ['Campo' => 'Predial', 'Error' => 'No encontrado', 'Sug' => 'Verificar si aplica'];
        }

        $result['errors'] = array_merge($critical_errors, $warnings);

        $result['valid'] = empty($critical_errors);

        $result['requires_manual_check'] = ! empty($warnings);

        unset($result['comprobante']['Sello'], $result['comprobante']['Certificado']);
        unset($result['timbreFiscalDigital']['SelloCFD'], $result['timbreFiscalDigital']['SelloSAT']);

        return $result;
    }

    /**
     * Helper para convertir atributos de SimpleXML a Array asociativo
     */
    private function getAttributesAsArray($node)
    {
        $attributes = [];
        if ($node && $node->attributes()) {
            foreach ($node->attributes() as $key => $val) {
                $attributes[$key] = (string) $val;
            }
        }

        return $attributes;
    }

    private function extractUuidFromXml(SimpleXMLElement $xml)
    {
        $namespaces = $xml->getNamespaces(true);

        if (isset($namespaces['tfd'])) {
            $xml->registerXPathNamespace('tfd', $namespaces['tfd']);
        } else {
            $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
        }

        $uuidNodes = $xml->xpath('//tfd:TimbreFiscalDigital/@UUID');
        if (! empty($uuidNodes)) {
            return strtolower((string) $uuidNodes[0]);
        }

        return null;
    }

    private function extractPeriodsFromText(string $text, array $meses, ?string $fallbackYear = null): array
    {
        $normalized = mb_strtoupper($this->normalize($text));
        $normalized = preg_replace('/\s+/', ' ', $normalized ?? '') ?? '';
        $pattern = '/\b(ENERO|FEBRERO|MARZO|ABRIL|MAYO|JUNIO|JULIO|AGOSTO|SEPTIEMBRE|SETIEMBRE|OCTUBRE|NOVIEMBRE|DICIEMBRE)\b(?:\s*(?:DE|DEL|AL)?\s*(\d{2,4}))?/i';

        preg_match_all($pattern, $normalized, $matches, PREG_SET_ORDER);

        $periodos = [];
        foreach ($matches as $match) {
            $mesNombre = mb_strtoupper(trim((string) ($match[1] ?? '')));
            $mesNum = $meses[$mesNombre] ?? null;
            if (! $mesNum) {
                continue;
            }

            $anio = $this->normalizeYear((string) ($match[2] ?? ''), $fallbackYear);
            if (! $anio) {
                continue;
            }

            $periodos[$anio.'-'.$mesNum] = true;
        }

        return array_keys($periodos);
    }

    private function normalizeYear(string $yearText, ?string $fallbackYear = null): ?string
    {
        $yearText = trim($yearText);

        if (preg_match('/^20\d{2}$/', $yearText)) {
            return $yearText;
        }

        if (preg_match('/^\d{2}$/', $yearText)) {
            $intYear = intval($yearText);

            return (string) (2000 + $intYear);
        }

        if ($fallbackYear && preg_match('/^20\d{2}$/', $fallbackYear)) {
            return $fallbackYear;
        }

        return null;
    }

    private function extractDepartamentoFromText(string $text): ?string
    {
        $normalized = mb_strtoupper($this->normalize($text));
        $normalized = preg_replace('/\s+/', ' ', $normalized ?? '') ?? '';

        $patterns = [
            '/\b(?:DEPARTAMENTO|DEPTO|DEPTO\.|DEP\.|UNIDAD|LOCAL|OFICINA|PUERTA|PH|GH)\s*(?:NO\.?|NUM\.?|#)?\s*([A-Z0-9\-]{1,12})\b/i',
            '/\b([A-Z]{1,4}\d{1,5}[A-Z]?)\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalized, $matches)) {
                $value = trim((string) ($matches[1] ?? ''));
                if ($value !== '' && ! in_array($value, ['HABITACIONAL', 'CONDOMINIO', 'SUBCONDOMINIO'], true)) {
                    return $value;
                }
            }
        }

        return null;
    }

    private function extractPredialFromText(string $text): ?string
    {
        $normalized = mb_strtoupper($this->normalize($text));
        $pattern = '/(?:PREDIAL|CATASTRAL|TABLAJE(?:\s+CATASTRAL)?)(?:[\s\.\:\#NRO\-]*)([0-9][0-9,\-\s]{2,})/i';

        if (! preg_match($pattern, $normalized, $matches)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) ($matches[1] ?? ''));

        return $digits !== '' ? $digits : null;
    }
}

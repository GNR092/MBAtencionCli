<?php

namespace App\Services;

use SimpleXMLElement;

class XmlValidationService
{
    private function normalize($text)
    {
        $text = strtolower(trim($text));

        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        return $text;
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

        $meses = ['enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04', 'mayo' => '05', 'junio' => '06', 'julio' => '07', 'agosto' => '08', 'septiembre' => '09', 'setiembre' => '09', 'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12'];
        $regexMes = '/\b(ENERO|FEBRERO|MARZO|ABRIL|MAYO|JUNIO|JULIO|AGOSTO|SEPTIEMBRE|SETIEMBRE|OCTUBRE|NOVIEMBRE|DICIEMBRE)\b/i';
        $regexDepto = '/(?:DEP(?:ARTAMENTO|TO|T)?\.?|PH|GH|LOCAL|OFICINA|UNIDAD)\s*(?:NO\.?|NUM\.?|#)?\s*([A-Z0-9\-\s]{1,10})\b/i';

        foreach ($conceptosNodes as $i => $node) {
            $data = $this->getAttributesAsArray($node);
            $descripcion = $data['Descripcion'] ?? '';

            $descClean = preg_replace('/\s+/', ' ', $descripcion);

            $conceptoMes = null;
            $conceptoAnio = null;
            $conceptoPeriodo = null;
            $conceptoDepartamento = null;

            if (preg_match($regexMes, $descClean, $matches)) {
                $conceptoMes = strtolower($matches[1]);
            }

            if (preg_match('/\b(20[2-3][0-9])\b/', $descClean, $matches)) {
                $conceptoAnio = $matches[1];
            }

            if (preg_match($regexDepto, $descClean, $matches)) {
                $conceptoDepartamento = trim(strtoupper($matches[1]));
            }

            if ($conceptoMes && $conceptoAnio) {
                $mesNum = $meses[$conceptoMes] ?? null;
                if ($mesNum) {
                    $conceptoPeriodo = $conceptoAnio.'-'.$mesNum;
                    $periodosDetectados[$conceptoPeriodo] = true;
                }
            }

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

                if (preg_match('/(?:PREDIAL|CATASTRAL)(?:[\s\.\:\#]*)([0-9]+)/i', $descClean, $matches)) {
                    $data['CuentaPredial'] = ['Numero' => $matches[1], 'Origen' => 'Texto'];
                    if (! $result['cuenta_predial']) {
                        $result['cuenta_predial'] = $matches[1];
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
                $critical_errors[] = [
                    'Campo' => 'Concepto '.($idx + 1),
                    'Error' => 'No se detectó el mes/año en la descripción',
                    'Sug' => 'Agregar formato correcto: "Enero 2025" o "Septiembre de 2025"',
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
}

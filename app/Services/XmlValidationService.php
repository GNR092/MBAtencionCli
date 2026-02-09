<?php

namespace App\Services;

use SimpleXMLElement;
use Illuminate\Support\Facades\Log;

class XmlValidationService
{

    private function normalize($text)
    {
        $text = strtolower(trim($text));

        // Quitar acentos con iconv
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

            // Datos Estructurales
            'comprobante' => [],
            'emisor' => [],
            'receptor' => [],
            'conceptos' => [],
            'impuestos' => null,
            'timbreFiscalDigital' => [],

            // Campos "Flattened" / Legacy para tu base de datos
            'emisor_name' => '',
            'receptor_name' => '',
            'uuid' => null,
            'proyectos' => '', // Campo libre
            'departamento' => null,
            'mes' => null,
            'anio' => null,
            'periodo_pago' => null,
            'isr' => 0.0,
            'iva' => 0.0,
            'valorUnitario' => null,
            'TipoFactor' => null, // Tasa, Cuota, Exento
            'tasaCuota' => null,  // 0.160000, 0.012500, etc.
            'regimenFiscal' => null,
            'cuenta_predial' => null, // Agregado al legacy para fácil acceso
        ];

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);

        if (!$xml) {
            $result['errors'][] = ['Campo' => 'Archivo', 'Error' => 'XML corrupto', 'Sug' => 'Verificar archivo'];
            return $result;
        }

        // Namespaces
        $ns = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('cfdi', $ns['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4');
        $xml->registerXPathNamespace('tfd', $ns['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital');

        // --- 1. EXTRACCIÓN BÁSICA ---

        // Timbre (UUID)
        $tfdNode = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;
        if ($tfdNode) {
            $result['timbreFiscalDigital'] = $this->getAttributesAsArray($tfdNode);
            $result['uuid'] = strtolower($result['timbreFiscalDigital']['UUID'] ?? '');
        }

        // Nodos Principales
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

        // --- 2. PROCESAMIENTO INTELIGENTE DE CONCEPTOS ---

        $conceptosNodes = $xml->xpath('//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');
        $foundPeriod = false;

        foreach ($conceptosNodes as $i => $node) {
            $data = $this->getAttributesAsArray($node);
            $descripcion = $data['Descripcion'] ?? '';

            // Limpieza básica de la descripción para facilitar regex
            $descClean = preg_replace('/\s+/', ' ', $descripcion); // Quitar dobles espacios

            // --- A. EXTRACCIÓN DE FECHA (Mes y Año) ---
            if (!$result['mes']) {
                // Busca: "Enero", "Enero 2025", "Mes de Enero", "SEPTIEMBRE"
                // El (?<!...) evita falsos positivos si hubiera palabras compuestas, pero \b es suficiente usualmente.
                $regexMes = '/\b(ENERO|FEBRERO|MARZO|ABRIL|MAYO|JUNIO|JULIO|AGOSTO|SEPTIEMBRE|SETIEMBRE|OCTUBRE|NOVIEMBRE|DICIEMBRE)\b/i';
                if (preg_match($regexMes, $descClean, $matches)) {
                    $result['mes'] = strtolower($matches[1]);
                }
            }

            if (!$result['anio']) {
                // Busca: 2024, 2025, 2026... (aislado por bordes de palabra)
                if (preg_match('/\b(20[2-3][0-9])\b/', $descClean, $matches)) {
                    $result['anio'] = $matches[1];
                }
            }

            // --- B. EXTRACCIÓN DE DEPARTAMENTO ---
            if (!$result['departamento']) {
                // Busca: "Depto 202", "Dep. 202", "Departamento 73205", "PH 3", "Local A-1"
                // Explicación:
                // 1. (?:DEP...|PH|LOCAL...) -> Busca palabras clave
                // 2. \.?\s* -> Punto opcional y espacios
                // 3. (?:NO\.?|NUM\.?|#)? -> Opcionalmente "No.", "Num", "#"
                // 4. \s* -> Espacios
                // 5. ([A-Z0-9\-\s]{1,10}) -> Captura de 1 a 10 caracteres (Letras, numeros, guiones)
                $regexDepto = '/(?:DEP(?:ARTAMENTO|TO|T)?\.?|PH|GH|LOCAL|OFICINA|UNIDAD)\s*(?:NO\.?|NUM\.?|#)?\s*([A-Z0-9\-\s]{1,10})\b/i';

                if (preg_match($regexDepto, $descClean, $matches)) {
                    // Limpiamos espacios extra del resultado (ej. " 2203" -> "2203")
                    $result['departamento'] = trim(strtoupper($matches[1]));
                }
            }

            // --- C. EXTRACCIÓN DE IMPUESTOS (Tu código corregido con XPath) ---
            $data['Impuestos'] = ['Traslados' => [], 'Retenciones' => []];

            // Traslados
            $trasladosNodes = $node->xpath('cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
            if (!empty($trasladosNodes)) {
                foreach ($trasladosNodes as $tras) {
                    $data['Impuestos']['Traslados'][] = $this->getAttributesAsArray($tras);
                }
            }
            // Retenciones
            $retencionesNodes = $node->xpath('cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion');
            if (!empty($retencionesNodes)) {
                foreach ($retencionesNodes as $ret) {
                    $data['Impuestos']['Retenciones'][] = $this->getAttributesAsArray($ret);
                }
            }

            // --- D. CUENTA PREDIAL ---
            $predialNode = $node->xpath('cfdi:CuentaPredial')[0] ?? null;
            if ($predialNode) {
                $attr = $this->getAttributesAsArray($predialNode);
                $data['CuentaPredial'] = $attr;
                if (!$result['cuenta_predial']) $result['cuenta_predial'] = $attr['Numero'] ?? null;
            } else {
                // Búsqueda en texto si no hay nodo
                if (preg_match('/(?:PREDIAL|CATASTRAL)(?:[\s\.\:\#]*)([0-9]+)/i', $descClean, $matches)) {
                    $data['CuentaPredial'] = ['Numero' => $matches[1], 'Origen' => 'Texto'];
                    if (!$result['cuenta_predial']) $result['cuenta_predial'] = $matches[1];
                }
            }

            // --- E. VALORES LEGACY (Solo 1er concepto para tabla principal) ---
            if ($i === 0) {
                $result['valorUnitario'] = $data['ValorUnitario'] ?? null;
                $foundTax = false;

                // Prioridad: Retención
                if (!empty($data['Impuestos']['Retenciones'])) {
                    foreach ($data['Impuestos']['Retenciones'] as $ret) {
                        if (isset($ret['TasaOCuota'])) {
                            $result['TipoFactor'] = $ret['TipoFactor'];
                            $result['tasaCuota'] = $ret['TasaOCuota'];
                            $foundTax = true;
                            break;
                        }
                    }
                }
                // Fallback: Traslados
                if (!$foundTax && !empty($data['Impuestos']['Traslados'])) {
                    foreach ($data['Impuestos']['Traslados'] as $tras) {
                        $tipo = $tras['TipoFactor'] ?? '';
                        if ($tipo === 'Tasa') {
                            $result['TipoFactor'] = 'Tasa';
                            $result['tasaCuota'] = $tras['TasaOCuota'];
                            $foundTax = true;
                            break;
                        }
                        if ($tipo === 'Exento' && !$result['TipoFactor']) {
                            $result['TipoFactor'] = 'Exento';
                            $result['tasaCuota'] = '0.000000';
                        }
                    }
                }
            }

            $result['conceptos'][] = $data;
        }

        // --- 3. TOTALES GLOBALES ---
        $impuestosGlobales = $xml->xpath('//cfdi:Comprobante/cfdi:Impuestos')[0] ?? null;
        if ($impuestosGlobales) {
            $result['impuestos'] = $this->getAttributesAsArray($impuestosGlobales);
            $result['isr'] = floatval($result['impuestos']['TotalImpuestosRetenidos'] ?? 0);
            $result['iva'] = floatval($result['impuestos']['TotalImpuestosTrasladados'] ?? 0);
        } else {
            // Si no hay nodo global (raro en CFDI 4.0 pero posible si todo es exento y el PAC lo omite), sumar manual
            // Para "Almas Activas" (Exento) el nodo global sí existe pero solo trae traslados.
        }

        // --- 4. CAMPOS DERIVADOS ---
        if ($result['anio'] && $result['mes']) {
            $meses = ['enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04', 'mayo' => '05', 'junio' => '06', 'julio' => '07', 'agosto' => '08', 'septiembre' => '09', 'setiembre' => '09', 'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12'];
            $mesNum = $meses[$result['mes']] ?? null;
            if ($mesNum) {
                $result['periodo_pago'] = $result['anio'] . '-' . $mesNum;
            }
        }

        // --- 5. VALIDACIONES ---
        $critical_errors = []; // Errores que impiden guardar la factura
        $warnings = [];        // Avisos (Faltan datos, pero la factura es fiscalmente válida)

        // A. VALIDACIONES CRÍTICAS (Fiscales / Estructurales)
        if (!$result['uuid']) {
            $critical_errors[] = ['Campo' => 'UUID', 'Error' => 'Falta Timbre Fiscal', 'Sug' => 'XML incompleto o sin timbrar'];
        }
        if (floatval($result['comprobante']['Total'] ?? 0) <= 0) {
            $critical_errors[] = ['Campo' => 'Total', 'Error' => 'Total en 0', 'Sug' => 'Verificar montos del XML'];
        }
        if (empty($result['receptor']['Rfc'])) {
            $critical_errors[] = ['Campo' => 'RFC Receptor', 'Error' => 'Falta RFC', 'Sug' => 'El nodo Receptor es obligatorio'];
        }

        // B. VALIDACIONES DE NEGOCIO (Advertencias)
        // Si fallan, permitimos pasar el XML pero avisamos al usuario para que complete manualmente
        if (!$result['mes']) {
            $warnings[] = ['Campo' => 'Mes', 'Error' => 'No detectado automát.', 'Sug' => 'Seleccionar mes manualmente'];
        }
        if (!$result['anio']) {
            $warnings[] = ['Campo' => 'Año', 'Error' => 'No detectado automát.', 'Sug' => 'Ingresar año manualmente'];
        }
        if (!$result['departamento']) {
            $warnings[] = ['Campo' => 'Departamento', 'Error' => 'No detectado', 'Sug' => 'Asignar departamento manualmente'];
        }

        // Validación Predial (Advertencia)
        if (!$result['cuenta_predial']) {
            $warnings[] = ['Campo' => 'Predial', 'Error' => 'No encontrado', 'Sug' => 'Verificar si aplica'];
        }

        // --- C. RESULTADO FINAL ---

        // Unimos todo para mostrarlo en el frontend si quieres
        $result['errors'] = array_merge($critical_errors, $warnings);

        // *** CAMBIO CLAVE ***
        // Solo es INVALIDO si hay errores críticos. Si solo faltó el mes, es VALIDO.
        $result['valid'] = empty($critical_errors);

        // Opcional: Flag para saber si requiere atención manual
        $result['requires_manual_check'] = !empty($warnings);

        // Limpieza visual
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
                $attributes[$key] = (string)$val;
            }
        }
        return $attributes;
    }

    private function extractUuidFromXml(SimpleXMLElement $xml)
    {
        $namespaces = $xml->getNamespaces(true);

        // Register TimbreFiscalDigital namespace
        if (isset($namespaces['tfd'])) {
            $xml->registerXPathNamespace('tfd', $namespaces['tfd']);
        } else {
            $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
        }

        // Extract UUID from TimbreFiscalDigital
        $uuidNodes = $xml->xpath('//tfd:TimbreFiscalDigital/@UUID');
        if (!empty($uuidNodes)) {
            return strtolower((string)$uuidNodes[0]);
        }

        return null;
    }
}
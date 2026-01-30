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

    public function validateXml($filePath, $filename)
    {
         $errors = [];
        $result = [
            'valid' => false,
            'errors' => [],
            'emisor_name' => '',
            'receptor_name' => '',
            'uuid' => null,
            'proyectos' => '',
            
        ];

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);
        
        if (!$xml) {
            $result['errors'][] = [
                'Campo' => 'Archivo',
                'Error Detectado' => 'XML inválido o corrupto',
                'Corrección Sugerida' => 'Verificar que el archivo XML esté bien formado'
            ];
            return $result;
        }

        // =============================
        // 1) Extraer UUID
        // =============================
        $uuid = $this->extractUuidFromXml($xml);
        $result['uuid'] = $uuid;
        
        if (!$uuid) {
            $errors[] = [
                'Campo' => 'UUID',
                'Error Detectado' => 'No encontrado',
                'Corrección Sugerida' => 'El XML debe contener un TimbreFiscalDigital con UUID válido'
            ];
        }

        // =============================
        // 2) Extraer namespaces y datos básicos
        // =============================
        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('cfdi', $namespaces['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4');

        $folio = (string)($xml->xpath('//cfdi:Comprobante/@Folio')[0] ?? '');
        $moneda = (string)($xml->xpath('//cfdi:Comprobante/@Moneda')[0] ?? '');
        $formaPago = (string)($xml->xpath('//cfdi:Comprobante/@FormaPago')[0] ?? '');
        $metodoPago = (string)($xml->xpath('//cfdi:Comprobante/@MetodoPago')[0] ?? '');

        $total = (string)($xml->xpath('//cfdi:Comprobante/@Total')[0] ?? ''); // nuevo campo

        $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;
        $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;

        $result['emisor_name'] = (string)($emisor['Nombre'] ?? '');
        $result['receptor_name'] = (string)($receptor['Nombre'] ?? '');

        $rfc = (string)($receptor['Rfc'] ?? '');
        $domFiscal = (string)($receptor['DomicilioFiscalReceptor'] ?? '');
        $regimen = (string)($receptor['RegimenFiscalReceptor'] ?? '');

        // === Nuevas líneas correctas ===

$tipoFactor = null;
$tasaCuota = null;
$valorUnitario = null;
$totalISR = 0.0;

// Recorremos todos los conceptos para detectar correctamente los valores
$conceptos = $xml->xpath('//cfdi:Concepto');
foreach ($conceptos as $concepto) {
    // Valor unitario
    if (isset($concepto['ValorUnitario'])) {
        $valorUnitario = (string)$concepto['ValorUnitario'];
    }
    // Buscar Traslados (IVA, Exento, etc.)
    $traslados = $concepto->xpath('.//cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
    foreach ($traslados as $traslado) {
        if (isset($traslado['TipoFactor'])) {
            $tipoFactor = (string)$traslado['TipoFactor'];
        }
        if (isset($traslado['TasaOCuota'])) {
            $tasaCuota = (string)$traslado['TasaOCuota'];
        }
    }

       // Buscar Retenciones (ISR, IVA retenido, etc.)
    $retenciones = $concepto->xpath('.//cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion');
    foreach ($retenciones as $retencion) {
        if (isset($retencion['TipoFactor'])) {
            $tipoFactor = (string)$retencion['TipoFactor'];
        }
        if (isset($retencion['TasaOCuota'])) {
            $tasaCuota = (string)$retencion['TasaOCuota'];
        }
    }
}

        // =============================
// 6) Calcular total de impuestos retenidos (ISR)
// =============================
$totalISR = 0.0;

// Primero intentamos a nivel global del comprobante
$impuestos = $xml->xpath('//cfdi:Impuestos')[0] ?? null;
if ($impuestos) {
    $totalISRAttr = (string)($impuestos['TotalImpuestosRetenidos'] ?? '');
    if (is_numeric($totalISRAttr)) {
        $totalISR += floatval($totalISRAttr);
    }
}

// Ahora verificamos a nivel de conceptos individuales (por si los XML los incluyen allí)
$conceptos = $xml->xpath('//cfdi:Concepto');
foreach ($conceptos as $concepto) {
    $retenciones = $concepto->xpath('.//cfdi:Retenciones/cfdi:Retencion');
    foreach ($retenciones as $retencion) {
        $impuesto = (string)$retencion['Impuesto'] ?? '';
        $importe = (float)($retencion['Importe'] ?? 0);

        // Solo sumamos las retenciones de ISR (Impuesto=001)
        if ($impuesto === '001') {
            $totalISR += $importe;
        }
    }
}

$result['isr'] = $totalISR;
        //
        $result['tipoFactor'] = $tipoFactor;
        $result['tasaCuota'] = $tasaCuota;
        $result['valorUnitario'] = $valorUnitario;
       
        // =============================
        // 3) Validaciones de campos obligatorios
        // =============================
        if (!$total || !is_numeric($total) || floatval($total) <= 0) {
            $errors[] = [
                'Campo' => 'Importe Total',
                'Error Detectado' => $total ?: 'Vacío',
                'Corrección Sugerida' => 'Debe contener un importe numérico mayor a 0'
            ];
        }

        if (!$result['receptor_name']) {
            $errors[] = [
                'Campo' => 'Nombre del receptor',
                'Error Detectado' => 'Vacío',
                'Corrección Sugerida' => 'Debe estar presente'
            ];
        }

        if (!$rfc) {
            $errors[] = [
                'Campo' => 'RFC del receptor',
                'Error Detectado' => 'Vacío',
                'Corrección Sugerida' => 'Debe estar presente'
            ];
        }

        if (!$domFiscal) {
            $errors[] = [
                'Campo' => 'Dirección fiscal',
                'Error Detectado' => 'Vacío',
                'Corrección Sugerida' => 'Debe estar presente'
            ];
        }

        if (!$folio) {
            $errors[] = [
                'Campo' => 'Folio',
                'Error Detectado' => 'Vacío',
                'Corrección Sugerida' => 'Debe estar presente'
            ];
        }

        if ($moneda !== 'MXN') {
            $errors[] = [
                'Campo' => 'Moneda',
                'Error Detectado' => $moneda,
                'Corrección Sugerida' => 'Debe ser MXN'
            ];
        }

        if (!$formaPago) {
            $errors[] = [
                'Campo' => 'Forma de pago',
                'Error Detectado' => 'Vacía',
                'Corrección Sugerida' => 'Debe incluir forma de pago válida'
            ];
        }

        if (!$metodoPago) {
            $errors[] = [
                'Campo' => 'Método de pago',
                'Error Detectado' => 'Vacío',
                'Corrección Sugerida' => 'Debe incluir método de pago válido'
            ];
        }

        

        // =============================
        // 4) Validar conceptos
        // =============================

$conceptos = $xml->xpath('//cfdi:Concepto');
$tienePredial = false; // flag para validar cuenta predial

    foreach ($conceptos as $i => $concepto) {
        $descripcion = (string)($concepto['Descripcion'] ?? '');
        $descripcionNorm = $this->normalize($descripcion);
        $coincide = false;
        $proyectoEncontrado = null;

        
    // =============================
    // Extraer MES y AÑO
    // =============================
    $mes = null;
    $anio = null;

    // Extraer mes
    if (preg_match('/(?:MES DE)?[:\-\s]*\b(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|setiembre|octubre|noviembre|diciembre)\b/i', $descripcion, $matches)) {
        $mes = strtolower($matches[1]); // Ej: "abril"
    }

    // Extraer año (2023, 2024, etc.)
    if (preg_match('/\b(20\d{2}|\d{4})\b/', $descripcion, $matchAnio)) {
        $anio = $matchAnio[1]; // Ej: 2024
    }

    // Convertir mes a número
    $meses = [
        'enero'=>'01','febrero'=>'02','marzo'=>'03','abril'=>'04','mayo'=>'05','junio'=>'06',
        'julio'=>'07','agosto'=>'08','septiembre'=>'09','setiembre'=>'09','octubre'=>'10','noviembre'=>'11','diciembre'=>'12'
    ];
    $mesNumero = $mes ? $meses[$mes] : null;

    // Guardar en resultado
    if ($mes) {
        if (empty($result['mes'])) {
            $result['mes'] = $mes; // Ej: "abril"
        }
    } else {
        $errors[] = [
            'Campo' => 'mes (Concepto #' . ($i+1) . ')',
            'Error Detectado' => $descripcion,
            'Corrección Sugerida' => 'Debe incluir el mes de pago'
        ];
    }

    if ($anio) {
        if (empty($result['anio'])) {
            $result['anio'] = $anio; // Ej: "2024"
        }
    } else {
        $errors[] = [
            'Campo' => 'año (Concepto #' . ($i+1) . ')',
            'Error Detectado' => $descripcion,
            'Corrección Sugerida' => 'Debe incluir el año del pago'
        ];
    }

    // Combinar para obtener "YYYY-MM"
    if ($mesNumero && $anio) {
        $result['periodo_pago'] = $anio . '-' . $mesNumero; // Ej: "2024-04"
    }


        // Extraer número de DEPARTAMENTO en la descripción
$departamento = null;
if (preg_match('/DEP(?:ARTAMENTO|TO)?[:\-\s]+([A-Z0-9]+)/i', $descripcion, $matches)) {
    $departamento = strtoupper($matches[1]); // opcional: para guardar siempre en mayúsculas
}

if ($departamento) {
    // Guardar el departamento en el resultado si aún no se definió
    if (empty($result['departamento'])) {
        $result['departamento'] = $departamento;
    }
} else {
    $errors[] = [
        'Campo' => 'Departamento (Concepto #' . ($i + 1) . ')',
        'Error Detectado' => $descripcion,
        'Corrección Sugerida' => 'Debe incluir "DEPARTAMENTO <número o código>" en la descripción'
    ];
}


            // Validar proyecto (con los nombres válidos)
            /*foreach ($this->validDepartments as $dep) {
                if (stripos($descripcionNorm, $dep) !== false) {
                    $coincide = true;
                    $proyectoEncontrado = $dep;
                    break;
                }
            }*/
            
          /*  if (!$coincide) {
                $errors[] = [
                    'Campo' => 'Tipo de departamento (Concepto #' . ($i + 1) . ')',
                    'Error Detectado' => $descripcion,
                    'Corrección Sugerida' => 'Debe incluir un nombre de departamento válido'
                ];
            } else {
                if (empty($result['proyectos'])) {
                    $result['proyectos'] = strtoupper($proyectoEncontrado);
                }
            }*/

            // Validar CuentaPredial
            $predial = $concepto->xpath('./cfdi:CuentaPredial/@Numero');
            if (!empty($predial)) {
                $tienePredial = true;
            }

            // Validar impuestos
            $traslados = $concepto->xpath('.//cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
            $exentoEncontrado = false;
            
            foreach ($traslados as $traslado) {
                $impuesto = (string)$traslado['Impuesto'] ?? '';
                $tipoFactor = (string)$traslado['TipoFactor'] ?? '';
                
                if ($impuesto === '002' && $tipoFactor === 'Exento') {
                    $exentoEncontrado = true;
                    break;
                }
            }
            
            if (!$exentoEncontrado) {
                $errors[] = [
                    'Campo' => 'Traslado IVA Exento (Concepto #' . ($i + 1) . ')',
                    'Error Detectado' => 'No encontrado',
                    'Corrección Sugerida' => 'Debe ser tipoFactor="Exento"'
                ];
            }
        }


        // Si ningún concepto tenía cuenta predial
        if (!$tienePredial) {
            $errors[] = [
                'Campo' => 'Cuenta Predial',
                'Error Detectado' => 'No encontrada en ningún concepto',
                'Corrección Sugerida' => 'Debe incluir <cfdi:CuentaPredial Numero="..."/> en al menos un concepto'
            ];
    }

        // =============================
        // 5) Resultado final
        // =============================
        $result['valid'] = empty($errors);
        $result['errors'] = $errors;

        return $result;
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
<?php

namespace App\Http\Controllers\Factura;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CuentasPorCobrar;
use App\Models\Impuesto;
use App\Models\Proyecto;
use App\Models\XmlFile;
use App\Services\DescripcionParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserFactController extends Controller
{
    public function uploadPdf(Request $request, $index)
    {
        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData) || $index < 0 || $index >= count($allFacturasData)) {
            return response()->json(['success' => false, 'message' => 'Factura no encontrada.']);
        }

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:20480',
        ]);

        $currentFacturaData = $allFacturasData[$index];
        $xmlData = $currentFacturaData['xmlData'];
        $factura = $this->MapingFacturas($xmlData);
        $uuid = $factura['uuid'] ?? null;

        if (! $uuid || $uuid === 'N/A') {
            return response()->json(['success' => false, 'message' => 'No se encontró UUID en el XML.']);
        }

        try {
            $pdfFile = $request->file('pdf_file');
            $originalName = $pdfFile->getClientOriginalName();
            $extension = $pdfFile->getClientOriginalExtension();

            if (! isset($allFacturasData[$index]['pdf_data'])) {
                $allFacturasData[$index]['pdf_data'] = [];
            }

            $tempPdfPath = $pdfFile->store('tmp', 'local');

            $allFacturasData[$index]['pdf_data'] = [
                'temp_path' => $tempPdfPath,
                'original_name' => $originalName,
                'extension' => $extension,
                'uuid' => $uuid,
            ];

            session()->put('factura_data', $allFacturasData);

            return response()->json([
                'success' => true,
                'message' => 'PDF subido correctamente',
                'filename' => $originalName,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al subir PDF: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al subir el PDF.']);
        }
    }

    public function viewPdf($index)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect('/inicio-de-sesion');
        }

        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData) || $index < 0 || $index >= count($allFacturasData)) {
            abort(404, 'Factura no encontrada.');
        }

        $currentFacturaData = $allFacturasData[$index];

        if (! isset($currentFacturaData['pdf_data']) || empty($currentFacturaData['pdf_data'])) {
            abort(404, 'PDF no encontrado.');
        }

        $pdfData = $currentFacturaData['pdf_data'];
        $tempPath = $pdfData['temp_path'];

        if (! Storage::disk('local')->exists($tempPath)) {
            abort(404, 'Archivo PDF no encontrado.');
        }

        $path = Storage::disk('local')->path($tempPath);
        $filename = $pdfData['original_name'] ?? 'factura.pdf';

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function showInvoice(Request $request, $index = 0)
    {
        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData)) {
            return view('User.FacturaError');
        }

        $totalFacturas = count($allFacturasData);

        if ($index < 0 || $index >= $totalFacturas) {
            session()->forget('factura_data');

            return redirect()->route('facturas.index');
        }

        $currentFacturaData = $allFacturasData[$index];

        if (! is_array($currentFacturaData) || ! isset($currentFacturaData['xmlData'])) {
            return redirect()->back()->withErrors(['message' => 'Datos de factura inválidos para el índice actual.']);
        }

        $xmlData = $currentFacturaData['xmlData'];

        $factura = $this->MapingFacturas($xmlData);

        // Validar que el emisor de la factura coincida con el usuario autenticado
        $user = Auth::user();
        $userMismatch = false;
        if (mb_strtolower(trim($factura['emisor_nombre'])) !== mb_strtolower(trim($user->name))) {
            $userMismatch = true;
        }

        $proyecto = Proyecto::find($currentFacturaData['select_project']);
        $factura['idproyect'] = $proyecto->id_proyecto ?? null;
        $factura['nombre_proyecto'] = $proyecto->nombre_proyecto ?? 'Proyecto Desconocido';

        $AllProyects = Proyecto::all()->toArray();
        $parser = new DescripcionParser;

        $des = $factura['conceptos'][0]['descripcion'];
        $parsedData = $parser->parsearDescripcion($des, $AllProyects);
        $parsedProjectInfo = $parsedData['proyecto'] ?? null;
        $parsedProjectId = $parsedProjectInfo['id_proyecto'] ?? null;
        $parsedProjectName = $parsedProjectInfo['nombre_proyecto'] ?? 'No detectado';
        $selectedProjectId = $currentFacturaData['select_project'];
        $selectedProjectName = $proyecto->nombre_proyecto ?? 'N/A';

        $departamentos = $parsedData['departamentos'] ?? [];
        $departamentoText = ! empty($departamentos) ? implode(', ', $departamentos) : null;

        $parsedFecha = $parsedData['fecha'] ?? null;
        $parsedMes = $parsedFecha['mes_nombre'] ?? null;
        $parsedAnio = $parsedFecha['anio'] ?? null;

        $folioPredial = $parsedData['folio_predial'] ?? null;

        $projectMismatch = false;
        if ($parsedProjectId === null || (int) $parsedProjectId !== (int) $selectedProjectId) {
            $projectMismatch = true;
        }

        $departamentoMissing = empty($departamentoText);
        $mesMissing = empty($parsedMes);
        $anioMissing = empty($parsedAnio);

        $uuid = $factura['uuid'] !== 'N/A' ? $factura['uuid'] : null;
        $uuidExists = $uuid && XmlFile::where('uuid', $uuid)->exists();

        $pdfUploaded = isset($currentFacturaData['pdf_data']) && ! empty($currentFacturaData['pdf_data']);
        $pdfFilename = $pdfUploaded ? ($currentFacturaData['pdf_data']['original_name'] ?? 'documento.pdf') : null;

        return view('User.UserFactView', compact(
            'factura', 'totalFacturas', 'index',
            'projectMismatch', 'parsedProjectId', 'parsedProjectName', 'selectedProjectId', 'selectedProjectName',
            'userMismatch', 'user', 'uuidExists',
            'pdfUploaded', 'pdfFilename',
            'departamentoText', 'parsedMes', 'parsedAnio',
            'departamentoMissing', 'mesMissing', 'anioMissing',
            'folioPredial'
        ));
    }

    private function MapingFacturas(array $xmlData): array
    {
        // Mapear datos del comprobante
        $comprobante = $xmlData['comprobante'] ?? [];
        $emisor = $xmlData['emisor'] ?? [];
        $receptor = $xmlData['receptor'] ?? [];
        $impuestos = $xmlData['impuestos'] ?? [];
        $timbre = $xmlData['timbreFiscalDigital'] ?? [];

        $factura = [
            'folio' => $comprobante['Folio'] ?? 'N/A',
            'fecha' => $comprobante['Fecha'] ?? 'N/A',
            'forma_pago' => $comprobante['FormaPago'] ?? 'N/A',
            'no_certificado' => $comprobante['NoCertificado'] ?? 'N/A',
            'subtotal' => (float) ($comprobante['SubTotal'] ?? 0),
            'total' => (float) ($comprobante['Total'] ?? 0),

            'emisor_rfc' => $emisor['Rfc'] ?? 'N/A',
            'emisor_nombre' => $emisor['Nombre'] ?? 'N/A',
            'emisor_regimen' => $emisor['RegimenFiscal'] ?? 'N/A',

            'receptor_rfc' => $receptor['Rfc'] ?? 'N/A',
            'receptor_nombre' => $receptor['Nombre'] ?? 'N/A',
            'receptor_domicilio' => $receptor['DomicilioFiscalReceptor'] ?? 'N/A',
            'receptor_uso_cfdi' => $receptor['UsoCFDI'] ?? 'N/A',

            'conceptos' => [],
            'impuestos_traslados' => [],
            'impuestos_retenciones' => [],
            'total_retenciones' => (float) ($impuestos['TotalImpuestosRetenidos'] ?? 0),

            'uuid' => $timbre['UUID'] ?? 'N/A',
            'fecha_timbrado' => $timbre['FechaTimbrado'] ?? 'N/A',
            'no_certificado_sat' => $timbre['NoCertificadoSAT'] ?? 'N/A',
            'rfc_prov_certif' => $timbre['RfcProvCertif'] ?? 'N/A',
            'sello_cfd' => $xmlData['sello_cfd'] ?? 'N/A',
            'sello_sat' => $xmlData['sello_sat'] ?? 'N/A',
        ];

        // Procesar conceptos con sus impuestos
        foreach ($xmlData['conceptos'] ?? [] as $concept) {
            $concepto = [
                'clave_prod_serv' => $concept['ClaveProdServ'] ?? 'N/A',
                'descripcion' => $concept['Descripcion'] ?? 'N/A',
                'cantidad' => (float) ($concept['Cantidad'] ?? 0),
                'unidad' => $concept['Unidad'] ?? 'N/A',
                'valor_unitario' => (float) ($concept['ValorUnitario'] ?? 0),
                'importe' => (float) ($concept['Importe'] ?? 0),
                'objeto_imp' => $concept['ObjetoImp'] ?? 'N/A',
                'traslados' => [],
                'retenciones' => [],
                'cuenta_predial' => $concept['CuentaPredial']['Numero'] ?? null,
            ];

            // Traslados del concepto
            foreach ($concept['Impuestos']['Traslados'] ?? [] as $traslado) {
                $trasladoData = [
                    'impuesto' => $traslado['Impuesto'] ?? 'N/A',
                    'tipo_factor' => $traslado['TipoFactor'] ?? 'N/A',
                    'tasa' => (float) ($traslado['TasaOCuota'] ?? 0),
                    'importe' => (float) ($traslado['Importe'] ?? 0),
                ];
                $concepto['traslados'][] = $trasladoData;

                $factura['impuestos_traslados'][] = [
                    'impuesto' => $trasladoData['impuesto'],
                    'tipo_factor' => $trasladoData['tipo_factor'],
                    'importe' => $trasladoData['importe'],
                ];
            }

            // Retenciones del concepto
            foreach ($concept['Impuestos']['Retenciones'] ?? [] as $retencion) {
                $retencionData = [
                    'impuesto' => $retencion['Impuesto'] ?? 'N/A',
                    'tasa' => (float) ($retencion['TasaOCuota'] ?? 0),
                    'importe' => (float) ($retencion['Importe'] ?? 0),
                ];
                $concepto['retenciones'][] = $retencionData;

                $factura['impuestos_retenciones'][] = [
                    'impuesto' => $retencionData['impuesto'],
                    'importe' => $retencionData['importe'],
                ];
            }

            $factura['conceptos'][] = $concepto;
        }

        return $factura;
    }

    public function confirmFactura(Request $request, $index)
    {
        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData) || $index < 0 || $index >= count($allFacturasData)) {
            return redirect()->back()->withErrors(['message' => 'Factura no encontrada o índice inválido para confirmar.']);
        }

        $currentFacturaData = $allFacturasData[$index];
        $xmlData = $currentFacturaData['xmlData'];
        $tmpFilePath = $currentFacturaData['file_path'];
        $selectedProjectId = $currentFacturaData['select_project'];

        if (! isset($currentFacturaData['pdf_data']) || empty($currentFacturaData['pdf_data'])) {
            return redirect()->back()->withErrors(['message' => 'Debe subir el PDF de la factura antes de confirmar.']);
        }

        $user = Auth::user();
        $factura = $this->MapingFacturas($xmlData);

        // Extraer metadatos de la descripción del primer concepto
        $parser = new DescripcionParser;
        $allProyects = Proyecto::all()->toArray();
        $des = $factura['conceptos'][0]['descripcion'] ?? '';
        $parsedData = $parser->parsearDescripcion($des, $allProyects);

        $parsedProjectInfo = $parsedData['proyecto'] ?? null;
        $parsedProjectId = $parsedProjectInfo['id_proyecto'] ?? null;
        $departamento = implode(',', $parsedData['departamentos'] ?? []) ?: null;
        $mes = $parsedData['fecha']['mes_nombre'] ?? null;
        $anio = $parsedData['fecha']['anio'] ?? null;

        // Validar que los datos de descripción estén presentes
        $errors = [];
        if (empty($parsedProjectId) || (int) $parsedProjectId !== (int) $selectedProjectId) {
            $errors[] = 'No se detectó el proyecto en la descripción o no coincide con el seleccionado. Ejemplo: "Campus University City", "Aldea Borboleta"';
        }
        if (empty($departamento)) {
            $errors[] = 'No se detectó el departamento en la descripción. Ejemplo: "Depto A3" o "Departamento 2203"';
        }
        if (empty($mes)) {
            $errors[] = 'No se detectó el mes en la descripción. Ejemplo: "Enero 2025" o "Septiembre de 2025"';
        }
        if (empty($anio)) {
            $errors[] = 'No se detectó el año en la descripción. Ejemplo: "Enero 2025"';
        }

        if (! empty($errors)) {
            return redirect()->back()->withErrors($errors);
        }

        // Ruta organizada en disco privado: facturas/{user_id}/xml/{año}/{mes}/{archivo}
        $carbonFecha = $this->parseFechaFactura($factura['fecha']);
        $filename = basename($tmpFilePath);
        $newFilePath = $this->buildXmlStoragePath($user->id, $carbonFecha, $filename);

        $pdfData = $currentFacturaData['pdf_data'];
        $uuid = $factura['uuid'] !== 'N/A' ? $factura['uuid'] : null;
        $pdfFilename = $uuid.'.pdf';
        $pdfNewPath = $this->buildPdfStoragePath($user->id, $carbonFecha, $pdfFilename);

        $xmlFile = null;

        try {
            DB::transaction(function () use (
                $factura,
                $user,
                $selectedProjectId,
                $tmpFilePath,
                $newFilePath,
                $filename,
                $departamento,
                $mes,
                $carbonFecha,
                $pdfData,
                $pdfFilename,
                $pdfNewPath,
                $uuid,
                &$xmlFile
            ) {
                // Mover XML de almacenamiento temporal al almacenamiento privado organizado
                $contents = Storage::disk('tmp')->get($tmpFilePath);
                Storage::disk('local')->put($newFilePath, $contents);

                // Mover PDF del almacenamiento temporal al almacenamiento permanente
                $pdfContents = Storage::disk('local')->get($pdfData['temp_path']);
                Storage::disk('local')->put($pdfNewPath, $pdfContents);
                Storage::disk('local')->delete($pdfData['temp_path']);

                $xmlFile = XmlFile::create([
                    'filename' => $filename,
                    'id_user' => $user->id,
                    'id_proyecto' => $selectedProjectId ?: null,
                    'uuid' => $uuid,
                    'is_valid' => true,
                    'fecha_inicio' => $carbonFecha?->toDateString(),
                    'emisor_name' => $factura['emisor_nombre'],
                    'receptor_name' => $factura['receptor_nombre'],
                    'file_path' => $newFilePath,
                    'pdf_filename' => $pdfFilename,
                    'pdf_path' => $pdfNewPath,
                    'pdf_uploaded' => true,
                    'departamento' => $departamento,
                    'mes' => $mes,
                ]);

                $this->saveImpuestos($xmlFile->id, $factura, $user->id);

                // Eliminar el XML temporal solo si el registro en BD fue exitoso
                Storage::disk('tmp')->delete($tmpFilePath);
            });
        } catch (\Exception $e) {
            Log::error('Error al confirmar factura [index='.$index.']: '.$e->getMessage());

            return redirect()->back()->withErrors(['message' => 'Ocurrió un error al guardar la factura. Por favor intenta de nuevo.']);
        }

        // Vincular el XML a la cuenta por cobrar correspondiente
        if ($xmlFile) {
            (new CuentasPorCobrar)->actualizarConXML($xmlFile);
        }

        array_splice($allFacturasData, $index, 1);
        session()->put('factura_data', $allFacturasData);

        if (! empty($allFacturasData)) {
            $nextIndex = ($index < count($allFacturasData)) ? $index : count($allFacturasData) - 1;

            return redirect()->route('user.factura.view', ['index' => $nextIndex]);
        }

        session()->forget('factura_data');

        return view('User.FacturaSuccess');
    }

    public function deleteFactura(Request $request, $index)
    {
        $allFacturasData = session()->get('factura_data', []);

        if (empty($allFacturasData) || $index < 0 || $index >= count($allFacturasData)) {
            return redirect()->back()->withErrors(['message' => 'Factura no encontrada o índice inválido para eliminar.']);
        }

        // Limpiar el archivo temporal del disco
        $tmpFilePath = $allFacturasData[$index]['file_path'] ?? null;
        if ($tmpFilePath && Storage::disk('tmp')->exists($tmpFilePath)) {
            Storage::disk('tmp')->delete($tmpFilePath);
        }

        array_splice($allFacturasData, $index, 1);
        session()->put('factura_data', $allFacturasData);

        if (! empty($allFacturasData)) {
            $nextIndex = ($index < count($allFacturasData)) ? $index : count($allFacturasData) - 1;

            return redirect()->route('user.factura.view', ['index' => $nextIndex]);
        }

        session()->forget('factura_data');

        return view('User.FacturaSuccess');
    }

    public function resetFactura()
    {
        // Limpiar todos los XMLs temporales antes de borrar la sesión
        $allFacturasData = session()->get('factura_data', []);
        foreach ($allFacturasData as $facturaData) {
            $tmpFilePath = $facturaData['file_path'] ?? null;
            if ($tmpFilePath && Storage::disk('tmp')->exists($tmpFilePath)) {
                Storage::disk('tmp')->delete($tmpFilePath);
            }
        }

        session()->forget('factura_data');

        return redirect()->route('user.facturacion');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Construye la ruta de almacenamiento organizada por usuario, año y mes.
     *
     * Estructura resultante en el disco 'local' (storage/app/private/):
     *   facturas/{user_id}/xml/{año}/{mes}/{archivo.xml}
     *
     * Esto permite:
     *   - Respaldar todos los archivos de un usuario con: facturas/{user_id}/
     *   - Verificar archivos por periodo: facturas/{user_id}/xml/2025/09/
     */
    private function buildXmlStoragePath(int $userId, ?Carbon $fecha, string $filename): string
    {
        $year = $fecha?->format('Y') ?? now()->format('Y');
        $month = $fecha?->format('m') ?? now()->format('m');

        return "facturas/{$userId}/xml/{$year}/{$month}/{$filename}";
    }

    private function buildPdfStoragePath(int $userId, ?Carbon $fecha, string $filename): string
    {
        $year = $fecha?->format('Y') ?? now()->format('Y');
        $month = $fecha?->format('m') ?? now()->format('m');

        return "facturas/{$userId}/pdf/{$year}/{$month}/{$filename}";
    }

    /**
     * Parsea la fecha del campo Fecha del CFDI (ISO 8601 o fecha simple).
     */
    private function parseFechaFactura(string $fecha): ?Carbon
    {
        try {
            return Carbon::parse($fecha);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Crea registros de Impuesto para cada traslado de cada concepto.
     * Si el concepto tiene retención de ISR (código 001), se asocia al mismo registro.
     * Si no existe ISR en el XML, se calcula usando el régimen fiscal del usuario.
     */
    private function saveImpuestos(int $xmlFileId, array $factura, int $userId): void
    {
        $regimenFiscal = $factura['emisor_regimen'];

        $tasaRetencion = 0;
        $regimenData = DB::table('regimen_fiscals')
            ->where('id_regimen', $regimenFiscal)
            ->first();
        if ($regimenData) {
            $tasaRetencion = floatval($regimenData->tasa_retencion);
        }

        foreach ($factura['conceptos'] as $concepto) {
            $importeBase = $concepto['importe'];

            foreach ($concepto['traslados'] as $traslado) {
                $isrImporte = collect($concepto['retenciones'])
                    ->where('impuesto', '001')
                    ->sum('importe');

                if ($isrImporte == 0 && $tasaRetencion > 0) {
                    $isrImporte = $importeBase * $tasaRetencion;
                }

                Impuesto::create([
                    'xml_file_id' => $xmlFileId,
                    'tipoFactor' => $traslado['tipo_factor'],
                    'regimenFiscal' => $regimenFiscal,
                    'importeBase' => $importeBase,
                    'tasaCuota' => $traslado['tasa'],
                    'tasaRetencion' => $tasaRetencion,
                    'isr' => $isrImporte,
                ]);
            }
        }
    }
}

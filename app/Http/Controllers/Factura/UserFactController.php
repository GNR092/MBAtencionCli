<?php

namespace App\Http\Controllers\Factura;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Impuesto;
use App\Models\Proyecto;
use App\Models\UserProyecto;
use App\Models\XmlFile;
use App\Services\DescripcionParser;
use App\Services\PdfUuidExtractionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserFactController extends Controller
{
    private PdfUuidExtractionService $pdfUuidExtractionService;

    public function __construct(PdfUuidExtractionService $pdfUuidExtractionService)
    {
        $this->pdfUuidExtractionService = $pdfUuidExtractionService;
    }

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

            // Guardar en storage/app (servidor)
            $tempPdfPath = $pdfFile->store('tmp', 'local');

            // Verificar que el archivo se guardó correctamente
            if (! $tempPdfPath || ! Storage::disk('local')->exists($tempPdfPath)) {
                Log::error('Error: PDF no se guardo en el servidor. Path: '.$tempPdfPath);

                return response()->json(['success' => false, 'message' => 'Error al guardar el PDF en el servidor.']);
            }

            $absolutePdfPath = Storage::disk('local')->path($tempPdfPath);
            $pdfUuid = $this->pdfUuidExtractionService->extractUuidFromPdf($absolutePdfPath);
            $normalizedXmlUuid = $this->normalizarUuid($uuid);
            $normalizedPdfUuid = $this->normalizarUuid($pdfUuid);

            if (! $normalizedPdfUuid) {
                Storage::disk('local')->delete($tempPdfPath);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo extraer un UUID válido del PDF. Verifica que sea el acuse CFDI.',
                ]);
            }

            if ($normalizedPdfUuid !== $normalizedXmlUuid) {
                Storage::disk('local')->delete($tempPdfPath);

                return response()->json([
                    'success' => false,
                    'message' => 'El UUID del PDF no coincide con el UUID del XML cargado.',
                ]);
            }

            $allFacturasData[$index]['pdf_data'] = [
                'temp_path' => $tempPdfPath,
                'original_name' => $originalName,
                'extension' => $extension,
                'uuid' => $uuid,
                'uuid_pdf' => $normalizedPdfUuid,
            ];

            session()->put('factura_data', $allFacturasData);

            Log::info('PDF subido correctamente al servidor: '.$tempPdfPath);

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
            return redirect('/login');
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

            return redirect()->route('user.facturacion');
        }

        $currentFacturaData = $allFacturasData[$index];

        if (! is_array($currentFacturaData) || ! isset($currentFacturaData['xmlData'])) {
            return redirect()->back()->withErrors(['message' => 'Datos de factura inválidos para el índice actual.']);
        }

        $xmlData = $currentFacturaData['xmlData'];

        $factura = $this->MapingFacturas($xmlData);

        $periodosDetectados = $xmlData['periodosDetectados'] ?? [];
        $hayMesesMezclados = count($periodosDetectados) > 1;

        $conceptosPorPeriodo = [];
        foreach ($factura['conceptos'] as $concepto) {
            $periodo = $concepto['periodo'] ?? 'sin-periodo';
            if (! isset($conceptosPorPeriodo[$periodo])) {
                $conceptosPorPeriodo[$periodo] = [];
            }
            $conceptosPorPeriodo[$periodo][] = $concepto;
        }

        $gruposFactura = [];
        $currentPeriod = date('Y-m');
        foreach ($conceptosPorPeriodo as $periodo => $conceptos) {
            $esRetroactivoGrupo = $periodo !== 'sin-periodo' && $periodo !== $currentPeriod;
            $totalImporte = array_sum(array_column($conceptos, 'importe'));
            $departamentosGrupo = array_unique(array_filter(array_column($conceptos, 'departamento')));
            $gruposFactura[] = [
                'periodo' => $periodo,
                'conceptos' => $conceptos,
                'total' => $totalImporte,
                'departamentos' => $departamentosGrupo,
                'retroactivo' => $esRetroactivoGrupo,
            ];
        }

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

        $allParsedConcepts = $parser->parsearConceptos($factura['conceptos'], $AllProyects);
        $parsedData = $allParsedConcepts[0] ?? [];

        $parsedProjectInfo = $parsedData['proyecto'] ?? null;
        $parsedProjectId = $parsedProjectInfo['id_proyecto'] ?? null;
        $parsedProjectName = $parsedProjectInfo['nombre_proyecto'] ?? 'No detectado';
        $selectedProjectId = $currentFacturaData['select_project'];

        Log::info("showInvoice project debug", [
            'parsedProjectId' => $parsedProjectId,
            'parsedProjectName' => $parsedProjectName,
            'selectedProjectId' => $selectedProjectId,
            'match' => $parsedProjectId && (int) $parsedProjectId === (int) $selectedProjectId,
            'firstConceptDesc' => substr($factura['conceptos'][0]['descripcion'] ?? 'N/A', 0, 100),
        ]);

        if ($parsedProjectId && (int) $parsedProjectId !== (int) $selectedProjectId) {
            $canAutoAssign = $this->canAssignProjectToUser($user->id, (int) $parsedProjectId);
            if ($canAutoAssign) {
                $selectedProjectId = (int) $parsedProjectId;
                $currentFacturaData['select_project'] = $selectedProjectId;
                $allFacturasData[$index] = $currentFacturaData;
                session()->put('factura_data', $allFacturasData);
                $proyecto = Proyecto::find($selectedProjectId);
            }
        }

        $selectedProjectName = $proyecto->nombre_proyecto ?? 'N/A';

        $allDepartamentos = collect($allParsedConcepts)->pluck('departamentos')->flatten()->unique()->values()->all();
        $allMeses = collect($allParsedConcepts)->pluck('fecha.mes_nombre')->filter()->unique()->values()->all();
        $allAnios = collect($allParsedConcepts)->pluck('fecha.anio')->filter()->unique()->values()->all();

        $departamentoText = ! empty($allDepartamentos) ? implode(', ', $allDepartamentos) : null;
        $parsedMes = $allMeses[0] ?? null;
        $parsedAnio = $allAnios[0] ?? null;

        $folioPredial = $parsedData['folio_predial'] ?? null;

        $projectMismatch = false;
        if ($parsedProjectId === null || (int) $parsedProjectId !== (int) $selectedProjectId) {
            $projectMismatch = true;
        }

        $departamentoMissing = empty($departamentoText);
        $mesMissing = empty($parsedMes);
        $anioMissing = empty($parsedAnio);

        $multipleDepartamentos = count($allDepartamentos) > 1;
        $multipleMeses = count($allMeses) > 1;
        $multipleAnios = count($allAnios) > 1;

        $uuid = $factura['uuid'] !== 'N/A' ? $factura['uuid'] : null;
        $uuidExists = $uuid && XmlFile::where('uuid', $uuid)->exists();

        $pdfUploaded = isset($currentFacturaData['pdf_data']) && ! empty($currentFacturaData['pdf_data']);
        $pdfFilename = $pdfUploaded ? ($currentFacturaData['pdf_data']['original_name'] ?? 'documento.pdf') : null;

        $retroactivo = $currentFacturaData['xmlData']['retroactivo'] ?? false;

        return view('User.UserFactView', compact(
            'factura', 'totalFacturas', 'index',
            'projectMismatch', 'parsedProjectId', 'parsedProjectName', 'selectedProjectId', 'selectedProjectName',
            'userMismatch', 'user', 'uuidExists',
            'pdfUploaded', 'pdfFilename',
            'departamentoText', 'parsedMes', 'parsedAnio',
            'departamentoMissing', 'mesMissing', 'anioMissing',
            'folioPredial', 'retroactivo',
            'gruposFactura', 'hayMesesMezclados', 'periodosDetectados',
            'multipleDepartamentos', 'multipleMeses', 'multipleAnios'
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
                'periodo' => $concept['concepto_periodo'] ?? null,
                'departamento' => $concept['concepto_departamento'] ?? null,
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

        $parser = new DescripcionParser;
        $allProyects = Proyecto::all()->toArray();
        $allParsedConcepts = $parser->parsearConceptos($factura['conceptos'], $allProyects);
        $parsedData = $allParsedConcepts[0] ?? [];
        $parsedProjectInfo = $parsedData['proyecto'] ?? null;
        $parsedProjectId = $parsedProjectInfo['id_proyecto'] ?? null;

        if ($parsedProjectId && (int) $parsedProjectId !== (int) $selectedProjectId) {
            if ($this->canAssignProjectToUser($user->id, (int) $parsedProjectId)) {
                $selectedProjectId = (int) $parsedProjectId;
                $currentFacturaData['select_project'] = $selectedProjectId;
                $allFacturasData[$index] = $currentFacturaData;
                session()->put('factura_data', $allFacturasData);
            }
        }

        $selectedProject = Proyecto::with('razonSocial')->find($selectedProjectId);

        $userProyecto = UserProyecto::where('id_user', $user->id)
            ->where('id_proyecto', $selectedProjectId)
            ->first();

        $contract = null;
        if ($userProyecto) {
            $contractQuery = Contract::with('userDepto')
                ->where('id_user_p', $userProyecto->id_user_p)
                ->orderByDesc('id');

            $contract = (clone $contractQuery)->where('estado', 'activo')->first() ?? $contractQuery->first();
        }

        if (! $contract) {
            return redirect()->back()->withErrors(['message' => 'No se encontró un contrato activo para este proyecto.']);
        }

        $contractDepto = $this->resolveContractDepto((int) $contract->id_user_p, $contract->id_user_depto ? (int) $contract->id_user_depto : null);
        if (! $contractDepto || ! $this->normalizarDepartamento($contractDepto->nombre ?? '')) {
            return redirect()->back()->withErrors(['message' => 'El contrato no tiene departamento configurado para validar la factura.']);
        }

        $uuid = $factura['uuid'] !== 'N/A' ? trim((string) $factura['uuid']) : null;
        if (! $uuid) {
            return redirect()->back()->withErrors(['message' => 'No se encontró UUID válido en el XML.']);
        }

        if ($this->normalizarTexto($factura['emisor_nombre'] ?? '') !== $this->normalizarTexto($user->name ?? '')) {
            return redirect()->back()->withErrors(['message' => 'El emisor del XML no coincide con el usuario autenticado.']);
        }

        $rfcRazonSocial = $this->normalizarRfc($selectedProject?->razonSocial?->rfc);
        $rfcReceptorXml = $this->normalizarRfc($factura['receptor_rfc'] ?? null);

        if ($rfcRazonSocial && $rfcReceptorXml && $rfcRazonSocial !== $rfcReceptorXml) {
            return redirect()->back()->withErrors([
                'message' => 'El RFC del receptor en el XML no coincide con la razón social del proyecto seleccionado.',
            ]);
        }

        if (XmlFile::whereRaw('LOWER(uuid) = ?', [mb_strtolower($uuid)])->exists()) {
            return redirect()->back()->withErrors(['message' => 'El UUID de esta factura ya existe en el sistema.']);
        }

        $pdfData = $currentFacturaData['pdf_data'] ?? null;
        $uuidPdf = $this->normalizarUuid($pdfData['uuid_pdf'] ?? null);
        if (! $uuidPdf && isset($pdfData['temp_path']) && Storage::disk('local')->exists($pdfData['temp_path'])) {
            $uuidPdf = $this->normalizarUuid($this->pdfUuidExtractionService->extractUuidFromPdf(Storage::disk('local')->path($pdfData['temp_path'])));
        }

        if (! $uuidPdf || $uuidPdf !== $this->normalizarUuid($uuid)) {
            return redirect()->back()->withErrors(['message' => 'El PDF no coincide con el UUID del XML. Sube nuevamente el PDF correcto.']);
        }

        $periodosDetectados = $xmlData['periodosDetectados'] ?? [];

        if (empty($periodosDetectados)) {
            return redirect()->back()->withErrors(['message' => 'No se detectaron meses en los conceptos de la factura.']);
        }

        $conceptosPorPeriodo = [];
        foreach ($factura['conceptos'] as $concepto) {
            $periodo = $concepto['periodo'] ?? 'sin-periodo';
            if (! isset($conceptosPorPeriodo[$periodo])) {
                $conceptosPorPeriodo[$periodo] = [];
            }
            $conceptosPorPeriodo[$periodo][] = $concepto;
        }

        // Validar que TODOS los conceptos tengan periodo
        foreach ($conceptosPorPeriodo as $periodo => $conceptos) {
            if ($periodo === 'sin-periodo') {
                return redirect()->back()->withErrors(['message' => 'Hay conceptos sin mes/año detectable. Por favor corrija las facturas.']);
            }
        }

        // Extraer metadatos de todos los conceptos para validaciones
        $folioPredial = $parsedData['folio_predial'] ?? ($xmlData['cuenta_predial'] ?? null);

        $parsedProjectInfo = $parsedData['proyecto'] ?? null;
        $parsedProjectId = $parsedProjectInfo['id_proyecto'] ?? null;
        $allDepartamentos = collect($allParsedConcepts)->pluck('departamentos')->flatten()->unique()->values()->all();
        $departamento = ! empty($allDepartamentos) ? implode(',', $allDepartamentos) : null;
        $errors = [];

        $departamentosNormalizados = collect($allDepartamentos)
            ->map(fn ($depto) => $this->normalizarDepartamento((string) $depto))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($departamentosNormalizados)) {
            $errors[] = 'No se detectó un departamento válido en la descripción de la factura.';
        } else {
            $userDeptos = DB::table('user_depto')
                ->where('id_user_p', (int) $userProyecto->id_user_p)
                ->get(['id_user_depto', 'nombre', 'predial']);
            $userDeptosNormalizados = $userDeptos
                ->mapWithKeys(fn ($ud) => [$this->normalizarDepartamento((string) $ud->nombre) => $ud])
                ->filter()
                ->all();

            $invalidDeptos = collect($departamentosNormalizados)
                ->filter(fn ($depto) => ! isset($userDeptosNormalizados[$depto]))
                ->values()
                ->all();

            if (! empty($invalidDeptos)) {
                $errors[] = 'Los departamentos '.implode(', ', $invalidDeptos).' de la factura no están asignados al usuario en este proyecto.';
            }
        }

        $predialXml = $this->normalizarPredial($folioPredial);
        $predialContrato = $this->normalizarPredial($contractDepto->predial ?? null);
        $predialStatus = 'no_validado';
        $predialObservacion = null;

        if (! $predialXml) {
            $predialStatus = 'sin_predial';
            $predialObservacion = 'Factura sin predial reportado.';
        } elseif (! $predialContrato) {
            $predialStatus = 'no_validado';
            $predialObservacion = 'Contrato sin predial de referencia para validar.';
        } elseif ($predialXml === $predialContrato) {
            $predialStatus = 'valido';
        } else {
            $predialStatus = 'no_coincide';
            $predialObservacion = 'Predial XML no coincide con el del contrato.';
        }

        // Validar proyecto
        if (empty($parsedProjectId) || (int) $parsedProjectId !== (int) $selectedProjectId) {
            $errors[] = 'No se detectó el proyecto en la descripción o no coincide con el seleccionado.';
        }

        if (! empty($errors)) {
            return redirect()->back()->withErrors($errors);
        }

        // Ruta organizada en disco privado
        $carbonFecha = $this->parseFechaFactura($factura['fecha']);
        $filename = basename($tmpFilePath);
        $newFilePath = $this->buildXmlStoragePath($user->id, $carbonFecha, $filename);

        $pdfFilename = $uuid.'.pdf';
        $pdfNewPath = $this->buildPdfStoragePath($user->id, $carbonFecha, $pdfFilename);
        $retroactivo = $currentFacturaData['xmlData']['retroactivo'] ?? false;

        if (! isset($pdfData['temp_path']) || ! Storage::disk('local')->exists($pdfData['temp_path'])) {
            Log::error('Error: PDF temporal no encontrado. temp_path: '.($pdfData['temp_path'] ?? 'N/A'));

            return redirect()->back()->withErrors(['message' => 'El PDF no se subió correctamente. Por favor, sube el PDF nuevamente antes de confirmar la factura.']);
        }

        $xmlFile = null;

        try {
            DB::transaction(function () use (
                $factura,
                $user,
                $userProyecto,
                $selectedProjectId,
                $tmpFilePath,
                $newFilePath,
                $filename,
                $departamento,
                $carbonFecha,
                $pdfData,
                $retroactivo,
                $pdfFilename,
                $pdfNewPath,
                $uuid,
                $conceptosPorPeriodo,
                $contract,
                $contractDepto,
                $predialXml,
                $predialStatus,
                $predialObservacion,
                &$xmlFile
            ) {
                if (XmlFile::whereRaw('LOWER(uuid) = ?', [mb_strtolower($uuid)])->exists()) {
                    throw new \Exception('El UUID de esta factura ya existe en el sistema.');
                }

                $contents = Storage::disk('tmp')->get($tmpFilePath);
                Storage::disk('local')->put($newFilePath, $contents);

                if (! isset($pdfData['temp_path']) || ! Storage::disk('local')->exists($pdfData['temp_path'])) {
                    throw new \Exception('El PDF no se encontró en el servidor. Por favor, sube el PDF nuevamente.');
                }

                Log::info('Moviendo PDF temporal a permanente: '.$pdfData['temp_path'].' -> '.$pdfNewPath);

                $pdfContents = Storage::disk('local')->get($pdfData['temp_path']);
                $saved = Storage::disk('local')->put($pdfNewPath, $pdfContents);

                if (! $saved) {
                    throw new \Exception('Error al guardar el PDF en el servidor.');
                }

                if (! Storage::disk('local')->exists($pdfNewPath)) {
                    throw new \Exception('El PDF no se guardo correctamente. Verifica los permisos de escritura.');
                }

                Log::info('PDF movido correctamente a: '.$pdfNewPath);

                Storage::disk('local')->delete($pdfData['temp_path']);

                $xmlFile = XmlFile::create([
                    'filename' => $filename,
                    'id_user' => $user->id,
                    'id_proyecto' => $selectedProjectId ?: null,
                    'id_contract' => $contract->id,
                    'id_user_depto' => $contractDepto->id_user_depto,
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
                    'predial_xml' => $predialXml,
                    'predial_status' => $predialStatus,
                    'predial_observacion' => $predialObservacion,
                    'mes' => $conceptosPorPeriodo[array_key_first($conceptosPorPeriodo)][0]['periodo'] ?? null,
                    'retroactivo' => $retroactivo,
                    'validation_flags' => $predialStatus === 'no_coincide' ? ['predial_warning' => true] : null,
                ]);

                $this->saveImpuestos($xmlFile->id, $factura, $user->id);

                $currentPeriod = date('Y-m');

                $periodosConceptos = collect($factura['conceptos'])
                    ->pluck('periodo')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (! empty($periodosConceptos)) {
                    DB::table('cuentasporpagar')
                        ->whereIn('id_contract', function ($q) use ($userProyecto) {
                            $q->select('id')
                                ->from('contract')
                                ->where('id_user_p', (int) $userProyecto->id_user_p)
                                ->where('estado', 'activo');
                        })
                        ->whereIn('mes_pago', $periodosConceptos)
                        ->whereNull('uuid')
                        ->delete();
                }

                $userDeptosByDepto = DB::table('user_depto')
                    ->where('id_user_p', (int) $userProyecto->id_user_p)
                    ->get(['id_user_depto', 'nombre'])
                    ->mapWithKeys(fn ($ud) => [$this->normalizarDepartamento((string) $ud->nombre) => $ud])
                    ->all();

                $contractsByDepto = DB::table('contract')
                    ->where('id_user_p', (int) $userProyecto->id_user_p)
                    ->where('estado', 'activo')
                    ->whereNotNull('id_user_depto')
                    ->get(['id', 'id_user_depto', 'importe_bruto_renta'])
                    ->mapWithKeys(fn ($c) => [(int) $c->id_user_depto => $c])
                    ->all();

                $deptoPrincipalId = (int) $contractDepto->id_user_depto;

                foreach ($factura['conceptos'] as $conceptIndex => $concepto) {
                    $periodo = $concepto['periodo'] ?? null;
                    if (empty($periodo)) {
                        continue;
                    }

                    $deptoNombre = $concepto['departamento'] ?? null;
                    $deptoNormalizado = $this->normalizarDepartamento((string) $deptoNombre);

                    $deptoId = $deptoPrincipalId;
                    $contractId = $contract->id;

                    if ($deptoNormalizado && isset($userDeptosByDepto[$deptoNormalizado])) {
                        $resolvedDeptoId = (int) $userDeptosByDepto[$deptoNormalizado]->id_user_depto;
                        if (isset($contractsByDepto[$resolvedDeptoId])) {
                            $deptoId = $resolvedDeptoId;
                            $contractId = (int) $contractsByDepto[$resolvedDeptoId]->id;
                        }
                    }

                    $esRetroactivo = $periodo !== $currentPeriod;

                    $importeConcepto = (float) ($concepto['importe'] ?? 0);
                    if ($importeConcepto <= 0) {
                        $importeIncremento = DB::table('incrementos_importe')
                            ->where('id_contract', $contractId)
                            ->whereDate('fecha_inicio', '<=', $periodo.'-01')
                            ->where(function ($q) use ($periodo) {
                                $q->whereNull('fecha_fin')
                                    ->orWhereDate('fecha_fin', '>=', $periodo.'-01');
                            })
                            ->value('importe_base');

                        $importeBruto = (float) ($contractsByDepto[$deptoId]->importe_bruto_renta ?? $contract->importe_bruto_renta);
                        $importeConcepto = (float) ($importeIncremento ?? $importeBruto);
                    }

                    $totalImpuestos = 0;
                    foreach ($concepto['traslados'] ?? [] as $traslado) {
                        $totalImpuestos += (float) ($traslado['importe'] ?? 0);
                    }
                    foreach ($concepto['retenciones'] ?? [] as $retencion) {
                        $totalImpuestos -= (float) ($retencion['importe'] ?? 0);
                    }

                    $totalNeto = max(0, round($importeConcepto - abs($totalImpuestos), 2));

                    DB::table('cuentasporpagar')->insert([
                        'id_contract' => $contractId,
                        'id_user_depto' => $deptoId,
                        'origen' => 'xml',
                        'uuid' => $uuid,
                        'mes_pago' => $periodo,
                        'es_retroactivo' => $esRetroactivo,
                        'xml_file_id' => $xmlFile->id,
                        'estado' => 'pendiente',
                        'saldo_neto' => $totalNeto,
                        'monto_pagado' => 0,
                        'saldo_pendiente' => $totalNeto,
                        'meses_cubiertos' => 1,
                        'es_extra' => false,
                        'mesesdepago' => json_encode(['mes' => $periodo, 'concepto_idx' => $conceptIndex]),
                        'mesespagados' => json_encode([]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                Storage::disk('tmp')->delete($tmpFilePath);
            });
        } catch (\Exception $e) {
            Log::error('Error al confirmar factura [index='.$index.']: '.$e->getMessage());

            return redirect()->back()->withErrors(['message' => 'Ocurrió un error al guardar la factura. Por favor intenta de nuevo.']);
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

    private function normalizarTexto(string $texto): string
    {
        $texto = trim($texto);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        return mb_strtolower($ascii !== false ? $ascii : $texto);
    }

    private function normalizarRfc(?string $rfc): ?string
    {
        if ($rfc === null) {
            return null;
        }

        $value = strtoupper(trim($rfc));

        return $value !== '' ? $value : null;
    }

    private function normalizarUuid(?string $uuid): ?string
    {
        if ($uuid === null) {
            return null;
        }

        $value = strtolower(trim($uuid));

        return $value !== '' ? $value : null;
    }

    private function normalizarDepartamento(string $depto): ?string
    {
        $value = strtoupper(trim($depto));
        $value = preg_replace('/[^A-Z0-9]+/', '', $value);

        return $value !== '' ? $value : null;
    }

    private function normalizarPredial(?string $predial): ?string
    {
        if ($predial === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $predial);

        return $digits !== '' ? $digits : null;
    }

    private function canAssignProjectToUser(int $userId, int $projectId): bool
    {
        $userProyecto = UserProyecto::where('id_user', $userId)
            ->where('id_proyecto', $projectId)
            ->first();

        if (! $userProyecto) {
            return false;
        }

        return Contract::where('id_user_p', $userProyecto->id_user_p)
            ->where(function ($query) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('user_depto as ud_directo')
                        ->whereColumn('ud_directo.id_user_depto', 'contract.id_user_depto');
                })->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('user_depto as ud_proyecto')
                        ->whereColumn('ud_proyecto.id_user_p', 'contract.id_user_p');
                });
            })
            ->exists();
    }

    private function resolveContractDepto(int $idUserP, ?int $idUserDepto): ?object
    {
        if ($idUserDepto) {
            $directo = DB::table('user_depto')
                ->where('id_user_p', $idUserP)
                ->where('id_user_depto', $idUserDepto)
                ->first(['id_user_depto', 'nombre', 'predial']);

            if ($directo) {
                return $directo;
            }
        }

        return DB::table('user_depto')
            ->where('id_user_p', $idUserP)
            ->orderBy('id_user_depto')
            ->first(['id_user_depto', 'nombre', 'predial']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\FileLog;
use App\Models\UserProyecto;
use App\Models\XmlBatch;
use App\Models\XmlFile;
use App\Services\PdfUuidExtractionService;
use App\Services\XmlValidationService;
// use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yasumi\Yasumi; // Import Auth facade

class CfdiValidatorController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private $xmlValidationService;

    private $pdfUuidExtractionService;

    // valida los servicios
    public function __construct(
        XmlValidationService $xmlValidationService,
        PdfUuidExtractionService $pdfUuidExtractionService
    ) {
        $this->xmlValidationService = $xmlValidationService;
        $this->pdfUuidExtractionService = $pdfUuidExtractionService;
    }

    private function getNextQuincenaDeadline(): Carbon
    {
        $today = now();

        if ($today->day <= 15) {
            $deadline = Carbon::create($today->year, $today->month, 15, 23, 59, 59);
        } else {
            $lastDayOfMonth = $today->endOfMonth()->day;
            $deadlineDay = $lastDayOfMonth >= 30 ? 30 : $lastDayOfMonth;
            $deadline = Carbon::create($today->year, $today->month, $deadlineDay, 23, 59, 59);
        }

        if ($deadline->isSaturday()) {
            $deadline->addDays(2);
        } elseif ($deadline->isSunday()) {
            $deadline->addDay();
        }

        $holidays = Yasumi::create('Mexico', $today->year, 'es_ES');
        while ($holidays->isHoliday($deadline)) {
            $deadline->addDay();
        }

        return $deadline;
    }

    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $batch = XmlBatch::where('session_id', $sessionId)->first();

        $deadline = $this->getNextQuincenaDeadline();
        $isDeadlinePassed = $deadline->isPast();

        $success = '';

        $user = Auth::user();
        $userP = UserProyecto::where('id_user', $user->id)->get();
        $idsProyectos = $userP->pluck('id_proyecto');
        $proyectos = $user->proyectos;

        if ($request->expectsJson()) {
            $html = view('User.factura', compact('batch', 'isDeadlinePassed', 'success', 'user'))->render();

            return response()->json(['html' => $html]);
        }

        return view('User.factura', compact('batch', 'isDeadlinePassed', 'success', 'user', 'proyectos'));
    }

    // Sube y valida archivos XML, Los inválidos no se guardan,Los válidos se guardan en disco y BD.
    public function uploadXmlFiles(Request $request)
    {
        try {
            $request->validate([
                // 'xml_files' => 'required|array|max:' . $this->maxBatchSize,
                'xml_files.*' => 'required|file|mimes:xml|max:10240',
                'user_email' => 'required|email',
                'proyect' => 'required|string',
            ]);

            $now = Carbon::now();
            $currentM = $now->format('m-Y');

            $sessionId = $request->session()->getId();
            $deadline = $this->getNextQuincenaDeadline();

            $proyecto = $request->input('proyect');

            $errors = [];
            $uuidMapping = [];

            $existingSessionData = session()->get('factura_data', []);
            foreach ($existingSessionData as $item) {
                $existingUuid = strtolower(trim((string) ($item['xmlData']['uuid'] ?? '')));
                if ($existingUuid !== '') {
                    $uuidMapping[$existingUuid] = true;
                }
            }

            $user = Auth::user();

            $xmlUserData = [];

            foreach ($request->file('xml_files') as $file) {
                $filename = $file->getClientOriginalName();
                $tempPath = $file->getPathname();

                $validationResult = $this->xmlValidationService->validateXml($tempPath, $filename);

                if (! $validationResult['valid']) {
                    $flatErrors = collect($validationResult['errors'])->flatten();
                    foreach ($flatErrors as $errorMsg) {
                        $errors[] = "Archivo {$filename}: {$errorMsg}";
                    }

                    continue;
                }

                $uuid = strtolower(trim((string) ($validationResult['uuid'] ?? '')));

                if ($uuid !== '' && isset($uuidMapping[$uuid])) {
                    $errors[] = "Archivo {$filename}: UUID duplicado {$validationResult['uuid']}";

                    continue;
                }

                if ($uuid !== '' && XmlFile::whereRaw('LOWER(uuid) = ?', [$uuid])->exists()) {
                    $errors[] = "Archivo {$filename}: el UUID {$validationResult['uuid']} ya fue cargado previamente.";

                    continue;
                }

                if ($uuid !== '') {
                    $uuidMapping[$uuid] = true;
                }

                $filePath = $file->store('xml_files', 'tmp');
                $xmlUserData[] = [
                    'select_project' => $proyecto,
                    'xmlData' => $validationResult,
                    'file_path' => $filePath,
                ];
            }
            if (empty($xmlUserData)) {
                return redirect()->back()->withErrors($errors !== [] ? $errors : ['No se detectaron XML válidos para procesar.']);
            }

            session()->put('factura_data', $xmlUserData);

            if ($errors !== []) {
                session()->flash('warnings', $errors);
            }

            return redirect()->route('user.factura.view', ['index' => 0]);
        } catch (\Exception $e) {
            Log::error('Error uploading XML files: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Reinicia el lote actual
    public function resetBatch(Request $request)
    {
        $sessionId = $request->session()->getId();

        $batch = XmlBatch::where('session_id', $sessionId)->first();

        if ($batch) {

            $batch->update(['session_id' => 'archived_'.$batch->id]);
        }

        return redirect()->back()->with('success', 'Lote reiniciado, puedes comenzar otro sin borrar el histórico.');
    }

    public function xmlFiles()
    {
        return $this->hasMany(XmlFile::class, 'batch_id');
    }

    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:20480',
        ]);

        try {
            $sessionId = $request->session()->getId();

            $batch = XmlBatch::where('session_id', $sessionId)->first();

            if (! $batch || $batch->valid_files === 0) {
                return redirect()->back()->withErrors([
                    'pdf' => 'No existen XML válidos para asociar el PDF',
                ]);
            }

            $pdfPath = $request->file('pdf_file')->store('pdf_files', 'public');

            $pdfUuid = $this->pdfUuidExtractionService
                ->extractUuidFromPdf(storage_path('app/public/'.$pdfPath));

            if (! $pdfUuid) {
                return redirect()->back()->withErrors([
                    'pdf' => 'No se pudo extraer un UUID válido del PDF',
                ]);
            }

            if (! isset($batch->uuid_mapping[$pdfUuid])) {
                return redirect()->back()->withErrors([
                    'pdf' => 'El UUID del PDF no coincide con ningún XML cargado',
                ]);
            }

            $xmlFile = XmlFile::where('batch_id', $batch->id)
                ->where('uuid', $pdfUuid)
                ->first();

            if (! $xmlFile) {
                return redirect()->back()->withErrors([
                    'pdf' => 'No se encontró el XML correspondiente al UUID',
                ]);
            }

            $xmlFile->update([
                'pdf_path' => $pdfPath,
                'pdf_uploaded' => true,
            ]);

            $batch->increment('uploaded_pdfs');

            FileLog::create([
                'filename' => basename($pdfPath),
                'file_type' => 'pdf',
                'uuid' => $pdfUuid,
                'is_valid' => true,
                'metadata' => [],
            ]);

            return redirect()->back()->with('success', 'PDF asociado correctamente al XML');
        } catch (\Exception $e) {
            Log::error('Error uploading PDF file: '.$e->getMessage());

            return redirect()->back()->withErrors([
                'pdf' => 'Ocurrió un error inesperado al procesar el archivo PDF.',
            ]);
        }
    }
}

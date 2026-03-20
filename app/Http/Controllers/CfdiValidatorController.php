<?php

namespace App\Http\Controllers;

use App\Models\FileLog;
use App\Models\Proyecto;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Yasumi\Yasumi; // Import Auth facade

class CfdiValidatorController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private $xmlValidationService;

    private $pdfUuidExtractionService;

    private $maxBatchSize = 2;

    // validar que el usuario tenga el proyecto asignado
    private function getProyectos()
    {
        $user = Auth::user();

        $usuario = DB::table('users')->where('id', $user->id)->first();

        if (! $usuario || empty($usuario->proyect)) {
            return null;
        }

        $proyectos = json_decode($usuario->proyect, true);

        if (! is_array($proyectos)) {

            $proyectos = is_object($proyectos) ? array_values((array) $proyectos) : [$proyectos];
        }

        return $proyectos;
    }

    // funcion para validar el email
    private function getMail()
    {
        $user = Auth::user();

        $usuario = DB::table('users')->where('id', $user->id)->first();

        if (! $usuario || empty($usuario->email)) {
            return null;
        } else {
            return $usuario->email;
        }
    }

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
            $uuidMapping = $batch->uuid_mapping ?? [];

            $user = Auth::user();

            $xmlUserData = [];

            foreach ($request->file('xml_files') as $file) {
                $filename = $file->getClientOriginalName();
                $tempPath = $file->getPathname();

                $validationResult = $this->xmlValidationService->validateXml($tempPath, $filename);
                if($validationResult['mes'] != $currentM){

                }

                // Storage::put('validation_results.txt', json_encode($validationResult, JSON_PRETTY_PRINT));

                if (! $validationResult['valid']) {
                    $flatErrors = collect($validationResult['errors'])->flatten();
                    foreach ($flatErrors as $errorMsg) {
                        $errors[] = "Archivo {$filename}: {$errorMsg}";
                    }

                    continue;
                }

                if (($uuid = $validationResult['uuid'] ?? null) && isset($uuidMapping[$uuid])) {
                    $errors[] = "Archivo {$filename}: UUID duplicado {$validationResult['uuid']}";

                    continue;
                }

                $filePath = $file->store('xml_files', 'tmp');
                $xmlUserData[] = [
                    'select_project' => $proyecto,
                    'xmlData' => $validationResult,
                    'file_path' => $filePath,
                ];
            }
            session()->put('factura_data', $xmlUserData);

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
            \Log::error('Error uploading PDF file: '.$e->getMessage());

            return redirect()->back()->withErrors([
                'pdf' => 'Ocurrió un error inesperado al procesar el archivo PDF.',
            ]);
        }
    }
}

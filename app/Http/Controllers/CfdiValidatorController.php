<?php

namespace App\Http\Controllers;

use Yasumi\Yasumi;
use App\Models\XmlBatch;
use App\Models\XmlFile;
use App\Models\Impuesto;
use App\Models\FileLog;
use App\Services\XmlValidationService;
use App\Services\PdfUuidExtractionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class CfdiValidatorController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private $xmlValidationService;
    private $pdfUuidExtractionService;
    private $maxBatchSize = 2;
    //validar que el usuario tenga el proyecto asignado
    private function getProyectos()
    {
        $user = Session::get('user');

        // Buscar al usuario
        $usuario = DB::table('users')->where('id', $user->id)->first();

        if (!$usuario || empty($usuario->proyect)) {
            return null; // No tiene proyectos
        }

        // Decodificar JSON como array
        $proyectos = json_decode($usuario->proyect, true);

        // Si $proyectos no es un array (p. ej. es un objeto, string o null), conviértelo a un array.
        if (!is_array($proyectos)) {
            // Si es un objeto, toma sus valores. Si no, envuélvelo en un array.
            $proyectos = is_object($proyectos) ? array_values((array)$proyectos) : [$proyectos];
        }

        return $proyectos;
    }

    //funcion para validar el email
    private function getMail()
    {
        $user = Session::get('user');

        // Buscar al usuario
        $usuario = DB::table('users')->where('id', $user->id)->first();

        if (!$usuario || empty($usuario->email)) {
            return null; // No tiene email
        } else {
            return $usuario->email;
        }
    }

    //valida los servicios
    public function __construct(
        XmlValidationService $xmlValidationService,
        PdfUuidExtractionService $pdfUuidExtractionService
    ) {
        $this->xmlValidationService = $xmlValidationService;
        $this->pdfUuidExtractionService = $pdfUuidExtractionService;
    }


    // Calcula la fecha límite de quincena (15 o 30) ajustando fines de semana y feriados.
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

        // Ajustar fines de semana
        if ($deadline->isSaturday()) {
            $deadline->addDays(2);
        } elseif ($deadline->isSunday()) {
            $deadline->addDay();
        }

        // Ajustar si coincide con feriado (México)
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

        if ($request->expectsJson()) {
            $html = view('factura', compact('batch', 'isDeadlinePassed', 'success'))->render();
            return response()->json(['html' => $html]);
        }

        return view('factura', compact('batch', 'isDeadlinePassed', 'success'));
    }

    //Sube y valida archivos XML, Los inválidos no se guardan,Los válidos se guardan en disco y BD.
    public function uploadXmlFiles(Request $request)
    {




        $request->validate([
            'xml_files' => 'required|array|max:' . $this->maxBatchSize,
            'xml_files.*' => 'required|file|mimes:xml|max:10240',
            'user_email' => 'required|email',
            'proyect' => 'required|string'
        ]);

        $sessionId = $request->session()->getId();
        $deadline = $this->getNextQuincenaDeadline();

        //validar email
        $compareMail = $this->getMail();
        if ($compareMail !== $request->input('user_email')) {
            return response()->json(['errors' => ['user_email' => ['El correo electrónico no coincide con el registrado.']]], 422);
        }

        // Validar proyecto
        $compareProyect = $this->getProyectos();


        if ($compareProyect === null) {
            return response()->json(['errors' => ['proyect' => ['El proyecto no existe']]], 422);
        }

        $proyecto = $request->input('proyect'); // siempre será un solo valor

        if (!in_array($proyecto, $compareProyect)) {
            return response()->json(['errors' => ['proyect' => ['El proyecto no es válido']]], 422);
        }

        // Verificar si la fecha límite ha pasado
        if ($deadline->isPast()) {
            return response()->json(['errors' => ['deadline' => ['La fecha límite ha vencido.']]], 422);
        }

        // Crear o recuperar el batch
        $batch = XmlBatch::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'total_files' => 0,
                'valid_files' => 0,
                'uploaded_pdfs' => 0,
                'uuid_mapping' => [],
                'user_email' => $request->user_email,
                'deadline' => $deadline
            ]
        );

        $errors = [];
        $uuidMapping = $batch->uuid_mapping ?? [];

        $user = Session::get('user');



        foreach ($request->file('xml_files') as $file) {
            $filename = $file->getClientOriginalName();
            $tempPath = $file->getPathname(); // archivo temporal


            // ✅ Validar primero
            $validationResult = $this->xmlValidationService->validateXml($tempPath, $filename);

            // Si no es válido → no guardar en disco ni en BD
            if (!$validationResult['valid']) {
                $flatErrors = collect($validationResult['errors'])->flatten();
                foreach ($flatErrors as $errorMsg) {
                    $errors[] = "Archivo {$filename}: {$errorMsg}";
                }
                continue;
            }


            // Revisar UUID duplicado
            if (($uuid = $validationResult['uuid'] ?? null) && isset($uuidMapping[$uuid])) {
                $errors[] = "Archivo {$filename}: UUID duplicado {$validationResult['uuid']}";
                continue;
            }

            // ✅ Guardar en disco ahora sí
            $filePath = $file->store('xml_files', 'public');

            // ✅ Guardar en base de datos

            $xmlFile = XmlFile::create([
                'batch_id' => $batch->id,
                'filename' => $filename,
                'uuid' => $validationResult['uuid'] ?? null,
                'is_valid' => true,
                'validation_errors' => json_encode($validationResult['errors']),
                'emisor_name' => $validationResult['emisor_name'],
                'receptor_name' => $validationResult['receptor_name'],
                //cambiar el valiudation resul por la comparativa de proyectos que se hizo arriba
                'proyectos' => $proyecto,
                'file_path' => $filePath,
                'departamento' => $validationResult['departamento'],
                //verificar si el id del usuario existe en la dba de factura
                'id_user' => $user->id,
                'mes' => $validationResult['periodo_pago'],
            ]);

            // Guardar impuestos si existen en el XML validado
            Impuesto::create([
                //tipo factor y regimen fiscal no aparecen en impuestos
                //checar que el regimrn fiscal u el tipo de factor aparescan
                'tipoFactor' => $validationResult['tipoFactor'] ?? null,
                'regimenFiscal' => $validationResult['regimenFiscal'] ?? null,
                'importeBase' => $validationResult['valorUnitario'] ?? 0,
                'tasaCuota' => $validationResult['tasaCuota'] ?? 0,
                'isr' => $validationResult['isr'] ?? 0,
                'xml_file_id' => $xmlFile->id, // Llave foránea
            ]);

            // Actualizar UUID mapping
            if ($validationResult['uuid']) {
                $uuidMapping[$validationResult['uuid']] = $filename;
                $batch->increment('valid_files');
            }

            // Log del archivo válido
            FileLog::create([
                'filename' => $filename,
                'file_type' => 'xml',
                'uuid' => $validationResult['uuid'],
                'is_valid' => true,
                'emisor_name' => $validationResult['emisor_name'],
                'receptor_name' => $validationResult['receptor_name'],
                'metadata' => ['validation_errors' => []]
            ]);
        }

        // Actualizar lote
        $batch->update([
            'total_files' => $batch->xmlFiles()->count(),
            'uuid_mapping' => $uuidMapping
        ]);

        if (!empty($errors)) {
            return response()->json(['errors' => ['xml_files' => $errors]], 422);
        }

        return response()->json(['success' => 'XMLs procesados correctamente'], 200);
    }

    //Reinicia el lote actual
    public function resetBatch(Request $request)
    {
        $sessionId = $request->session()->getId();

        $batch = XmlBatch::where('session_id', $sessionId)->first();

        if ($batch) {
            // 👇 En vez de borrar los xmlFiles, solo marcamos el batch como "cerrado"
            $batch->update(['session_id' => 'archived_' . $batch->id]);
        }

        return redirect()->back()->with('success', 'Lote reiniciado, puedes comenzar otro sin borrar el histórico.');
    }

    // App\Models\XmlBatch.php
    public function xmlFiles()
    {
        return $this->hasMany(XmlFile::class, 'batch_id');
    }

    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:20480',
        ]);

        $sessionId = $request->session()->getId();

        // Obtener lote activo
        $batch = XmlBatch::where('session_id', $sessionId)->first();

        if (!$batch || $batch->valid_files === 0) {
            return redirect()->back()->withErrors([
                'pdf' => 'No existen XML válidos para asociar el PDF'
            ]);
        }

        // Guardar PDF
        $pdfPath = $request->file('pdf_file')->store('pdf_files', 'public');

        // 🔐 Extraer UUID REAL del PDF (texto visible)
        $pdfUuid = $this->pdfUuidExtractionService
            ->extractUuidFromPdf(storage_path('app/public/' . $pdfPath));

        if (!$pdfUuid) {
            return redirect()->back()->withErrors([
                'pdf' => 'No se pudo extraer un UUID válido del PDF'
            ]);
        }

        // Validar UUID contra XML del lote
        if (!isset($batch->uuid_mapping[$pdfUuid])) {
            return redirect()->back()->withErrors([
                'pdf' => 'El UUID del PDF no coincide con ningún XML cargado'
            ]);
        }

        // Obtener XML correspondiente
        $xmlFile = XmlFile::where('batch_id', $batch->id)
            ->where('uuid', $pdfUuid)
            ->first();

        if (!$xmlFile) {
            return redirect()->back()->withErrors([
                'pdf' => 'No se encontró el XML correspondiente al UUID'
            ]);
        }

        // Asociar PDF al XML
        $xmlFile->update([
            'pdf_path' => $pdfPath,
        ]);

        // Actualizar contador
        $batch->increment('uploaded_pdfs');

        // Log de auditoría
        FileLog::create([
            'filename' => basename($pdfPath),
            'file_type' => 'pdf',
            'uuid' => $pdfUuid,
            'is_valid' => true,
            'metadata' => [],
        ]);

        return redirect()->back()->with('success', 'PDF asociado correctamente al XML');
    }
}
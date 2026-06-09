<?php

namespace App\Http\Controllers;

use App\Models\XmlFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadFactura extends Controller
{
    /**
     * Mostrar listado de facturas de todos los usuarios con filtros opcionales
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $query = XmlFile::with(['user:id,name', 'user.proyectos:id_proyecto,nombre_proyecto']);

        if ($request->filled('month')) {
            $year = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);

            $query->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->input('fecha'));
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->input('batch_id'));
        }

        if ($request->filled('emisor_name')) {
            $query->where('emisor_name', 'LIKE', '%'.$request->input('emisor_name').'%');
        }

        $search = session('search');
        $categoria = session('categoria');

        if ($search && $categoria) {
            switch ($categoria) {
                case 'id':
                    if (ctype_digit((string) $search)) {
                        $query->where('batch_id', $search);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                    break;
                case 'inversionista':
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE LOWER(?)', ['%'.$search.'%']);
                    });
                    break;
                case 'fecha':
                    if ($search != Carbon::parse($search)->format('Y-m-d')) {
                        session()->forget(['search', 'categoria']);

                        return redirect()->back()->withErrors(['search' => 'La fecha no es válida.']);
                    }
                    $query->whereDate('created_at', $search);
                    break;
            }
        }

        $xmlFiles = $query->paginate(6);

        return view('facturas', [
            'xmlFiles' => $xmlFiles,
        ], compact('search', 'categoria'));
    }

    public function limpiar()
    {

        session()->forget(['search', 'categoria']);

        return redirect()->route('facturas.index');
    }

    public function buscar(Request $request)
    {

        session([
            'search' => $request->input('search'),
            'categoria' => $request->input('categoria'),
        ]);

        return redirect()->route('facturas.index');
    }

    /**
     * Descargar un XML específico
     */
    public function descargar($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $xmlFile = XmlFile::find($id);
        if (! $xmlFile) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        $filePath = $xmlFile->file_path;

        if (Storage::exists($filePath)) {
            $fullPath = Storage::path($filePath);
        } elseif (Storage::disk('tmp')->exists($filePath)) {
            $fullPath = Storage::disk('tmp')->path($filePath);
        } elseif (Storage::disk('tmp')->exists('xml_files/'.basename($filePath))) {
            $fullPath = Storage::disk('tmp')->path('xml_files/'.basename($filePath));
        } else {
            return back()->with('error', 'El archivo físico no existe.');
        }

        return response()->streamDownload(function () use ($fullPath) {
            $stream = fopen($fullPath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, basename($filePath), ['Content-Type' => 'application/xml']);
    }

    /**
     * Descargar el PDF asociado a un XML
     */
    public function descargarPdf($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $xmlFile = XmlFile::find($id);

        if (! $xmlFile) {
            return back()->with('error', 'Registro no encontrado. ID: '.$id);
        }

        if (! $xmlFile->pdf_path && ! $xmlFile->pdf_uploaded) {
            return back()->with('error', 'Este XML no tiene un PDF asociado.');
        }

        $pdfPath = $xmlFile->pdf_path;

        // PDFs guardados desde CfdiValidatorController (public disk)
        if (str_starts_with($pdfPath, 'pdf_files/')) {
            $fullPath = storage_path('app/public/'.$pdfPath);
        }
        // PDFs guardados desde UserFactController (local disk en Laravel 11+ usa private/)
        elseif (str_starts_with($pdfPath, 'facturas/')) {
            $fullPath = storage_path('app/private/'.$pdfPath);
        }
        // Fallback: solo nombre de archivo
        else {
            $fullPath = storage_path('app/public/pdf_files/'.$pdfPath);
        }

        // Si no existe, buscar en carpeta Facturas del proyecto
        if (! file_exists($fullPath)) {
            $filename = basename($pdfPath);
            $projectFacturasPath = base_path('Facturas');

            // Buscar recursivamente en Facturas/
            $foundFiles = glob($projectFacturasPath.'/**/'.$filename, GLOB_BRACE);
            if (! empty($foundFiles)) {
                $fullPath = $foundFiles[0];
            }
        }

        if (! file_exists($fullPath)) {
            \Log::error('PDF no encontrado: '.$fullPath.' | pdf_path en BD: '.$pdfPath);

            return back()->with('error', 'El archivo PDF físico no existe. Ruta: '.$pdfPath);
        }

        return response()->download($fullPath, basename($fullPath));
    }

    /**
     * Visualizar el PDF en el navegador (en lugar de forzar descarga)
     */
    public function verPdf($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $xmlFile = XmlFile::find($id);

        if (! $xmlFile) {
            return back()->with('error', 'Registro no encontrado. ID: '.$id);
        }

        if (! $xmlFile->pdf_path && ! $xmlFile->pdf_uploaded) {
            return back()->with('error', 'Este XML no tiene un PDF asociado.');
        }

        $fullPath = $this->resolvePdfPath($xmlFile->pdf_path);

        if (! $fullPath) {
            \Log::error('PDF no encontrado: pdf_path en BD: '.$xmlFile->pdf_path);

            return back()->with('error', 'El archivo PDF físico no existe. Ruta: '.$xmlFile->pdf_path);
        }

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($fullPath).'"',
        ]);
    }

    /**
     * Resuelve la ruta fisica de un PDF segun su prefijo en BD.
     * Retorna null si no existe.
     */
    private function resolvePdfPath(string $pdfPath): ?string
    {
        if (str_starts_with($pdfPath, 'pdf_files/')) {
            $fullPath = storage_path('app/public/'.$pdfPath);
        } elseif (str_starts_with($pdfPath, 'facturas/')) {
            $fullPath = storage_path('app/private/'.$pdfPath);
        } else {
            $fullPath = storage_path('app/public/pdf_files/'.$pdfPath);
        }

        if (file_exists($fullPath)) {
            return $fullPath;
        }

        $filename = basename($pdfPath);
        $projectFacturasPath = base_path('Facturas');
        $foundFiles = glob($projectFacturasPath.'/**/'.$filename, GLOB_BRACE);
        if (! empty($foundFiles)) {
            return $foundFiles[0];
        }

        return null;
    }
}

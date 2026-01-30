<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; 
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\XmlFile;
use App\Http\Controllers\Controller;

class UploadFactura extends Controller
{
    /**
     * Mostrar listado de facturas de todos los usuarios con filtros opcionales
     */
    public function index(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $query = DB::table('xml_files')
            ->join('users', 'xml_files.id_user', '=', 'users.id')
            ->select('xml_files.*', 'users.proyect', 'users.name as inversor_name');

            // FILTRO POR MES
            if ($request->filled('month')) {
                $year  = substr($request->month, 0, 4);
                $month = substr($request->month, 5, 2);

                $query->whereYear('xml_files.created_at', $year)
                    ->whereMonth('xml_files.created_at', $month);
            }


        if ($request->filled('fecha')) {
            $query->whereDate('xml_files.created_at', $request->input('fecha'));
        }

        if ($request->filled('batch_id')) {
            $query->where('xml_files.batch_id', $request->input('batch_id'));
        }

        if ($request->filled('emisor_name')) {
            $query->where('xml_files.emisor_name', 'LIKE', '%' . $request->input('emisor_name') . '%');
        }


                // Leer filtros desde la sesión
        $search    = session('search');
        $categoria = session('categoria');

        if ($search && $categoria) {
            switch ($categoria) {
                case 'id':
                    $query->where('batch_id', $search);
                    break;
                case 'inversionista':
                    $query->where('emisor_name', $search);
                    break;
                case 'fecha':
                    $query->whereDate('created_at', $search);
                    if ($search != Carbon::parse($search)->format('Y-m-d')) {
                        // Si la fecha no es válida, limpiamos los filtros
                        session()->forget(['search', 'categoria']);
                        return redirect()->back()->withErrors(['search' => 'La fecha no es válida.']);
                    }
                    break;
            }
        }

        $xmlFiles = $query->paginate(6);

        return view('facturas', [
            'xmlFiles' => $xmlFiles
        ],compact('search', 'categoria'));
    }

    public function limpiar(){
            // Borrar filtros guardados en la sesión
            session()->forget(['search', 'categoria']);

            // Redirigir al listado sin filtros
            return redirect()->route('facturas');
    }

    public function buscar(Request $request){
        // Guardar filtros en la sesión
        session([
            'search'    => $request->input('search'),
            'categoria' => $request->input('categoria'),
        ]);

        // Redirigir al index sin parámetros en la URL
        return redirect()->route('facturas');
    }
    /**
     * Descargar un XML específico
     */
    public function descargar($id)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $xmlFile = XmlFile::find($id);

        if (!$xmlFile) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        // Detecta si file_path trae la carpeta o solo el archivo
        $filePath = $xmlFile->file_path;

        if (basename($filePath) === $filePath) {
            $fullPath = public_path('storage/xml_files/' . $filePath);
        } else {
            $fullPath = public_path('storage/' . $filePath);
        }

        if (!file_exists($fullPath)) {
            return back()->with('error', 'El archivo físico no existe: ' . $fullPath);
        }

        return response()->download($fullPath, basename($fullPath));
    }

    /**
 * Descargar el PDF asociado a un XML
 */
    public function descargarPdf($id)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $xmlFile = XmlFile::find($id);

        if (!$xmlFile) {
            return back()->with('error', 'Registro no encontrado.');
        }

        if (!$xmlFile->pdf_path) {
            return back()->with('error', 'Este XML no tiene un PDF asociado.');
        }

        $pdfPath = $xmlFile->pdf_path;

        // Detectar ruta completa
        if (basename($pdfPath) === $pdfPath) {
            $fullPath = public_path('storage/pdf_files/' . $pdfPath);
        } else {
            $fullPath = public_path('storage/' . $pdfPath);
        }

        if (!file_exists($fullPath)) {
            return back()->with('error', 'El archivo PDF físico no existe.');
        }

        return response()->download($fullPath, basename($fullPath));
    }

}

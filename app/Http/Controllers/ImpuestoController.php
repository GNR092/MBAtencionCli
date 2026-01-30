<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; 
use App\Models\XmlFile;
use App\Exports\XmlFilesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\XmlValidationService;

class ImpuestoController extends Controller
{
    /* ================= EXPORTAR ================= */
    public function export(Request $request)
    {
        $query = DB::table('xml_files')
            ->join('users', 'xml_files.id_user', '=', 'users.id')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->select(
                'xml_files.*',
                'users.proyect',
                'users.name as inversor_name',
                'impuesto.tipoFactor',
                'users.regimenFiscal',
                'impuesto.importeBase',
                'impuesto.tasaCuota',
                'impuesto.isr'
            );

        // 🔹 Filtros reutilizables
        $this->aplicarFiltros($query, $request);
                 // Filtros manuales basados en el modal

        if ($request->filled('desde')) 
            { $query->whereDate('impuesto.created_at', '>=', $request->desde); } 
        if ($request->filled('hasta')) 
            { $query->whereDate('impuesto.created_at', '<=', $request->hasta); }

        // 🔹 Calcular totales
        $totalISR = (clone $query)->sum('impuesto.isr');
        $totalBase = (clone $query)->sum('impuesto.importeBase');

        return Excel::download(
            new XmlFilesExport($query, $totalBase, $totalISR),
            'xml_files.xlsx'
        );
    }

    /* ================= INDEX ================= */
public function index(Request $request)
{

    // Verificar sesión de usuario
    $user = Session::get('user');
    if (!$user) {
        return redirect('/inicio-de-sesion');
    }
    

    // Consulta base
    $query = DB::table('xml_files')
    
        ->join('users', 'xml_files.id_user', '=', 'users.id')
        ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
        ->select(
            'xml_files.*',
            'users.name as usuario',
            'users.regimenFiscal as regimenFiscal',
            'users.proyect as proyecto',
            'impuesto.isr as isr',
            'impuesto.importeBase as importeBase',
            'impuesto.tasaCuota as tasaCuota',
            'impuesto.tipoFactor as tipoFactor',

        );

        // FILTRO POR MES
        if ($request->filled('month')) {
            $year = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);

            $query->whereYear('xml_files.created_at', $year)
                ->whereMonth('xml_files.created_at', $month);
        }

        

    // filtro dinámico (si el usuario busca)
    if ($request->filled('search') && $request->filled('categoria')) {
        $search = $request->input('search');
        $categoria = $request->input('categoria');

        switch ($categoria) {
            case 'proyecto':
                $query->where('users.proyect', 'LIKE', "%{$search}%");
                break;

            case 'inversionista':
                $query->where('xml_files.emisor_name', 'LIKE', "%{$search}%");
                break;

            case 'departamento':
                $query->where('xml_files.departamento', 'LIKE', "%{$search}%");
                break;
        }
    }

    //  Clonamos la consulta para calcular totales SIN afectar la paginación
    $totalISR = (clone $query)->sum('impuesto.isr');
    $totalBase = (clone $query)->sum('impuesto.importeBase');

    //  Paginación (SE HACE AL FINAL y se respetan los filtros)
    $xmlFiles = $query->paginate(6)->appends($request->query());

    //  Enviamos a la vista
    return view('inpuestos', compact('xmlFiles', 'totalISR', 'totalBase'));
}


    /* ================= LIMPIAR FILTROS ================= */
    public function limpiar()
    {
        session()->forget(['search', 'categoria']);
        return redirect()->route('inpuestos');
    }

    /* ================= FUNCIÓN PARA REUTILIZAR FILTROS ================= */
    private function aplicarFiltros(&$query, Request $request)
    {
        if ($request->filled('fecha')) {
            $query->whereDate('xml_files.created_at', $request->input('fecha'));
        }

        if ($request->filled('id')) {
            $query->where('xml_files.id', $request->input('id'));
        }

        if ($request->filled('emisor_name')) {
            $query->where('xml_files.emisor_name', 'LIKE', '%'.$request->input('emisor_name').'%');
        }

        if ($request->filled('uuid')) {
            $query->where('xml_files.uuid', $request->input('uuid'));
        }

        if ($request->filled('departamento')) {
            $query->where('xml_files.departamento', $request->input('departamento'));
        }

        if ($request->filled('tipoFactor')) {
            $query->where('impuesto.tipoFactor', $request->input('tipoFactor'));
        }

        if ($request->filled('tasaCuota')) {
            $query->where('impuesto.tasaCuota', $request->input('tasaCuota'));
        }

        if ($request->filled('importeBase')) {
            $query->where('impuesto.importeBase', $request->input('importeBase'));
        }

        if ($request->filled('isr')) {
            $query->where('impuesto.isr', $request->input('isr'));
        }

        // 🔎 Búsqueda por categoría
        if ($request->filled('search') && $request->filled('categoria')) {
            $search = $request->input('search');
            $categoria = $request->input('categoria');

            switch ($categoria) {
                case 'proyecto':
                    $query->where('users.proyect', 'LIKE', "%{$search}%");
                    break;
                case 'inversionista':
                    $query->where('xml_files.emisor_name', 'LIKE', "%{$search}%");
                    break;
                case 'departamento':
                    $query->where('xml_files.departamento', 'LIKE', "%{$search}%");
                    break;
            }
        }
    }
}

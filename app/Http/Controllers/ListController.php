<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; 
use Carbon\Carbon;

class ListController extends Controller
{
    public function limpiar()
    {
        
        session()->forget(['search', 'categoria']);

        
        return redirect()->route('listInver');
    }

    public function index(Request $request)
    {
        $user = Session::get('user');

        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        
        $monthParam = $request->input('month', now()->format('Y-m'));
        [$year, $month] = explode('-', $monthParam);

        
        $query = DB::table('xml_files')
            ->join('users', 'xml_files.id_user', '=', 'users.id')
            ->leftJoin('proyectos', 'xml_files.id_proyecto', '=', 'proyectos.id_proyecto')
            ->select('xml_files.*', 'users.name as inversor_name', 'proyectos.nombre_proyecto');

        
        $query->whereYear('xml_files.created_at', $year)
              ->whereMonth('xml_files.created_at', $month);

        
        if ($request->filled('fecha')) {
            $query->whereDate('xml_files.created_at', $request->input('created_at'));
        }

        if ($request->filled('batch_id')) {
            $query->where('xml_files.batch_id', $request->input('batch_id'));
        }

        if ($request->filled('emisor_name')) {
            $query->where('xml_files.emisor_name', 'LIKE', '%' . $request->input('emisor_name') . '%');
        }

        
        if ($request->filled('search') && $request->filled('categoria')) {
            $search = $request->input('search');
            $categoria = $request->input('categoria');

            switch ($categoria) {
                case 'proyectos':
                    $query->where('proyectos.nombre_proyecto', 'LIKE', "%{$search}%");
                    if ($search == '') {
                        $query->orWhereNull('proyectos.nombre_proyecto');
                    }
                    break;

                case 'nombre':
                    $query->where('xml_files.emisor_name', 'LIKE', "%{$search}%");
                    break;

                case 'factura':
                    $query->where('xml_files.id', $search);
                    break;
            }
        }

        
        $xmlFiles = $query->paginate(10)->appends($request->query());

        return view('listInver', [
            'xmlFiles' => $xmlFiles,
            'selectedMonth' => $monthParam 
        ]);
    }
}

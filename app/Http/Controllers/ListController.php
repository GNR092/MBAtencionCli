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
        // Borrar filtros guardados en la sesión
        session()->forget(['search', 'categoria']);

        // Redirigir al listado sin filtros (pero sigue mostrando el mes actual)
        return redirect()->route('listInver');
    }

    public function index(Request $request)
    {
        $user = Session::get('user');

        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        // Si no viene el parámetro "month", usamos el mes actual
        $monthParam = $request->input('month', now()->format('Y-m'));
        [$year, $month] = explode('-', $monthParam);

        // Hacemos join con users
        $query = DB::table('xml_files')
            ->join('users', 'xml_files.id_user', '=', 'users.id')
            ->select('xml_files.*', 'users.proyect', 'users.name as inversor_name');

        // 📌 Filtro obligatorio por mes (siempre aplicado)
        $query->whereYear('xml_files.created_at', $year)
              ->whereMonth('xml_files.created_at', $month);

        // 🔎 Filtros generales adicionales
        if ($request->filled('fecha')) {
            $query->whereDate('xml_files.created_at', $request->input('created_at'));
        }

        if ($request->filled('batch_id')) {
            $query->where('xml_files.batch_id', $request->input('batch_id'));
        }

        if ($request->filled('emisor_name')) {
            $query->where('xml_files.emisor_name', 'LIKE', '%' . $request->input('emisor_name') . '%');
        }

        // 🔎 Filtro dinámico
        if ($request->filled('search') && $request->filled('categoria')) {
            $search = $request->input('search');
            $categoria = $request->input('categoria');

            switch ($categoria) {
                case 'proyectos':
                    $query->where('xml_files.proyectos', 'LIKE', "%{$search}%");
                    if ($search == '') {
                        $query->orWhereNull('xml_files.proyectos');
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

        // Paginación con query string para mantener filtros
        $xmlFiles = $query->paginate(10)->appends($request->query());

        return view('listInver', [
            'xmlFiles' => $xmlFiles,
            'selectedMonth' => $monthParam // 🔹 pasamos el mes actual o seleccionado
        ]);
    }
}

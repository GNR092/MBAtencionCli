<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; 
use App\Services\PdfReaderService; 

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $query = DB::table('contract')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'asc');

        
        $search    = session('search');
        $categoria = session('categoria');

        if ($search && $categoria) {
            switch ($categoria) {
                case 'id':
                    $query->where('id', $search);
                    break;
                case 'folio':
                    $query->where('folio', $search);
                    break;
                case 'fecha':
                    $query->whereDate('fecha', $search);
                    break;
            }
        }

        $contratos = $query->paginate(6);

        if ($request->expectsJson()) {
            $html = view('contratos', compact('contratos', 'search', 'categoria'))->render();
            return response()->json(['html' => $html]);
        }

        return view('contratos', compact('contratos', 'search', 'categoria'));
    }

    public function buscar(Request $request)
    {
        
        session([
            'search'    => $request->input('search'),
            'categoria' => $request->input('categoria'),
        ]);

        
        return redirect()->route('contratos.index');
    }

    public function limpiar()
    {
        
        session()->forget(['search', 'categoria']);

        
        return redirect()->route('contratos.index');
    }
    
    public function search(Request $request)
    {
        
        session([
            'search'    => $request->input('search'),
            'categoria' => $request->input('categoria'),
        ]);

        
        return redirect()->route('contratos.show');
    }

    public function clean()
    {
        
        session()->forget(['search', 'categoria']);

        
        return redirect()->route('contratos.show');
    }

    public function confirmPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $admin = Session::get('user');

        
        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        
        if (Hash::check($request->password, $admin->password)) {
            
            session(['validated_admin_contract' => true]);

            
            return redirect()->route('contratos.crear');
        }

        
        return back()->with('error', 'Contraseña incorrecta, intenta nuevamente.');
    }

    public function confirmPasswordEdit(Request $request){
                $request->validate([
            'user_id' => 'required|integer',
            'password' => 'required|string',
        ]);

        
        $admin = Session::get('user');
        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        
        if (!Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta']);
        }

        
        session(['validated_edit_contrato' => $request->user_id]);

        
        return redirect()->route('contratos.editar', $request->user_id);
    }
  
    public function editar($id)
    {
        $admin = Session::get('user');

        
        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        
        if (session('validated_edit_contrato') != $id) {
            return redirect()->route('contratos.show')
                            ->withErrors(['auth' => 'Debes confirmar tu contraseña antes de editar este contrato.']);
        }

        
        $contractToEdit = Contract::findOrFail($id);

        
        $users = User::all();

        
        return view('editContrato', compact('admin', 'contractToEdit', 'users'));
    }
    
    public function actualizar(Request $request,$id){
        $request->validate([
            'importe_bruto_renta' => 'required',
        ]);

        $contrato = \App\Models\Contract::findOrFail($id);

        
        $contrato->user_id = $request->input('user_id', $contrato->user_id);
        $contrato->importe_bruto_renta = str_replace(['$', ','], '', $request->input('importe_bruto_renta'));
        $contrato->proyecto = $request->input('proyect');
        $contrato->estado = $request->input('activo') ? 'activo' : ($request->input('inactivo') ? 'inactivo' : 'desconocido');

        
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $contrato->nombre = $archivo->getClientOriginalName();
            $contrato->tipo = $archivo->getMimeType();
            $contrato->contenido = file_get_contents($archivo->getRealPath());
        }

        $contrato->updated_at = now();
        $contrato->save();

        return redirect()->route('contratos.show')->with('success', 'Contrato actualizado correctamente.');
    }
    public function subir(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:2048', 
        ]);

        $importe = str_replace(['$', ','], '', $request->input('importe_bruto_renta'));
        $archivo = $request->file('archivo');
        $contenidoBinario = file_get_contents($archivo->getRealPath());

        $fechaInicio= $request->input('fecha_inicio');
        $fechaTerminacion= $request->input('fecha_terminacion');

        //validar proyecto
        $proyecto = $request->input('proyect'); 

        $user = Session::get('user');
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $userId = $user->role === 'administrador' && $request->filled('user_id')
            ? $request->user_id
            : $user->id;

        DB::table('contract')->insert([
            'user_id'   => $userId,
            'nombre'    => $archivo->getClientOriginalName(),
            'tipo'      => $archivo->getMimeType(),
            'contenido' => $contenidoBinario,
            'created_at'=> now(),
            'updated_at'=> now(),
            'folio'     => $this->generarFolio(),
            'fecha'=> $filectime = date('Y-m-d H:i:s'),
            'estado'    => $this->generarEstado($request),
            'importe_bruto_renta'=> $importe, 
            'fecha_inicio'=> $fechaInicio,
            'fecha_terminacion'=> $fechaTerminacion,
            'proyecto'=> $proyecto,

        ]);

        return back()->with('success', '✅ Archivo enviado correctamente.');
    }

    public function delete(Request $request)
    {
        $admin = Session::get('user');

        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        
        if (!Hash::check($request->input('password'), $admin->password)) {
            return back()->with('error', 'Contraseña incorrecta');
        }

        $contratoId = $request->input('id');

        
        $contrato = contract::find($contratoId);

        if (!$contrato) {
            return back()->with('error', 'Contrato no encontrado.');
        }

        $contrato->delete();

        return back()->with('success', 'Contrato eliminado correctamente.');
    }
    

    public function crear()
    {
        $admin = Session::get('user');

        
        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        
        if (!session('validated_admin_contract')) {
            return redirect()->route('contratos.show')->with('error', '⚠️ Debes confirmar tu contraseña antes de crear un contrato.');
        }

        
        session()->forget('validated_admin_contract');

        $users = User::all();
        return view('adContrato', compact('users'));
    }


    public function show()
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $users = User::all();

        
        $query = DB::table('contract')
            ->join('users', 'contract.user_id', '=', 'users.id')
            ->select('contract.*', 'users.name as user_name')
            ->orderBy('contract.created_at', 'asc');

            
            if (request()->filled('month')) {
                $year  = substr(request('month'), 0, 4);
                $month = substr(request('month'), 5, 2);

                $query->whereYear('contract.created_at', $year)
                    ->whereMonth('contract.created_at', $month);
            }


        
        $search    = session('search');
        $categoria = session('categoria');

        if ($search && $categoria) {
            switch ($categoria) {
                case 'id':
                    $query->where('contract.id', $search);
                    break;
                case 'name':
                    $query->where('users.name', 'like', "%{$search}%");
                    break;
            }
        }

        
        $contratos = $query->paginate(6);

        
        session()->forget('validated_edit_contract');

        return view('subirContrato', compact('users', 'contratos', 'search', 'categoria'));
    }


    public function descargar($id)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        
        $contrato = DB::table('contract')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$contrato) {
            abort(404, 'Contrato no encontrado');
        }

        
        return response($contrato->contenido)
            ->header('Content-Type', $contrato->tipo)
            ->header('Content-Disposition', 'attachment; filename="'.$contrato->nombre.'"');
    }

    private function generarFolio()
    {
        $fecha = date('Ymd');
        $ultimoFolio = DB::table('contract')
            ->where('folio', 'like', "CTR-{$fecha}-%")
            ->orderBy('folio', 'desc')
            ->first();

        if ($ultimoFolio) {
            $numeroActual = (int) substr($ultimoFolio->folio, -4);
            $nuevoNumero = str_pad($numeroActual + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nuevoNumero = '0001';
        }

        return "CTR-{$fecha}-{$nuevoNumero}";
    }

    private function generarEstado(Request $request)
    {
        if ($request->input('activo')) {
            return 'activo';
        } elseif ($request->input('inactivo')) {
            return 'inactivo';
        }

        return 'desconocido';
    }


}

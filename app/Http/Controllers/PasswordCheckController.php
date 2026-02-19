<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PasswordCheckController extends Controller
{
    public function check(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        
        $dbUser = User::find($user->id);

        if (!$dbUser || !Hash::check($request->password, $dbUser->password)) {
            return response()->json(['message' => 'Contraseña incorrecta'], 403);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}

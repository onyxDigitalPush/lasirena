<?php

namespace App\Http\Controllers\MainApp;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

use App\Models\MainApp\Project;

class UserController extends Controller
{
    public function index()
    {
        $array_users = User::select('id', 'name', 'email', 'type_user')
            ->orderBy('id', 'desc')
            ->get();

        return view('users.user_list', compact('array_users'));
    }
    public function edit($id)
    {
        $user = User::find($id);

        if (!$user) {
            // Si es AJAX, devolvemos error en formato JSON
            if (request()->ajax()) {
                return response()->json(['error' => 'Usuario no encontrado.'], 404);
            }
            // Si no es AJAX, redirige con mensaje
            return redirect()->route('usuarios.index')->with('error', 'Usuario no encontrado.');
        }

        // Si es una petición AJAX, devolver JSON
        if (request()->ajax()) {
            return response()->json($user);
        }

        // Si no es AJAX, devuelve vista normalmente
        return view('users.user_edit', compact('user'));
    }
}

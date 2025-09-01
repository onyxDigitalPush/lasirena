<?php

namespace App\Http\Controllers\MainApp;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

use App\Models\MainApp\Project;

class UserController extends Controller
{
    public function index()
    {
    // Traer también type_user_multi para la vista
    $array_users = User::select('id', 'name', 'email', 'type_user', 'type_user_multi')
            ->orderBy('id', 'desc')
            ->get();

        return view('users.user_list', compact('array_users'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'type_user_multi' => 'nullable|array',
            'type_user_multi.*' => 'integer',
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        // Guardamos en type_user_multi para permitir múltiples selección
        $user->type_user_multi = $data['type_user_multi'] ?? [];
        // También, para compatibilidad con código antiguo que usa type_user (single int),
        // establecemos type_user al primer elemento si existe
        if (!empty($user->type_user_multi)) {
            $user->type_user = intval($user->type_user_multi[0]);
        }
        $user->save();

        return redirect()->back()->with('success', 'Usuario creado correctamente.');
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
    public function destroy(Request $request)
    {
        $user = User::findOrFail($request->input('id'));
        $user->delete();
        return redirect()->back()->with('success', 'Usuario eliminado correctamente.');
    }
    public function update(Request $request)
    {
        $user = User::find($request->input('id'));
        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'type_user_multi' => 'nullable|array',
            'type_user_multi.*' => 'integer',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->type_user_multi = $data['type_user_multi'] ?? [];
        if (!empty($user->type_user_multi)) {
            $user->type_user = intval($user->type_user_multi[0]);
        }
        $user->save();

        return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
    }
    public function cambiarContrasena(Request $request)
    {
    
        $user = User::find($request->user_id);
        $user->password = bcrypt($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Contraseña actualizada correctamente.');
    }
}

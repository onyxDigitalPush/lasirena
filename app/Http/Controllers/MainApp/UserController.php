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
        $array_users = User::select('id', 'name', 'email', 'type_user')
            ->orderBy('id', 'desc')
            ->get();

        return view('users.user_list', compact('array_users'));
    }
    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->type_user = $request->input('type_user');
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

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->type_user = $request->input('type_user');
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

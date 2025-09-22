<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    /**
     * Buscar material por codigo y devolver descripcion.
     */
    public function buscar(Request $request)
    {
        $codigo = $request->query('codigo');
        if (!$codigo) {
            return response()->json(['success' => false, 'message' => 'codigo requerido'], 400);
        }

        $material = DB::table('materiales')->where('codigo', $codigo)->first();

        if (!$material) {
            return response()->json(['success' => false, 'message' => 'no encontrado', 'descripcion' => null], 200);
        }

        return response()->json(['success' => true, 'descripcion' => $material->descripcion], 200);
    }
}

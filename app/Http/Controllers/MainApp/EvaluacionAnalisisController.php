<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tienda;
use App\Models\Analitica;

class EvaluacionAnalisisController extends Controller
{
    public function historialEvaluaciones(){
        // Obtener todas las tiendas
        $tiendas = \App\Models\Tienda::with(['analiticas' => function($q) {
            $q->orderByDesc('fecha_real_analitica');
        }])->get();

        // Calcular para cada tienda si la última analítica está vencida
        $hoy = now();
        foreach ($tiendas as $tienda) {
            $ultima = $tienda->analiticas->first();
            if ($ultima) {
                $fecha = \Carbon\Carbon::parse($ultima->fecha_real_analitica);
                $periodo = $ultima->periodicidad;
                switch ($periodo) {
                    case '1 mes':
                        $fechaLimite = $fecha->addMonth();
                        break;
                    case '3 meses':
                        $fechaLimite = $fecha->addMonths(3);
                        break;
                    case '6 meses':
                        $fechaLimite = $fecha->addMonths(6);
                        break;
                    case 'anual':
                        $fechaLimite = $fecha->addYear();
                        break;
                    default:
                        $fechaLimite = $fecha;
                }
                $tienda->analitica_vencida = $hoy->greaterThan($fechaLimite);
                $tienda->fecha_limite_analitica = $fechaLimite->format('Y-m-d');
                $tienda->fecha_ultima_analitica = $ultima->fecha_real_analitica;
                $tienda->periodicidad_ultima_analitica = $periodo;
            } else {
                $tienda->analitica_vencida = true;
                $tienda->fecha_limite_analitica = null;
                $tienda->fecha_ultima_analitica = null;
                $tienda->periodicidad_ultima_analitica = null;
            }
        }
        // Retornar vista de historial de evaluaciones con las tiendas
        return view('MainApp/evaluacion_analisis.historial_evaluaciones', compact('tiendas'));
    }
    public function guardarAnalitica(Request $request)
    {
        Analitica::create($request->all());

        return redirect()->back()->with('success', 'Analítica guardada correctamente.');
    }
}

<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tienda;
use App\Models\Analitica;
use App\Models\MainApp\Proveedor;
use App\Models\TendenciaSuperficie;
use Carbon\Carbon;

class EvaluacionAnalisisController extends Controller
{
    public function historialEvaluaciones(){
        // Obtener todas las tiendas
        $tiendas = Tienda::with(['analiticas' => function($q) {
            $q->orderByDesc('fecha_real_analitica');
        }])->get();

        // Calcular para cada tienda si la última analítica está vencida
        $hoy = now();
        foreach ($tiendas as $tienda) {
            $ultima = $tienda->analiticas->first();
            if ($ultima) {
        $fecha = Carbon::parse($ultima->fecha_real_analitica);
                $periodo = $ultima->periodicidad;
                switch ($periodo) {
                    case '1 mes':
            $fechaLimite = $fecha->copy()->addMonth();
                        break;
                    case '3 meses':
            $fechaLimite = $fecha->copy()->addMonths(3);
                        break;
                    case '6 meses':
            $fechaLimite = $fecha->copy()->addMonths(6);
                        break;
                    case 'anual':
            $fechaLimite = $fecha->copy()->addYear();
                        break;
                    default:
                        $fechaLimite = $fecha;
                }
        $tienda->setAttribute('analitica_vencida', $hoy->greaterThan($fechaLimite));
        $tienda->setAttribute('fecha_limite_analitica', $fechaLimite->format('Y-m-d'));
        $tienda->setAttribute('fecha_ultima_analitica', $ultima->fecha_real_analitica);
        $tienda->setAttribute('periodicidad_ultima_analitica', $periodo);
            } else {
        $tienda->setAttribute('analitica_vencida', true);
        $tienda->setAttribute('fecha_limite_analitica', null);
        $tienda->setAttribute('fecha_ultima_analitica', null);
        $tienda->setAttribute('periodicidad_ultima_analitica', null);
            }
        }

    // Obtener proveedores para el modal
        $proveedores = Proveedor::select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

    // Retornar vista de historial de evaluaciones con las tiendas y proveedores
    return view('MainApp/evaluacion_analisis.historial_evaluaciones', compact('tiendas', 'proveedores'));
    }
    public function guardarAnalitica(Request $request)
    {
        Analitica::create($request->all());

        return redirect()->back()->with('success', 'Analítica guardada correctamente.');
    }

    public function evaluacionList(Request $request)
    {
        // Listar analíticas con nombre de tienda (join) y proveedor
        $analiticas = Analitica::leftJoin('tiendas', 'analiticas.num_tienda', '=', 'tiendas.num_tienda')
            ->select('analiticas.*', 'tiendas.nombre_tienda as tienda_nombre')
            ->with('proveedor')
            ->orderByDesc('fecha_real_analitica')
            ->paginate(20);

        $proveedores = Proveedor::select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        return view('MainApp/evaluacion_analisis.evaluacion_list', compact('analiticas', 'proveedores'));
    }

    /**
     * Listar tendencias superficie
     */
    public function tendenciasSuperficieList(Request $request)
    {
        $tendencias = TendenciaSuperficie::with(['tienda','proveedor'])
            ->orderByDesc('fecha_muestra')
            ->paginate(20);

        $proveedores = Proveedor::select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        return view('MainApp/evaluacion_analisis.tendencias_superficie_list', compact('tendencias','proveedores'));
    }

    /**
     * Guardar una tendencia superficie (desde modal)
     */
    public function guardarTendenciaSuperficie(Request $request)
    {
        $data = $request->validate([
            'tienda_id' => 'nullable|exists:tiendas,id',
            'num_tienda' => 'nullable|string',
            'proveedor_id' => 'nullable|exists:proveedores,id_proveedor',
            'fecha_muestra' => 'nullable|date',
            'anio' => 'nullable|string',
            'mes' => 'nullable|string',
            'semana' => 'nullable|string',
            'codigo_centro' => 'nullable|string',
            'descripcion_centro' => 'nullable|string',
            'provincia' => 'nullable|string',
            'numero_muestras' => 'nullable|integer',
            'numero_factura' => 'nullable|string',
            'codigo_referencia' => 'nullable|string',
            'referencias' => 'nullable|string',

            'aerobios_mesofilos_30c_valor' => 'nullable|string',
            'aerobios_mesofilos_30c_result' => 'nullable|in:correcto,incorrecto',

            'enterobacterias_valor' => 'nullable|string',
            'enterobacterias_result' => 'nullable|in:correcto,incorrecto',

            'listeria_monocytogenes_valor' => 'nullable|string',
            'listeria_monocytogenes_result' => 'nullable|in:correcto,incorrecto',

            'accion_correctiva' => 'nullable|string',
            'repeticion_n1' => 'nullable|string',
            'repeticion_n2' => 'nullable|string',
        ]);

        if (!empty($data['fecha_muestra'])) {
            $d = Carbon::parse($data['fecha_muestra']);
            $data['anio'] = $d->year;
            $data['mes'] = str_pad($d->month, 2, '0', STR_PAD_LEFT);
            $data['semana'] = $d->weekOfYear;
        }

        // Si no se envía tienda_id pero sí num_tienda, buscar el id correspondiente
        if (empty($data['tienda_id']) && !empty($data['num_tienda'])) {
            $ti = Tienda::where('num_tienda', $data['num_tienda'])->first();
            if ($ti) {
                $data['tienda_id'] = $ti->id;
            }
        }

        // Eliminar num_tienda si existe para evitar columnas inesperadas
        if (isset($data['num_tienda'])) unset($data['num_tienda']);

        TendenciaSuperficie::create($data);

        return redirect()->back()->with('success', 'Tendencia superficie guardada correctamente.');
    }
}

<?php

namespace App\Http\Controllers\MainApp;

use App\Models\MainApp\MaterialKilo;
use App\Models\MainApp\ProveedorMetric;
use App\Models\MainApp\IncidenciaProveedor;
use App\Models\MainApp\DevolucionProveedor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MaterialKiloController extends Controller   
{    public function index()
{
    $query = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
        ->join('materiales', 'material_kilos.codigo_material', '=', 'materiales.codigo')
        ->select(
            'material_kilos.id',
            'material_kilos.total_kg',
            'proveedores.nombre_proveedor',
            'materiales.descripcion as nombre_material',
            'material_kilos.ctd_emdev',
            'material_kilos.umb',
            'material_kilos.ce',
            'material_kilos.valor_emdev',
            'material_kilos.factor_conversion',
            'material_kilos.codigo_material',
            'material_kilos.mes',
        );

    // Aplicar ordenamiento según el filtro
    $orden = request('orden');
    if ($orden == 'total_kg_desc') {
        $query->orderBy('material_kilos.total_kg', 'desc');
    } elseif ($orden == 'total_kg_asc') {
        $query->orderBy('material_kilos.total_kg', 'asc');
    } else {
        $query->orderBy('material_kilos.id', 'asc');
    }

    $array_material_kilo = $query->paginate(25);

    return view('MainApp/material_kilo.material_kilo_list', compact('array_material_kilo'));
}



    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(MaterialKilo $materialKilo)
    {
        //
    }

  
    public function edit(MaterialKilo $materialKilo)
    {
        //
    }


    public function update(Request $request, MaterialKilo $materialKilo)
    {
        //
    }


    public function destroy(Request $request)
    {
        $materialKilo = MaterialKilo::findOrFail($request->input('id_material_kilo'));
        $materialKilo->delete();
        return redirect()->route('material_kilo.index')->with('success', 'Material Kilo eliminado correctamente.');

    }    public function totalKgPorProveedor(Request $request)
    {
        // Obtener filtros con valores por defecto (Enero del año actual)
        $mes = $request->get('mes', 1); // Por defecto enero (mes 1)
        $año = $request->get('año', date('Y')); // Por defecto año actual
        
        // Query base para totales por proveedor
        $query = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select(
                'proveedores.id_proveedor',
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gp_ls_material_kilos.total_kg) as total_kg_proveedor'),
                DB::raw('COUNT(gp_ls_material_kilos.id) as cantidad_registros')
            );
        
        // Aplicar filtros
        $query->where('material_kilos.año', $año);
        
        // Si mes está seleccionado, filtrar por mes específico
        if ($mes) {
            $query->where('material_kilos.mes', $mes);
        }
        
        $totales_por_proveedor = $query->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderByDesc('total_kg_proveedor')
            ->get();
        
        // Obtener proveedores ordenados alfabéticamente para el modal
        $proveedores_alfabetico = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->where('material_kilos.año', $año)
            ->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderBy('proveedores.nombre_proveedor', 'asc')
            ->get();
        
        // Obtener métricas existentes para el período filtrado
        $metricas_query = ProveedorMetric::where('año', $año);
        
        // Si mes está seleccionado, filtrar métricas por mes específico
        if ($mes) {
            $metricas_query->where('mes', $mes);
        }
        
        $metricas_por_proveedor = $metricas_query->get()->keyBy('proveedor_id');
        
        return view('MainApp/material_kilo.total_kg_por_proveedor', compact(
            'totales_por_proveedor', 
            'metricas_por_proveedor',
            'mes',
            'año',
            'proveedores_alfabetico'
        ));
    }

    public function guardarMetricas(Request $request)
    {
        try {
            $request->validate([
                'metricas' => 'required|array',
                'año' => 'required|integer',
                'mes' => 'required|integer|between:1,12'
            ]);

            $metricas = $request->input('metricas');
            $año = $request->input('año');
            $mes = $request->input('mes');

            foreach ($metricas as $proveedor_id => $datos) {
                ProveedorMetric::updateOrCreate(
                    [
                        'proveedor_id' => $proveedor_id,
                        'año' => $año,
                        'mes' => $mes
                    ],
                    [
                        'rg1' => $datos['rg1'] ?? null,
                        'rl1' => $datos['rl1'] ?? null,
                        'dev1' => $datos['dev1'] ?? null,
                        'rok1' => $datos['rok1'] ?? null,
                        'ret1' => $datos['ret1'] ?? null
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Métricas guardadas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las métricas: ' . $e->getMessage()
            ], 500);
        }
    }    public function evaluacionContinuaProveedores(Request $request)
    {
        $mes = $request->get('mes', 1); // Por defecto enero (mes 1)
        $año = $request->get('año', \Carbon\Carbon::now()->year);

        // Obtener totales por proveedor para el mes y año específicos
        $query = DB::table('material_kilos')
            ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select(
                'proveedores.id_proveedor',
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gp_ls_material_kilos.total_kg) as total_kg_proveedor'),
                DB::raw('COUNT(gp_ls_material_kilos.id) as cantidad_registros')
            )
            ->where('material_kilos.año', $año);

        // Si mes está seleccionado, filtrar por mes específico
        if ($mes) {
            $query->where('material_kilos.mes', $mes);
        }

        $totales_por_proveedor = $query->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderBy('total_kg_proveedor', 'desc')
            ->get();

        // Obtener métricas existentes para los proveedores
        $metricas_por_proveedor = [];
        if ($totales_por_proveedor->isNotEmpty()) {
            $proveedores_ids = $totales_por_proveedor->pluck('id_proveedor')->toArray();
            $metricas_query = ProveedorMetric::whereIn('proveedor_id', $proveedores_ids)
                ->where('año', $año);

            // Si mes está seleccionado, filtrar métricas por mes específico
            if ($mes) {
                $metricas_query->where('mes', $mes);
                $metricas = $metricas_query->get();
                
                foreach ($metricas as $metrica) {
                    $metricas_por_proveedor[$metrica->proveedor_id] = $metrica;
                }
            } else {
                // Si no hay mes específico, calcular promedio de todas las métricas del año
                $metricas = $metricas_query->get();
                $metricas_agrupadas = $metricas->groupBy('proveedor_id');
                
                foreach ($metricas_agrupadas as $proveedor_id => $metricas_proveedor) {
                    $promedio = new \stdClass();
                    $promedio->proveedor_id = $proveedor_id;
                    $promedio->rg1 = $metricas_proveedor->avg('rg1');
                    $promedio->rl1 = $metricas_proveedor->avg('rl1');
                    $promedio->dev1 = $metricas_proveedor->avg('dev1');
                    $promedio->rok1 = $metricas_proveedor->avg('rok1');
                    $promedio->ret1 = $metricas_proveedor->avg('ret1');
                    
                    $metricas_por_proveedor[$proveedor_id] = $promedio;
                }
            }
        }

        // Calcular indicadores y ponderados para cada proveedor
        foreach ($totales_por_proveedor as $proveedor) {
            $metricas = isset($metricas_por_proveedor[$proveedor->id_proveedor]) 
                ? $metricas_por_proveedor[$proveedor->id_proveedor] 
                : null;            if ($metricas && $proveedor->total_kg_proveedor > 0) {
                // Cálculos de indicadores (valores * 1000000 / total_kg)
                $proveedor->rg_ind1 = ($metricas->rg1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->rl_ind1 = ($metricas->rl1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->dev_ind1 = ($metricas->dev1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->rok_ind1 = ($metricas->rok1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->ret_ind1 = ($metricas->ret1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
                $proveedor->total_ind1 = $proveedor->rg_ind1 + $proveedor->rl_ind1 + $proveedor->dev_ind1 + $proveedor->rok_ind1 + $proveedor->ret_ind1;

                // Cálculos de ponderados (usando los valores por millón * porcentajes)
                $proveedor->rg_pond1 = $proveedor->rg_ind1 * 0.30; // RGind1 * 30%
                $proveedor->rl_pond1 = $proveedor->rl_ind1 * 0.05; // RLind1 * 5%
                $proveedor->dev_pond1 = $proveedor->dev_ind1 * 0.20; // DEVind1 * 20%
                $proveedor->rok_pond1 = $proveedor->rok_ind1 * 0.10; // ROKind1 * 10%
                $proveedor->ret_pond1 = $proveedor->ret_ind1 * 0.35; // RETind1 * 35%
                $proveedor->total_pond1 = $proveedor->rg_pond1 + $proveedor->rl_pond1 + $proveedor->dev_pond1 + $proveedor->rok_pond1 + $proveedor->ret_pond1;
            } else {
                // Si no hay métricas o total_kg es 0, inicializar en 0
                $proveedor->rg_ind1 = 0;
                $proveedor->rl_ind1 = 0;
                $proveedor->dev_ind1 = 0;
                $proveedor->rok_ind1 = 0;
                $proveedor->ret_ind1 = 0;
                $proveedor->total_ind1 = 0;
                $proveedor->rg_pond1 = 0;
                $proveedor->rl_pond1 = 0;
                $proveedor->dev_pond1 = 0;
                $proveedor->rok_pond1 = 0;
                $proveedor->ret_pond1 = 0;
                $proveedor->total_pond1 = 0;
            }
        }

        return view('MainApp/material_kilo.evaluacion_continua_proveedores', compact(
            'totales_por_proveedor', 
            'metricas_por_proveedor',
            'mes',
            'año'
        ));
    }

    public function incidenciasProveedores(Request $request)
    {
        $mes = $request->get('mes', 1); // Por defecto enero (mes 1)
        $año = $request->get('año', \Carbon\Carbon::now()->year);

        // Obtener incidencias de proveedores para el mes y año específicos
        $query = DB::table('incidencias_proveedores')
            ->join('proveedores', 'incidencias_proveedores.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select(
                'proveedores.id_proveedor',
                'proveedores.nombre_proveedor',
                DB::raw('COUNT(incidencias_proveedores.id) as cantidad_incidencias')
            )
            ->whereYear('incidencias_proveedores.fecha_incidencia', $año)
            ->whereMonth('incidencias_proveedores.fecha_incidencia', $mes)
            ->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderBy('cantidad_incidencias', 'desc');

        $incidencias_por_proveedor = $query->get();

        return view('MainApp/material_kilo.incidencias_proveedores', compact(
            'incidencias_por_proveedor',
            'mes',
            'año'
        ));
    }

    public function crearIncidencia(Request $request)
    {
        try {
            $request->validate([
                'proveedor_id' => 'required|exists:proveedores,id_proveedor',
                'descripcion' => 'required|string|max:255',
                'fecha_incidencia' => 'required|date',
            ]);

            IncidenciaProveedor::create([
                'proveedor_id' => $request->input('proveedor_id'),
                'descripcion' => $request->input('descripcion'),
                'fecha_incidencia' => $request->input('fecha_incidencia'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Incidencia creada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la incidencia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarIncidencia(Request $request)
    {
        try {
            $request->validate([
                'id_incidencia' => 'required|exists:incidencias_proveedores,id'
            ]);

            $incidencia = IncidenciaProveedor::findOrFail($request->input('id_incidencia'));
            $incidencia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Incidencia eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la incidencia: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Guardar una nueva incidencia de proveedor
     */
    public function guardarIncidencia(Request $request)
    {
        try {
            $request->validate([
                'id_proveedor' => 'required|integer',
                'año' => 'required|integer',
                'mes' => 'required|integer|between:1,12',
                'clasificacion_incidencia' => 'nullable|string|max:255',
                'origen' => 'nullable|string|max:255',
                'fecha_incidencia' => 'nullable|date',
                'numero_inspeccion_sap' => 'nullable|string|max:255',
                'resolucion_almacen' => 'nullable|string|max:255',
                'cantidad_devuelta' => 'nullable|numeric',
                'kg_un' => 'nullable|numeric',
                'pedido_sap_devolucion' => 'nullable|string|max:255',
                'resolucion_tienda' => 'nullable|string|max:255',
                'retirada_tiendas' => 'nullable|in:Si,No',
                'cantidad_afectada' => 'nullable|numeric',
                'descripcion_incidencia' => 'nullable|string',
                'codigo' => 'nullable|string|max:255',
                'producto' => 'nullable|string|max:255',
                'lote_sirena' => 'nullable|string|max:255',
                'lote_proveedor' => 'nullable|string|max:255',
                'fcp' => 'nullable|date',
                'informe_a_proveedor' => 'nullable|in:Si,No',
                'numero_informe' => 'nullable|string|max:255',
                'fecha_envio_proveedor' => 'nullable|date',
                'fecha_respuesta_proveedor' => 'nullable|date',
                'informe_respuesta' => 'nullable|string',
                'comentarios' => 'nullable|string',
                'fecha_reclamacion_respuesta1' => 'nullable|date',
                'fecha_reclamacion_respuesta2' => 'nullable|date',
                'fecha_decision_destino_producto' => 'nullable|date'
            ]);

            // Obtener el nombre del proveedor
            $proveedor = DB::table('material_kilos')
                ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
                ->where('proveedores.id_proveedor', $request->id_proveedor)
                ->select('proveedores.nombre_proveedor')
                ->first();

            if (!$proveedor) {
                return response()->json(['error' => 'Proveedor no encontrado'], 404);
            }

            // Calcular días de respuesta si hay fechas
            $dias_respuesta_proveedor = null;
            $dias_sin_respuesta_informe = null;
            $tiempo_respuesta = null;

            if ($request->fecha_envio_proveedor && $request->fecha_respuesta_proveedor) {
                $fecha_envio = \Carbon\Carbon::parse($request->fecha_envio_proveedor);
                $fecha_respuesta = \Carbon\Carbon::parse($request->fecha_respuesta_proveedor);
                $dias_respuesta_proveedor = $fecha_envio->diffInDays($fecha_respuesta);
            }

            if ($request->fecha_envio_proveedor && !$request->fecha_respuesta_proveedor) {
                $fecha_envio = \Carbon\Carbon::parse($request->fecha_envio_proveedor);
                $dias_sin_respuesta_informe = $fecha_envio->diffInDays(\Carbon\Carbon::now());
            }

            // Crear la incidencia
            $incidencia = IncidenciaProveedor::create([
                'id_proveedor' => $request->id_proveedor,
                'nombre_proveedor' => $proveedor->nombre_proveedor,
                'año' => $request->año,
                'mes' => $request->mes,
                'clasificacion_incidencia' => $request->clasificacion_incidencia,
                'origen' => $request->origen,
                'fecha_incidencia' => $request->fecha_incidencia,
                'numero_inspeccion_sap' => $request->numero_inspeccion_sap,
                'resolucion_almacen' => $request->resolucion_almacen,
                'cantidad_devuelta' => $request->cantidad_devuelta,
                'kg_un' => $request->kg_un,
                'pedido_sap_devolucion' => $request->pedido_sap_devolucion,
                'resolucion_tienda' => $request->resolucion_tienda,
                'retirada_tiendas' => $request->retirada_tiendas,
                'cantidad_afectada' => $request->cantidad_afectada,
                'descripcion_incidencia' => $request->descripcion_incidencia,
                'codigo' => $request->codigo,
                'producto' => $request->producto,
                'lote_sirena' => $request->lote_sirena,
                'lote_proveedor' => $request->lote_proveedor,
                'fcp' => $request->fcp,
                'informe_a_proveedor' => $request->informe_a_proveedor,
                'numero_informe' => $request->numero_informe,
                'fecha_envio_proveedor' => $request->fecha_envio_proveedor,
                'fecha_respuesta_proveedor' => $request->fecha_respuesta_proveedor,
                'informe_respuesta' => $request->informe_respuesta,
                'comentarios' => $request->comentarios,
                'dias_respuesta_proveedor' => $dias_respuesta_proveedor,
                'dias_sin_respuesta_informe' => $dias_sin_respuesta_informe,
                'tiempo_respuesta' => $tiempo_respuesta,
                'fecha_reclamacion_respuesta1' => $request->fecha_reclamacion_respuesta1,
                'fecha_reclamacion_respuesta2' => $request->fecha_reclamacion_respuesta2,
                'fecha_decision_destino_producto' => $request->fecha_decision_destino_producto
            ]);

            // Actualizar las métricas automáticamente
            $this->actualizarMetricasIncidencias($request->id_proveedor, $request->año, $request->mes);

            return response()->json([
                'success' => true,
                'message' => 'Incidencia guardada correctamente',
                'incidencia' => $incidencia
            ]);

        } catch (\Exception $e) {
            Log::error('Error al guardar incidencia: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al guardar la incidencia: ' . $e->getMessage(),
                'debug' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Actualizar métricas basadas en incidencias
     */
    private function actualizarMetricasIncidencias($id_proveedor, $año, $mes)
    {
        // Contar incidencias por tipo para el proveedor, año y mes específicos
        $metricas = DB::table('incidencias_proveedores')
            ->where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->select([
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "DEV1" THEN 1 ELSE 0 END) as dev1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "ROK1" THEN 1 ELSE 0 END) as rok1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RET1" THEN 1 ELSE 0 END) as ret1'),
            ])
            ->first();

        // Actualizar o crear las métricas del proveedor
        ProveedorMetric::updateOrCreate(
            [
                'proveedor_id' => $id_proveedor,
                'año' => $año,
                'mes' => $mes
            ],
            [
                'rg1' => $metricas->rg1 ?? 0,
                'rl1' => $metricas->rl1 ?? 0,
                'dev1' => $metricas->dev1 ?? 0,
                'rok1' => $metricas->rok1 ?? 0,
                'ret1' => $metricas->ret1 ?? 0,
            ]
        );
    }

    /**
     * Obtener incidencias de un proveedor
     */
    public function obtenerIncidencias(Request $request)
    {
        $id_proveedor = $request->get('id_proveedor');
        $año = $request->get('año');
        $mes = $request->get('mes');

        $incidencias = IncidenciaProveedor::where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->orderBy('fecha_incidencia', 'desc')
            ->get();

        return response()->json($incidencias);
    }
    
    /**
     * Guardar una nueva devolución de proveedor
     */
    public function guardarDevolucion(Request $request)
    {
        try {
            $request->validate([
                'codigo_producto' => 'required|string|max:255',
                'nombre_proveedor' => 'required|string|max:255',
                'codigo_proveedor' => 'nullable|string|max:255',
                'descripcion_producto' => 'nullable|string',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
                'np' => 'nullable|string|max:255',
                'año' => 'required|integer',
                'mes' => 'required|integer|between:1,12',
                'fecha_reclamacion' => 'nullable|date',
                'top100fy2' => 'nullable|string|max:255',
                'descripcion_motivo' => 'nullable|string',
                'especificacion_motivo_reclamacion_leve' => 'nullable|string',
                'especificacion_motivo_reclamacion_grave' => 'nullable|string',
                'recuperamos_objeto_extraño' => 'nullable|in:Si,No',
                'descripcion_queja' => 'nullable|string',
                'nombre_tienda' => 'nullable|string|max:255',
                'no_queja' => 'nullable|string|max:255',
                'origen' => 'nullable|string|max:255',
                'lote_sirena' => 'nullable|string|max:255',
                'lote_proveedor' => 'nullable|string|max:255',
                'informe_a_proveedor' => 'nullable|in:Si,No',
                'informe' => 'nullable|string',
                'fecha_envio_proveedor' => 'nullable|date',
                'fecha_respuesta_proveedor' => 'nullable|date',
                'informe_respuesta' => 'nullable|string',
                'tipo_reclamacion' => 'nullable|string|max:255',
                'comentarios' => 'nullable|string',
                'fecha_reclamacion_respuesta' => 'nullable|date',
                'abierto' => 'nullable|in:Si,No'
            ]);

            // Calcular tiempo de respuesta si hay fechas
            $tiempo_respuesta = null;
            if ($request->fecha_envio_proveedor && $request->fecha_respuesta_proveedor) {
                $fecha_envio = \Carbon\Carbon::parse($request->fecha_envio_proveedor);
                $fecha_respuesta = \Carbon\Carbon::parse($request->fecha_respuesta_proveedor);
                $dias = $fecha_envio->diffInDays($fecha_respuesta);
                $tiempo_respuesta = $dias . ' días';
            }

            // Crear la devolución
            $devolucion = DevolucionProveedor::create([
                'codigo_producto' => $request->codigo_producto,
                'nombre_proveedor' => $request->nombre_proveedor,
                'codigo_proveedor' => $request->codigo_proveedor,
                'descripcion_producto' => $request->descripcion_producto,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'np' => $request->np,
                'año' => $request->año,
                'mes' => $request->mes,
                'fecha_reclamacion' => $request->fecha_reclamacion,
                'top100fy2' => $request->top100fy2,
                'descripcion_motivo' => $request->descripcion_motivo,
                'especificacion_motivo_reclamacion_leve' => $request->especificacion_motivo_reclamacion_leve,
                'especificacion_motivo_reclamacion_grave' => $request->especificacion_motivo_reclamacion_grave,
                'recuperamos_objeto_extraño' => $request->recuperamos_objeto_extraño,
                'descripcion_queja' => $request->descripcion_queja,
                'nombre_tienda' => $request->nombre_tienda,
                'no_queja' => $request->no_queja,
                'origen' => $request->origen,
                'lote_sirena' => $request->lote_sirena,
                'lote_proveedor' => $request->lote_proveedor,
                'informe_a_proveedor' => $request->informe_a_proveedor,
                'informe' => $request->informe,
                'fecha_envio_proveedor' => $request->fecha_envio_proveedor,
                'fecha_respuesta_proveedor' => $request->fecha_respuesta_proveedor,
                'tiempo_respuesta' => $tiempo_respuesta,
                'informe_respuesta' => $request->informe_respuesta,
                'tipo_reclamacion' => $request->tipo_reclamacion,
                'comentarios' => $request->comentarios,
                'fecha_reclamacion_respuesta' => $request->fecha_reclamacion_respuesta,
                'abierto' => $request->abierto ?? 'Si'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Devolución guardada correctamente',
                'devolucion' => $devolucion
            ]);

        } catch (\Exception $e) {
            Log::error('Error al guardar devolución: ' . $e->getMessage());
            return response()->json(['error' => 'Error al guardar la devolución'], 500);
        }
    }

    /**
     * Obtener devoluciones de un proveedor
     */
    public function obtenerDevoluciones(Request $request)
    {
        $codigo_proveedor = $request->get('codigo_proveedor');
        $año = $request->get('año');
        $mes = $request->get('mes');

        $query = DevolucionProveedor::query();

        if ($codigo_proveedor) {
            $query->where('codigo_proveedor', $codigo_proveedor);
        }
        if ($año) {
            $query->where('año', $año);
        }
        if ($mes) {
            $query->where('mes', $mes);
        }

        $devoluciones = $query->orderBy('fecha_reclamacion', 'desc')->get();

        return response()->json($devoluciones);
    }

    /**
     * Buscar proveedores para autocompletado
     */
    public function buscarProveedores(Request $request)
    {
        $term = $request->get('term');
        
        $proveedores = DB::table('material_kilos')
            ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->where('proveedores.nombre_proveedor', 'LIKE', '%' . $term . '%')
            ->select('proveedores.id_proveedor as codigo', 'proveedores.nombre_proveedor as nombre')
            ->distinct()
            ->orderBy('proveedores.nombre_proveedor', 'asc')
            ->limit(10)
            ->get();

        return response()->json($proveedores);
    }

    /**
     * Buscar productos de un proveedor
     */
    public function buscarProductosProveedor(Request $request)
    {
        $codigo_proveedor = $request->get('codigo_proveedor');
        $term = $request->get('term');
        
        $query = DB::table('material_kilos')
            ->join('materiales', 'material_kilos.codigo_material', '=', 'materiales.codigo')
            ->where('material_kilos.proveedor_id', $codigo_proveedor);
            
        if ($term) {
            $query->where(function($q) use ($term) {
                $q->where('materiales.codigo', 'LIKE', '%' . $term . '%')
                  ->orWhere('materiales.descripcion', 'LIKE', '%' . $term . '%');
            });
        }
        
        $productos = $query->select('materiales.codigo', 'materiales.descripcion')
            ->distinct()
            ->limit(10)
            ->get();

        return response()->json($productos);
    }

    /**
     * Buscar códigos de productos para autocompletar
     */
    public function buscarCodigosProductos(Request $request)
    {
        $term = $request->get('term');
        
        if (!$term) {
            return response()->json([]);
        }
        
        $productos = DB::table('material_kilos')
            ->join('materiales', 'material_kilos.codigo_material', '=', 'materiales.codigo')
            ->where('materiales.codigo', 'LIKE', '%' . $term . '%')
            ->select('materiales.codigo', 'materiales.descripcion')
            ->distinct()
            ->limit(10)
            ->get();
        
        return response()->json($productos);
    }
    
    /**
     * Buscar producto por código para obtener su nombre
     */
    public function buscarProductoPorCodigo(Request $request)
    {
        $codigo = $request->get('codigo');
        
        if (!$codigo) {
            return response()->json(['error' => 'Código de producto requerido'], 400);
        }
        
        $producto = DB::table('material_kilos')
            ->join('materiales', 'material_kilos.codigo_material', '=', 'materiales.codigo')
            ->where('materiales.codigo', $codigo)
            ->select('materiales.codigo', 'materiales.descripcion')
            ->distinct()
            ->first();
        
        if ($producto) {
            return response()->json([
                'success' => true,
                'codigo' => $producto->codigo,
                'descripcion' => $producto->descripcion
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }

    /**
     * Método de prueba para diagnosticar problemas de incidencias
     */
    public function testIncidencia(Request $request)
    {
        try {
            // Verificar si la tabla existe
            $tableExists = Schema::hasTable('incidencias_proveedores');
            
            // Verificar campos de la tabla
            $columns = [];
            if ($tableExists) {
                $columns = Schema::getColumnListing('incidencias_proveedores');
            }
            
            // Probar crear una incidencia simple
            $testData = [
                'id_proveedor' => 1,
                'nombre_proveedor' => 'Test Proveedor',
                'año' => 2025,
                'mes' => 1,
                'clasificacion_incidencia' => 'RG1'
            ];
            
            return response()->json([
                'table_exists' => $tableExists,
                'columns' => $columns,
                'test_data' => $testData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

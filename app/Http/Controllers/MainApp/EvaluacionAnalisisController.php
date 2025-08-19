<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tienda;
use App\Models\Analitica;
use App\Models\MainApp\Proveedor;
use App\Models\TendenciaSuperficie;
use App\Models\TendenciaMicro;
use App\Models\MainApp\Product;
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
        $modo = $request->input('modo_edicion', 'agregar');
        
        if ($modo === 'editar' && $request->filled('id_registro')) {
            // Actualizar registro existente
            $analitica = Analitica::find($request->id_registro);
            if ($analitica) {
                $analitica->update($request->except(['modo_edicion', 'id_registro', '_token']));
                return redirect()->back()->with('success', 'Analítica actualizada correctamente.');
            } else {
                return redirect()->back()->with('error', 'No se encontró la analítica a actualizar.');
            }
        } else {
            // Crear nuevo registro
            Analitica::create($request->except(['modo_edicion', 'id_registro']));
            return redirect()->back()->with('success', 'Analítica guardada correctamente.');
        }
    }

    public function evaluacionList(Request $request)
    {
        // Construir query base
        $query = Analitica::leftJoin('tiendas', 'analiticas.num_tienda', '=', 'tiendas.num_tienda')
            ->select('analiticas.*', 'tiendas.nombre_tienda as tienda_nombre')
            ->with('proveedor');

        // Aplicar filtros si existen
        if ($request->filled('num_tienda')) {
            $query->where('analiticas.num_tienda', 'like', '%' . $request->num_tienda . '%');
        }

        if ($request->filled('nombre_tienda')) {
            $query->where('tiendas.nombre_tienda', 'like', '%' . $request->nombre_tienda . '%');
        }

        if ($request->filled('tipo_analitica')) {
            $query->where('analiticas.tipo_analitica', 'like', '%' . $request->tipo_analitica . '%');
        }

        // Ordenar y paginar
        $analiticas = $query->orderByDesc('fecha_real_analitica')
            ->paginate(25)
            ->appends($request->query());

        $proveedores = Proveedor::select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        return view('MainApp/evaluacion_analisis.evaluacion_list', compact('analiticas', 'proveedores'));
    }

    // Vista de gestión completa con estado de vencimiento
    public function gestionAnalisis(Request $request)
    {
        // Construir query base - unión de todas las analíticas
        $analiticasQuery = Analitica::leftJoin('tiendas', 'analiticas.num_tienda', '=', 'tiendas.num_tienda')
            ->select(
                'analiticas.id',
                'analiticas.num_tienda',
                'tiendas.nombre_tienda as tienda_nombre',
                'analiticas.tipo_analitica',
                'analiticas.fecha_real_analitica',
                'analiticas.periodicidad',
                'analiticas.proveedor_id',
                DB::raw("'analitica' as tabla_origen")
            )
            ->with('proveedor');

        $superficieQuery = TendenciaSuperficie::leftJoin('tiendas', 'tendencias_superficie.tienda_id', '=', 'tiendas.id')
            ->select(
                'tendencias_superficie.id',
                'tiendas.num_tienda',
                'tiendas.nombre_tienda as tienda_nombre',
                DB::raw("'Tendencias superficie' as tipo_analitica"),
                'tendencias_superficie.fecha_muestra as fecha_real_analitica',
                DB::raw("'3 meses' as periodicidad"),
                'tendencias_superficie.proveedor_id',
                DB::raw("'superficie' as tabla_origen")
            );

        $microQuery = TendenciaMicro::leftJoin('tiendas', 'tendencias_micro.tienda_id', '=', 'tiendas.id')
            ->select(
                'tendencias_micro.id',
                'tiendas.num_tienda',
                'tiendas.nombre_tienda as tienda_nombre',
                DB::raw("'Tendencias micro' as tipo_analitica"),
                'tendencias_micro.fecha_toma_muestras as fecha_real_analitica',
                DB::raw("'1 mes' as periodicidad"),
                'tendencias_micro.proveedor_id',
                DB::raw("'micro' as tabla_origen")
            );

        // Aplicar filtros
        if ($request->filled('num_tienda')) {
            $analiticasQuery->where('analiticas.num_tienda', 'like', '%' . $request->num_tienda . '%');
            $superficieQuery->where('tiendas.num_tienda', 'like', '%' . $request->num_tienda . '%');
            $microQuery->where('tiendas.num_tienda', 'like', '%' . $request->num_tienda . '%');
        }

        if ($request->filled('nombre_tienda')) {
            $analiticasQuery->where('tiendas.nombre_tienda', 'like', '%' . $request->nombre_tienda . '%');
            $superficieQuery->where('tiendas.nombre_tienda', 'like', '%' . $request->nombre_tienda . '%');
            $microQuery->where('tiendas.nombre_tienda', 'like', '%' . $request->nombre_tienda . '%');
        }

        if ($request->filled('tipo_analitica')) {
            if ($request->tipo_analitica == 'Resultados agua') {
                $superficieQuery = $superficieQuery->whereRaw('1=0'); // Excluir
                $microQuery = $microQuery->whereRaw('1=0'); // Excluir
            } elseif ($request->tipo_analitica == 'Tendencias superficie') {
                $analiticasQuery = $analiticasQuery->whereRaw('1=0'); // Excluir
                $microQuery = $microQuery->whereRaw('1=0'); // Excluir
            } elseif ($request->tipo_analitica == 'Tendencias micro') {
                $analiticasQuery = $analiticasQuery->whereRaw('1=0'); // Excluir
                $superficieQuery = $superficieQuery->whereRaw('1=0'); // Excluir
            }
        }

        // Unir todas las consultas
        $unionQuery = $analiticasQuery->union($superficieQuery)->union($microQuery);

        // Ejecutar y paginar
        $resultados = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery->getQuery())
            ->orderByDesc('fecha_real_analitica')
            ->paginate(25)
            ->appends($request->query());

        // Calcular estado de vencimiento para cada resultado
        $hoy = now();
        foreach ($resultados as $resultado) {
            if ($resultado->fecha_real_analitica) {
                $fecha = Carbon::parse($resultado->fecha_real_analitica);
                $periodo = $resultado->periodicidad;
                
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
                
                $resultado->vencido = $hoy->greaterThan($fechaLimite);
                $resultado->fecha_limite = $fechaLimite->format('Y-m-d');
                $resultado->dias_restantes = $hoy->diffInDays($fechaLimite, false);
            } else {
                $resultado->vencido = true;
                $resultado->fecha_limite = null;
                $resultado->dias_restantes = null;
            }

            // Determinar si la entrada ya tiene datos/resultados asociados (fecha_realizacion)
            $resultado->fecha_realizacion = null;
            $resultado->realizada = false;

            try {
                if (isset($resultado->tabla_origen) && $resultado->tabla_origen === 'analitica') {
                    // Buscar la analítica por id y revisar campo fecha_realizacion o registros vinculados
                    $anal = Analitica::find($resultado->id);
                    if ($anal) {
                        if (!empty($anal->fecha_realizacion)) {
                            $resultado->fecha_realizacion = $anal->fecha_realizacion;
                            $resultado->realizada = true;
                        } else {
                            // Comprobar si hay tendencias vinculadas a esta analítica
                            $ts = TendenciaSuperficie::where('analitica_id', $anal->id)->orderByDesc('created_at')->first();
                            if ($ts) {
                                $resultado->fecha_realizacion = $ts->fecha_muestra ?? ($ts->created_at ? $ts->created_at->format('Y-m-d') : null);
                                $resultado->realizada = true;
                            } else {
                                $tm = TendenciaMicro::where('analitica_id', $anal->id)->orderByDesc('created_at')->first();
                                if ($tm) {
                                    $resultado->fecha_realizacion = $tm->fecha_toma_muestras ?? ($tm->created_at ? $tm->created_at->format('Y-m-d') : null);
                                    $resultado->realizada = true;
                                }
                            }
                        }
                    }
                } elseif (isset($resultado->tabla_origen) && $resultado->tabla_origen === 'superficie') {
                    // Este registro ya proviene de TendenciaSuperficie -> considerar realizada
                    $resultado->fecha_realizacion = $resultado->fecha_real_analitica ?: null;
                    $resultado->realizada = true;
                } elseif (isset($resultado->tabla_origen) && $resultado->tabla_origen === 'micro') {
                    // Registro de TendenciaMicro
                    $resultado->fecha_realizacion = $resultado->fecha_real_analitica ?: null;
                    $resultado->realizada = true;
                }
            } catch (\Exception $e) {
                // No interrumpir la carga si alguna comprobación lanza error
                $resultado->fecha_realizacion = $resultado->fecha_realizacion ?? null;
                $resultado->realizada = $resultado->realizada ?? false;
            }
        }

        $proveedores = Proveedor::select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        return view('MainApp/evaluacion_analisis.gestion_analisis', compact('resultados', 'proveedores'));
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
            'analitica_id' => 'nullable|exists:analiticas,id',
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

        // Aceptar analitica_id si viene en el request
        if ($request->filled('analitica_id')) {
            $data['analitica_id'] = $request->input('analitica_id');
        }

        // Eliminar num_tienda si existe para evitar columnas inesperadas
        if (isset($data['num_tienda'])) unset($data['num_tienda']);

        $modo = $request->input('modo_edicion', 'agregar');
        
        if ($modo === 'editar' && $request->filled('id_registro')) {
            // Actualizar registro existente
            $tendencia = TendenciaSuperficie::find($request->id_registro);
            if ($tendencia) {
                $tendencia->update($data);
                return redirect()->back()->with('success', 'Tendencia superficie actualizada correctamente.');
            } else {
                return redirect()->back()->with('error', 'No se encontró la tendencia superficie a actualizar.');
            }
        } else {
            // Crear nuevo registro
            TendenciaSuperficie::create($data);
            return redirect()->back()->with('success', 'Tendencia superficie guardada correctamente.');
        }
    }

    // Listar tendencias micro
    public function tendenciasMicroList()
    {
        $tendencias = TendenciaMicro::with(['tienda', 'proveedor'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('MainApp.evaluacion_analisis.tendencias_micro_list', compact('tendencias'));
    }

    // Guardar tendencia micro
    public function guardarTendenciaMicro(Request $request)
    {
        $request->validate([
            'fecha_toma_muestras' => 'required|date',
            'analitica_id' => 'nullable|exists:analiticas,id',
            'codigo_producto' => 'nullable|string',
            'codigo_proveedor' => 'nullable|string',
        ]);

        $data = $request->all();

        // Si no se envía tienda_id pero sí num_tienda, buscar el id correspondiente
        if (empty($data['tienda_id']) && !empty($data['num_tienda'])) {
            $ti = Tienda::where('num_tienda', $data['num_tienda'])->first();
            if ($ti) {
                $data['tienda_id'] = $ti->id;
            }
        }

        // Aceptar analitica_id si viene en el request
        if ($request->filled('analitica_id')) {
            $data['analitica_id'] = $request->input('analitica_id');
        }

        // Eliminar num_tienda si existe para evitar columnas inesperadas
        if (isset($data['num_tienda'])) unset($data['num_tienda']);

        $modo = $request->input('modo_edicion', 'agregar');
        
        if ($modo === 'editar' && $request->filled('id_registro')) {
            // Actualizar registro existente
            $tendencia = TendenciaMicro::find($request->id_registro);
            if ($tendencia) {
                $tendencia->update($data);
                return redirect()->back()->with('success', 'Tendencia micro actualizada correctamente.');
            } else {
                return redirect()->back()->with('error', 'No se encontró la tendencia micro a actualizar.');
            }
        } else {
            // Crear nuevo registro
            TendenciaMicro::create($data);
            return redirect()->back()->with('success', 'Tendencia micro guardada correctamente.');
        }
    }

    // Buscar producto por código
    public function buscarProducto(Request $request)
    {
        $codigo = $request->input('codigo');
        $producto = Product::where('product_cod', $codigo)->first();
        
        if ($producto) {
            return response()->json([
                'success' => true,
                'producto' => [
                    'codigo' => $producto->product_cod,
                    'descripcion' => $producto->product_description
                ]
            ]);
        }
        
        return response()->json(['success' => false]);
    }

    // Buscar proveedor por código
    public function buscarProveedor(Request $request)
    {
        $codigo = $request->input('codigo');
        $proveedor = Proveedor::where('id_proveedor', $codigo)->first();
        
        if ($proveedor) {
            return response()->json([
                'success' => true,
                'proveedor' => [
                    'codigo' => $proveedor->id_proveedor,
                    'nombre' => $proveedor->nombre_proveedor
                ]
            ]);
        }
        
        return response()->json(['success' => false]);
    }

    // Obtener datos específicos para edición
    public function obtenerDatosAnalisis(Request $request)
    {
        $tipo = $request->tipo;
        $id = $request->id;
        $num_tienda = $request->num_tienda;
        
        // Si solo se envía ID (sin tipo), intentar buscar en la tabla analiticas
        if ($id && !$tipo) {
            $datos = Analitica::with('proveedor')->find($id);
            if ($datos) {
                return response()->json(['success' => true, 'analitica' => $datos]);
            }
            return response()->json(['success' => false, 'message' => 'Analítica no encontrada']);
        }
        
        // Si se proporciona num_tienda y tipo, buscar por esos campos
        if ($num_tienda && $tipo) {
            switch ($tipo) {
                case 'Resultados agua':
                    $datos = Analitica::where('num_tienda', $num_tienda)->with('proveedor')->first();
                    break;
                case 'Tendencias superficie':
                    // Buscar por tienda_id relacionando con num_tienda
                    $tienda = Tienda::where('num_tienda', $num_tienda)->first();
                    if ($tienda) {
                        $datos = TendenciaSuperficie::where('tienda_id', $tienda->id)->with(['tienda', 'proveedor'])->first();
                    } else {
                        $datos = null;
                    }
                    break;
                case 'Tendencias micro':
                    // Buscar por tienda_id relacionando con num_tienda
                    $tienda = Tienda::where('num_tienda', $num_tienda)->first();
                    if ($tienda) {
                        $datos = TendenciaMicro::where('tienda_id', $tienda->id)->with(['tienda', 'proveedor'])->first();
                    } else {
                        $datos = null;
                    }
                    break;
                default:
                    return response()->json(['success' => false]);
            }
        } else if ($id) {
            // Búsqueda original por ID
            switch ($tipo) {
                case 'analitica':
                    $datos = Analitica::with('proveedor')->find($id);
                    break;
                case 'superficie':
                    $datos = TendenciaSuperficie::with(['tienda', 'proveedor'])->find($id);
                    break;
                case 'micro':
                    $datos = TendenciaMicro::with(['tienda', 'proveedor'])->find($id);
                    break;
                default:
                    return response()->json(['success' => false]);
            }
        } else {
            return response()->json(['success' => false]);
        }
        
        if ($datos) {
            return response()->json(['success' => true, 'data' => $datos]);
        }
        
        return response()->json(['success' => false]);
    }

    // Actualizar analítica
    public function actualizarAnalisis(Request $request)
    {
        $tipo = $request->tipo;
        $id = $request->id;
        
        switch ($tipo) {
            case 'analitica':
                $modelo = Analitica::find($id);
                if ($modelo) {
                    $modelo->update($request->except(['tipo', 'id']));
                    return redirect()->back()->with('success', 'Analítica actualizada correctamente.');
                }
                break;
            case 'superficie':
                $modelo = TendenciaSuperficie::find($id);
                if ($modelo) {
                    $modelo->update($request->except(['tipo', 'id']));
                    return redirect()->back()->with('success', 'Tendencia superficie actualizada correctamente.');
                }
                break;
            case 'micro':
                $modelo = TendenciaMicro::find($id);
                if ($modelo) {
                    $modelo->update($request->except(['tipo', 'id']));
                    return redirect()->back()->with('success', 'Tendencia micro actualizada correctamente.');
                }
                break;
        }
        
        return redirect()->back()->with('error', 'No se pudo actualizar el registro.');
    }

    // Eliminar analítica
    public function eliminarAnalisis(Request $request)
    {
        $tipo = $request->tipo;
        $id = $request->id;
        
        switch ($tipo) {
            case 'analitica':
                $modelo = Analitica::find($id);
                if ($modelo) {
                    $modelo->delete();
                    return redirect()->back()->with('success', 'Analítica eliminada correctamente.');
                }
                break;
            case 'superficie':
                $modelo = TendenciaSuperficie::find($id);
                if ($modelo) {
                    $modelo->delete();
                    return redirect()->back()->with('success', 'Tendencia superficie eliminada correctamente.');
                }
                break;
            case 'micro':
                $modelo = TendenciaMicro::find($id);
                if ($modelo) {
                    $modelo->delete();
                    return redirect()->back()->with('success', 'Tendencia micro eliminada correctamente.');
                }
                break;
        }
        
        return redirect()->back()->with('error', 'No se pudo eliminar el registro.');
    }
}

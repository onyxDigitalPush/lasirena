<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Tienda;
use App\Models\Analitica;
use App\Models\MainApp\Proveedor;
use App\Models\TendenciaSuperficie;
use App\Models\TendenciaMicro;
use App\Models\MainApp\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
                $data = $request->except(['modo_edicion', 'id_registro', '_token', 'crear_siguiente', 'siguiente_fecha_teorica', 'siguiente_tipo', 'siguiente_proveedor_id', 'siguiente_periodicidad', 'siguiente_asesor_externo_nombre', 'siguiente_asesor_externo_empresa']);
                
                // Si se cambió el estado a realizada, registrar la fecha
                if (isset($data['estado_analitica']) && $data['estado_analitica'] === 'realizada' && 
                    $analitica->estado_analitica !== 'realizada') {
                    $data['fecha_cambio_estado'] = now();
                }
                
                $analitica->update($data);
                
                // Si se solicita crear la siguiente analítica automáticamente
                if ($request->filled('crear_siguiente') && $request->input('crear_siguiente') == '1') {
                    // Verificar si procede antes de crear la siguiente analítica
                    $procedeCrearSiguiente = $analitica->procede ?? null;
                    
                    if ($procedeCrearSiguiente === 1) {
                        try {
                            $siguienteData = [
                                'num_tienda' => $analitica->num_tienda,
                                'tipo_analitica' => $request->input('siguiente_tipo'),
                                'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                                'periodicidad' => $request->input('siguiente_periodicidad'),
                                'proveedor_id' => $request->input('siguiente_proveedor_id'),
                                'asesor_externo_nombre' => $request->input('siguiente_asesor_externo_nombre', ''),
                                'asesor_externo_empresa' => $request->input('siguiente_asesor_externo_empresa', ''),
                                'estado_analitica' => 'sin_iniciar',
                                'observaciones' => 'Creada automáticamente al marcar la anterior como realizada'
                            ];
                            
                            Analitica::create($siguienteData);
                            
                            return redirect()->back()->with('success', 'Analítica actualizada correctamente y siguiente analítica creada automáticamente para el ' . $request->input('siguiente_fecha_teorica') . '.');
                        } catch (\Exception $e) {
                            Log::error('Error creando siguiente analítica automáticamente: ' . $e->getMessage());
                            return redirect()->back()->with('warning', 'Analítica actualizada correctamente, pero hubo un error al crear la siguiente automáticamente.');
                        }
                    } else {
                        // No procede crear la siguiente analítica
                        $mensaje = $procedeCrearSiguiente === 0 ? 
                            'Analítica actualizada correctamente. No se creó la siguiente analítica porque está marcada como "No procede".' :
                            'Analítica actualizada correctamente. No se creó la siguiente analítica porque el campo "Procede" no está definido.';
                        return redirect()->back()->with('info', $mensaje);
                    }
                }
                
                return redirect()->back()->with('success', 'Analítica actualizada correctamente.');
            } else {
                return redirect()->back()->with('error', 'No se encontró la analítica a actualizar.');
            }
        }

        // Duplicar: clonar una analítica existente a otra tienda/proveedor
        if ($modo === 'duplicar' && $request->filled('id_registro')) {
            $origenId = $request->input('id_registro');
            $origen = Analitica::find($origenId);
            if (!$origen) {
                return redirect()->back()->with('error', 'No se encontró la analítica origen.');
            }

            // Determinar si la analítica origen ya está realizada -> prohibir duplicar
            $esRealizada = false;
            if ($origen->estado_analitica === 'realizada') {
                $esRealizada = true;
            } else if (!empty($origen->fecha_realizacion)) {
                $esRealizada = true;
            } else {
                $esRealizada = TendenciaSuperficie::where('analitica_id', $origen->id)->exists() || TendenciaMicro::where('analitica_id', $origen->id)->exists();
            }
            if ($esRealizada) {
                return redirect()->back()->with('error', 'No se puede duplicar una analítica que ya está realizada.');
            }

            // Preparar datos para el clon: tomar los campos fillable de la analítica origen
            $fillable = (new Analitica())->getFillable();
            $origenArr = $origen->toArray();
            $data = array_intersect_key($origenArr, array_flip($fillable));

            // Sobrescribir con los valores seleccionados en el formulario de duplicado
            if ($request->filled('num_tienda')) {
                $data['num_tienda'] = $request->input('num_tienda');
            }
            if ($request->filled('proveedor_id')) {
                $data['proveedor_id'] = $request->input('proveedor_id');
            }
            // Fecha real de la analítica (si el usuario la envía en el modal de duplicado)
            if ($request->filled('fecha_real_analitica')) {
                $data['fecha_real_analitica'] = $request->input('fecha_real_analitica');
            }

            // Asegurarse de no copiar campos relacionados con el estado realizada
            $data['fecha_realizacion'] = null;
            $data['fecha_cambio_estado'] = null;
            $data['estado_analitica'] = 'sin_iniciar'; // Nueva analítica siempre inicia sin iniciar

            // Crear el clon
            try {
                Analitica::create($data);
                return redirect()->back()->with('success', 'Analítica duplicada correctamente.');
            } catch (\Exception $e) {
                Log::error('Error duplicando analítica: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Error al duplicar la analítica.');
            }
        }

        else {
            // Crear nuevo registro
            $data = $request->except(['modo_edicion', 'id_registro', '_token', 'crear_siguiente', 'siguiente_fecha_teorica', 'siguiente_tipo', 'siguiente_proveedor_id', 'siguiente_periodicidad', 'siguiente_asesor_externo_nombre', 'siguiente_asesor_externo_empresa']);
            
            // Si se marca como realizada desde el inicio, registrar la fecha
            if (isset($data['estado_analitica']) && $data['estado_analitica'] === 'realizada') {
                $data['fecha_cambio_estado'] = now();
            }
            
            $analitica = Analitica::create($data);
            
            // Si se solicita crear la siguiente analítica automáticamente
            if ($request->filled('crear_siguiente') && $request->input('crear_siguiente') == '1') {
                // Verificar si procede antes de crear la siguiente analítica
                $procedeCrearSiguiente = $analitica->procede ?? null;
                
                if ($procedeCrearSiguiente === 1) {
                    try {
                        $siguienteData = [
                            'num_tienda' => $data['num_tienda'],
                            'tipo_analitica' => $request->input('siguiente_tipo'),
                            'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                            'periodicidad' => $request->input('siguiente_periodicidad'),
                            'proveedor_id' => $request->input('siguiente_proveedor_id'),
                            'asesor_externo_nombre' => $request->input('siguiente_asesor_externo_nombre', ''),
                            'asesor_externo_empresa' => $request->input('siguiente_asesor_externo_empresa', ''),
                            'estado_analitica' => 'sin_iniciar',
                            'observaciones' => 'Creada automáticamente al marcar la anterior como realizada'
                        ];
                        
                        Analitica::create($siguienteData);
                        
                        return redirect()->back()->with('success', 'Analítica guardada correctamente y siguiente analítica creada automáticamente para el ' . $request->input('siguiente_fecha_teorica') . '.');
                    } catch (\Exception $e) {
                        Log::error('Error creando siguiente analítica automáticamente: ' . $e->getMessage());
                        return redirect()->back()->with('warning', 'Analítica guardada correctamente, pero hubo un error al crear la siguiente automáticamente.');
                    }
                } else {
                    // No procede crear la siguiente analítica
                    $mensaje = $procedeCrearSiguiente === 0 ? 
                        'Analítica guardada correctamente. No se creó la siguiente analítica porque está marcada como "No procede".' :
                        'Analítica guardada correctamente. No se creó la siguiente analítica porque el campo "Procede" no está definido.';
                    return redirect()->back()->with('info', $mensaje);
                }
            }
            
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

        // Obtener lista de tiendas para la opción "Duplicar"
        $tiendas = Tienda::select('num_tienda', 'nombre_tienda')
            ->orderBy('nombre_tienda')
            ->get();

        return view('MainApp/evaluacion_analisis.evaluacion_list', compact('analiticas', 'proveedores', 'tiendas'));
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

        // Limitar analíticas a las que ya están diligenciadas (tienen fecha de realización)
        // y excluir las que además tienen registros en tendencias_* para evitar duplicados
        $analiticasQuery->where(function($q) {
            if (Schema::hasColumn('analiticas', 'fecha_realizacion')) {
                // Sólo incluir analíticas con fecha_realizacion definida
                $q->whereNotNull('analiticas.fecha_realizacion');
            } else {
                // Fallback: si no existe la columna fecha_realizacion, usar fecha_real_analitica
                $q->whereNotNull('analiticas.fecha_real_analitica');
            }

            // Excluir las anal edticas que tengan tendencias vinculadas para evitar
            // que aparezcan duplicadas (esas se mostrar e1n a trav e9s de las queries de tendencias).
            $q->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('tendencias_superficie')
                    ->whereColumn('tendencias_superficie.analitica_id', 'analiticas.id');
            });

            $q->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('tendencias_micro')
                    ->whereColumn('tendencias_micro.analitica_id', 'analiticas.id');
            });
        });

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

    // Filtrar resultados: eliminar entradas que estén "vigentes" (no vencidas)
    // y que además no tengan fecha_realizacion (es decir, sin resultados asociados).
    // Esto evita mostrar filas vacías/irrelevantes en la gestión.

    try {
            $items = collect($resultados->items())->filter(function($r) {
                // Si el registro NO está vencido (vencido == false) y no tiene fecha_realizacion,
                // entonces lo consideramos 'vigente sin realización' y lo excluimos.
                $vencido = isset($r->vencido) ? (bool)$r->vencido : false;
                $fecha_realizacion = isset($r->fecha_realizacion) ? $r->fecha_realizacion : null;
                return !(!$vencido && empty($fecha_realizacion));
            });

            // Reconstruir paginador con los items filtrados (mismo page/perPage/query params)
            if ($items->isEmpty()) {
                // Empty page
                $resultados = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $resultados->perPage(), $resultados->currentPage(), ['path' => request()->url(), 'query' => request()->query()]);
            } else {
                $resultados = new \Illuminate\Pagination\LengthAwarePaginator($items->values(), $items->count(), $resultados->perPage(), $resultados->currentPage(), ['path' => request()->url(), 'query' => request()->query()]);
            }
    } catch (\Exception $e) {
            // Si algo falla en el filtrado, dejamos los resultados originales y registramos el error.
            Log::error('Error filtrando resultados en gestionAnalisis: ' . $e->getMessage());
    }

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
            'estado_analitica' => 'nullable|in:sin_iniciar,pendiente,realizada',

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

        // Debug: registrar si se solicita crear siguiente analítica
        if ($request->filled('crear_siguiente')) {
            Log::info('guardarTendenciaSuperficie - crear_siguiente presente. Request keys: ' . implode(',', array_keys($request->all())));
        }

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
    // Guardar num_tienda original (si existe) para posibles creaciones de analíticas automáticas
    $numTiendaOriginal = isset($data['num_tienda']) ? $data['num_tienda'] : null;

        // Aceptar analitica_id si viene en el request
        if ($request->filled('analitica_id')) {
            $data['analitica_id'] = $request->input('analitica_id');
        }

        // Eliminar num_tienda si existe para evitar columnas inesperadas
    if (isset($data['num_tienda'])) unset($data['num_tienda']);

        // Si se cambió el estado a realizada, registrar la fecha
        if (isset($data['estado_analitica']) && $data['estado_analitica'] === 'realizada') {
            $data['fecha_cambio_estado'] = now();
        }

        $modo = $request->input('modo_edicion', 'agregar');
        
        if ($modo === 'editar' && $request->filled('id_registro')) {
            // Actualizar registro existente
            $tendencia = TendenciaSuperficie::find($request->id_registro);
            if ($tendencia) {
                // Verificar si se cambió el estado para registrar fecha
                if (isset($data['estado_analitica']) && $data['estado_analitica'] === 'realizada' && 
                    $tendencia->estado_analitica !== 'realizada') {
                    $data['fecha_cambio_estado'] = now();
                }
                
                $tendencia->update($data);

                // También actualizar el estado en la analítica principal si existe
                if ($tendencia->analitica_id) {
                    $analitica = Analitica::find($tendencia->analitica_id);
                    if ($analitica && isset($data['estado_analitica'])) {
                        $analitica->cambiarEstado($data['estado_analitica']);
                    }
                }

                // Si se solicita crear la siguiente analítica automáticamente desde este formulario
                if ($request->filled('crear_siguiente') && $request->input('crear_siguiente') == '1') {
                    // Verificar si procede en la analítica asociada
                    $analitica = $tendencia->analitica_id ? Analitica::find($tendencia->analitica_id) : null;
                    $procedeCrearSiguiente = $analitica ? $analitica->procede : null;
                    
                    if ($procedeCrearSiguiente === 1) {
                        try {
                            $siguienteData = [
                                'num_tienda' => $numTiendaOriginal ?: ($tendencia->tienda_id ? Tienda::where('id', $tendencia->tienda_id)->value('num_tienda') : null),
                                'tipo_analitica' => $request->input('siguiente_tipo'),
                                'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                                'periodicidad' => $request->input('siguiente_periodicidad'),
                                'proveedor_id' => $request->input('siguiente_proveedor_id'),
                                'asesor_externo_nombre' => $request->input('siguiente_asesor_externo_nombre', ''),
                                'asesor_externo_empresa' => $request->input('siguiente_asesor_externo_empresa', ''),
                                'estado_analitica' => 'sin_iniciar',
                                'observaciones' => 'Creada automáticamente al marcar la anterior como realizada desde Tendencias Superficie'
                            ];
                            // Solo crear si tenemos num_tienda
                            if (!empty($siguienteData['num_tienda'])) {
                                Analitica::create($siguienteData);
                            } else {
                                Log::warning('No se pudo crear analítica automática (num_tienda faltante) en guardarTendenciaSuperficie', $siguienteData);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error creando siguiente analítica automáticamente desde Tendencias Superficie: ' . $e->getMessage());
                        }
                    } else {
                        // Log para indicar que no se creó por el campo procede
                        $razon = $procedeCrearSiguiente === 0 ? 'marcada como "No procede"' : 'campo "Procede" no definido';
                        Log::info("No se creó siguiente analítica automática desde Tendencias Superficie: {$razon}");
                    }
                }

                return redirect()->back()->with('success', 'Tendencia superficie actualizada correctamente.');
            } else {
                return redirect()->back()->with('error', 'No se encontró la tendencia superficie a actualizar.');
            }
        } else {
            // Crear nuevo registro
            $tendencia = TendenciaSuperficie::create($data);

            // También actualizar el estado en la analítica principal si existe
            if ($tendencia->analitica_id && isset($data['estado_analitica'])) {
                $analitica = Analitica::find($tendencia->analitica_id);
                if ($analitica) {
                    $analitica->cambiarEstado($data['estado_analitica']);
                }
            }

            // Si se solicita crear la siguiente analítica automáticamente desde este formulario
            if ($request->filled('crear_siguiente') && $request->input('crear_siguiente') == '1') {
                // Verificar si procede en la analítica asociada
                $analitica = $tendencia->analitica_id ? Analitica::find($tendencia->analitica_id) : null;
                $procedeCrearSiguiente = $analitica ? $analitica->procede : null;
                
                if ($procedeCrearSiguiente === 1) {
                    try {
                        $siguienteData = [
                            'num_tienda' => $numTiendaOriginal ?: ($tendencia->tienda_id ? Tienda::where('id', $tendencia->tienda_id)->value('num_tienda') : null),
                            'tipo_analitica' => $request->input('siguiente_tipo'),
                            'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                            'periodicidad' => $request->input('siguiente_periodicidad'),
                            'proveedor_id' => $request->input('siguiente_proveedor_id'),
                            'asesor_externo_nombre' => $request->input('siguiente_asesor_externo_nombre', ''),
                            'asesor_externo_empresa' => $request->input('siguiente_asesor_externo_empresa', ''),
                            'estado_analitica' => 'sin_iniciar',
                            'observaciones' => 'Creada automáticamente al marcar la anterior como realizada desde Tendencias Superficie'
                        ];
                        if (!empty($siguienteData['num_tienda'])) {
                            Analitica::create($siguienteData);
                        } else {
                            Log::warning('No se pudo crear analítica automática (num_tienda faltante) en guardarTendenciaSuperficie (nuevo)', $siguienteData);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error creando siguiente analítica automáticamente desde Tendencias Superficie (nuevo): ' . $e->getMessage());
                    }
                } else {
                    // Log para indicar que no se creó por el campo procede
                    $razon = $procedeCrearSiguiente === 0 ? 'marcada como "No procede"' : 'campo "Procede" no definido';
                    Log::info("No se creó siguiente analítica automática desde Tendencias Superficie (nuevo): {$razon}");
                }
            }

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
            'estado_analitica' => 'nullable|in:sin_iniciar,pendiente,realizada',
        ]);

        $data = $request->all();

        // Debug: registrar si se solicita crear siguiente analítica
        if ($request->filled('crear_siguiente')) {
            Log::info('guardarTendenciaMicro - crear_siguiente presente. Request keys: ' . implode(',', array_keys($request->all())));
        }

        // Guardar num_tienda original (si existe) para posibles creaciones de analíticas automáticas
        $numTiendaOriginal = isset($data['num_tienda']) ? $data['num_tienda'] : null;

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

        // Si se cambió el estado a realizada, registrar la fecha
        if (isset($data['estado_analitica']) && $data['estado_analitica'] === 'realizada') {
            $data['fecha_cambio_estado'] = now();
        }

        $modo = $request->input('modo_edicion', 'agregar');
        
        if ($modo === 'editar' && $request->filled('id_registro')) {
            // Actualizar registro existente
            $tendencia = TendenciaMicro::find($request->id_registro);
            if ($tendencia) {
                // Verificar si se cambió el estado para registrar fecha
                if (isset($data['estado_analitica']) && $data['estado_analitica'] === 'realizada' && 
                    $tendencia->estado_analitica !== 'realizada') {
                    $data['fecha_cambio_estado'] = now();
                }
                
                $tendencia->update($data);

                // También actualizar el estado en la analítica principal si existe
                if ($tendencia->analitica_id && isset($data['estado_analitica'])) {
                    $analitica = Analitica::find($tendencia->analitica_id);
                    if ($analitica) {
                        $analitica->cambiarEstado($data['estado_analitica']);
                    }
                }

                // Si se solicita crear la siguiente analítica automáticamente desde este formulario
                if ($request->filled('crear_siguiente') && $request->input('crear_siguiente') == '1') {
                    // Verificar el campo procede en la analítica asociada antes de duplicar
                    $analiticaAsociada = $tendencia->analitica_id ? Analitica::find($tendencia->analitica_id) : null;
                    $procede = $analiticaAsociada ? $analiticaAsociada->procede : 1; // Default a 1 si no hay analítica asociada
                    
                    Log::info('Verificando procede en guardarTendenciaMicro (editar)', [
                        'analitica_id' => $tendencia->analitica_id,
                        'procede' => $procede,
                        'crear_siguiente' => $request->input('crear_siguiente')
                    ]);
                    
                    if ($procede == 1) {
                        try {
                            $siguienteData = [
                                'num_tienda' => $numTiendaOriginal ?: ($tendencia->tienda_id ? Tienda::where('id', $tendencia->tienda_id)->value('num_tienda') : null),
                                'tipo_analitica' => $request->input('siguiente_tipo'),
                                'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                                'periodicidad' => $request->input('siguiente_periodicidad'),
                                'proveedor_id' => $request->input('siguiente_proveedor_id'),
                                'asesor_externo_nombre' => $request->input('siguiente_asesor_externo_nombre', ''),
                                'asesor_externo_empresa' => $request->input('siguiente_asesor_externo_empresa', ''),
                                'estado_analitica' => 'sin_iniciar',
                                'observaciones' => 'Creada automáticamente al marcar la anterior como realizada desde Tendencias Micro'
                            ];
                            if (!empty($siguienteData['num_tienda'])) {
                                Analitica::create($siguienteData);
                                Log::info('Analítica siguiente creada automáticamente desde guardarTendenciaMicro (editar)');
                            } else {
                                Log::warning('No se pudo crear analítica automática (num_tienda faltante) en guardarTendenciaMicro', $siguienteData);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error creando siguiente analítica automáticamente desde Tendencias Micro: ' . $e->getMessage());
                        }
                    } else {
                        Log::info('Auto-duplicación cancelada - procede=0 en guardarTendenciaMicro (editar)', [
                            'analitica_id' => $tendencia->analitica_id
                        ]);
                    }
                }

                return redirect()->back()->with('success', 'Tendencia micro actualizada correctamente.');
            } else {
                return redirect()->back()->with('error', 'No se encontró la tendencia micro a actualizar.');
            }
        } else {
            // Crear nuevo registro
            $tendencia = TendenciaMicro::create($data);

            // También actualizar el estado en la analítica principal si existe
            if ($tendencia->analitica_id && isset($data['estado_analitica'])) {
                $analitica = Analitica::find($tendencia->analitica_id);
                if ($analitica) {
                    $analitica->cambiarEstado($data['estado_analitica']);
                }
            }

            // Si se solicita crear la siguiente analítica automáticamente desde este formulario
            if ($request->filled('crear_siguiente') && $request->input('crear_siguiente') == '1') {
                // Verificar el campo procede en la analítica asociada antes de duplicar
                $analiticaAsociada = $tendencia->analitica_id ? Analitica::find($tendencia->analitica_id) : null;
                $procede = $analiticaAsociada ? $analiticaAsociada->procede : 1; // Default a 1 si no hay analítica asociada
                
                Log::info('Verificando procede en guardarTendenciaMicro (crear)', [
                    'analitica_id' => $tendencia->analitica_id,
                    'procede' => $procede,
                    'crear_siguiente' => $request->input('crear_siguiente')
                ]);
                
                if ($procede == 1) {
                    try {
                        $siguienteData = [
                            'num_tienda' => $numTiendaOriginal ?: ($tendencia->tienda_id ? Tienda::where('id', $tendencia->tienda_id)->value('num_tienda') : null),
                            'tipo_analitica' => $request->input('siguiente_tipo'),
                            'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                            'periodicidad' => $request->input('siguiente_periodicidad'),
                            'proveedor_id' => $request->input('siguiente_proveedor_id'),
                            'asesor_externo_nombre' => $request->input('siguiente_asesor_externo_nombre', ''),
                            'asesor_externo_empresa' => $request->input('siguiente_asesor_externo_empresa', ''),
                            'estado_analitica' => 'sin_iniciar',
                            'observaciones' => 'Creada automáticamente al marcar la anterior como realizada desde Tendencias Micro'
                        ];
                        if (!empty($siguienteData['num_tienda'])) {
                            Analitica::create($siguienteData);
                            Log::info('Analítica siguiente creada automáticamente desde guardarTendenciaMicro (crear)');
                        } else {
                            Log::warning('No se pudo crear analítica automática (num_tienda faltante) en guardarTendenciaMicro (nuevo)', $siguienteData);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error creando siguiente analítica automáticamente desde Tendencias Micro (nuevo): ' . $e->getMessage());
                    }
                } else {
                    Log::info('Auto-duplicación cancelada - procede=0 en guardarTendenciaMicro (crear)', [
                        'analitica_id' => $tendencia->analitica_id
                    ]);
                }
            }

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

        try {
            // Si solo se envía ID (sin tipo), intentar buscar en la tabla analiticas
            if ($id && !$tipo) {
                $datos = Analitica::with('proveedor')->find($id);
                if ($datos) {
                    // Determinar si la analítica ya fue realizada (fecha_realizacion o tendencias vinculadas)
                    $realizada = false;
                    if (!empty($datos->fecha_realizacion)) {
                        $realizada = true;
                    } else {
                        $realizada = TendenciaSuperficie::where('analitica_id', $datos->id)->exists() || TendenciaMicro::where('analitica_id', $datos->id)->exists();
                    }
                    $datos->realizada = $realizada;
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
                        $tienda = Tienda::where('num_tienda', $num_tienda)->first();
                        $datos = $tienda ? TendenciaSuperficie::where('tienda_id', $tienda->id)->with(['tienda', 'proveedor'])->first() : null;
                        break;
                    case 'Tendencias micro':
                        $tienda = Tienda::where('num_tienda', $num_tienda)->first();
                        $datos = $tienda ? TendenciaMicro::where('tienda_id', $tienda->id)->with(['tienda', 'proveedor'])->first() : null;
                        break;
                    default:
                        return response()->json(['success' => false]);
                }
            } elseif ($id) {
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

            if (isset($datos) && $datos) {
                // Si devolvimos una Analitica, añadir flag 'realizada'
                if ($datos instanceof Analitica) {
                    $realizada = false;
                    if (!empty($datos->fecha_realizacion)) {
                        $realizada = true;
                    } else {
                        $realizada = TendenciaSuperficie::where('analitica_id', $datos->id)->exists() || TendenciaMicro::where('analitica_id', $datos->id)->exists();
                    }
                    $datos->realizada = $realizada;
                    return response()->json(['success' => true, 'analitica' => $datos]);
                }

                return response()->json(['success' => true, 'data' => $datos]);
            }

            return response()->json(['success' => false]);
        } catch (\Exception $e) {
            // Registrar y devolver JSON claro para que el frontend muestre el error
            Log::error('Error obtenerDatosAnalisis: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error interno al obtener los datos', 'error' => $e->getMessage()], 500);
        }
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
        try {
            switch ($tipo) {
                case 'analitica':
                    $modelo = Analitica::find($id);
                    if ($modelo) {
                        // Borrar tendencias vinculadas (si existen)
                        TendenciaSuperficie::where('analitica_id', $modelo->id)->delete();
                        TendenciaMicro::where('analitica_id', $modelo->id)->delete();
                        $modelo->delete();
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => true]);
                        }
                        return redirect()->back()->with('success', 'Analítica eliminada correctamente.');
                    }
                    break;
                case 'superficie':
                    $modelo = TendenciaSuperficie::find($id);
                    if ($modelo) {
                        $modelo->delete();
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => true]);
                        }
                        return redirect()->back()->with('success', 'Tendencia superficie eliminada correctamente.');
                    }
                    break;
                case 'micro':
                    $modelo = TendenciaMicro::find($id);
                    if ($modelo) {
                        $modelo->delete();
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => true]);
                        }
                        return redirect()->back()->with('success', 'Tendencia micro eliminada correctamente.');
                    }
                    break;
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar el registro.'], 404);
            }

            return redirect()->back()->with('error', 'No se pudo eliminar el registro.');
        } catch (\Exception $e) {
            Log::error('Error eliminarAnalisis: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error interno al eliminar registro', 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error interno al eliminar registro');
        }
    }
}

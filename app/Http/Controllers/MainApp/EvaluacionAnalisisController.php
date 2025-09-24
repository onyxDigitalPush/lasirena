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
                $data = $request->except(['modo_edicion', 'id_registro', '_token', 'crear_siguiente', 'siguiente_fecha_teorica', 'siguiente_tipo', 'siguiente_proveedor_id', 'siguiente_periodicidad']);
                
                // Procesar checkboxes "no procede" y calcular el campo 'procede'
                $proveedorNoProcede = $request->has('proveedor_no_procede') ? 1 : 0;
                $periodicidadNoProcede = $request->has('periodicidad_no_procede') ? 1 : 0;
                
                $data['proveedor_no_procede'] = $proveedorNoProcede;
                $data['periodicidad_no_procede'] = $periodicidadNoProcede;
                
                // Si cualquiera de los dos "no procede" está marcado, entonces procede = 0
                $data['procede'] = ($proveedorNoProcede || $periodicidadNoProcede) ? 0 : 1;
                
                // Si proveedor no procede, limpiar proveedor_id
                if ($proveedorNoProcede) {
                    $data['proveedor_id'] = null;
                }
                
                // Si periodicidad no procede, almacenar cadena vacía para evitar constraint NOT NULL
                if ($periodicidadNoProcede) {
                    $data['periodicidad'] = '';
                }
                
                Log::info('Procesando analítica (editar) con nuevos campos', [
                    'proveedor_no_procede' => $proveedorNoProcede,
                    'periodicidad_no_procede' => $periodicidadNoProcede,
                    'procede_calculado' => $data['procede']
                ]);
                
                // Si se cambió el estado a realizada, registrar la fecha
                if (isset($data['estado_analitica']) && $data['estado_analitica'] === 'realizada' && 
                    $analitica->estado_analitica !== 'realizada') {
                    $data['fecha_cambio_estado'] = now();
                }
                Log::info('guardarAnalitica - editar payload', $data);
                $analitica->update($data);
                
                // Procesar archivos subidos en edición
                if ($request->hasFile('archivos')) {
                    $this->procesarArchivosSubidos($request, $analitica);
                }
                
                // Si se solicita crear la siguiente analítica automáticamente
                if ($request->filled('crear_siguiente') && $request->input('crear_siguiente') == '1') {
                    // Verificar si procede antes de crear la siguiente analítica
                    $procedeCrearSiguiente = $data['procede'];
                    
                    Log::info('Verificando auto-duplicación en editar analítica', [
                        'procede' => $procedeCrearSiguiente,
                        'crear_siguiente' => $request->input('crear_siguiente')
                    ]);
                    
                    if ($procedeCrearSiguiente === 1) {
                        try {
                            $siguienteData = [
                                'num_tienda' => $analitica->num_tienda,
                                'tipo_analitica' => $request->input('siguiente_tipo'),
                                'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                                'periodicidad' => $request->input('siguiente_periodicidad'),
                                    'proveedor_id' => $request->input('siguiente_proveedor_id'),
                                    'estado_analitica' => 'sin_iniciar',
                                    'observaciones' => 'Creada automáticamente al marcar la anterior como realizada'
                            ];
                            
                            Analitica::create($siguienteData);
                            Log::info('Analítica siguiente creada automáticamente desde editar');
                            
                            return redirect()->back()->with('success', 'Analítica actualizada correctamente y siguiente analítica creada automáticamente para el ' . $request->input('siguiente_fecha_teorica') . '.');
                        } catch (\Exception $e) {
                            Log::error('Error creando siguiente analítica automáticamente: ' . $e->getMessage());
                            return redirect()->back()->with('warning', 'Analítica actualizada correctamente, pero hubo un error al crear la siguiente automáticamente.');
                        }
                    } else {
                        // No procede crear la siguiente analítica
                        Log::info('Auto-duplicación cancelada - procede=0 en editar analítica');
                        return redirect()->back()->with('info', 'Analítica actualizada correctamente. No se creó la siguiente analítica porque hay campos marcados como "No procede".');
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
            $data = $request->except(['modo_edicion', 'id_registro', '_token', 'crear_siguiente', 'siguiente_fecha_teorica', 'siguiente_tipo', 'siguiente_proveedor_id', 'siguiente_periodicidad']);
            
            // Procesar checkboxes "no procede" y calcular el campo 'procede'
            $proveedorNoProcede = $request->has('proveedor_no_procede') ? 1 : 0;
            $periodicidadNoProcede = $request->has('periodicidad_no_procede') ? 1 : 0;
            
            $data['proveedor_no_procede'] = $proveedorNoProcede;
            $data['periodicidad_no_procede'] = $periodicidadNoProcede;
            
            // Si cualquiera de los dos "no procede" está marcado, entonces procede = 0
            $data['procede'] = ($proveedorNoProcede || $periodicidadNoProcede) ? 0 : 1;
            
            // Si proveedor no procede, limpiar proveedor_id
            if ($proveedorNoProcede) {
                $data['proveedor_id'] = null;
            }
            
            // Si periodicidad no procede, almacenar cadena vacía para evitar constraint NOT NULL
            if ($periodicidadNoProcede) {
                $data['periodicidad'] = '';
            }
            
            Log::info('Procesando analítica (crear) con nuevos campos', [
                'proveedor_no_procede' => $proveedorNoProcede,
                'periodicidad_no_procede' => $periodicidadNoProcede,
                'procede_calculado' => $data['procede']
            ]);
            
            // Si se marca como realizada desde el inicio, registrar la fecha
            if (isset($data['estado_analitica']) && $data['estado_analitica'] === 'realizada') {
                $data['fecha_cambio_estado'] = now();
            }
            Log::info('guardarAnalitica - crear payload', $data);
            $analitica = Analitica::create($data);
            
            // Procesar archivos subidos
            if ($request->hasFile('archivos')) {
                $this->procesarArchivosSubidos($request, $analitica);
            }
            
            // Si se solicita crear la siguiente analítica automáticamente
            if ($request->filled('crear_siguiente') && $request->input('crear_siguiente') == '1') {
                // Verificar si procede antes de crear la siguiente analítica
                $procedeCrearSiguiente = $data['procede'];
                
                Log::info('Verificando auto-duplicación en crear analítica', [
                    'procede' => $procedeCrearSiguiente,
                    'crear_siguiente' => $request->input('crear_siguiente')
                ]);
                
                if ($procedeCrearSiguiente === 1) {
                    try {
                        $siguienteData = [
                            'num_tienda' => $data['num_tienda'],
                            'tipo_analitica' => $request->input('siguiente_tipo'),
                            'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                            'periodicidad' => $request->input('siguiente_periodicidad'),
                            'proveedor_id' => $request->input('siguiente_proveedor_id'),
                            'estado_analitica' => 'sin_iniciar',
                            'observaciones' => 'Creada automáticamente al marcar la anterior como realizada'
                        ];
                        
                        Analitica::create($siguienteData);
                        Log::info('Analítica siguiente creada automáticamente desde crear');
                        
                        return redirect()->back()->with('success', 'Analítica guardada correctamente y siguiente analítica creada automáticamente para el ' . $request->input('siguiente_fecha_teorica') . '.');
                    } catch (\Exception $e) {
                        Log::error('Error creando siguiente analítica automáticamente: ' . $e->getMessage());
                        return redirect()->back()->with('warning', 'Analítica guardada correctamente, pero hubo un error al crear la siguiente automáticamente.');
                    }
                } else {
                    // No procede crear la siguiente analítica
                    Log::info('Auto-duplicación cancelada - procede=0 en crear analítica');
                    return redirect()->back()->with('info', 'Analítica guardada correctamente. No se creó la siguiente analítica porque hay campos marcados como "No procede".');
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
                'analiticas.estado_analitica',
                'analiticas.fecha_cambio_estado',
                'analiticas.proveedor_no_procede',
                'analiticas.periodicidad_no_procede',
                'analiticas.archivos',
                DB::raw('NULL as analitica_id'),
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
            ->leftJoin('analiticas', 'tendencias_superficie.analitica_id', '=', 'analiticas.id')
            ->select(
                'tendencias_superficie.id',
                'tiendas.num_tienda',
                'tiendas.nombre_tienda as tienda_nombre',
                DB::raw("'Tendencias superficie' as tipo_analitica"),
                'tendencias_superficie.fecha_muestra as fecha_real_analitica',
                'analiticas.periodicidad',
                'tendencias_superficie.estado_analitica',
                'tendencias_superficie.fecha_cambio_estado',
                'analiticas.proveedor_no_procede',
                'analiticas.periodicidad_no_procede',
                'analiticas.archivos',
                'tendencias_superficie.analitica_id as analitica_id',
                'tendencias_superficie.proveedor_id',
                DB::raw("'superficie' as tabla_origen")
            );

        $microQuery = TendenciaMicro::leftJoin('tiendas', 'tendencias_micro.tienda_id', '=', 'tiendas.id')
            ->leftJoin('analiticas', 'tendencias_micro.analitica_id', '=', 'analiticas.id')
            ->select(
                'tendencias_micro.id',
                'tiendas.num_tienda',
                'tiendas.nombre_tienda as tienda_nombre',
                DB::raw("'Tendencias micro' as tipo_analitica"),
                'tendencias_micro.fecha_toma_muestras as fecha_real_analitica',
                'analiticas.periodicidad',
                'tendencias_micro.estado_analitica',
                'tendencias_micro.fecha_cambio_estado',
                'analiticas.proveedor_no_procede',
                'analiticas.periodicidad_no_procede',
                'analiticas.archivos',
                'tendencias_micro.analitica_id as analitica_id',
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

        // Filtro por rango de fechas (fecha_real_analitica / fecha_muestra / fecha_toma_muestras)
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        // Normalizar: si ambas fechas vienen y la inicio es mayor que la fin, invertirlas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            try {
                $d1 = Carbon::parse($fechaInicio);
                $d2 = Carbon::parse($fechaFin);
                if ($d1->greaterThan($d2)) {
                    $tmp = $fechaInicio; $fechaInicio = $fechaFin; $fechaFin = $tmp;
                }
            } catch (\Exception $e) {
                // ignorar parse errors y usar valores tal cual
            }
        }

        if (!empty($fechaInicio) || !empty($fechaFin)) {
            if (!empty($fechaInicio) && !empty($fechaFin)) {
                $analiticasQuery->whereBetween('analiticas.fecha_real_analitica', [$fechaInicio, $fechaFin]);
                $superficieQuery->whereBetween('tendencias_superficie.fecha_muestra', [$fechaInicio, $fechaFin]);
                $microQuery->whereBetween('tendencias_micro.fecha_toma_muestras', [$fechaInicio, $fechaFin]);
            } elseif (!empty($fechaInicio)) {
                $analiticasQuery->where('analiticas.fecha_real_analitica', '>=', $fechaInicio);
                $superficieQuery->where('tendencias_superficie.fecha_muestra', '>=', $fechaInicio);
                $microQuery->where('tendencias_micro.fecha_toma_muestras', '>=', $fechaInicio);
            } elseif (!empty($fechaFin)) {
                $analiticasQuery->where('analiticas.fecha_real_analitica', '<=', $fechaFin);
                $superficieQuery->where('tendencias_superficie.fecha_muestra', '<=', $fechaFin);
                $microQuery->where('tendencias_micro.fecha_toma_muestras', '<=', $fechaFin);
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
                    // Si la tendencia hace referencia a una analítica, propagar flags de "no_procede"
                    if (isset($resultado->analitica_id) && $resultado->analitica_id) {
                        $linked = Analitica::find($resultado->analitica_id);
                        if ($linked) {
                            $resultado->periodicidad_no_procede = $linked->periodicidad_no_procede ?? 0;
                            $resultado->proveedor_no_procede = $linked->proveedor_no_procede ?? 0;
                            if (!empty($resultado->periodicidad_no_procede)) {
                                // Evitar que el switch de periodicidad lo trate como un periodo válido
                                $resultado->periodicidad = '';
                            }
                        }
                    }
                    $resultado->fecha_realizacion = $resultado->fecha_real_analitica ?: null;
                    $resultado->realizada = true;
                } elseif (isset($resultado->tabla_origen) && $resultado->tabla_origen === 'micro') {
                    // Si la tendencia hace referencia a una analítica, propagar flags de "no_procede"
                    if (isset($resultado->analitica_id) && $resultado->analitica_id) {
                        $linked = Analitica::find($resultado->analitica_id);
                        if ($linked) {
                            $resultado->periodicidad_no_procede = $linked->periodicidad_no_procede ?? 0;
                            $resultado->proveedor_no_procede = $linked->proveedor_no_procede ?? 0;
                            if (!empty($resultado->periodicidad_no_procede)) {
                                $resultado->periodicidad = '';
                            }
                        }
                    }
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
                $procedeCrearSiguiente = 1; // Default
                
                if ($analitica) {
                    // Calcular procede basado en los campos no_procede
                    $procedeCrearSiguiente = ($analitica->proveedor_no_procede || $analitica->periodicidad_no_procede) ? 0 : 1;
                }
                
                if ($procedeCrearSiguiente === 1) {
                    try {
                        $siguienteData = [
                            'num_tienda' => $numTiendaOriginal ?: ($tendencia->tienda_id ? Tienda::where('id', $tendencia->tienda_id)->value('num_tienda') : null),
                            'tipo_analitica' => $request->input('siguiente_tipo'),
                            'fecha_real_analitica' => $request->input('siguiente_fecha_teorica'),
                            'periodicidad' => $request->input('siguiente_periodicidad'),
                            'proveedor_id' => $request->input('siguiente_proveedor_id'),
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
                    $procede = 1; // Default
                    
                    if ($analiticaAsociada) {
                        // Calcular procede basado en los campos no_procede
                        $procede = ($analiticaAsociada->proveedor_no_procede || $analiticaAsociada->periodicidad_no_procede) ? 0 : 1;
                    }
                    
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
                $procede = 1; // Default
                
                if ($analiticaAsociada) {
                    // Calcular procede basado en los campos no_procede
                    $procede = ($analiticaAsociada->proveedor_no_procede || $analiticaAsociada->periodicidad_no_procede) ? 0 : 1;
                }
                
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
                    // Asegurar que los campos nuevos estén presentes en el payload
                    $datos->detalle_tipo = $datos->detalle_tipo ?? null;
                    $datos->codigo_producto = $datos->codigo_producto ?? ($datos->codigo ?? null);
                    $datos->descripcion_producto = $datos->descripcion_producto ?? ($datos->descripcion ?? $datos->nombre_producto ?? $datos->product_description ?? $datos->nombre ?? null);
                    
                    // Agregar archivos de la analítica
                    $datos->archivos = $datos->getArchivosArray();
                    
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
                        // Normalizar campos para modal: detalle_tipo, codigo_producto, descripcion_producto
                        $datos->detalle_tipo = $datos->detalle_tipo ?? null;
                        $datos->codigo_producto = $datos->codigo_producto ?? ($datos->codigo ?? null);
                        $datos->descripcion_producto = $datos->descripcion_producto ?? ($datos->descripcion ?? $datos->nombre_producto ?? $datos->product_description ?? $datos->nombre ?? null);
                        
                        // Agregar archivos de la analítica
                        $datos->archivos = $datos->getArchivosArray();
                        
                    return response()->json(['success' => true, 'analitica' => $datos]);
                }

                // Para TendenciaSuperficie / TendenciaMicro, mapear campos al formato esperado por el modal
                if ($datos instanceof TendenciaSuperficie || $datos instanceof TendenciaMicro) {
                    $mapped = $datos;
                    $mapped->detalle_tipo = $mapped->detalle_tipo ?? null;
                    $mapped->codigo_producto = $mapped->codigo_producto ?? ($mapped->codigo_producto ?? $mapped->codigo ?? null);
                    $mapped->descripcion_producto = $mapped->descripcion_producto ?? ($mapped->descripcion ?? $mapped->nombre_producto ?? $mapped->product_description ?? $mapped->nombre ?? null);
                    
                    // Agregar archivos
                    if (method_exists($mapped, 'getArchivosArray')) {
                        $mapped->archivos = $mapped->getArchivosArray();
                    } else {
                        // Si no tiene el método, procesar directamente
                        $archivos = [];
                        if ($mapped->archivos && is_string($mapped->archivos)) {
                            $archivos = json_decode($mapped->archivos, true) ?: [];
                        }
                        $mapped->archivos = $archivos;
                    }
                    
                    return response()->json(['success' => true, 'data' => $mapped]);
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
        
        // Validar archivos si se envían
        if ($request->hasFile('archivos')) {
            $request->validate([
                'archivos.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240'
            ]);
        }
        
        switch ($tipo) {
            case 'analitica':
                $modelo = Analitica::find($id);
                if ($modelo) {
                    $datos = $request->except(['tipo', 'id', 'archivos']);
                    
                    // Manejar archivos
                    if ($request->hasFile('archivos')) {
                        $archivosExistentes = [];
                        if ($modelo->archivos && is_string($modelo->archivos)) {
                            $archivosExistentes = json_decode($modelo->archivos, true) ?: [];
                        }
                        $nuevosArchivos = $this->procesarArchivos($request->file('archivos'), 'analiticas');
                        $datos['archivos'] = json_encode(array_merge($archivosExistentes, $nuevosArchivos));
                    }
                    
                    $modelo->update($datos);
                    return redirect()->back()->with('success', 'Analítica actualizada correctamente.');
                }
                break;
            case 'superficie':
                $modelo = TendenciaSuperficie::find($id);
                if ($modelo) {
                    $datos = $request->except(['tipo', 'id', 'archivos']);
                    
                    // Manejar archivos
                    if ($request->hasFile('archivos')) {
                        $archivosExistentes = [];
                        if ($modelo->archivos && is_string($modelo->archivos)) {
                            $archivosExistentes = json_decode($modelo->archivos, true) ?: [];
                        }
                        $nuevosArchivos = $this->procesarArchivos($request->file('archivos'), 'tendencias_superficie');
                        $datos['archivos'] = json_encode(array_merge($archivosExistentes, $nuevosArchivos));
                    }
                    
                    $modelo->update($datos);
                    return redirect()->back()->with('success', 'Tendencia superficie actualizada correctamente.');
                }
                break;
            case 'micro':
                $modelo = TendenciaMicro::find($id);
                if ($modelo) {
                    $datos = $request->except(['tipo', 'id', 'archivos']);
                    
                    // Manejar archivos
                    if ($request->hasFile('archivos')) {
                        $archivosExistentes = [];
                        if ($modelo->archivos && is_string($modelo->archivos)) {
                            $archivosExistentes = json_decode($modelo->archivos, true) ?: [];
                        }
                        $nuevosArchivos = $this->procesarArchivos($request->file('archivos'), 'tendencias_micro');
                        $datos['archivos'] = json_encode(array_merge($archivosExistentes, $nuevosArchivos));
                    }
                    
                    $modelo->update($datos);
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

    // Método privado para procesar archivos subidos
    private function procesarArchivosSubidos(Request $request, Analitica $analitica)
    {
        try {
            $archivos = $request->file('archivos');
            if (!$archivos) {
                Log::info('No se encontraron archivos en la request');
                return;
            }

            if (!is_array($archivos)) {
                $archivos = [$archivos];
            }

            Log::info('Procesando archivos subidos', [
                'analitica_id' => $analitica->id,
                'cantidad_archivos' => count($archivos),
                'archivos_existentes_antes' => count($analitica->getArchivosArray())
            ]);

            $archivosGuardados = 0;
            foreach ($archivos as $index => $archivo) {
                if ($archivo && $archivo->isValid()) {
                    Log::info('Procesando archivo', [
                        'index' => $index,
                        'nombre_original' => $archivo->getClientOriginalName(),
                        'tamaño' => $archivo->getSize()
                    ]);

                    // Validar el archivo
                    $extensionesPermitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
                    $extension = strtolower($archivo->getClientOriginalExtension());
                    
                    if (!in_array($extension, $extensionesPermitidas)) {
                        Log::warning('Extensión de archivo no permitida: ' . $extension);
                        continue;
                    }
                    
                    if ($archivo->getSize() > 10240 * 1024) { // 10MB
                        Log::warning('Archivo demasiado grande: ' . $archivo->getSize());
                        continue;
                    }
                    
                    // Generar nombre único para el archivo
                    $nombreOriginal = $archivo->getClientOriginalName();
                    $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
                    
                    // Crear directorio si no existe
                    $directorioArchivos = public_path('storage/analiticas');
                    if (!file_exists($directorioArchivos)) {
                        mkdir($directorioArchivos, 0755, true);
                    }
                    
                    // Mover archivo
                    $rutaArchivo = $directorioArchivos . '/' . $nombreArchivo;
                    $archivo->move($directorioArchivos, $nombreArchivo);
                    
                    // Agregar información del archivo al modelo
                    $infoArchivo = [
                        'nombre' => $nombreArchivo,
                        'nombre_original' => $nombreOriginal,
                        'ruta' => '/storage/analiticas/' . $nombreArchivo,
                        'tamano' => filesize($rutaArchivo),
                        'tipo' => $archivo->getClientMimeType(),
                        'fecha_subida' => now()->toDateTimeString()
                    ];
                    
                    $analitica->addArchivo($infoArchivo);
                    $archivosGuardados++;
                    Log::info('Archivo agregado al modelo', $infoArchivo);
                }
            }
            
            $analitica->save();
            Log::info('Archivos procesados correctamente', [
                'analitica_id' => $analitica->id,
                'archivos_guardados' => $archivosGuardados,
                'archivos_finales' => count($analitica->getArchivosArray())
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error procesando archivos: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    // Métodos para manejar archivos de analíticas
    public function subirArchivo(Request $request)
    {
        try {
            $request->validate([
                'analitica_id' => 'required|exists:analiticas,id',
                'archivo' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif'
            ]);

            $analitica = Analitica::find($request->analitica_id);
            $archivo = $request->file('archivo');
            
            // Generar nombre único para el archivo
            $nombreOriginal = $archivo->getClientOriginalName();
            $extension = $archivo->getClientOriginalExtension();
            $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
            
            // Crear directorio si no existe
            $directorioArchivos = public_path('storage/analiticas');
            if (!file_exists($directorioArchivos)) {
                mkdir($directorioArchivos, 0755, true);
            }
            
            // Mover archivo
            $rutaArchivo = $directorioArchivos . '/' . $nombreArchivo;
            $archivo->move($directorioArchivos, $nombreArchivo);
            
            // Agregar información del archivo al modelo
            $infoArchivo = [
                'nombre' => $nombreArchivo,
                'nombre_original' => $nombreOriginal,
                'ruta' => '/storage/analiticas/' . $nombreArchivo,
                'tamano' => filesize($rutaArchivo),
                'tipo' => $archivo->getClientMimeType(),
                'fecha_subida' => now()->toDateTimeString()
            ];
            
            $analitica->addArchivo($infoArchivo);
            $analitica->save();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo subido correctamente',
                    'archivo' => $infoArchivo
                ]);
            }
            
            return redirect()->back()->with('success', 'Archivo subido correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error subiendo archivo: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir el archivo: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al subir el archivo');
        }
    }

    public function eliminarArchivo(Request $request)
    {
        try {
            $request->validate([
                'analitica_id' => 'required|exists:analiticas,id',
                'nombre_archivo' => 'required|string'
            ]);

            $analitica = Analitica::find($request->analitica_id);
            $nombreArchivo = $request->nombre_archivo;
            
            // Buscar el archivo en la analítica
            $archivos = $analitica->getArchivosArray();
            $archivoEncontrado = null;
            
            foreach ($archivos as $archivo) {
                if (is_array($archivo) && isset($archivo['nombre']) && $archivo['nombre'] === $nombreArchivo) {
                    $archivoEncontrado = $archivo;
                    break;
                }
            }
            
            if (!$archivoEncontrado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }
            
            // Eliminar archivo físico
            $rutaCompleta = public_path('storage/analiticas/' . $nombreArchivo);
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }
            
            // Remover del modelo
            $analitica->removeArchivo($nombreArchivo);
            $analitica->save();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo eliminado correctamente'
                ]);
            }
            
            return redirect()->back()->with('success', 'Archivo eliminado correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error eliminando archivo: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el archivo: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al eliminar el archivo');
        }
    }

    public function descargarArchivo(Request $request, $analiticaId, $nombreArchivo)
    {
        try {
            $analitica = Analitica::find($analiticaId);
            
            if (!$analitica) {
                abort(404, 'Analítica no encontrada');
            }
            
            // Verificar que el archivo existe en los archivos de la analítica
            $archivos = $analitica->getArchivosArray();
            $archivoEncontrado = null;
            
            foreach ($archivos as $archivo) {
                if (is_array($archivo) && isset($archivo['nombre']) && $archivo['nombre'] === $nombreArchivo) {
                    $archivoEncontrado = $archivo;
                    break;
                }
            }
            
            if (!$archivoEncontrado) {
                abort(404, 'Archivo no encontrado');
            }
            
            $rutaCompleta = public_path('storage/analiticas/' . $nombreArchivo);
            
            if (!file_exists($rutaCompleta)) {
                abort(404, 'Archivo físico no encontrado');
            }
            
            return response()->download($rutaCompleta, isset($archivoEncontrado['nombre_original']) ? $archivoEncontrado['nombre_original'] : $nombreArchivo);
            
        } catch (\Exception $e) {
            Log::error('Error descargando archivo: ' . $e->getMessage());
            abort(500, 'Error interno al descargar el archivo');
        }
    }
}

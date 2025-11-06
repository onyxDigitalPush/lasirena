<?php

namespace App\Http\Controllers\MainApp;

use App\Models\MainApp\MaterialKilo;
use App\Models\MainApp\ProveedorMetric;
use App\Models\MainApp\IncidenciaProveedor;
use App\Models\MainApp\DevolucionProveedor;
use App\Models\MainApp\EstadoIncidenciaReclamacion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\MainApp\Proveedor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MaterialKiloController extends Controller
{
    public function index()
    {
        // Debug temporal - remover después
        $orden = request('orden');
        $filtro = request('filtro');
        Log::info('Parámetros recibidos:', ['orden' => $orden, 'filtro' => $filtro]);

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
                'material_kilos.proveedor_id'
            );

        // Aplicar filtros de búsqueda del servidor
        if (request('codigo_material')) {
            $query->where('material_kilos.codigo_material', 'LIKE', '%' . request('codigo_material') . '%');
        }

        if (request('proveedor_id')) {
            $query->where('material_kilos.proveedor_id', 'LIKE', '%' . request('proveedor_id') . '%');
        }

        if (request('nombre_proveedor')) {
            $query->where('proveedores.nombre_proveedor', 'LIKE', '%' . request('nombre_proveedor') . '%');
        }

        if (request('nombre_material')) {
            $query->where('materiales.descripcion', 'LIKE', '%' . request('nombre_material') . '%');
        }

        if (request('mes')) {
            $mesInput = request('mes');
            Log::info('Filtro mes aplicado:', ['mes_input' => $mesInput, 'type' => gettype($mesInput)]);

            // Registrar algunos valores existentes de mes en la BD para comparar
            $existingMeses = MaterialKilo::select('mes')->distinct()->limit(10)->pluck('mes')->toArray();
            Log::info('Valores existentes de mes en BD:', ['meses' => $existingMeses]);

            // Cambiar LIKE por igualdad exacta para números
            if (is_numeric($mesInput)) {
                $query->where('material_kilos.mes', '=', intval($mesInput));
                Log::info('Aplicando filtro numérico exacto:', ['mes' => intval($mesInput)]);
            } else {
                $query->where('material_kilos.mes', 'LIKE', '%' . $mesInput . '%');
                Log::info('Aplicando filtro LIKE:', ['mes' => $mesInput]);
            }
        }

        // Aplicar filtros de factor de conversión
        $filtro = request('filtro');
        if ($filtro == 'con_factor') {
            $query->whereNotNull('material_kilos.factor_conversion')
                ->where('material_kilos.factor_conversion', '>', 0);
        } elseif ($filtro == 'sin_factor') {
            $query->whereNull('material_kilos.factor_conversion');
        } elseif ($filtro == 'factor_cero') {
            $query->where('material_kilos.factor_conversion', '=', 0);
        }

        // Aplicar ordenamiento según el filtro
        $orden = request('orden');
        Log::info('Aplicando ordenamiento:', ['orden' => $orden]);

        if ($orden == 'total_kg_desc') {
            $query->orderBy('material_kilos.total_kg', 'desc');
        } elseif ($orden == 'total_kg_asc') {
            $query->orderBy('material_kilos.total_kg', 'asc');
        } elseif ($orden == 'factor_desc') {
            // Ordenar por factor de conversión de mayor a menor
            $query->orderBy('material_kilos.factor_conversion', 'desc');
        } elseif ($orden == 'factor_asc') {
            // Ordenar por factor de conversión de menor a mayor
            $query->orderBy('material_kilos.factor_conversion', 'asc');
        } else {
            $query->orderBy('material_kilos.id', 'asc');
        }

        Log::info('SQL generado:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        // Debug adicional: contar registros antes de paginar
        $totalRecords = $query->count();
        Log::info('Total de registros encontrados con filtros:', ['total' => $totalRecords]);

        $array_material_kilo = $query->paginate(25);

        // Mantener los parámetros de query en la paginación
        $array_material_kilo->appends(request()->query());

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


    public function edit($id)
    {
        try {
            $material_kilo = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
                ->join('materiales', 'material_kilos.codigo_material', '=', 'materiales.codigo')
                ->select(
                    'material_kilos.id',
                    'material_kilos.total_kg',
                    'proveedores.nombre_proveedor',
                    'proveedores.email_proveedor', // <-- AGREGA ESTO
                    'materiales.descripcion as nombre_material',
                    'material_kilos.ctd_emdev',
                    'material_kilos.umb',
                    'material_kilos.ce',
                    'material_kilos.valor_emdev',
                    'material_kilos.factor_conversion',
                    'material_kilos.codigo_material',
                    'material_kilos.mes',
                    'material_kilos.año',
                    'material_kilos.proveedor_id'
                )
                ->where('material_kilos.id', $id)
                ->first();

            if (!$material_kilo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Material no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'material' => $material_kilo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateMaterial(Request $request)
    {
        try {
            // Validación básica
            $request->validate([
                'material_kilo_id' => 'required|exists:material_kilos,id',
                'factor_conversion' => 'required|numeric|min:0',
                'aplicar_rango' => 'nullable|boolean'
            ]);

            $factorConversion = $request->factor_conversion;
            $aplicarRango = $request->has('aplicar_rango') && $request->aplicar_rango == '1';

            // Caso 1: Solo actualizar el registro actual
            if (!$aplicarRango) {
                $material = MaterialKilo::findOrFail($request->material_kilo_id);
                
                $material->factor_conversion = $factorConversion;
                
                // Recalcular el total_kg
                if ($factorConversion > 0 && $material->ctd_emdev) {
                    $material->total_kg = $material->ctd_emdev * $factorConversion;
                } else {
                    $material->total_kg = 0;
                }
                
                $material->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Material actualizado correctamente',
                    'registros_actualizados' => 1
                ]);
            }

            // Caso 2: Aplicar a rango de fechas
            $request->validate([
                'codigo_material_hidden' => 'required',
                'mes_inicio' => 'required|integer|min:1|max:12',
                'anio_inicio' => 'required|integer|min:2020|max:2030',
                'mes_fin' => 'required|integer|min:1|max:12',
                'anio_fin' => 'required|integer|min:2020|max:2030'
            ]);

            // Obtener el código del material
            $codigoMaterial = $request->codigo_material_hidden;

            // Obtener los valores de mes y año
            $mesInicio = $request->mes_inicio;
            $anioInicio = $request->anio_inicio;
            $mesFin = $request->mes_fin;
            $anioFin = $request->anio_fin;

            // Validar que el rango sea correcto
            $fechaInicioComparacion = $anioInicio * 100 + $mesInicio;
            $fechaFinComparacion = $anioFin * 100 + $mesFin;
            
            if ($fechaInicioComparacion > $fechaFinComparacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha de inicio no puede ser posterior a la fecha fin'
                ], 400);
            }

            // Buscar todos los registros del material en el rango de fechas
            // Considerando que mes y año son columnas separadas
            $materiales = MaterialKilo::where('codigo_material', $codigoMaterial)
                ->where(function($query) use ($anioInicio, $anioFin, $mesInicio, $mesFin) {
                    if ($anioInicio == $anioFin) {
                        // Mismo año: filtrar solo por mes dentro del año
                        $query->where('año', $anioInicio)
                              ->whereBetween('mes', [$mesInicio, $mesFin]);
                    } else {
                        // Diferentes años
                        $query->where(function($q) use ($anioInicio, $anioFin, $mesInicio, $mesFin) {
                            // Registros del año inicio desde el mes inicio hasta diciembre
                            $q->where(function($subQ) use ($anioInicio, $mesInicio) {
                                $subQ->where('año', $anioInicio)
                                     ->where('mes', '>=', $mesInicio);
                            })
                            // Registros de años intermedios completos
                            ->orWhere(function($subQ) use ($anioInicio, $anioFin) {
                                $subQ->where('año', '>', $anioInicio)
                                     ->where('año', '<', $anioFin);
                            })
                            // Registros del año fin desde enero hasta el mes fin
                            ->orWhere(function($subQ) use ($anioFin, $mesFin) {
                                $subQ->where('año', $anioFin)
                                     ->where('mes', '<=', $mesFin);
                            });
                        });
                    }
                })
                ->get();

            if ($materiales->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron registros del material en el rango de fechas especificado'
                ], 404);
            }

            // Actualizar todos los registros encontrados
            $registrosActualizados = 0;
            foreach ($materiales as $material) {
                $material->factor_conversion = $factorConversion;
                
                // Recalcular el total_kg
                if ($factorConversion > 0 && $material->ctd_emdev) {
                    $material->total_kg = $material->ctd_emdev * $factorConversion;
                } else {
                    $material->total_kg = 0;
                }
                
                $material->save();
                $registrosActualizados++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Materiales actualizados correctamente',
                'registros_actualizados' => $registrosActualizados
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el material: ' . $e->getMessage()
            ], 500);
        }
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
    }
    public function totalKgPorProveedor(Request $request)
    {
        // Obtener filtros - si no se especifica mes, será null (todos los meses)
        $mes = $request->has('mes') ? $request->get('mes') : null;
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

        // Si mes está seleccionado Y no está vacío, filtrar por mes específico
        if ($mes !== null && $mes !== '') {
            $query->where('material_kilos.mes', $mes);
        }

        $totales_por_proveedor = $query->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderByDesc('total_kg_proveedor')
            ->get();

        // Agregar versión formateada para cada proveedor (miles con punto, decimales con coma)
        foreach ($totales_por_proveedor as $p) {
            $p->total_kg_proveedor_fmt = number_format((float) $p->total_kg_proveedor, 2, ',', '.');
        }

        // Obtener proveedores ordenados alfabéticamente para el modal
        $proveedores_alfabetico = MaterialKilo::join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->where('material_kilos.año', $año)
            ->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderBy('proveedores.nombre_proveedor', 'asc')
            ->get();

        // Obtener métricas existentes para el período filtrado
        $metricas_query = ProveedorMetric::where('año', $año);

        // Si mes está seleccionado Y no está vacío, filtrar métricas por mes específico
        if ($mes !== null && $mes !== '') {
            $metricas_query->where('mes', $mes);
            $metricas_por_proveedor = $metricas_query->get()->keyBy('proveedor_id');
        } else {
            // Si no hay mes seleccionado, sumar todas las métricas del año
            $metricas_agrupadas = ProveedorMetric::where('año', $año)
                ->select(
                    'proveedor_id',
                    DB::raw('SUM(rg1) as rg1'),
                    DB::raw('SUM(rl1) as rl1'),
                    DB::raw('SUM(dev1) as dev1'),
                    DB::raw('SUM(rok1) as rok1'),
                    DB::raw('SUM(ret1) as ret1')
                )
                ->groupBy('proveedor_id')
                ->get()
                ->keyBy('proveedor_id');
            $metricas_por_proveedor = $metricas_agrupadas;
        }

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
    }
    public function guardarExcel(Request $request)
    {
        // Validar que se haya subido un archivo
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,csv',
        ]);

        try {
            $archivo = $request->file('archivo_excel');
            // Ahora leerá tanto los datos como la info de fechas de la cabecera
            $resultado = $this->leerArchivoExcelOCsvMaterialKilo($archivo);
            $datos = $resultado['datos'];
            $fecha_inicio = $resultado['fecha_inicio'];
            $fecha_fin = $resultado['fecha_fin'];
            $año = $resultado['año'];
            $mes = $resultado['mes'];

            $now = now();
            $insertados = [];
            // Contador de filas consecutivas vacías (según columnas clave)
            $consecEmpty = 0;
            foreach ($datos as $idx => $item) {
                // Normalizar y validar valores desde el Excel
                $codigo_proveedor_excel = isset($item['Código Proveedor']) ? trim((string)$item['Código Proveedor']) : null;
                $nombre_proveedor_excel = isset($item['Nombre Proveedor']) ? trim((string)$item['Nombre Proveedor']) : null;

                // Detectar filas vacías: si Código Producto, Descripcion Queja y Código Proveedor están vacíos
                $codigo_producto_val = isset($item['Código Producto']) ? trim((string)$item['Código Producto']) : '';
                $descripcion_queja_val = isset($item['Descripcion Queja']) ? trim((string)$item['Descripcion Queja']) : '';
                $codigo_proveedor_val = $codigo_proveedor_excel !== null ? trim((string)$codigo_proveedor_excel) : '';

                if ($codigo_producto_val === '' && $descripcion_queja_val === '' && $codigo_proveedor_val === '') {
                    // fila considerada vacía
                    $consecEmpty++;
                    // Si hay más de 2 filas vacías consecutivas, detener procesamiento
                    if ($consecEmpty > 2) {
                        Log::info('guardarExcel: más de 2 filas vacías consecutivas, deteniendo import.', ['fila' => $idx + 3]);
                        break;
                    }
                    // saltar esta fila vacía
                    continue;
                } else {
                    // resetear contador si encontramos fila con datos
                    $consecEmpty = 0;
                }

                // Intentar buscar proveedor por código (preferido)
                $proveedor = null;
                if ($codigo_proveedor_excel !== null && $codigo_proveedor_excel !== '') {
                    $proveedor = DB::table('proveedores')->where('id_proveedor', $codigo_proveedor_excel)->first();
                }

                // Si no se encuentra por código, intentar por nombre exacto (segunda opción)
                if (!$proveedor && $nombre_proveedor_excel) {
                    $proveedor = DB::table('proveedores')->where('nombre_proveedor', $nombre_proveedor_excel)->first();
                }

                // Determinar valores finales a insertar
                if ($proveedor) {
                    $codigo_proveedor_db = $proveedor->id_proveedor;
                    $nombre_proveedor = $proveedor->nombre_proveedor;
                } else {
                    // Fallback: usar lo que venga en el Excel para evitar null
                    $codigo_proveedor_db = $codigo_proveedor_excel;
                    $nombre_proveedor = $nombre_proveedor_excel;
                    // Loguear para debugging si no hay match en la BD
                    Log::warning('guardarExcel: proveedor no encontrado en BD, se usa valor del Excel', [
                        'fila' => $idx + 3, // +3 porque empezamos a leer datos desde fila 3
                        'codigo_excel' => $codigo_proveedor_excel,
                        'nombre_excel' => $nombre_proveedor_excel
                    ]);
                }

                $insert = [
                    'codigo_producto' => $item['Código Producto'],
                    'codigo_proveedor' => $codigo_proveedor_db,
                    'nombre_proveedor' => $nombre_proveedor,
                    'descripcion_producto' => $item['Descripcion Producto'],
                    'descripcion_queja' => $item['Descripcion Queja'],
                    'fecha_reclamacion' => $now,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'clasificacion_incidencia' => 'RL1',
                    'nombre_tienda' => $item['Nombre Tienda'],
                    'origen' => $item['origen'],
                    'lote_sirena' => $item['La Sirena Lot'],
                    'lote_proveedor' => $item['provider Lot'],
                    'abierto' => 'Si',
                    'año' => $año,
                    'mes' => $mes,
                    'top100fy2' => ($idx < 100 ? 'Si' : null),
                ];
                DB::table('devoluciones_proveedores')->insert($insert);
                $insertados[] = $insert;
            }

            // 🆕 ACTUALIZAR MÉTRICAS AUTOMÁTICAMENTE
            // Obtener proveedores únicos y sus períodos afectados
            $proveedores_afectados = [];
            foreach ($insertados as $devolucion) {
                $key = $devolucion['codigo_proveedor'] . '-' . $devolucion['año'] . '-' . $devolucion['mes'];
                if (!isset($proveedores_afectados[$key])) {
                    $proveedores_afectados[$key] = [
                        'codigo_proveedor' => $devolucion['codigo_proveedor'],
                        'año' => $devolucion['año'],
                        'mes' => $devolucion['mes']
                    ];
                }
            }

            // Recalcular métricas para cada proveedor/período afectado
            $metricas_actualizadas = 0;
            foreach ($proveedores_afectados as $proveedor_info) {
                try {
                    $this->actualizarMetricasIncidencias(
                        $proveedor_info['codigo_proveedor'],
                        $proveedor_info['año'],
                        $proveedor_info['mes']
                    );
                    $metricas_actualizadas++;
                } catch (\Exception $e) {
                    Log::warning('Error al actualizar métricas del proveedor', [
                        'proveedor' => $proveedor_info['codigo_proveedor'],
                        'año' => $proveedor_info['año'],
                        'mes' => $proveedor_info['mes'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $mensaje = count($insertados) . ' devoluciones insertadas correctamente';
            if ($metricas_actualizadas > 0) {
                $mensaje .= ' y métricas actualizadas para ' . $metricas_actualizadas . ' proveedores';
            }

            return redirect()->back()->with('success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error en guardarExcel: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Lee un archivo Excel o CSV y retorna los datos desde la fila 2,
     * mapeando columnas específicas para MaterialKilo.
     * @param \Illuminate\Http\UploadedFile|string $archivo
     * @return array
     * @throws \Exception
     */
    private function leerArchivoExcelOCsvMaterialKilo($archivo)
    {
        // Aumentar límites de memoria y tiempo de ejecución
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        // Usar PhpSpreadsheet
        $extension = strtolower($archivo->getClientOriginalExtension());
        if ($extension === 'csv') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $reader->setDelimiter(",");
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        $spreadsheet = $reader->load($archivo->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Leer la primera fila para extraer fechas
        $primera_fila = $rows[1] ?? [];
        $texto_cabecera = '';
        // Concatenar columnas A-J para buscar el texto
        foreach (range('A', 'J') as $col) {
            $texto_cabecera .= (isset($primera_fila[$col]) ? $primera_fila[$col] : '') . ' ';
        }
        $texto_cabecera = trim($texto_cabecera);

        // Intentar extraer fechas desde la cabecera en varios formatos.
        // 1) Formato textual: "del 25 al 31 de julio de 2025"
        // 2) Formato con ambas fechas completas: "del 01/10 al 02/10/25"
        // 3) Formato mixto: "del 15 al 21/08/25" (inicio solo día, fin con dd/mm/yy)
        // 4) Dos fechas completas: "15/08/2025 al 21/08/2025" o similares
        $fecha_inicio = null;
        $fecha_fin = null;
        $mes = null;
        $año = null;

        // 1) Texto con nombre de mes y año de 4 dígitos
        $regex_textual = '/del\s+(\d{1,2})\s+al\s+(\d{1,2})\s+de\s+([a-zA-Záéíóúñ]+)\s+de\s+(\d{4})/u';
        if (preg_match($regex_textual, $texto_cabecera, $matches)) {
            $dia_inicio = $matches[1];
            $dia_fin = $matches[2];
            $mes_nombre = strtolower($matches[3]);
            $año = (int)$matches[4];
            // Mapear nombre de mes a número
            $meses = [
                'enero' => 1,
                'febrero' => 2,
                'marzo' => 3,
                'abril' => 4,
                'mayo' => 5,
                'junio' => 6,
                'julio' => 7,
                'agosto' => 8,
                'septiembre' => 9,
                'setiembre' => 9,
                'octubre' => 10,
                'noviembre' => 11,
                'diciembre' => 12
            ];
            $mes = $meses[$mes_nombre] ?? null;
            if ($mes && $año) {
                $fecha_inicio = sprintf('%04d-%02d-%02d', $año, $mes, $dia_inicio);
                $fecha_fin = sprintf('%04d-%02d-%02d', $año, $mes, $dia_fin);
            }
        } else {
            // 2) Formato con ambas fechas completas: "del 01/10 al 02/10/25" o "del 01/10/25 al 02/10/25"
            // Este nuevo regex captura ambas fechas completas con día/mes y opcionalmente año
            $regex_ambas_fechas = '/del\s+(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?\s+al\s+(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/';
            if (preg_match($regex_ambas_fechas, $texto_cabecera, $matches)) {
                $dia_inicio = (int)$matches[1];
                $mes_inicio = (int)$matches[2];
                $yr_inicio = isset($matches[3]) && $matches[3] !== '' ? (int)$matches[3] : null;
                $dia_fin = (int)$matches[4];
                $mes_fin = (int)$matches[5];
                $yr_fin = (int)$matches[6];

                // Normalizar año de fin (siempre presente)
                if (strlen($matches[6]) === 2) {
                    $año = ($yr_fin < 70) ? (2000 + $yr_fin) : (1900 + $yr_fin);
                } else {
                    $año = $yr_fin;
                }

                // Si año de inicio no está presente, usar el año de fin
                $año_inicio = $año;
                if ($yr_inicio !== null) {
                    if (strlen((string)$yr_inicio) === 2) {
                        $año_inicio = ($yr_inicio < 70) ? (2000 + $yr_inicio) : (1900 + $yr_inicio);
                    } else {
                        $año_inicio = $yr_inicio;
                    }
                }

                // Usar el mes de fin como mes principal (como se hacía antes)
                $mes = $mes_fin;
                $fecha_inicio = sprintf('%04d-%02d-%02d', $año_inicio, $mes_inicio, $dia_inicio);
                $fecha_fin = sprintf('%04d-%02d-%02d', $año, $mes_fin, $dia_fin);
            } else {
                // 3) Formato mixto antiguo: "del 15 al 21/08/25" -> tomar día inicio del primer número y mes/año del segundo
                $regex_mixto = '/del\s+(\d{1,2})\s+al\s+(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/';
                if (preg_match($regex_mixto, $texto_cabecera, $matches)) {
                    $dia_inicio = $matches[1];
                    $dia_fin = $matches[2];
                    $mes = (int)$matches[3];
                    $yr = (int)$matches[4];
                    // Normalizar años de 2 dígitos -> suponer 2000..2069 para 00..69, 1900..1999 para 70..99
                    if (strlen($matches[4]) === 2) {
                        $año = ($yr < 70) ? (2000 + $yr) : (1900 + $yr);
                    } else {
                        $año = $yr;
                    }
                    if ($mes && $año) {
                        $fecha_inicio = sprintf('%04d-%02d-%02d', $año, $mes, $dia_inicio);
                        $fecha_fin = sprintf('%04d-%02d-%02d', $año, $mes, $dia_fin);
                    }
                } else {
                    // 4) Dos fechas completas en formato dd/mm/yy o dd/mm/yyyy (puede aparecer en cualquier parte)
                    $regex_dos_fechas = '/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4}).*?(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/';
                    if (preg_match($regex_dos_fechas, $texto_cabecera, $matches)) {
                        $dia_inicio = $matches[1];
                        $mes_inicio = (int)$matches[2];
                        $yr1 = (int)$matches[3];
                        $dia_fin = $matches[4];
                        $mes_fin = (int)$matches[5];
                        $yr2 = (int)$matches[6];
                        // Normalizar años de 2 dígitos
                        if (strlen($matches[3]) === 2) {
                            $a1 = ($yr1 < 70) ? (2000 + $yr1) : (1900 + $yr1);
                        } else {
                            $a1 = $yr1;
                        }
                        if (strlen($matches[6]) === 2) {
                            $a2 = ($yr2 < 70) ? (2000 + $yr2) : (1900 + $yr2);
                        } else {
                            $a2 = $yr2;
                        }
                        // Preferir la fecha de inicio para año/mes; si meses coinciden usar ese mes
                        $año = $a1;
                        $mes = $mes_inicio;
                        $fecha_inicio = sprintf('%04d-%02d-%02d', $a1, $mes_inicio, $dia_inicio);
                        $fecha_fin = sprintf('%04d-%02d-%02d', $a2, $mes_fin, $dia_fin);
                    }
                }
            }
        }

        $datos = [];
        // Comenzar desde la fila 3 (índice 3, ya que $rows es 1-indexado)
        foreach ($rows as $i => $row) {
            if ($i < 3) continue; // Saltar encabezado y fila de títulos

            $datos[] = [
                'Código Producto' => $row['A'] ?? null,
                'Descripcion Producto' => $row['B'] ?? null,
                'Descripcion Motivo' => $row['C'] ?? null,
                'Descripcion Queja' => $row['D'] ?? null,
                'Código Proveedor' => $row['E'] ?? null,
                'Nombre Proveedor' => $row['F'] ?? null,
                'Codigo Tienda' => $row['G'] ?? null,
                'Nombre Tienda' => $row['H'] ?? null,
                'Identificadoractividad' => $row['I'] ?? null,
                'origen' => $row['J'] ?? null,
                'La Sirena Lot' => $row['K'] ?? null,
                'provider Lot' => $row['L'] ?? null,
            ];
        }
        return [
            'datos' => $datos,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'año' => $año,
            'mes' => $mes,
        ];
    }
    public function evaluacionContinuaProveedores(Request $request)
    {
        $mes = $request->get('mes');
        $año = $request->get('año', \Carbon\Carbon::now()->year);
        $proveedor = $request->get('proveedor', ''); // Asegurar que sea string
        $idProveedor = $request->get('id_proveedor', ''); // Asegurar que sea string
        $familia = $request->get('familia', ''); // Nuevo filtro de familia

        // Asegurar que $año sea numérico
        $año = (int) $año;

        // Debug: Log para verificar los tipos de variables
        Log::info('Variables recibidas en evaluacionContinuaProveedores', [
            'mes' => $mes,
            'año' => $año,
            'proveedor' => $proveedor,
            'proveedor_type' => gettype($proveedor),
            'idProveedor' => $idProveedor,
            'idProveedor_type' => gettype($idProveedor),
            'familia' => $familia,
            'familia_type' => gettype($familia)
        ]);

        // Obtener totales por proveedor para el mes y año específicos
        $query = DB::table('material_kilos')
            ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select(
                'proveedores.id_proveedor',
                'proveedores.nombre_proveedor',
                'proveedores.familia',
                DB::raw('SUM(gp_ls_material_kilos.total_kg) as total_kg_proveedor'),
                DB::raw('COUNT(gp_ls_material_kilos.id) as cantidad_registros')
            )
            ->where('material_kilos.año', $año);

        // Si mes está seleccionado, filtrar por mes específico
        if ($mes) {
            $query->where('material_kilos.mes', $mes);
        }

        // Filtrar por proveedor si está seleccionado
        if ($proveedor && is_string($proveedor)) {
            $query->where('proveedores.nombre_proveedor', $proveedor);
        }

        // Filtrar por ID proveedor si está especificado
        if ($idProveedor && is_string($idProveedor)) {
            $query->where('proveedores.id_proveedor', 'LIKE', '%' . $idProveedor . '%');
        }

        // Filtrar por familia si está seleccionada
        if ($familia && is_string($familia)) {
            $query->where('proveedores.familia', $familia);
        }

        $totales_por_proveedor = $query->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor', 'proveedores.familia')
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
                : null;

            if ($metricas && $proveedor->total_kg_proveedor > 0) {
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

        // Agregar una versión formateada del total (miles con punto y decimales con coma)
        // Esto permite mostrar el número en el formato local en la vista sin alterar
        // el valor numérico original que usa el JavaScript para filtros/cálculos.
        foreach ($totales_por_proveedor as $p) {
            $p->total_kg_proveedor_fmt = number_format((float) $p->total_kg_proveedor, 2, ',', '.');
        }

        // Obtener todos los proveedores disponibles para el select (sin filtros)
        $proveedores_disponibles = DB::table('material_kilos')
            ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->select('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->where('material_kilos.año', $año)
            ->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderBy('proveedores.nombre_proveedor', 'asc')
            ->get();

        // Asegurar que $proveedores_disponibles no esté vacío
        if ($proveedores_disponibles->isEmpty()) {
            $proveedores_disponibles = collect();
        }

        // Asegurar que las variables sean strings o null
        $proveedor = is_string($proveedor) ? $proveedor : '';
        $idProveedor = is_string($idProveedor) ? $idProveedor : '';
        $familia = is_string($familia) ? $familia : '';

        return view('MainApp/material_kilo.evaluacion_continua_proveedores', compact(
            'totales_por_proveedor',
            'metricas_por_proveedor',
            'mes',
            'año',
            'proveedor',
            'idProveedor',
            'familia',
            'proveedores_disponibles'
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
    public function buscarProveedor($codigo)
    {
        $proveedor = Proveedor::where('id_proveedor', $codigo)->first();

        if (!$proveedor) {
            return response()->json(['error' => 'Proveedor no encontrado'], 404);
        }

        return response()->json([
            'id_proveedor' => $proveedor->id_proveedor,
            'nombre' => $proveedor->nombre_proveedor
        ]);
    }

    public function eliminarIncidencia(Request $request, $id = null)
    {
        try {
            // Obtener ID desde la ruta o desde el request
            $incidenciaId = $id ?? $request->input('id_incidencia');
            
            if (!$incidenciaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de incidencia no proporcionado'
                ], 400);
            }

            $incidencia = IncidenciaProveedor::find($incidenciaId);
            
            if (!$incidencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incidencia no encontrada'
                ], 404);
            }

            // Eliminar archivos físicos asociados
            $archivos = $incidencia->archivos ?? [];
            foreach ($archivos as $archivo) {
                $rutaArchivo = storage_path('app/public/incidencias/' . $archivo['nombre']);
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
            }

            $nombreProveedor = $incidencia->nombre_proveedor ?? 'Proveedor';
            $incidencia->delete();

            return response()->json([
                'success' => true,
                'message' => "Incidencia del proveedor {$nombreProveedor} eliminada correctamente"
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar incidencia: ' . $e->getMessage());
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
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

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
                'fecha_decision_destino_producto' => 'nullable|date',
                'archivos.*' => 'nullable|file|max:10240', // Máximo 10MB por archivo,
                'estado' => 'nullable|string|in:Registrada,Gestionada,En Pausa,Cerrada',
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

            // Procesar archivos subidos
            $archivosData = [];
            if ($request->hasFile('archivos')) {
                $archivos = $request->file('archivos');
                foreach ($archivos as $archivo) {
                    if ($archivo->isValid()) {
                        // Obtener información del archivo ANTES de moverlo
                        $nombreOriginal = $archivo->getClientOriginalName();
                        $extension = $archivo->getClientOriginalExtension();
                        $tamanoArchivo = $archivo->getSize(); // Obtener tamaño antes del move
                        $nombreUnico = time() . '_' . uniqid() . '.' . $extension;

                        // Crear directorio si no existe
                        $rutaDirectorio = storage_path('app/public/incidencias');
                        if (!file_exists($rutaDirectorio)) {
                            mkdir($rutaDirectorio, 0755, true);
                        }

                        // Mover archivo
                        $archivo->move($rutaDirectorio, $nombreUnico);

                        // Guardar información del archivo
                        $archivosData[] = [
                            'nombre' => $nombreUnico,
                            'nombre_original' => $nombreOriginal,
                            'ruta' => asset('storage/incidencias/' . $nombreUnico),
                            'tamano' => $tamanoArchivo,
                            'fecha_subida' => now()->format('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            // Validar estado (si no existe o es inválido, poner "Registrada")
            $estadosValidos = ['Registrada', 'Gestionada', 'En Pausa', 'Cerrada'];
            $estadoFinal = in_array($request->estado, $estadosValidos) ? $request->estado : 'Registrada';

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
                'fecha_decision_destino_producto' => $request->fecha_decision_destino_producto,
                'tipo_incidencia' => $request->tipo_incidencia ?? '',
                'archivos' => $archivosData,
                'estado' => $estadoFinal,
            ]);

            EstadoIncidenciaReclamacion::create([
                'id_incidencia_proveedor' => $incidencia->id,
                'id_devolucion_proveedor' => null,
                'id_user' => $user->id,
                'estado' => $estadoFinal,
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
     * Actualizar métricas basadas en incidencias Y devoluciones
     */
    private function actualizarMetricasIncidencias($id_proveedor, $año, $mes)
    {
        // Contar INCIDENCIAS por tipo (DEV1, ROK1, RET1)
        $metricas_incidencias = DB::table('incidencias_proveedores')
            ->where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->select([
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "DEV1" THEN 1 ELSE 0 END) as dev1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "ROK1" THEN 1 ELSE 0 END) as rok1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RET1" THEN 1 ELSE 0 END) as ret1'),
            ])
            ->first();

        // Contar DEVOLUCIONES por tipo (RG1, RL1)
        // NOTA: codigo_proveedor es VARCHAR en devoluciones_proveedores
        $metricas_devoluciones = DB::table('devoluciones_proveedores')
            ->where('codigo_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->select([
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
                DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
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
                'rg1' => $metricas_devoluciones->rg1 ?? 0,
                'rl1' => $metricas_devoluciones->rl1 ?? 0,
                'dev1' => $metricas_incidencias->dev1 ?? 0,
                'rok1' => $metricas_incidencias->rok1 ?? 0,
                'ret1' => $metricas_incidencias->ret1 ?? 0,
            ]
        );
    }

    public function obtenerIncidencia($id)
    {
        try {
            $incidencia = DB::table('incidencias_proveedores')
                ->where('id', $id)
                ->first();

            if (!$incidencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incidencia no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'incidencia' => $incidencia
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la incidencia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerDevolucion($id)
    {
        try {
            $devolucion = DB::table('devoluciones_proveedores')
                ->where('id', $id)
                ->first();

            if (!$devolucion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Devolución no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'devolucion' => $devolucion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la devolución: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de incidencias y devoluciones
     */
    public function historialIncidenciasYDevoluciones(Request $request)
    {
        $mes = $request->get('mes');
        $año = $request->get('año', \Carbon\Carbon::now()->year);
        $proveedor = $request->get('proveedor', '');
        $tipo = $request->get('tipo', ''); // 'incidencia', 'devolucion', o vacío para ambos

        // Nuevos filtros
        $codigo_proveedor = $request->get('codigo_proveedor', '');
        $codigo_producto = $request->get('codigo_producto', '');
        $gravedad = $request->get('gravedad', ''); // 'grave', 'leve', o vacío
        $no_queja = $request->get('no_queja', '');

        $resultados = collect();

        // Si se filtra por no_queja, solo buscar en devoluciones
        $buscar_solo_devoluciones = !empty($no_queja);

        // Obtener incidencias usando las tablas correctas
        if (!$buscar_solo_devoluciones && (!$tipo || $tipo === 'incidencia')) {
            $incidencias = DB::table('incidencias_proveedores as i')
                ->leftJoin('proveedores as p', 'i.id_proveedor', '=', 'p.id_proveedor')
                ->select(
                    'i.id',
                    'i.id_proveedor',
                    'p.nombre_proveedor',
                    'i.fecha_incidencia as fecha_principal',
                    'i.clasificacion_incidencia',
                    'i.descripcion_incidencia',
                    'i.codigo',
                    'i.producto',
                    'i.origen',
                    'i.mes',
                    'i.año',
                    'i.fecha_respuesta_proveedor',
                    'i.fecha_envio_proveedor',
                    'i.numero_informe',
                    'i.estado',
                    DB::raw('NULL as np'),
                    DB::raw('NULL as codigo_producto'),
                    DB::raw('NULL as no_queja'),
                    DB::raw('NULL as abierto'), // Para incidencias no aplica
                    DB::raw("'incidencia' as tipo_registro")
                )
                ->where('i.año', $año);

            if ($mes) {
                $incidencias->where('i.mes', $mes);
            }

            if ($proveedor) {
                $incidencias->where('p.nombre_proveedor', 'LIKE', '%' . $proveedor . '%');
            }

            // Filtro por código de proveedor (en incidencias es id_proveedor)
            if ($codigo_proveedor) {
                $incidencias->where('i.id_proveedor', 'LIKE', '%' . $codigo_proveedor . '%');
            }

            // Filtro por código de producto (en incidencias es 'codigo')
            if ($codigo_producto) {
                $incidencias->where('i.codigo', 'LIKE', '%' . $codigo_producto . '%');
            }

            // Filtro por gravedad
            if ($gravedad === 'grave') {
                $incidencias->where('i.clasificacion_incidencia', 'RG1');
            } elseif ($gravedad === 'leve') {
                $incidencias->where('i.clasificacion_incidencia', 'RL1');
            }

            $resultados = $resultados->merge($incidencias->get());
        }

        // Obtener devoluciones usando las tablas correctas
        if (!$tipo || $tipo === 'devolucion') {
            $devoluciones = DB::table('devoluciones_proveedores as d')
                ->leftJoin('proveedores as p', 'd.codigo_proveedor', '=', 'p.id_proveedor')
                ->select(
                    'd.id',
                    'd.codigo_proveedor',
                    'p.nombre_proveedor',
                    'd.fecha_inicio as fecha_principal',
                    'd.clasificacion_incidencia',
                    'd.descripcion_motivo as descripcion_incidencia',
                    'd.codigo_producto',
                    'd.descripcion_producto',
                    'd.origen',
                    'd.mes',
                    'd.año',
                    'd.fecha_respuesta_proveedor',
                    'd.fecha_envio_proveedor',
                    'd.np',
                    'd.no_queja',
                    'd.abierto',
                    DB::raw('NULL as codigo'),
                    DB::raw('NULL as producto'),
                    DB::raw('NULL as numero_informe'),
                    DB::raw("'devolucion' as tipo_registro")
                )
                ->where('d.año', $año);

            if ($mes) {
                $devoluciones->where('d.mes', $mes);
            }

            if ($proveedor) {
                $devoluciones->where('p.nombre_proveedor', 'LIKE', '%' . $proveedor . '%');
            }

            // Filtro por código de proveedor (en devoluciones es codigo_proveedor)
            if ($codigo_proveedor) {
                $devoluciones->where('d.codigo_proveedor', 'LIKE', '%' . $codigo_proveedor . '%');
            }

            // Filtro por código de producto (en devoluciones es 'codigo_producto')
            if ($codigo_producto) {
                $devoluciones->where('d.codigo_producto', 'LIKE', '%' . $codigo_producto . '%');
            }

            // Filtro por gravedad
            if ($gravedad === 'grave') {
                $devoluciones->where('d.clasificacion_incidencia', 'RG1');
            } elseif ($gravedad === 'leve') {
                $devoluciones->where('d.clasificacion_incidencia', 'RL1');
            }

            // Filtro por número de queja
            if ($no_queja) {
                $devoluciones->where('d.no_queja', 'LIKE', '%' . $no_queja . '%');
            }

            $resultados = $resultados->merge($devoluciones->get());
        }

        // Ordenar por fecha descendente
        $resultados = $resultados->sortByDesc('fecha_principal');

        // Obtener proveedores para el filtro usando la tabla correcta
        $proveedores_disponibles = DB::table('proveedores')
            ->select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        // Estadísticas
        $total_incidencias = $resultados->where('tipo_registro', 'incidencia')->count();
        $total_devoluciones = $resultados->where('tipo_registro', 'devolucion')->count();
        $total_registros = $resultados->count();

        return view('MainApp/material_kilo.historial_incidencias_devoluciones', compact(
            'resultados',
            'proveedores_disponibles',
            'mes',
            'año',
            'proveedor',
            'tipo',
            'codigo_proveedor',
            'codigo_producto',
            'gravedad',
            'no_queja',
            'total_incidencias',
            'total_devoluciones',
            'total_registros'
        ));
    }

    /**
     * Obtener incidencias de un proveedor para modal
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
     * Mostrar formulario para crear nueva incidencia
     */
    public function crearIncidencia()
    {
        // Obtener proveedores disponibles
        $proveedores = DB::table('proveedores')
            ->select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        // Variables para valores por defecto
        $mes = now()->month;
        $año = now()->year;

        return view('MainApp/material_kilo.incidencia_form', compact('proveedores', 'mes', 'año'));
    }

    /**
     * Mostrar formulario para editar incidencia
     */
    public function editarIncidencia($id)
    {
        $incidencia = IncidenciaProveedor::with('proveedor')->findOrFail($id);

        // Obtener proveedores disponibles
        $proveedores = DB::table('proveedores')
            ->select('id_proveedor', 'nombre_proveedor', 'email_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        // Variables para valores por defecto
        $mes = now()->month;
        $año = now()->year;

        return view('MainApp/material_kilo.incidencia_form', compact('incidencia', 'proveedores', 'mes', 'año'));
    }

    /**
     * Guardar nueva incidencia desde página completa
     */
    public function guardarIncidenciaCompleta(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

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
            'lote' => 'nullable|string|max:255',
            'fcp' => 'nullable|date',
            'caducidad' => 'nullable|date',
            'cantidad_kg' => 'nullable|numeric',
            'cantidad_unidades' => 'nullable|numeric',
            'proveedor_alternativo' => 'nullable|string|max:255',
            'dias_sin_servicio' => 'nullable|numeric',
            'informe_a_proveedor' => 'nullable|in:Si,No',
            'numero_informe' => 'nullable|string|max:255',
            'fecha_envio_proveedor' => 'nullable|date',
            'fecha_respuesta_proveedor' => 'nullable|date',
            'informe_respuesta' => 'nullable|string',
            'comentarios' => 'nullable|string',
            'estado' => 'nullable|string|in:Registrada,Gestionada,En Pausa,Cerrada',
        ]);

        try {
            // Obtener el nombre del proveedor
            $proveedor = DB::table('proveedores')
                ->where('id_proveedor', $request->id_proveedor)
                ->select('nombre_proveedor')
                ->first();

            if (!$proveedor) {
                return redirect()->back()->with('error', 'Proveedor no encontrado');
            }

            // Calcular días de respuesta si hay fechas
            $dias_respuesta_proveedor = null;
            $dias_sin_respuesta_informe = null;

            if ($request->fecha_envio_proveedor && $request->fecha_respuesta_proveedor) {
                $fecha_envio = \Carbon\Carbon::parse($request->fecha_envio_proveedor);
                $fecha_respuesta = \Carbon\Carbon::parse($request->fecha_respuesta_proveedor);
                $dias_respuesta_proveedor = $fecha_envio->diffInDays($fecha_respuesta);
            }

            if ($request->fecha_envio_proveedor && !$request->fecha_respuesta_proveedor) {
                $fecha_envio = \Carbon\Carbon::parse($request->fecha_envio_proveedor);
                $dias_sin_respuesta_informe = $fecha_envio->diffInDays(\Carbon\Carbon::now());
            }

            // Validar estado (si no existe o es inválido, poner "Registrada")
            $estadosValidos = ['Registrada', 'Gestionada', 'En Pausa', 'Cerrada'];
            $estadoFinal = in_array($request->estado, $estadosValidos) ? $request->estado : 'Registrada';

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
                'lote' => $request->lote,
                'fcp' => $request->fcp,
                'caducidad' => $request->caducidad,
                'cantidad_kg' => $request->cantidad_kg,
                'cantidad_unidades' => $request->cantidad_unidades,
                'proveedor_alternativo' => $request->proveedor_alternativo,
                'dias_sin_servicio' => $request->dias_sin_servicio,
                'informe_a_proveedor' => $request->informe_a_proveedor,
                'numero_informe' => $request->numero_informe,
                'fecha_envio_proveedor' => $request->fecha_envio_proveedor,
                'fecha_respuesta_proveedor' => $request->fecha_respuesta_proveedor,
                'informe_respuesta' => $request->informe_respuesta,
                'comentarios' => $request->comentarios,
                'dias_respuesta_proveedor' => $dias_respuesta_proveedor,
                'dias_sin_respuesta_informe' => $dias_sin_respuesta_informe,
                'tipo_incidencia' => $request->tipo_incidencia ?? '',
                'estado' => $estadoFinal,
            ]);

            EstadoIncidenciaReclamacion::create([
                'id_incidencia_proveedor' => $incidencia->id,
                'id_devolucion_proveedor' => null,
                'id_user' => $user->id,
                'estado' => $estadoFinal,
            ]);

            // Actualizar las métricas automáticamente
            $this->actualizarMetricasIncidencias($request->id_proveedor, $request->año, $request->mes);

            return redirect()->route('material_kilo.historial_incidencias_devoluciones')->with('success', 'Incidencia guardada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al guardar incidencia: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al guardar la incidencia: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Actualizar incidencia existente
     */
    public function actualizarIncidencia(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

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
            'pedido_sap_devolucion' => 'nullable|string|max:255',
            'resolucion_tienda' => 'nullable|string|max:255',
            'retirada_tiendas' => 'nullable|in:Si,No',
            'cantidad_afectada' => 'nullable|numeric',
            'descripcion_incidencia' => 'nullable|string',
            'codigo' => 'nullable|string|max:255',
            'producto' => 'nullable|string|max:255',
            'lote_sirena' => 'nullable|string|max:255',
            'lote_proveedor' => 'nullable|string|max:255',
            'lote' => 'nullable|string|max:255',
            'fcp' => 'nullable|date',
            'caducidad' => 'nullable|date',
            'cantidad_kg' => 'nullable|numeric',
            'cantidad_unidades' => 'nullable|numeric',
            'proveedor_alternativo' => 'nullable|string|max:255',
            'dias_sin_servicio' => 'nullable|numeric',
            'informe_a_proveedor' => 'nullable|in:Si,No',
            'numero_informe' => 'nullable|string|max:255',
            'fecha_envio_proveedor' => 'nullable|date',
            'fecha_respuesta_proveedor' => 'nullable|date',
            'informe_respuesta' => 'nullable|string',
            'comentarios' => 'nullable|string',
            'archivos.*' => 'nullable|file|max:10240', // Máximo 10MB por archivo
            'estado' => 'nullable|string|in:Registrada,Gestionada,En Pausa,Cerrada',
        ]);

        try {
            $incidencia = IncidenciaProveedor::findOrFail($id);

            // Obtener el nombre del proveedor
            $proveedor = DB::table('proveedores')
                ->where('id_proveedor', $request->id_proveedor)
                ->select('nombre_proveedor')
                ->first();

            if (!$proveedor) {
                return redirect()->back()->with('error', 'Proveedor no encontrado');
            }

            // Calcular días de respuesta si hay fechas
            $dias_respuesta_proveedor = null;
            $dias_sin_respuesta_informe = null;

            if ($request->fecha_envio_proveedor && $request->fecha_respuesta_proveedor) {
                $fecha_envio = \Carbon\Carbon::parse($request->fecha_envio_proveedor);
                $fecha_respuesta = \Carbon\Carbon::parse($request->fecha_respuesta_proveedor);
                $dias_respuesta_proveedor = $fecha_envio->diffInDays($fecha_respuesta);
            }

            if ($request->fecha_envio_proveedor && !$request->fecha_respuesta_proveedor) {
                $fecha_envio = \Carbon\Carbon::parse($request->fecha_envio_proveedor);
                $dias_sin_respuesta_informe = $fecha_envio->diffInDays(\Carbon\Carbon::now());
            }

            // Procesar archivos subidos (mantener archivos existentes)
            $archivosExistentes = $incidencia->archivos ?? [];
            if ($request->hasFile('archivos')) {
                $archivos = $request->file('archivos');
                foreach ($archivos as $archivo) {
                    if ($archivo->isValid()) {
                        // Obtener información del archivo ANTES de moverlo
                        $nombreOriginal = $archivo->getClientOriginalName();
                        $extension = $archivo->getClientOriginalExtension();
                        $tamanoArchivo = $archivo->getSize(); // Obtener tamaño antes del move
                        $nombreUnico = time() . '_' . uniqid() . '.' . $extension;

                        // Crear directorio si no existe
                        $rutaDirectorio = storage_path('app/public/incidencias');
                        if (!file_exists($rutaDirectorio)) {
                            mkdir($rutaDirectorio, 0755, true);
                        }

                        // Mover archivo
                        $archivo->move($rutaDirectorio, $nombreUnico);

                        // Agregar nuevo archivo a la lista existente
                        $archivosExistentes[] = [
                            'nombre' => $nombreUnico,
                            'nombre_original' => $nombreOriginal,
                            'ruta' => asset('storage/incidencias/' . $nombreUnico),
                            'tamano' => $tamanoArchivo,
                            'fecha_subida' => now()->format('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            // Validar estado (si no existe o es inválido, poner "Registrada")
            $estadosValidos = ['Registrada', 'Gestionada', 'En Pausa', 'Cerrada'];
            $estadoFinal = in_array($request->estado, $estadosValidos) ? $request->estado : 'Registrada';

            // Actualizar la incidencia
            $incidencia->update([
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
                'lote' => $request->lote,
                'fcp' => $request->fcp,
                'caducidad' => $request->caducidad,
                'cantidad_kg' => $request->cantidad_kg,
                'cantidad_unidades' => $request->cantidad_unidades,
                'proveedor_alternativo' => $request->proveedor_alternativo,
                'dias_sin_servicio' => $request->dias_sin_servicio,
                'informe_a_proveedor' => $request->informe_a_proveedor,
                'numero_informe' => $request->numero_informe,
                'fecha_envio_proveedor' => $request->fecha_envio_proveedor,
                'fecha_respuesta_proveedor' => $request->fecha_respuesta_proveedor,
                'informe_respuesta' => $request->informe_respuesta,
                'comentarios' => $request->comentarios,
                'dias_respuesta_proveedor' => $dias_respuesta_proveedor,
                'dias_sin_respuesta_informe' => $dias_sin_respuesta_informe,
                'tipo_incidencia' => $request->tipo_incidencia ?? '',
                'archivos' => $archivosExistentes,
                'estado' => $estadoFinal,
            ]);

            EstadoIncidenciaReclamacion::create([
                'id_incidencia_proveedor' => $incidencia->id,
                'id_devolucion_proveedor' => null,
                'id_user' => $user->id,
                'estado' => $estadoFinal,
            ]);

            // Actualizar las métricas automáticamente
            $this->actualizarMetricasIncidencias($request->id_proveedor, $request->año, $request->mes);

            return redirect()->action([\App\Http\Controllers\MainApp\MaterialKiloController::class, 'editarIncidencia'], ['id' => $incidencia->id])
                ->with('success', 'Incidencia actualizada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar incidencia: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar la incidencia: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mostrar formulario para crear nueva devolución
     */
    public function crearDevolucion()
    {
        // Obtener proveedores disponibles
        $proveedores = DB::table('proveedores')
            ->select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        // Variables para valores por defecto
        $mes = now()->month;
        $año = now()->year;

        return view('MainApp/material_kilo.devolucion_form', compact('proveedores', 'mes', 'año'));
    }

    /**
     * Mostrar formulario para editar devolución
     */
    public function editarDevolucion($id)
    {
        $devolucion = DevolucionProveedor::with('proveedor')->findOrFail($id);

        // Proveedores disponibles
        $proveedores = DB::table('proveedores')
            ->select('id_proveedor', 'nombre_proveedor', 'email_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        $mes = now()->month;
        $año = now()->year;

        return view('MainApp/material_kilo.devolucion_form', compact('devolucion', 'proveedores', 'mes', 'año'));
    }

    /**
     * Guardar nueva devolución desde página completa
     */
    public function guardarDevolucionCompleta(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $request->validate([
            'codigo_producto' => 'required|string|max:255',
            'descripcion_producto' => 'nullable|string|max:255',
            'año' => 'required|integer',
            'mes' => 'required|integer|between:1,12',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'fecha_reclamacion' => 'nullable|date',
            'fecha_reclamacion_respuesta' => 'nullable|date',
            'np' => 'nullable|string|max:255',
            'no_queja' => 'nullable|string|max:255',
            'origen' => 'nullable|string|max:255',
            'nombre_tienda' => 'nullable|string|max:255',
            'clasificacion_incidencia' => 'nullable|string|max:255',
            'tipo_reclamacion' => 'nullable|string|max:255',
            'top100fy2' => 'nullable|string|max:255',
            'descripcion_motivo' => 'nullable|string',
            'descripcion_queja' => 'nullable|string',
            'especificacion_motivo_reclamacion_leve' => 'nullable|string',
            'especificacion_motivo_reclamacion_grave' => 'nullable|string',
            'lote_sirena' => 'nullable|string|max:255',
            'lote_proveedor' => 'nullable|string|max:255',
            'recuperamos_objeto_extraño' => 'nullable|in:Si,No',
            'informe_a_proveedor' => 'nullable|in:Si,No',
            'fecha_envio_proveedor' => 'nullable|date',
            'fecha_respuesta_proveedor' => 'nullable|date',
            'informe' => 'nullable|string',
            'informe_respuesta' => 'nullable|string',
            'abierto' => 'nullable|in:Si,No',
            'comentarios' => 'nullable|string',
            'archivos.*' => 'nullable|file|max:10240', // Máximo 10MB por archivo
        ]);

        try {
            // Obtener el nombre del proveedor
            $proveedor = DB::table('proveedores')
                ->where('id_proveedor', $request->codigo_proveedor)
                ->select('nombre_proveedor')
                ->first();

            if (!$proveedor) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Proveedor no encontrado'], 404);
                }
                return redirect()->back()->with('error', 'Proveedor no encontrado');
            }

            // Procesar archivos subidos
            $archivosData = [];
            if ($request->hasFile('archivos')) {
                $archivos = $request->file('archivos');
                foreach ($archivos as $archivo) {
                    if ($archivo->isValid()) {
                        // Obtener información del archivo ANTES de moverlo
                        $nombreOriginal = $archivo->getClientOriginalName();
                        $extension = $archivo->getClientOriginalExtension();
                        $tamanoArchivo = $archivo->getSize(); // Obtener tamaño antes del move
                        $nombreUnico = time() . '_' . uniqid() . '.' . $extension;

                        // Crear directorio si no existe
                        $rutaDirectorio = storage_path('app/public/devoluciones');
                        if (!file_exists($rutaDirectorio)) {
                            mkdir($rutaDirectorio, 0755, true);
                        }

                        // Mover archivo
                        $archivo->move($rutaDirectorio, $nombreUnico);

                        // Guardar información del archivo
                        $archivosData[] = [
                            'nombre' => $nombreUnico,
                            'nombre_original' => $nombreOriginal,
                            'ruta' => asset('storage/devoluciones/' . $nombreUnico),
                            'tamano' => $tamanoArchivo,
                            'fecha_subida' => now()->format('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            // Crear la devolución
            $devolucion = DevolucionProveedor::create([
                'codigo_producto' => $request->codigo_producto,
                'codigo_proveedor' => $request->codigo_proveedor,
                'nombre_proveedor' => $proveedor->nombre_proveedor,
                'descripcion_producto' => $request->descripcion_producto,
                'año' => $request->año,
                'mes' => $request->mes,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'fecha_reclamacion' => $request->fecha_reclamacion,
                'fecha_reclamacion_respuesta' => $request->fecha_reclamacion_respuesta,
                'np' => $request->np,
                'no_queja' => $request->no_queja,
                'origen' => $request->origen,
                'nombre_tienda' => $request->nombre_tienda,
                'clasificacion_incidencia' => $request->clasificacion_incidencia,
                'tipo_reclamacion' => $request->tipo_reclamacion,
                'tipo_reclamacion_grave' => $request->tipo_reclamacion_grave,
                'top100fy2' => $request->top100fy2,
                'descripcion_motivo' => $request->descripcion_motivo,
                'descripcion_queja' => $request->descripcion_queja,
                'especificacion_motivo_reclamacion_leve' => $request->especificacion_motivo_reclamacion_leve,
                'especificacion_motivo_reclamacion_grave' => $request->especificacion_motivo_reclamacion_grave,
                'lote_sirena' => $request->lote_sirena,
                'lote_proveedor' => $request->lote_proveedor,
                'recuperamos_objeto_extraño' => $request->recuperamos_objeto_extraño,
                'informe_a_proveedor' => $request->informe_a_proveedor,
                'fecha_envio_proveedor' => $request->fecha_envio_proveedor,
                'fecha_respuesta_proveedor' => $request->fecha_respuesta_proveedor,
                'informe' => $request->informe,
                'informe_respuesta' => $request->informe_respuesta,
                'abierto' => $request->abierto ?? 'Si',
                'comentarios' => $request->comentarios,
                'archivos' => $archivosData
            ]);

            // Actualizar las métricas automáticamente
            $this->actualizarMetricasIncidencias($request->codigo_proveedor, $request->año, $request->mes);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Devolución guardada correctamente']);
            }
            return redirect()->route('material_kilo.historial_incidencias_devoluciones')->with('success', 'Devolución guardada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al guardar devolución: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al guardar la devolución: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error al guardar la devolución: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Actualizar devolución existente
     */
    public function actualizarDevolucion(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $request->validate([
            'codigo_producto' => 'required|string|max:255',
            'codigo_proveedor' => 'required|integer',
            'descripcion_producto' => 'nullable|string|max:255',
            'año' => 'required|integer',
            'mes' => 'required|integer|between:1,12',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'fecha_reclamacion' => 'nullable|date',
            'fecha_reclamacion_respuesta' => 'nullable|date',
            'np' => 'nullable|string|max:255',
            'no_queja' => 'nullable|string|max:255',
            'origen' => 'nullable|string|max:255',
            'nombre_tienda' => 'nullable|string|max:255',
            'clasificacion_incidencia' => 'nullable|string|max:255',
            'tipo_reclamacion' => 'nullable|string|max:255',
            'top100fy2' => 'nullable|string|max:255',
            'descripcion_motivo' => 'nullable|string',
            'descripcion_queja' => 'nullable|string',
            'especificacion_motivo_reclamacion_leve' => 'nullable|string',
            'especificacion_motivo_reclamacion_grave' => 'nullable|string',
            'lote_sirena' => 'nullable|string|max:255',
            'lote_proveedor' => 'nullable|string|max:255',
            'recuperamos_objeto_extraño' => 'nullable|in:Si,No',
            'informe_a_proveedor' => 'nullable|in:Si,No',
            'fecha_envio_proveedor' => 'nullable|date',
            'fecha_respuesta_proveedor' => 'nullable|date',
            'informe' => 'nullable|string',
            'informe_respuesta' => 'nullable|string',
            'abierto' => 'nullable|in:Si,No',
            'comentarios' => 'nullable|string',
            'archivos.*' => 'nullable|file|max:10240', // Máximo 10MB por archivo
        ]);

        try {
            $devolucion = DevolucionProveedor::findOrFail($id);

            $proveedor = DB::table('proveedores')
                ->where('id_proveedor', $request->codigo_proveedor)
                ->select('nombre_proveedor')
                ->first();

            if (!$proveedor) {
                return redirect()->back()->with('error', 'Proveedor no encontrado');
            }

            $archivosExistentes = $devolucion->archivos ?? [];
            if ($request->hasFile('archivos')) {
                $archivos = $request->file('archivos');
                foreach ($archivos as $archivo) {
                    if ($archivo->isValid()) {
                        $nombreOriginal = $archivo->getClientOriginalName();
                        $extension = $archivo->getClientOriginalExtension();
                        $tamanoArchivo = $archivo->getSize();
                        $nombreUnico = time() . '_' . uniqid() . '.' . $extension;

                        $rutaDirectorio = storage_path('app/public/devoluciones');
                        if (!file_exists($rutaDirectorio)) {
                            mkdir($rutaDirectorio, 0755, true);
                        }

                        $archivo->move($rutaDirectorio, $nombreUnico);

                        $archivosExistentes[] = [
                            'nombre' => $nombreUnico,
                            'nombre_original' => $nombreOriginal,
                            'ruta' => asset('storage/devoluciones/' . $nombreUnico),
                            'tamano' => $tamanoArchivo,
                            'fecha_subida' => now()->format('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            // Procesar archivos del informe (mantener archivos existentes del informe)
            $archivosInformeExistentes = $devolucion->archivos_informe ?? [];
            if ($request->hasFile('archivos_informe')) {
                $archivosInforme = $request->file('archivos_informe');
                foreach ($archivosInforme as $archivo) {
                    if ($archivo->isValid()) {
                        // Obtener información del archivo ANTES de moverlo
                        $nombreOriginal = $archivo->getClientOriginalName();
                        $extension = $archivo->getClientOriginalExtension();
                        $tamanoArchivo = $archivo->getSize();
                        $nombreUnico = time() . '_' . uniqid() . '.' . $extension;
                        
                        // Crear directorio específico para archivos del informe
                        $rutaDirectorio = storage_path('app/public/devoluciones/archivos_informe');
                        if (!file_exists($rutaDirectorio)) {
                            mkdir($rutaDirectorio, 0755, true);
                        }
                        
                        // Mover archivo
                        $archivo->move($rutaDirectorio, $nombreUnico);
                        
                        // Agregar nuevo archivo del informe a la lista existente
                        $archivosInformeExistentes[] = [
                            'nombre' => $nombreUnico,
                            'nombre_original' => $nombreOriginal,
                            'ruta' => asset('storage/devoluciones/archivos_informe/' . $nombreUnico),
                            'tamano' => $tamanoArchivo,
                            'fecha_subida' => now()->format('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            // Validar estado (si no existe o es inválido, poner "Registrada")
            $estadosValidos = ['Registrada', 'Gestionada', 'En Pausa', 'Cerrada'];
            $estadoFinal = in_array($request->estado, $estadosValidos) ? $request->estado : 'Registrada';

            $devolucion->update([
                'codigo_producto' => $request->codigo_producto,
                'codigo_proveedor' => $request->codigo_proveedor,
                'nombre_proveedor' => $proveedor->nombre_proveedor,
                'descripcion_producto' => $request->descripcion_producto,
                'año' => $request->año,
                'mes' => $request->mes,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'fecha_reclamacion' => $request->fecha_reclamacion,
                'fecha_reclamacion_respuesta' => $request->fecha_reclamacion_respuesta,
                'np' => $request->np,
                'no_queja' => $request->no_queja,
                'origen' => $request->origen,
                'nombre_tienda' => $request->nombre_tienda,
                'clasificacion_incidencia' => $request->clasificacion_incidencia,
                'tipo_reclamacion' => $request->tipo_reclamacion,
                'tipo_reclamacion_grave' => $request->tipo_reclamacion_grave,
                'top100fy2' => $request->top100fy2,
                'descripcion_motivo' => $request->descripcion_motivo,
                'descripcion_queja' => $request->descripcion_queja,
                'especificacion_motivo_reclamacion_leve' => $request->especificacion_motivo_reclamacion_leve,
                'especificacion_motivo_reclamacion_grave' => $request->especificacion_motivo_reclamacion_grave,
                'lote_sirena' => $request->lote_sirena,
                'lote_proveedor' => $request->lote_proveedor,
                'recuperamos_objeto_extraño' => $request->recuperamos_objeto_extraño,
                'informe_a_proveedor' => $request->informe_a_proveedor,
                'fecha_envio_proveedor' => $request->fecha_envio_proveedor,
                'fecha_respuesta_proveedor' => $request->fecha_respuesta_proveedor,
                'informe' => $request->informe,
                'informe_respuesta' => $request->informe_respuesta,
                'abierto' => $request->abierto ?? 'Si',
                'comentarios' => $request->comentarios,
                'archivos' => $archivosExistentes
            ]);

            // Actualizar las métricas automáticamente
            $this->actualizarMetricasIncidencias($request->codigo_proveedor, $request->año, $request->mes);

            // redirige al formulario de edición: editarDevolucion carga proveedores, mes y año
            return redirect()->action([\App\Http\Controllers\MainApp\MaterialKiloController::class, 'editarDevolucion'], ['id' => $devolucion->id])
                ->with('success', 'Devolución actualizada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar devolución: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar la devolución: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Buscar producto por código (AJAX)
     */
    public function buscarProductoPorCodigo(Request $request)
    {
        $codigo = $request->get('codigo');
        $producto = DB::table('materiales')->where('codigo', $codigo)->first();
        if ($producto) {
            return response()->json(['success' => true, 'producto' => $producto]);
        } else {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
        }
    }

    /**
     * Buscar productos por término (AJAX para autocompletar)
     */
    public function buscarCodigosProductos(Request $request)
    {
        $term = $request->get('term');
        $productos = DB::table('materiales')
            ->where('codigo', 'like', "%{$term}%")
            ->orWhere('descripcion', 'like', "%{$term}%")
            ->limit(20)
            ->get();
        $result = [];
        foreach ($productos as $producto) {
            $result[] = [
                'id' => $producto->codigo,
                'label' => $producto->codigo . ' - ' . $producto->descripcion,
                'value' => $producto->codigo,
                'descripcion' => $producto->descripcion
            ];
        }
        return response()->json($result);
    }

    /**
     * Descargar archivo de incidencia
     */
    public function descargarArchivoIncidencia($incidenciaId, $nombreArchivo)
    {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }

        $incidencia = IncidenciaProveedor::find($incidenciaId);
        if (!$incidencia) {
            abort(404, 'Incidencia no encontrada');
        }

        $rutaArchivo = storage_path('app/public/incidencias/' . $nombreArchivo);
        if (!file_exists($rutaArchivo)) {
            abort(404, 'Archivo no encontrado en: ' . $rutaArchivo);
        }

        // Obtener el nombre original del archivo desde la base de datos
        $archivos = $incidencia->archivos ?? [];
        $nombreOriginal = $nombreArchivo;

        foreach ($archivos as $archivo) {
            if (isset($archivo['nombre']) && $archivo['nombre'] === $nombreArchivo) {
                $nombreOriginal = $archivo['nombre_original'] ?? $nombreArchivo;
                break;
            }
        }

        // Obtener información del archivo
        $mimeType = mime_content_type($rutaArchivo);
        $fileSize = filesize($rutaArchivo);

        // Configurar headers para la descarga
        return response()->download($rutaArchivo, $nombreOriginal, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Content-Disposition' => 'attachment; filename="' . $nombreOriginal . '"'
        ]);
    }

    /**
     * Descargar archivo de devolución
     */
    public function descargarArchivoDevolucion($devolucionId, $nombreArchivo)
    {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }

        $devolucion = DevolucionProveedor::find($devolucionId);
        if (!$devolucion) {
            abort(404, 'Devolución no encontrada');
        }

        $rutaArchivo = storage_path('app/public/devoluciones/' . $nombreArchivo);
        if (!file_exists($rutaArchivo)) {
            abort(404, 'Archivo no encontrado en: ' . $rutaArchivo);
        }

        // Obtener el nombre original del archivo desde la base de datos
        $archivos = $devolucion->archivos ?? [];
        $nombreOriginal = $nombreArchivo;

        foreach ($archivos as $archivo) {
            if (isset($archivo['nombre']) && $archivo['nombre'] === $nombreArchivo) {
                $nombreOriginal = $archivo['nombre_original'] ?? $nombreArchivo;
                break;
            }
        }

        // Obtener información del archivo
        $mimeType = mime_content_type($rutaArchivo);
        $fileSize = filesize($rutaArchivo);

        // Configurar headers para la descarga
        return response()->download($rutaArchivo, $nombreOriginal, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Content-Disposition' => 'attachment; filename="' . $nombreOriginal . '"'
        ]);
    }

    /**
     * Eliminar archivo de incidencia
     */
    public function eliminarArchivoIncidencia(Request $request)
    {
        try {
            $incidenciaId = $request->input('incidencia_id');
            $nombreArchivo = $request->input('nombre_archivo');

            $incidencia = IncidenciaProveedor::find($incidenciaId);
            if (!$incidencia) {
                return response()->json(['success' => false, 'message' => 'Incidencia no encontrada'], 404);
            }

            $archivos = $incidencia->archivos ?? [];
            $archivos = array_filter($archivos, function ($archivo) use ($nombreArchivo) {
                return $archivo['nombre'] !== $nombreArchivo;
            });

            // Eliminar archivo físico
            $rutaArchivo = storage_path('app/public/incidencias/' . $nombreArchivo);
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }

            // Actualizar registro
            $incidencia->archivos = array_values($archivos);
            $incidencia->save();

            return response()->json(['success' => true, 'message' => 'Archivo eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar archivo'], 500);
        }
    }

    /**
     * Eliminar archivo de devolución
     */
    public function eliminarArchivoDevolucion(Request $request)
    {
        try {
            $devolucionId = $request->input('devolucion_id');
            $nombreArchivo = $request->input('nombre_archivo');

            $devolucion = DevolucionProveedor::find($devolucionId);
            if (!$devolucion) {
                return response()->json(['success' => false, 'message' => 'Devolución no encontrada'], 404);
            }

            $archivos = $devolucion->archivos ?? [];
            $archivos = array_filter($archivos, function ($archivo) use ($nombreArchivo) {
                return $archivo['nombre'] !== $nombreArchivo;
            });

            // Eliminar archivo físico
            $rutaArchivo = storage_path('app/public/devoluciones/' . $nombreArchivo);
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }

            // Actualizar registro
            $devolucion->archivos = array_values($archivos);
            $devolucion->save();

            return response()->json(['success' => true, 'message' => 'Archivo eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar archivo'], 500);
        }
    }

    /**
     * Eliminar archivo del informe de devolución
     */
    public function eliminarArchivoInformeDevolucion(Request $request)
    {
        try {
            $devolucionId = $request->input('devolucion_id');
            $nombreArchivo = $request->input('nombre_archivo');

            $devolucion = DevolucionProveedor::find($devolucionId);
            if (!$devolucion) {
                return response()->json(['success' => false, 'message' => 'Devolución no encontrada'], 404);
            }

            $archivosInforme = $devolucion->archivos_informe ?? [];
            $archivosInforme = array_filter($archivosInforme, function($archivo) use ($nombreArchivo) {
                return $archivo['nombre'] !== $nombreArchivo;
            });

            // Eliminar archivo físico
            $rutaArchivo = storage_path('app/public/devoluciones/archivos_informe/' . $nombreArchivo);
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }

            // Actualizar registro
            $devolucion->archivos_informe = array_values($archivosInforme);
            $devolucion->save();

            return response()->json(['success' => true, 'message' => 'Archivo del informe eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar archivo del informe'], 500);
        }
    }

    /**
     * Eliminar devolución completa
     */
    public function eliminarDevolucion($id)
    {
        try {
            $devolucion = DevolucionProveedor::find($id);
            
            if (!$devolucion) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Reclamación no encontrada'
                ], 404);
            }

            // Eliminar archivos físicos asociados
            $archivos = $devolucion->archivos ?? [];
            foreach ($archivos as $archivo) {
                $rutaArchivo = storage_path('app/public/devoluciones/' . $archivo['nombre']);
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
            }

            // Eliminar la devolución de la base de datos
            $nombreProveedor = $devolucion->nombre_proveedor ?? 'Proveedor';
            $devolucion->delete();

            return response()->json([
                'success' => true, 
                'message' => "Reclamación del proveedor {$nombreProveedor} eliminada correctamente"
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar devolución: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error al eliminar la reclamación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método de prueba para descargar archivos directamente
     */
    public function testDescargaArchivo($nombreArchivo)
    {
        // Limpiar buffer de salida
        while (ob_get_level()) {
            ob_end_clean();
        }

        $rutaArchivo = storage_path('app/public/incidencias/' . $nombreArchivo);

        if (!file_exists($rutaArchivo)) {
            return response('Archivo no encontrado: ' . $rutaArchivo, 404);
        }

        // Obtener tipo MIME
        $mimeType = mime_content_type($rutaArchivo) ?: 'application/octet-stream';

        // Retornar archivo directamente
        return response()->file($rutaArchivo, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($nombreArchivo) . '"'
        ]);
    }

    /**
     * Muestra la vista para recalcular todas las métricas de proveedores
     */
    public function recalcularMetricasWeb(Request $request)
    {
        return view('MainApp.material_kilo.recalcular_metricas');
    }

    /**
     * Ejecuta el recálculo completo de todas las métricas de proveedores
     */
    public function ejecutarRecalculoMetricas(Request $request)
    {
        try {
            \Log::info('===== INICIO RECÁLCULO WEB DE MÉTRICAS =====');

            $inicio = microtime(true);
            $resultados = [
                'total_periodos' => 0,
                'procesados' => 0,
                'errores' => 0,
                'detalles_errores' => [],
                'tiempo_ejecucion' => 0
            ];

            // Paso 1: Limpiar tabla de métricas existente
            \Log::info('Truncando tabla proveedor_metrics...');
            DB::table('proveedor_metrics')->truncate();

            // Paso 2: Obtener todos los períodos únicos de incidencias
            \Log::info('Obteniendo períodos de incidencias...');
            $periodosIncidencias = DB::table('incidencias_proveedores')
                ->select('id_proveedor', 'año', 'mes')
                ->distinct()
                ->get();

            // Paso 3: Obtener todos los períodos únicos de devoluciones
            \Log::info('Obteniendo períodos de devoluciones...');
            $periodosDevoluciones = DB::table('devoluciones_proveedores')
                ->select('id_proveedor', 'año', 'mes')
                ->distinct()
                ->get();

            // Paso 4: Combinar períodos únicos
            $todosLosPeriodos = $periodosIncidencias->concat($periodosDevoluciones)
                ->unique(function ($item) {
                    return $item->id_proveedor . '-' . $item->año . '-' . $item->mes;
                });

            $resultados['total_periodos'] = $todosLosPeriodos->count();
            \Log::info("Total de períodos únicos encontrados: {$resultados['total_periodos']}");

            // Paso 5: Procesar cada período
            foreach ($todosLosPeriodos as $periodo) {
                try {
                    $id_proveedor = $periodo->id_proveedor;
                    $año = $periodo->año;
                    $mes = $periodo->mes;

                    \Log::info("Procesando proveedor {$id_proveedor}, año {$año}, mes {$mes}");

                    // Contar incidencias por clasificación
                    $rg1 = DB::table('incidencias_proveedores')
                        ->where('id_proveedor', $id_proveedor)
                        ->where('año', $año)
                        ->where('mes', $mes)
                        ->where('clasificacion_incidencia', 'RG1')
                        ->count();

                    $rl1 = DB::table('incidencias_proveedores')
                        ->where('id_proveedor', $id_proveedor)
                        ->where('año', $año)
                        ->where('mes', $mes)
                        ->where('clasificacion_incidencia', 'RL1')
                        ->count();

                    // Contar devoluciones por clasificación
                    $dev1 = DB::table('devoluciones_proveedores')
                        ->where('id_proveedor', $id_proveedor)
                        ->where('año', $año)
                        ->where('mes', $mes)
                        ->where('clasificacion_devolucion', 'DEV1')
                        ->count();

                    $rok1 = DB::table('devoluciones_proveedores')
                        ->where('id_proveedor', $id_proveedor)
                        ->where('año', $año)
                        ->where('mes', $mes)
                        ->where('clasificacion_devolucion', 'ROK1')
                        ->count();

                    $ret1 = DB::table('devoluciones_proveedores')
                        ->where('id_proveedor', $id_proveedor)
                        ->where('año', $año)
                        ->where('mes', $mes)
                        ->where('clasificacion_devolucion', 'RET1')
                        ->count();

                    // Insertar o actualizar métricas
                    DB::table('proveedor_metrics')->updateOrInsert(
                        [
                            'proveedor_id' => $id_proveedor,
                            'año' => $año,
                            'mes' => $mes
                        ],
                        [
                            'rg1' => $rg1,
                            'rl1' => $rl1,
                            'dev1' => $dev1,
                            'rok1' => $rok1,
                            'ret1' => $ret1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    $resultados['procesados']++;
                    \Log::info("✓ Métricas actualizadas: RG1={$rg1}, RL1={$rl1}, DEV1={$dev1}, ROK1={$rok1}, RET1={$ret1}");
                } catch (\Exception $e) {
                    $resultados['errores']++;
                    $error = "Error en proveedor {$periodo->id_proveedor} ({$periodo->año}-{$periodo->mes}): {$e->getMessage()}";
                    $resultados['detalles_errores'][] = $error;
                    \Log::error($error);
                }
            }

            $fin = microtime(true);
            $resultados['tiempo_ejecucion'] = round($fin - $inicio, 2);

            \Log::info("===== FIN RECÁLCULO WEB DE MÉTRICAS =====");
            \Log::info("Total procesados: {$resultados['procesados']}/{$resultados['total_periodos']}");
            \Log::info("Errores: {$resultados['errores']}");
            \Log::info("Tiempo: {$resultados['tiempo_ejecucion']} segundos");

            return response()->json([
                'success' => true,
                'message' => 'Recálculo de métricas completado exitosamente',
                'resultados' => $resultados
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fatal en recálculo de métricas: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar el recálculo: ' . $e->getMessage(),
                'resultados' => $resultados ?? []
            ], 500);
        }
    }

    /**
     * Descargar archivo de formato para Excel de reclamaciones
     */
    public function descargarFormatoQuejas()
    {
        $rutaArchivo = resource_path('views/MainApp/material_kilo/FormatoQuejas.xlsx');

        if (!file_exists($rutaArchivo)) {
            abort(404, 'Archivo de formato no encontrado');
        }

        // Asegurar que no haya salida previa que pueda corromper el archivo
        if (ob_get_level()) {
            @ob_end_clean();
        }

        // Forzar la descarga con cabeceras apropiadas y transferencia binaria
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="FormatoQuejas.xlsx"'
        ];

        return response()->download($rutaArchivo, 'FormatoQuejas.xlsx', $headers);
    }

    public function obtenerHistorialEstados($tipo, $id)
    {
        try {
            $query = DB::table('estado_incidencia_reclamacion as e')
                ->join('users as u', 'e.id_user', '=', 'u.id')
                ->select(
                    'e.id',
                    'e.estado',
                    'e.created_at',
                    'u.name as user_name',
                    'u.email as user_email'
                );

            if ($tipo === 'incidencia') {
                $query->where('e.id_incidencia_proveedor', $id)
                    ->whereNull('e.id_devolucion_proveedor');
            } elseif ($tipo === 'devolucion') {
                $query->where('e.id_devolucion_proveedor', $id)
                    ->whereNull('e.id_incidencia_proveedor');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de registro inválido'
                ], 400);
            }

            $estados = $query->orderBy('e.created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $estados,
                'total' => $estados->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener historial de estados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar respuesta a incidencia o devolución
     */
    public function guardarRespuesta(Request $request)
    {
        try {
            // Validaciones básicas
            $request->validate([
                'tipo' => 'required|in:incidencia,devolucion',
                'id' => 'required|integer',
                'descripcion' => 'required|string|min:10|max:2000',
                'persona_contacto' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'fecha_respuesta' => 'nullable|date',
                'archivo1' => 'sometimes|nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt,zip,rar',
                'archivo2' => 'sometimes|nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt,zip,rar',
                'archivo3' => 'sometimes|nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt,zip,rar'
            ], [
                'tipo.required' => 'El tipo es requerido',
                'tipo.in' => 'Tipo inválido',
                'id.required' => 'ID es requerido',
                'id.integer' => 'ID debe ser un número',
                'descripcion.required' => 'La descripción es requerida',
                'descripcion.min' => 'La descripción debe tener al menos 10 caracteres',
                'descripcion.max' => 'La descripción no puede exceder 2000 caracteres',
                'persona_contacto.required' => 'La persona de contacto es requerida',
                'fecha_respuesta.date' => 'La fecha debe ser válida',
                'archivo1.mimes' => 'Archivo 1: Solo se permiten PDF, DOC, DOCX, JPG, JPEG, PNG, TXT',
                'archivo1.max' => 'Archivo 1: No puede exceder 10MB',
                'archivo2.mimes' => 'Archivo 2: Solo se permiten PDF, DOC, DOCX, JPG, JPEG, PNG, TXT',
                'archivo2.max' => 'Archivo 2: No puede exceder 10MB',
                'archivo3.mimes' => 'Archivo 3: Solo se permiten PDF, DOC, DOCX, JPG, JPEG, PNG, TXT',
                'archivo3.max' => 'Archivo 3: No puede exceder 10MB'
            ]);

            // Verificar que el registro existe
            if ($request->tipo === 'incidencia') {
                $registro = IncidenciaProveedor::find($request->id);
                if (!$registro) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Incidencia no encontrada'
                    ], 404);
                }
            } else {
                $registro = DevolucionProveedor::find($request->id);
                if (!$registro) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Devolución no encontrada'
                    ], 404);
                }
            }

            DB::beginTransaction();

            // Crear respuesta primero para obtener el ID
            $fechaRespuesta = $request->fecha_respuesta ? $request->fecha_respuesta : now()->toDateString();

            $respuestaData = [
                'fecha_respuesta' => $fechaRespuesta,
                'descripcion' => $request->descripcion,
                'persona_contacto' => $request->persona_contacto,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'rutas_archivos' => null
            ];

            if ($request->tipo === 'incidencia') {
                $respuestaData['id_incidencia_proveedor'] = $request->id;
            } else {
                $respuestaData['id_devolucion_proveedor'] = $request->id;
            }

            $respuesta = \App\Models\MainApp\RespuestaIncidenciaReclamacion::create($respuestaData);

            // Procesar archivos (máximo 3 slots) usando el ID de la respuesta
            $archivosGuardados = [];
            foreach (['archivo1', 'archivo2', 'archivo3'] as $index => $input) {
                if ($request->hasFile($input)) {
                    $archivo = $request->file($input);
                    if ($archivo && $archivo->isValid()) {
                        // Generar nombre único conservando la extensión original
                        $nombreOriginal = $archivo->getClientOriginalName();
                        $extension = $archivo->getClientOriginalExtension();
                        $nombreSinExtension = pathinfo($nombreOriginal, PATHINFO_FILENAME);
                        
                        // Crear nombre único: nombreOriginal_timestamp.extension
                        $nombreUnico = $nombreSinExtension . '_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        // Guardar con nombre específico
                        $rutaArchivo = $archivo->storeAs("emails_respuesta/{$respuesta->id}", $nombreUnico, 'public');

                        if ($rutaArchivo) {
                            // USAR EL NOMBRE ORIGINAL COMO CLAVE, pero almacenar la ruta con nombre único
                            $archivosGuardados[$nombreOriginal] = $rutaArchivo;
                        }
                    }
                }
            }

            // Actualizar rutas de archivos si hay archivos guardados
            if (!empty($archivosGuardados)) {
                $respuesta->rutas_archivos = json_encode($archivosGuardados);
                $respuesta->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Respuesta guardada correctamente',
                'data' => [
                    'id' => $respuesta->id,
                    'descripcion' => $respuesta->descripcion,
                    'fecha_respuesta' => $respuesta->fecha_respuesta,
                    'persona_contacto' => $respuesta->persona_contacto,
                    'total_archivos' => count($archivosGuardados)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar respuesta: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de respuestas
     */
    public function obtenerHistorialRespuestas($tipo, $id)
    {
        try {
            $query = \App\Models\MainApp\RespuestaIncidenciaReclamacion::query();

            if ($tipo === 'incidencia') {
                $query->where('id_incidencia_proveedor', $id);
            } else {
                $query->where('id_devolucion_proveedor', $id);
            }

            $respuestas = $query->orderBy('fecha_respuesta', 'desc')
                ->get()
                ->map(function ($respuesta) {
                    $archivos = [];
                    if ($respuesta->rutas_archivos) {
                        $archivosData = json_decode($respuesta->rutas_archivos, true);
                        if (is_array($archivosData)) {
                            foreach ($archivosData as $nombreOriginal => $rutaArchivo) {
                                if (!empty($rutaArchivo)) {
                                    $url = asset('storage/' . $rutaArchivo);

                                    // Detectar si es imagen usando el nombre original
                                    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                                    $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);

                                    $archivos[] = [
                                        'nombre_original' => $nombreOriginal,
                                        'ruta_completa' => $rutaArchivo,
                                        'url' => $url,
                                        'es_imagen' => $esImagen,
                                        'extension' => $extension
                                    ];
                                }
                            }
                        }
                    }

                    return [
                        'id' => $respuesta->id,
                        'descripcion' => $respuesta->descripcion,
                        'fecha_respuesta' => \Carbon\Carbon::parse($respuesta->fecha_respuesta)->format('d/m/Y'),
                        'persona_contacto' => $respuesta->persona_contacto,
                        'telefono' => $respuesta->telefono,
                        'email' => $respuesta->email,
                        'archivos' => $archivos,
                        'total_archivos' => count($archivos)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $respuestas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener historial de respuestas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar archivo de respuesta usando nombre directo
     */
    public function descargarArchivoRespuesta($respuestaId, $nombreArchivo)
    {
        try {

            // Validar parámetros de entrada
            if (!is_numeric($respuestaId) || empty($nombreArchivo)) {
                Log::warning("Parámetros inválidos - respuestaId: {$respuestaId}, nombreArchivo: {$nombreArchivo}");
                abort(400, 'Parámetros inválidos');
            }

            $respuesta = \App\Models\MainApp\RespuestaIncidenciaReclamacion::findOrFail($respuestaId);

            // Obtener la ruta real del archivo desde el JSON
            if (!$respuesta->rutas_archivos) {
                Log::warning("No hay archivos en la respuesta ID: {$respuestaId}");
                abort(404, 'No hay archivos en esta respuesta');
            }

            $archivos = json_decode($respuesta->rutas_archivos, true);
            if (!is_array($archivos) || !isset($archivos[$nombreArchivo])) {
                Log::warning("Archivo no encontrado: {$nombreArchivo} en respuesta {$respuestaId}");
                abort(404, 'Archivo no encontrado');
            }

            $rutaArchivo = $archivos[$nombreArchivo];

            // Normalizar la ruta del archivo (convertir / a \ para Windows)
            $rutaArchivoNormalizada = str_replace('/', DIRECTORY_SEPARATOR, $rutaArchivo);
            
            // Intentar diferentes ubicaciones para el archivo
            $posiblesRutas = [
                storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $rutaArchivoNormalizada),
                storage_path('app' . DIRECTORY_SEPARATOR . $rutaArchivoNormalizada),
                public_path('storage' . DIRECTORY_SEPARATOR . $rutaArchivoNormalizada)
            ];

            $rutaCompleta = null;
            foreach ($posiblesRutas as $ruta) {
                // Normalizar la ruta completa también
                $rutaNormalizada = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ruta);
                if (file_exists($rutaNormalizada)) {
                    $rutaCompleta = $rutaNormalizada;
                    break;
                }
            }

            if (!$rutaCompleta) {
                abort(404, 'Archivo físico no encontrado en el sistema');
            }

            $extension = pathinfo($rutaCompleta, PATHINFO_EXTENSION);
            $tamaño = filesize($rutaCompleta);

            // Verificar que el archivo no esté corrupto (tamaño > 0)
            if ($tamaño === 0) {
                Log::warning("Archivo está vacío: {$rutaCompleta}");
                abort(422, 'El archivo está vacío o corrupto');
            }
            
            // Determinar MIME type basado en la extensión
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'webp' => 'image/webp',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'txt' => 'text/plain',
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed'
            ];
            
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            
            // Limpiar cualquier buffer anterior
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Configurar headers HTTP directamente
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Content-Length: ' . $tamaño);
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Leer y enviar el archivo directamente
            readfile($rutaCompleta);
            
            exit;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Respuesta no encontrada - ID: {$respuestaId}");
            abort(404, 'Respuesta no encontrada');
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo de respuesta', [
                'respuesta_id' => $respuestaId,
                'nombre_archivo' => $nombreArchivo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error interno al procesar la descarga: ' . $e->getMessage());
        }
    }

    /**
     * Obtener datos de una respuesta específica para edición
     */
    public function obtenerDatosRespuesta($respuestaId)
    {
        try {
            $respuesta = \App\Models\MainApp\RespuestaIncidenciaReclamacion::findOrFail($respuestaId);

            // Procesar archivos si los hay
            $archivos = [];
            if ($respuesta->rutas_archivos) {
                $archivosData = json_decode($respuesta->rutas_archivos, true);
                if (is_array($archivosData)) {
                    foreach ($archivosData as $nombreOriginal => $rutaArchivo) {
                        if (!empty($rutaArchivo)) {
                            $url = asset('storage/' . $rutaArchivo);

                            // Detectar si es imagen usando el nombre original
                            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                            $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);

                            $archivos[] = [
                                'nombre_original' => $nombreOriginal,
                                'ruta_completa' => $rutaArchivo,
                                'url' => $url,
                                'es_imagen' => $esImagen,
                                'extension' => $extension
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'id' => $respuesta->id,
                'fecha_respuesta' => $respuesta->fecha_respuesta,
                'descripcion' => $respuesta->descripcion,
                'persona_contacto' => $respuesta->persona_contacto,
                'telefono' => $respuesta->telefono,
                'email' => $respuesta->email,
                'archivos' => $archivos
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de respuesta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos de la respuesta'
            ], 500);
        }
    }

    /**
     * Actualizar respuesta existente
     */
    public function actualizarRespuesta(Request $request, $respuestaId)
    {
        try {

            $request->validate([
                'fecha_respuesta' => 'required|date',
                'descripcion' => 'required|string',
                'persona_contacto' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255', // CORREGIDO
                'archivo1' => 'sometimes|nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt,zip,rar',
                'archivo2' => 'sometimes|nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt,zip,rar',
                'archivo3' => 'sometimes|nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt,zip,rar',
            ]);

            $respuesta = \App\Models\MainApp\RespuestaIncidenciaReclamacion::findOrFail($respuestaId);

            // Actualizar datos básicos
            $respuesta->fecha_respuesta = $request->fecha_respuesta;
            $respuesta->descripcion = $request->descripcion;
            $respuesta->persona_contacto = $request->persona_contacto;
            $respuesta->telefono = $request->telefono;
            $respuesta->email = $request->email; // CORREGIDO: usar 'email' no 'email_contacto'

            // Obtener archivos existentes con manejo seguro
            $archivosExistentes = [];
            if ($respuesta->rutas_archivos) {
                $decoded = json_decode($respuesta->rutas_archivos, true);
                $archivosExistentes = is_array($decoded) ? $decoded : [];
            }

            // Procesar nuevos archivos subidos
            $archivosFinales = $archivosExistentes; // Mantener archivos existentes
            
            foreach (['archivo1', 'archivo2', 'archivo3'] as $index => $campo) {
                if ($request->hasFile($campo)) {
                    $archivo = $request->file($campo);

                    // Generar nombre único conservando la extensión original
                    $nombreOriginal = $archivo->getClientOriginalName();
                    $extension = $archivo->getClientOriginalExtension();
                    $nombreSinExtension = pathinfo($nombreOriginal, PATHINFO_FILENAME);
                    
                    // Crear nombre único: nombreOriginal_timestamp.extension
                    $nombreUnico = $nombreSinExtension . '_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Guardar con nombre específico
                    $rutaArchivo = $archivo->storeAs("emails_respuesta/{$respuestaId}", $nombreUnico, 'public');

                    if ($rutaArchivo) {
                        // USAR EL NOMBRE ORIGINAL COMO CLAVE, pero almacenar la ruta con nombre único
                        $archivosFinales[$nombreOriginal] = $rutaArchivo;
                    }
                }
            }

            $respuesta->rutas_archivos = !empty($archivosFinales) ? json_encode($archivosFinales) : null;
            $respuesta->save();

            return response()->json([
                'success' => true,
                'message' => 'Respuesta actualizada correctamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Error de validación: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar respuesta: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la respuesta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar archivo individual de una respuesta
     */
    public function eliminarArchivoRespuesta(Request $request, $respuestaId, $nombreArchivo)
    {
        try {
            $respuesta = \App\Models\MainApp\RespuestaIncidenciaReclamacion::findOrFail($respuestaId);

            if (!$respuesta->rutas_archivos) {
                Log::warning("⚠️ No hay archivos en respuesta $respuestaId");
                return response()->json([
                    'success' => false,
                    'message' => 'No hay archivos en esta respuesta'
                ], 404);
            }

            $archivos = json_decode($respuesta->rutas_archivos, true);
            if (!is_array($archivos)) {
                Log::error("❌ Error decodificando JSON de archivos");
                return response()->json([
                    'success' => false,
                    'message' => 'Error en formato de archivos'
                ], 400);
            }

            if (!isset($archivos[$nombreArchivo])) {
                Log::warning("⚠️ Archivo no encontrado: $nombreArchivo");
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            // Obtener info del archivo antes de eliminar
            $rutaArchivo = $archivos[$nombreArchivo];

            // Eliminar archivo físico si existe
            if (!empty($rutaArchivo)) {
                // Normalizar ruta para Windows
                $rutaArchivoNormalizada = str_replace('/', DIRECTORY_SEPARATOR, $rutaArchivo);
                $rutaCompleta = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $rutaArchivoNormalizada);
                
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                } else {
                    Log::warning("⚠️ Archivo físico no encontrado: $rutaCompleta");
                }
            }

            // Eliminar del array usando nombre como clave
            unset($archivos[$nombreArchivo]);

            // Solo limpiar si está completamente vacío
            if (empty($archivos)) {
                $respuesta->rutas_archivos = null;
            } else {
                $respuesta->rutas_archivos = json_encode($archivos);
            }

            $respuesta->save();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("❌ Respuesta no encontrada: $respuestaId");
            return response()->json([
                'success' => false,
                'message' => 'Respuesta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ Error al eliminar archivo de respuesta: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar respuesta completa con todos sus archivos
     */
    public function eliminarRespuesta(Request $request, $respuestaId)
    {
        try {
            $respuesta = \App\Models\MainApp\RespuestaIncidenciaReclamacion::findOrFail($respuestaId);

            // Eliminar todos los archivos asociados
            if ($respuesta->rutas_archivos) {
                $archivos = json_decode($respuesta->rutas_archivos, true);
                if (is_array($archivos)) {
                    foreach ($archivos as $rutaArchivo) {
                        if (!empty($rutaArchivo)) {
                            // Normalizar ruta para Windows
                            $rutaArchivoNormalizada = str_replace('/', DIRECTORY_SEPARATOR, $rutaArchivo);
                            $rutaCompleta = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $rutaArchivoNormalizada);
                            
                            if (file_exists($rutaCompleta)) {
                                unlink($rutaCompleta);
                            }
                        }
                    }

                    // Intentar eliminar la carpeta si está vacía
                    $directorioRespuesta = storage_path('app/public/emails_respuesta/' . $respuestaId);
                    if (is_dir($directorioRespuesta) && count(scandir($directorioRespuesta)) == 2) {
                        rmdir($directorioRespuesta);
                    }
                }
            }

            // Eliminar el registro de la base de datos
            $respuesta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Respuesta eliminada correctamente'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("❌ Respuesta no encontrada: $respuestaId");
            return response()->json([
                'success' => false,
                'message' => 'Respuesta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ Error al eliminar respuesta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la respuesta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug - Verificar estructura de archivos de respuesta
     */
    public function debugRespuestaArchivos($respuestaId)
    {
        try {
            $respuesta = \App\Models\MainApp\RespuestaIncidenciaReclamacion::findOrFail($respuestaId);
            
            $archivos = [];
            if ($respuesta->rutas_archivos) {
                $archivos = json_decode($respuesta->rutas_archivos, true);
            }
            
            $debug = [
                'respuesta_id' => $respuesta->id,
                'json_raw' => $respuesta->rutas_archivos,
                'archivos_decoded' => $archivos,
                'archivos_fisica' => []
            ];
            
            // Verificar archivos físicos
            if (is_array($archivos)) {
                foreach ($archivos as $nombreOriginal => $rutaArchivo) {
                    // Normalizar ruta para Windows
                    $rutaArchivoNormalizada = str_replace('/', DIRECTORY_SEPARATOR, $rutaArchivo);
                    $rutaCompleta = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $rutaArchivoNormalizada);
                    
                    $debug['archivos_fisica'][$nombreOriginal] = [
                        'ruta_json' => $rutaArchivo,
                        'ruta_normalizada' => $rutaArchivoNormalizada,
                        'ruta_completa' => $rutaCompleta,
                        'existe' => file_exists($rutaCompleta),
                        'tamaño' => file_exists($rutaCompleta) ? filesize($rutaCompleta) : 0
                    ];
                }
            }
            
            return response()->json($debug);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Eliminar archivo individual de respuesta
     */
    public function eliminarArchivoIndividualRespuesta(Request $request)
    {
        try {
            $respuestaId = $request->input('respuesta_id');
            $nombreArchivo = $request->input('nombre_archivo');

            if (!$respuestaId || empty($nombreArchivo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos'
                ], 400);
            }

            $respuesta = \App\Models\MainApp\RespuestaIncidenciaReclamacion::findOrFail($respuestaId);

            if (!$respuesta->rutas_archivos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay archivos en esta respuesta'
                ], 404);
            }

            $archivos = json_decode($respuesta->rutas_archivos, true);
            if (!is_array($archivos) || !isset($archivos[$nombreArchivo])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            // Eliminar archivo físico
            $rutaArchivo = $archivos[$nombreArchivo];
            // Normalizar ruta para Windows
            $rutaArchivoNormalizada = str_replace('/', DIRECTORY_SEPARATOR, $rutaArchivo);
            $rutaCompleta = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $rutaArchivoNormalizada);
            
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }

            // Remover del array usando el nombre del archivo como clave
            unset($archivos[$nombreArchivo]);

            // Actualizar en la base de datos
            if (empty($archivos)) {
                $respuesta->rutas_archivos = null;
            } else {
                $respuesta->rutas_archivos = json_encode($archivos);
            }
            $respuesta->save();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al eliminar archivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar exportación por lotes para archivos grandes
     */
    public function iniciarExportacionLotes(Request $request)
    {
        try {
            // Crear registro de job de exportación
            $jobId = uniqid('export_', true);

            // Obtener filtros
            $filtros = [
                'mes' => $request->get('mes'),
                'año' => $request->get('año', \Carbon\Carbon::now()->year),
                'proveedor' => $request->get('proveedor', ''),
                'tipo' => $request->get('tipo', ''),
                'codigo_proveedor' => $request->get('codigo_proveedor', ''),
                'codigo_producto' => $request->get('codigo_producto', ''),
                'gravedad' => $request->get('gravedad', ''),
                'no_queja' => $request->get('no_queja', ''),
            ];

            // Contar total de registros
            $totalRegistros = $this->contarRegistrosExport($filtros);

            if ($totalRegistros === 0) {
                return response()->json([
                    'error' => 'No se encontraron datos para exportar con los filtros aplicados'
                ], 404);
            }

            // Crear directorio temporal para el job
            $tempDir = storage_path('app/exports/' . $jobId);
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Guardar configuración del job
            $jobConfig = [
                'id' => $jobId,
                'filtros' => $filtros,
                'total_registros' => $totalRegistros,
                'registros_procesados' => 0,
                'lote_actual' => 0,
                'lotes_totales' => ceil($totalRegistros / 1000), // 1000 registros por lote
                'estado' => 'iniciado',
                'archivo_final' => null,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            file_put_contents($tempDir . '/config.json', json_encode($jobConfig, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'job_id' => $jobId,
                'total_registros' => $totalRegistros,
                'lotes_totales' => $jobConfig['lotes_totales'],
                'mensaje' => 'Exportación iniciada. Procesando por lotes...'
            ]);
        } catch (\Exception $e) {
            Log::error('Error iniciando exportación por lotes: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al iniciar la exportación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar siguiente lote
     */
    public function procesarSiguienteLote(Request $request)
    {
        try {
            $jobId = $request->get('job_id');

            $tempDir = storage_path('app/exports/' . $jobId);
            $configFile = $tempDir . '/config.json';

            if (!file_exists($configFile)) {
                return response()->json(['error' => 'Job no encontrado'], 404);
            }

            // Leer configuración actual
            $jobConfig = json_decode(file_get_contents($configFile), true);

            if ($jobConfig['estado'] === 'completado') {
                return response()->json([
                    'completado' => true,
                    'archivo_final' => $jobConfig['archivo_final']
                ]);
            }

            // Procesar siguiente lote
            $loteActual = $jobConfig['lote_actual'];
            $offset = $loteActual * 1000;
            $limit = 1000;

            // Obtener datos del lote
            $datosLote = $this->obtenerDatosLote($jobConfig['filtros'], $offset, $limit);

            // Guardar lote como archivo temporal
            $archivoLote = $tempDir . '/lote_' . $loteActual . '.json';
            file_put_contents($archivoLote, json_encode($datosLote));

            // Actualizar progreso
            $jobConfig['registros_procesados'] += count($datosLote);
            $jobConfig['lote_actual']++;
            $jobConfig['updated_at'] = now()->toISOString();

            // Verificar si es el último lote
            if ($jobConfig['lote_actual'] >= $jobConfig['lotes_totales']) {
                $jobConfig['estado'] = 'listo_para_generar';
            }

            file_put_contents($configFile, json_encode($jobConfig, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'progreso' => [
                    'lote_actual' => $jobConfig['lote_actual'],
                    'lotes_totales' => $jobConfig['lotes_totales'],
                    'registros_procesados' => $jobConfig['registros_procesados'],
                    'total_registros' => $jobConfig['total_registros'],
                    'porcentaje' => round(($jobConfig['registros_procesados'] / $jobConfig['total_registros']) * 100, 2)
                ],
                'completado' => $jobConfig['estado'] === 'listo_para_generar'
            ]);
        } catch (\Exception $e) {
            Log::error('Error procesando lote: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error procesando lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar Excel final
     */
    public function generarExcelFinal(Request $request)
    {
        try {
            $jobId = $request->get('job_id');

            $tempDir = storage_path('app/exports/' . $jobId);
            $configFile = $tempDir . '/config.json';

            $jobConfig = json_decode(file_get_contents($configFile), true);
            $jobConfig['estado'] = 'generando_excel';
            file_put_contents($configFile, json_encode($jobConfig, JSON_PRETTY_PRINT));

            // Crear Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Historial');

            // Headers
            $headers = [
                'Tipo',
                'ID Proveedor',
                'Nombre Proveedor',
                'Fecha Reclamación',
                'Clasificación',
                'Descripción',
                'Estado',
                'Código Producto',
                'No. Queja'
            ];

            foreach ($headers as $col => $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            }

            // Estilo para encabezados
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']]
            ];
            $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

            // Procesar todos los lotes guardados
            $row = 2;
            for ($lote = 0; $lote < $jobConfig['lotes_totales']; $lote++) {
                $archivoLote = $tempDir . '/lote_' . $lote . '.json';

                if (file_exists($archivoLote)) {
                    $datosLote = json_decode(file_get_contents($archivoLote), true);

                    foreach ($datosLote as $dato) {
                        // Aplicar misma lógica que la vista para descripción/producto
                        $descripcionFinal = '';
                        if ($dato['tipo'] === 'Incidencia') {
                            $descripcionFinal = $dato['descripcion_incidencia'] ?? $dato['producto'] ?? 'Sin descripción';
                        } else {
                            $descripcionFinal = $dato['descripcion_producto'] ?? $dato['codigo_producto'] ?? 'Sin descripción';
                        }

                        $sheet->setCellValue('A' . $row, $dato['tipo']);
                        $sheet->setCellValue('B' . $row, $dato['id_proveedor'] ?? '');
                        $sheet->setCellValue('C' . $row, $dato['nombre_proveedor'] ?? '');
                        $sheet->setCellValue('D' . $row, $dato['fecha_reclamacion'] ? date('d/m/Y', strtotime($dato['fecha_reclamacion'])) : '');
                        $sheet->setCellValue('E' . $row, $dato['clasificacion_incidencia'] ?? '');
                        $sheet->setCellValue('F' . $row, $descripcionFinal);
                        $sheet->setCellValue('G' . $row, $dato['estado'] ?? '');
                        $sheet->setCellValue('H' . $row, $dato['codigo_producto'] ?? '');
                        $sheet->setCellValue('I' . $row, $dato['no_queja'] ?? '');
                        $row++;
                    }
                }
            }

            // Ajustar ancho de columnas
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Nombre de archivo
            $año = $jobConfig['filtros']['año'];
            $mes = $jobConfig['filtros']['mes'];
            $filename = 'Incidencias_Devoluciones_' . $año;
            if ($mes) {
                $meses = [
                    '',
                    'Enero',
                    'Febrero',
                    'Marzo',
                    'Abril',
                    'Mayo',
                    'Junio',
                    'Julio',
                    'Agosto',
                    'Septiembre',
                    'Octubre',
                    'Noviembre',
                    'Diciembre'
                ];
                $filename .= '_' . $meses[$mes];
            }
            $filename .= '.xlsx';

            // Guardar Excel final
            $archivoFinal = $tempDir . '/' . $filename;
            $writer = new Xlsx($spreadsheet);
            $writer->save($archivoFinal);

            // Actualizar configuración
            $jobConfig['estado'] = 'completado';
            $jobConfig['archivo_final'] = $archivoFinal;
            $jobConfig['filename'] = $filename;
            $jobConfig['updated_at'] = now()->toISOString();
            file_put_contents($configFile, json_encode($jobConfig, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'archivo_final' => $archivoFinal,
                'filename' => $filename,
                'job_id' => $jobId
            ]);
        } catch (\Exception $e) {
            Log::error('Error generando Excel final: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error generando Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar Excel generado
     */
    public function descargarExcelGenerado($jobId)
    {
        $tempDir = storage_path('app/exports/' . $jobId);
        $configFile = $tempDir . '/config.json';

        if (!file_exists($configFile)) {
            abort(404, 'Archivo no encontrado');
        }

        $jobConfig = json_decode(file_get_contents($configFile), true);

        if ($jobConfig['estado'] !== 'completado' || !$jobConfig['archivo_final']) {
            abort(404, 'Excel no está listo');
        }

        return response()->download($jobConfig['archivo_final'], $jobConfig['filename']);
    }

    /**
     * Exportar historial de incidencias y devoluciones a Excel
     */
    public function exportarHistorialExcel(Request $request)
    {
        try {
            // Configuración para archivos grandes
            set_time_limit(300);
            ini_set('memory_limit', '512M');

            // Obtener filtros
            $mes = $request->get('mes');
            $año = $request->get('año', \Carbon\Carbon::now()->year);
            $proveedor = $request->get('proveedor', '');
            $tipo = $request->get('tipo', '');
            $codigo_proveedor = $request->get('codigo_proveedor', '');
            $codigo_producto = $request->get('codigo_producto', '');
            $gravedad = $request->get('gravedad', '');
            $no_queja = $request->get('no_queja', '');

            $datos = [];

            // Obtener incidencias - MISMOS FILTROS QUE LA VISTA
            if (!$tipo || $tipo === 'incidencia') {
                $incidencias = DB::table('incidencias_proveedores as i')
                    ->leftJoin('proveedores as p', 'i.id_proveedor', '=', 'p.id_proveedor')
                    ->select(
                        DB::raw("'Incidencia' as tipo"),
                        'i.id_proveedor',
                        'p.nombre_proveedor',
                        'i.fecha_incidencia as fecha_reclamacion',
                        'i.clasificacion_incidencia',
                        'i.descripcion_incidencia',
                        'i.producto',
                        'i.estado',
                        'i.codigo as codigo_producto',
                        DB::raw("'' as no_queja")
                    )
                    ->where('i.año', $año);

                // Aplicar TODOS los filtros igual que la vista
                if ($mes) $incidencias->where('i.mes', $mes);
                if ($proveedor) $incidencias->where('p.nombre_proveedor', 'LIKE', '%' . $proveedor . '%');
                if ($codigo_proveedor) $incidencias->where('i.id_proveedor', 'LIKE', '%' . $codigo_proveedor . '%');
                if ($codigo_producto) $incidencias->where('i.codigo', 'LIKE', '%' . $codigo_producto . '%');
                if ($gravedad === 'grave') $incidencias->where('i.clasificacion_incidencia', 'RG1');
                elseif ($gravedad === 'leve') $incidencias->where('i.clasificacion_incidencia', 'RL1');
                if ($no_queja) $incidencias->where('i.no_queja', 'LIKE', '%' . $no_queja . '%');

                foreach ($incidencias->get() as $incidencia) {
                    $datos[] = (array)$incidencia;
                }
            }

            // Obtener devoluciones - MISMOS FILTROS QUE LA VISTA
            if (!$tipo || $tipo === 'devolucion') {
                $devoluciones = DB::table('devoluciones_proveedores as d')
                    ->leftJoin('proveedores as p', 'd.codigo_proveedor', '=', 'p.id_proveedor')
                    ->select(
                        DB::raw("'Devolucion' as tipo"),
                        'd.codigo_proveedor as id_proveedor',
                        'p.nombre_proveedor',
                        'd.fecha_reclamacion',
                        'd.clasificacion_incidencia',
                        'd.descripcion_producto',
                        'd.codigo_producto',
                        'd.estado',
                        'd.codigo_producto',
                        'd.no_queja'
                    )
                    ->where('d.año', $año);

                // Aplicar TODOS los filtros igual que la vista
                if ($mes) $devoluciones->where('d.mes', $mes);
                if ($proveedor) $devoluciones->where('p.nombre_proveedor', 'LIKE', '%' . $proveedor . '%');
                if ($codigo_proveedor) $devoluciones->where('d.codigo_proveedor', 'LIKE', '%' . $codigo_proveedor . '%');
                if ($codigo_producto) $devoluciones->where('d.codigo_producto', 'LIKE', '%' . $codigo_producto . '%');
                if ($gravedad === 'grave') $devoluciones->where('d.clasificacion_incidencia', 'RG1');
                elseif ($gravedad === 'leve') $devoluciones->where('d.clasificacion_incidencia', 'RL1');
                if ($no_queja) $devoluciones->where('d.no_queja', 'LIKE', '%' . $no_queja . '%');

                foreach ($devoluciones->get() as $devolucion) {
                    $datos[] = (array)$devolucion;
                }
            }

            // Crear Excel siempre (aunque no haya datos)
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Historial');

            // Encabezados
            $sheet->setCellValue('A1', 'Tipo');
            $sheet->setCellValue('B1', 'ID Proveedor');
            $sheet->setCellValue('C1', 'Nombre Proveedor');
            $sheet->setCellValue('D1', 'Fecha Reclamacion');
            $sheet->setCellValue('E1', 'Clasificacion');
            $sheet->setCellValue('F1', 'Descripcion/Producto');
            $sheet->setCellValue('G1', 'Estado');
            $sheet->setCellValue('H1', 'Codigo Producto');
            $sheet->setCellValue('I1', 'No Queja');

            // Estilo para encabezados
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']]
            ];
            $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

            // Llenar datos
            $row = 2;
            foreach ($datos as $dato) {
                // Aplicar misma lógica que la vista para descripción/producto
                $descripcionFinal = '';
                if ($dato['tipo'] === 'Incidencia') {
                    $descripcionFinal = $dato['descripcion_incidencia'] ?? $dato['producto'] ?? 'Sin descripción';
                } else {
                    $descripcionFinal = $dato['descripcion_producto'] ?? $dato['codigo_producto'] ?? 'Sin descripción';
                }

                $sheet->setCellValue('A' . $row, $dato['tipo']);
                $sheet->setCellValue('B' . $row, $dato['id_proveedor'] ?? '');
                $sheet->setCellValue('C' . $row, $dato['nombre_proveedor'] ?? '');
                $sheet->setCellValue('D' . $row, $dato['fecha_reclamacion'] ? date('d/m/Y', strtotime($dato['fecha_reclamacion'])) : '');
                $sheet->setCellValue('E' . $row, $dato['clasificacion_incidencia'] ?? '');
                $sheet->setCellValue('F' . $row, $descripcionFinal);
                $sheet->setCellValue('G' . $row, $dato['estado'] ?? '');
                $sheet->setCellValue('H' . $row, $dato['codigo_producto'] ?? '');
                $sheet->setCellValue('I' . $row, $dato['no_queja'] ?? '');
                $row++;
            }

            // Ajustar ancho de columnas
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Nombre de archivo mejorado
            $filename = 'Incidencias_Devoluciones_' . $año;
            if ($mes) {
                $meses = [
                    '',
                    'Enero',
                    'Febrero',
                    'Marzo',
                    'Abril',
                    'Mayo',
                    'Junio',
                    'Julio',
                    'Agosto',
                    'Septiembre',
                    'Octubre',
                    'Noviembre',
                    'Diciembre'
                ];
                $filename .= '_' . $meses[$mes];
            }
            $filename .= '.xlsx';

            // Usar método directo sin archivos temporales
            $writer = new Xlsx($spreadsheet);

            // Headers para descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            // Limpiar cualquier output previo
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Enviar directamente
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Error en exportar Excel: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al generar el archivo Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Contar registros para exportación
     */
    private function contarRegistrosExport($filtros)
    {
        $total = 0;

        // Contar incidencias - TODOS LOS FILTROS IGUAL QUE LA VISTA
        if (!$filtros['tipo'] || $filtros['tipo'] === 'incidencia') {
            $query = DB::table('incidencias_proveedores as i')
                ->leftJoin('proveedores as p', 'i.id_proveedor', '=', 'p.id_proveedor')
                ->where('i.año', $filtros['año']);

            if ($filtros['mes']) $query->where('i.mes', $filtros['mes']);
            if ($filtros['proveedor']) $query->where('p.nombre_proveedor', 'LIKE', '%' . $filtros['proveedor'] . '%');
            if ($filtros['codigo_proveedor']) $query->where('i.id_proveedor', 'LIKE', '%' . $filtros['codigo_proveedor'] . '%');
            if ($filtros['codigo_producto']) $query->where('i.codigo', 'LIKE', '%' . $filtros['codigo_producto'] . '%');
            if ($filtros['gravedad'] === 'grave') $query->where('i.clasificacion_incidencia', 'RG1');
            elseif ($filtros['gravedad'] === 'leve') $query->where('i.clasificacion_incidencia', 'RL1');
            if ($filtros['no_queja']) $query->where('i.no_queja', 'LIKE', '%' . $filtros['no_queja'] . '%');

            $total += $query->count();
        }

        // Contar devoluciones - TODOS LOS FILTROS IGUAL QUE LA VISTA
        if (!$filtros['tipo'] || $filtros['tipo'] === 'devolucion') {
            $query = DB::table('devoluciones_proveedores as d')
                ->leftJoin('proveedores as p', 'd.codigo_proveedor', '=', 'p.id_proveedor')
                ->where('d.año', $filtros['año']);

            if ($filtros['mes']) $query->where('d.mes', $filtros['mes']);
            if ($filtros['proveedor']) $query->where('p.nombre_proveedor', 'LIKE', '%' . $filtros['proveedor'] . '%');
            if ($filtros['codigo_proveedor']) $query->where('d.codigo_proveedor', 'LIKE', '%' . $filtros['codigo_proveedor'] . '%');
            if ($filtros['codigo_producto']) $query->where('d.codigo_producto', 'LIKE', '%' . $filtros['codigo_producto'] . '%');
            if ($filtros['gravedad'] === 'grave') $query->where('d.clasificacion_incidencia', 'RG1');
            elseif ($filtros['gravedad'] === 'leve') $query->where('d.clasificacion_incidencia', 'RL1');
            if ($filtros['no_queja']) $query->where('d.no_queja', 'LIKE', '%' . $filtros['no_queja'] . '%');

            $total += $query->count();
        }

        return $total;
    }

    /**
     * Obtener datos de un lote específico
     */
    private function obtenerDatosLote($filtros, $offset, $limit)
    {
        $datos = [];

        // Obtener incidencias si es necesario - USAR MISMAS TABLAS QUE LA VISTA
        if (!$filtros['tipo'] || $filtros['tipo'] === 'incidencia') {
            $incidencias = DB::table('incidencias_proveedores as i')
                ->leftJoin('proveedores as p', 'i.id_proveedor', '=', 'p.id_proveedor')
                ->select(
                    DB::raw("'Incidencia' as tipo"),
                    'i.id_proveedor',
                    'p.nombre_proveedor',
                    'i.fecha_incidencia as fecha_reclamacion',
                    'i.clasificacion_incidencia',
                    'i.descripcion_incidencia',
                    'i.producto',
                    'i.estado',
                    'i.codigo as codigo_producto',
                    DB::raw("'' as no_queja")
                )
                ->where('i.año', $filtros['año']);

            // Aplicar TODOS los filtros igual que la vista
            if ($filtros['mes']) $incidencias->where('i.mes', $filtros['mes']);
            if ($filtros['proveedor']) $incidencias->where('p.nombre_proveedor', 'LIKE', '%' . $filtros['proveedor'] . '%');
            if ($filtros['codigo_proveedor']) $incidencias->where('i.id_proveedor', 'LIKE', '%' . $filtros['codigo_proveedor'] . '%');
            if ($filtros['codigo_producto']) $incidencias->where('i.codigo', 'LIKE', '%' . $filtros['codigo_producto'] . '%');
            if ($filtros['gravedad'] === 'grave') $incidencias->where('i.clasificacion_incidencia', 'RG1');
            elseif ($filtros['gravedad'] === 'leve') $incidencias->where('i.clasificacion_incidencia', 'RL1');
            if ($filtros['no_queja']) $incidencias->where('i.no_queja', 'LIKE', '%' . $filtros['no_queja'] . '%');

            foreach ($incidencias->offset($offset)->limit($limit)->get() as $incidencia) {
                $datos[] = (array)$incidencia;
            }
        }

        // Obtener devoluciones si es necesario - USAR MISMAS TABLAS QUE LA VISTA
        if (!$filtros['tipo'] || $filtros['tipo'] === 'devolucion') {
            $devoluciones = DB::table('devoluciones_proveedores as d')
                ->leftJoin('proveedores as p', 'd.codigo_proveedor', '=', 'p.id_proveedor')
                ->select(
                    DB::raw("'Devolucion' as tipo"),
                    'd.codigo_proveedor as id_proveedor',
                    'p.nombre_proveedor',
                    'd.fecha_reclamacion',
                    'd.clasificacion_incidencia',
                    'd.descripcion_producto',
                    'd.codigo_producto',
                    'd.estado',
                    'd.codigo_producto',
                    'd.no_queja'
                )
                ->where('d.año', $filtros['año']);

            // Aplicar TODOS los filtros igual que la vista
            if ($filtros['mes']) $devoluciones->where('d.mes', $filtros['mes']);
            if ($filtros['proveedor']) $devoluciones->where('p.nombre_proveedor', 'LIKE', '%' . $filtros['proveedor'] . '%');
            if ($filtros['codigo_proveedor']) $devoluciones->where('d.codigo_proveedor', 'LIKE', '%' . $filtros['codigo_proveedor'] . '%');
            if ($filtros['codigo_producto']) $devoluciones->where('d.codigo_producto', 'LIKE', '%' . $filtros['codigo_producto'] . '%');
            if ($filtros['gravedad'] === 'grave') $devoluciones->where('d.clasificacion_incidencia', 'RG1');
            elseif ($filtros['gravedad'] === 'leve') $devoluciones->where('d.clasificacion_incidencia', 'RL1');
            if ($filtros['no_queja']) $devoluciones->where('d.no_queja', 'LIKE', '%' . $filtros['no_queja'] . '%');

            foreach ($devoluciones->offset($offset)->limit($limit)->get() as $devolucion) {
                $datos[] = (array)$devolucion;
            }
        }

        return $datos;
    }
}

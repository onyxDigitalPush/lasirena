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
use App\Models\MainApp\Proveedor;

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
            $query->where('material_kilos.mes', 'LIKE', '%' . request('mes') . '%');
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
                    'materiales.descripcion as nombre_material',
                    'material_kilos.ctd_emdev',
                    'material_kilos.umb',
                    'material_kilos.ce',
                    'material_kilos.valor_emdev',
                    'material_kilos.factor_conversion',
                    'material_kilos.codigo_material',
                    'material_kilos.mes',
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
            $request->validate([
                'material_kilo_id' => 'required|exists:material_kilos,id',
                'factor_conversion' => 'nullable|numeric|min:0'
            ]);

            $material_kilo = MaterialKilo::findOrFail($request->material_kilo_id);

            // Actualizar solo el factor de conversión
            $material_kilo->factor_conversion = $request->factor_conversion;

            // Recalcular el total_kg si hay factor de conversión
            if ($request->factor_conversion && $request->factor_conversion > 0) {
                $material_kilo->total_kg = $material_kilo->ctd_emdev * $request->factor_conversion;
            } else {
                $material_kilo->total_kg = 0;
            }

            $material_kilo->save();

            return response()->json([
                'success' => true,
                'message' => 'Material actualizado correctamente'
            ]);
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
            foreach ($datos as $idx => $item) {
                // Buscar nombre del proveedor
                $proveedor = DB::table('proveedores')->where('id_proveedor', $item['Código Proveedor'])->first();
                $nombre_proveedor = $proveedor ? $proveedor->nombre_proveedor : null;

                $insert = [
                    'codigo_producto' => $item['Código Producto'],
                    'codigo_proveedor' => $item['Código Proveedor'],
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
            return redirect()->back()->with('success', 'Devoluciones insertadas correctamente');
        } catch (\Exception $e) {
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

        // Regex para extraer: del 25 al 31 de julio de 2025
        $regex = '/del\s+(\d{1,2})\s+al\s+(\d{1,2})\s+de\s+([a-zA-Záéíóúñ]+)\s+de\s+(\d{4})/u';
        $fecha_inicio = null;
        $fecha_fin = null;
        $mes = null;
        $año = null;
        if (preg_match($regex, $texto_cabecera, $matches)) {
            $dia_inicio = $matches[1];
            $dia_fin = $matches[2];
            $mes_nombre = strtolower($matches[3]);
            $año = (int)$matches[4];
            // Mapear nombre de mes a número
            $meses = [
                'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6,
                'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10,
                'noviembre' => 11, 'diciembre' => 12
            ];
            $mes = $meses[$mes_nombre] ?? null;
            if ($mes && $año) {
                $fecha_inicio = sprintf('%04d-%02d-%02d', $año, $mes, $dia_inicio);
                $fecha_fin = sprintf('%04d-%02d-%02d', $año, $mes, $dia_fin);
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

        // Asegurar que $año sea numérico
        $año = (int) $año;

        // Debug: Log para verificar los tipos de variables
        Log::info('Variables recibidas en evaluacionContinuaProveedores', [
            'mes' => $mes,
            'año' => $año,
            'proveedor' => $proveedor,
            'proveedor_type' => gettype($proveedor),
            'idProveedor' => $idProveedor,
            'idProveedor_type' => gettype($idProveedor)
        ]);

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

        // Filtrar por proveedor si está seleccionado
        if ($proveedor && is_string($proveedor)) {
            $query->where('proveedores.nombre_proveedor', $proveedor);
        }

        // Filtrar por ID proveedor si está especificado
        if ($idProveedor && is_string($idProveedor)) {
            $query->where('proveedores.id_proveedor', 'LIKE', '%' . $idProveedor . '%');
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

        return view('MainApp/material_kilo.evaluacion_continua_proveedores', compact(
            'totales_por_proveedor',
            'metricas_por_proveedor',
            'mes',
            'año',
            'proveedor',
            'idProveedor',
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
                'fecha_decision_destino_producto' => $request->fecha_decision_destino_producto,
                'tipo_incidencia' => $request->tipo_incidencia ?? ''
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

        $resultados = collect();

        // Obtener incidencias usando las tablas correctas (sin _kilo)
        if (!$tipo || $tipo === 'incidencia') {
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

            $resultados = $resultados->merge($incidencias->get());
        }

        // Obtener devoluciones usando las tablas correctas (sin _kilo)
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
                    'd.abierto',
                    DB::raw("'devolucion' as tipo_registro")
                )
                ->where('d.año', $año);

            if ($mes) {
                $devoluciones->where('d.mes', $mes);
            }

            if ($proveedor) {
                $devoluciones->where('p.nombre_proveedor', 'LIKE', '%' . $proveedor . '%');
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
        $incidencia = IncidenciaProveedor::findOrFail($id);

        // Obtener proveedores disponibles
        $proveedores = DB::table('proveedores')
            ->select('id_proveedor', 'nombre_proveedor')
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
                'tipo_incidencia' => $request->tipo_incidencia ?? ''
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
                'tipo_incidencia' => $request->tipo_incidencia ?? ''
            ]);

            // Actualizar las métricas automáticamente
            $this->actualizarMetricasIncidencias($request->id_proveedor, $request->año, $request->mes);

            return redirect()->route('material_kilo.historial_incidencias_devoluciones')->with('success', 'Incidencia actualizada correctamente');
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
        $devolucion = DevolucionProveedor::findOrFail($id);

        // Obtener proveedores disponibles
        $proveedores = DB::table('proveedores')
            ->select('id_proveedor', 'nombre_proveedor')
            ->orderBy('nombre_proveedor')
            ->get();

        // Variables para valores por defecto
        $mes = now()->month;
        $año = now()->year;

        return view('MainApp/material_kilo.devolucion_form', compact('devolucion', 'proveedores', 'mes', 'año'));
    }

    /**
     * Guardar nueva devolución desde página completa
     */
    public function guardarDevolucionCompleta(Request $request)
    {
        
        // $request->validate([
        //     'codigo_producto' => 'required|string|max:255',
        //     'descripcion_producto' => 'nullable|string|max:255',
        //     'año' => 'required|integer',
        //     'mes' => 'required|integer|between:1,12',
        //     'fecha_inicio' => 'nullable|date',
        //     'fecha_fin' => 'nullable|date',
        //     'fecha_reclamacion' => 'nullable|date',
        //     'fecha_reclamacion_respuesta' => 'nullable|date',
        //     'np' => 'nullable|string|max:255',
        //     'no_queja' => 'nullable|string|max:255',
        //     'origen' => 'nullable|string|max:255',
        //     'nombre_tienda' => 'nullable|string|max:255',
        //     'clasificacion_incidencia' => 'nullable|string|max:255',
        //     'tipo_reclamacion' => 'nullable|string|max:255',
        //     'top100fy2' => 'nullable|string|max:255',
        //     'descripcion_motivo' => 'nullable|string',
        //     'descripcion_queja' => 'nullable|string',
        //     'especificacion_motivo_reclamacion_leve' => 'nullable|string',
        //     'especificacion_motivo_reclamacion_grave' => 'nullable|string',
        //     'lote_sirena' => 'nullable|string|max:255',
        //     'lote_proveedor' => 'nullable|string|max:255',
        //     'recuperamos_objeto_extraño' => 'nullable|in:Si,No',
        //     'informe_a_proveedor' => 'nullable|in:Si,No',
        //     'fecha_envio_proveedor' => 'nullable|date',
        //     'fecha_respuesta_proveedor' => 'nullable|date',
        //     'informe' => 'nullable|string',
        //     'informe_respuesta' => 'nullable|string',
        //     'abierto' => 'nullable|in:Si,No',
        //     'comentarios' => 'nullable|string',
        // ]);

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
            ]);


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
        ]);

        try {
            $devolucion = DevolucionProveedor::findOrFail($id);

            // Obtener el nombre del proveedor
            $proveedor = DB::table('proveedores')
                ->where('id_proveedor', $request->codigo_proveedor)
                ->select('nombre_proveedor')
                ->first();

            if (!$proveedor) {
                return redirect()->back()->with('error', 'Proveedor no encontrado');
            }

            // Actualizar la devolución
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
            ]);

            return redirect()->route('material_kilo.historial_incidencias_devoluciones')->with('success', 'Devolución actualizada correctamente');
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
}

@extends('layouts.app')

@section('app_name', config('app.name'))

@section('custom_head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .btn-group .btn {
        margin-left: 5px;
    }
    .btn-group .btn:first-child {
        margin-left: 0;
    }
    .card-header .btn-group .btn {
        border: 1px solid rgba(255,255,255,0.3);
    }
    .card-header .btn-group .btn:hover {
        background-color: rgba(255,255,255,0.1);
    }
    
    /* Estilos para filas clickeables */
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    
    /* Badges para tipos */
    .badge-incidencia {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    .badge-devolucion {
        background-color: #17a2b8 !important;
        color: #fff !important;
    }
</style>
<script>
    // URLs globales
    window.appBaseUrl = '{{ url("/") }}';
    window.guardarIncidenciaUrl = '{{ route("material_kilo.guardar_incidencia") }}';
    window.guardarDevolucionUrl = '{{ route("material_kilo.guardar_devolucion") }}';
    window.obtenerIncidenciasUrl = '{{ route("material_kilo.obtener_incidencias") }}';
    window.obtenerDevolucionesUrl = '{{ route("material_kilo.obtener_devoluciones") }}';
    window.obtenerIncidenciaUrl = '{{ url("material_kilo/obtener-incidencia") }}';
    window.obtenerDevolucionUrl = '{{ url("material_kilo/obtener-devolucion") }}';
    
    // Variables globales para los filtros actuales
    window.filtroMes = {{ $mes ?? 'null' }};
    window.filtroAño = {{ $año }};
    window.filtroProveedor = '{{ $proveedor }}';
    window.filtroTipo = '{{ $tipo }}';
    
    // Página cargada sin modales
    $(document).ready(function() {
        console.log('Página cargada - Sistema sin modales');
        
        // Inicializar DataTable
        $('#table_historial').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "pageLength": 25,
            "order": [[ 3, "desc" ]]
        });
    });
    
    // Función para editar registros
    function editarRegistro(tipo, id) {
        if (tipo === 'incidencia') {
            window.location.href = '{{ url("material_kilo/incidencia/editar") }}/' + id;
        } else if (tipo === 'devolucion') {
            window.location.href = '{{ url("material_kilo/devolucion/editar") }}/' + id;
        }
    }
    
    // Manejar filtros
    $('#aplicarFiltros').click(function() {
        var params = new URLSearchParams();
        
        if ($('#filtro_mes').val()) params.append('mes', $('#filtro_mes').val());
        if ($('#filtro_año').val()) params.append('año', $('#filtro_año').val());
        if ($('#filtro_proveedor').val()) params.append('proveedor', $('#filtro_proveedor').val());
        if ($('#filtro_tipo').val()) params.append('tipo', $('#filtro_tipo').val());
        
        window.location.href = '{{ route("material_kilo.historial_incidencias_devoluciones") }}?' + params.toString();
    });
    
    $('#limpiarFiltros').click(function() {
        window.location.href = '{{ route("material_kilo.historial_incidencias_devoluciones") }}';
    });
</script>
@endsection

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-history icon-gradient bg-secondary"></i>
            </div>
            <div>Historial de Incidencias y Devoluciones
                <div class="page-title-subheading">
                    Gestión y seguimiento de incidencias y devoluciones de proveedores
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <a class="m-2 btn btn-primary" href="{{ route('material_kilo.total_kg_proveedor') }}">
                <i class="fa fa-bar-chart mr-2"></i>Total KG por Proveedor
            </a>
            <a class="m-2 btn btn-success" href="{{ route('material_kilo.evaluacion_continua_proveedores') }}">
                <i class="fa fa-line-chart mr-2"></i>Evaluación Continua
            </a>
        </div>
    </div>
@endsection

@section('main_content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-filter mr-2"></i>Filtros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filtrosForm" class="row">
                            <div class="col-md-2">
                                <label for="filtro_mes">Mes:</label>
                                <select id="filtro_mes" name="mes" class="form-control">
                                    <option value="" {{ !$mes ? 'selected' : '' }}>Todos los meses</option>
                                    <option value="1" {{ $mes == 1 ? 'selected' : '' }}>Enero</option>
                                    <option value="2" {{ $mes == 2 ? 'selected' : '' }}>Febrero</option>
                                    <option value="3" {{ $mes == 3 ? 'selected' : '' }}>Marzo</option>
                                    <option value="4" {{ $mes == 4 ? 'selected' : '' }}>Abril</option>
                                    <option value="5" {{ $mes == 5 ? 'selected' : '' }}>Mayo</option>
                                    <option value="6" {{ $mes == 6 ? 'selected' : '' }}>Junio</option>
                                    <option value="7" {{ $mes == 7 ? 'selected' : '' }}>Julio</option>
                                    <option value="8" {{ $mes == 8 ? 'selected' : '' }}>Agosto</option>
                                    <option value="9" {{ $mes == 9 ? 'selected' : '' }}>Septiembre</option>
                                    <option value="10" {{ $mes == 10 ? 'selected' : '' }}>Octubre</option>
                                    <option value="11" {{ $mes == 11 ? 'selected' : '' }}>Noviembre</option>
                                    <option value="12" {{ $mes == 12 ? 'selected' : '' }}>Diciembre</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_año">Año:</label>
                                <select id="filtro_año" name="año" class="form-control" required>
                                    @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ $year == $año ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_proveedor">Proveedor:</label>
                                <select id="filtro_proveedor" name="proveedor" class="form-control">
                                    <option value="">Todos los proveedores</option>
                                    @foreach($proveedores_disponibles as $prov)
                                        <option value="{{ $prov->nombre_proveedor }}" {{ $proveedor == $prov->nombre_proveedor ? 'selected' : '' }}>
                                            {{ $prov->nombre_proveedor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_tipo">Tipo:</label>
                                <select id="filtro_tipo" name="tipo" class="form-control">
                                    <option value="" {{ $tipo == '' ? 'selected' : '' }}>Todos</option>
                                    <option value="incidencia" {{ $tipo == 'incidencia' ? 'selected' : '' }}>Solo Incidencias</option>
                                    <option value="devolucion" {{ $tipo == 'devolucion' ? 'selected' : '' }}>Solo Devoluciones</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="aplicarFiltros" class="btn btn-primary mr-2">
                                    <i class="fa fa-search mr-1"></i>Aplicar Filtros
                                </button>
                                <button type="button" id="limpiarFiltros" class="btn btn-secondary mr-2">
                                    <i class="fa fa-times mr-1"></i>Limpiar
                                </button>
                                <div class="btn-group mr-2">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-plus mr-1"></i>Nuevo
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('material_kilo.crear_incidencia') }}">
                                            <i class="fa fa-exclamation-triangle mr-2 text-warning"></i>Nueva Incidencia
                                        </a>
                                        <a class="dropdown-item" href="{{ route('material_kilo.crear_devolucion') }}">
                                            <i class="fa fa-undo mr-2 text-info"></i>Nueva Devolución
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen de contadores -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-exclamation-triangle mr-2"></i>Total Incidencias
                        </h5>
                        <h3 class="card-text">{{ $total_incidencias }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-undo mr-2"></i>Total Devoluciones
                        </h5>
                        <h3 class="card-text">{{ $total_devoluciones }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-list mr-2"></i>Total Registros
                        </h5>
                        <h3 class="card-text">{{ $total_incidencias + $total_devoluciones }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-calendar mr-2"></i>Período
                        </h5>
                        <h3 class="card-text">
                            @php
                                $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            @endphp
                            @if($mes)
                                {{ $meses[$mes] }} {{ $año }}
                            @else
                                Todo {{ $año }}
                            @endif
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de historial -->
        <table id="table_historial"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">ID Proveedor</th>
                    <th class="text-center">Nombre Proveedor</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Mes/Año</th>
                    <th class="text-center">Clasificación</th>
                    <th class="text-center">Descripción/Producto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resultados as $registro)
                    <tr class="registro-fila" 
                        data-tipo="{{ $registro->tipo_registro }}" 
                        data-id="{{ $registro->id }}"
                        data-proveedor-id="{{ $registro->tipo_registro == 'incidencia' ? $registro->id_proveedor : $registro->codigo_proveedor }}"
                        style="cursor: pointer;"
                        onclick="editarRegistro('{{ $registro->tipo_registro }}', {{ $registro->id }})">
                        <td class="text-center">
                            @if($registro->tipo_registro == 'incidencia')
                                <span class="badge badge-incidencia">
                                    <i class="fa fa-exclamation-triangle mr-1"></i>Incidencia
                                </span>
                            @else
                                <span class="badge badge-devolucion">
                                    <i class="fa fa-undo mr-1"></i>Devolución
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $registro->tipo_registro == 'incidencia' ? $registro->id_proveedor : $registro->codigo_proveedor }}
                        </td>
                        <td class="text-center">{{ $registro->nombre_proveedor }}</td>
                        <td class="text-center">
                            @if($registro->fecha_principal)
                                {{ \Carbon\Carbon::parse($registro->fecha_principal)->format('d/m/Y') }}
                            @else
                                <span class="text-muted">Sin fecha</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-secondary">
                                @php
                                    $meses_cortos = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                @endphp
                                {{ $meses_cortos[$registro->mes] ?? 'N/A' }}/{{ $registro->año }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($registro->clasificacion_incidencia)
                                @php
                                    $clase = $registro->clasificacion_incidencia == 'RG1' ? 'danger' : ($registro->clasificacion_incidencia == 'RL1' ? 'warning' : 'info');
                                    $texto = preg_replace('/1$/', '', $registro->clasificacion_incidencia);
                                @endphp
                                <span class="badge badge-{{ $clase }}">
                                    {{ $texto }}
                                </span>
                            @else
                                <span class="text-muted">Sin clasificar</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($registro->tipo_registro == 'incidencia')
                                {{ $registro->descripcion_incidencia ?? $registro->producto ?? 'Sin descripción' }}
                            @else
                                {{ $registro->descripcion_producto ?? $registro->codigo_producto ?? 'Sin descripción' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection

@section('custom_footer')
    <script>
        // Script inline simple para manejo de eventos
        $(document).ready(function() {
            $('#aplicarFiltros').click(function() {
                var params = new URLSearchParams();
                
                if ($('#filtro_mes').val()) params.append('mes', $('#filtro_mes').val());
                if ($('#filtro_año').val()) params.append('año', $('#filtro_año').val());
                if ($('#filtro_proveedor').val()) params.append('proveedor', $('#filtro_proveedor').val());
                if ($('#filtro_tipo').val()) params.append('tipo', $('#filtro_tipo').val());
                
                window.location.href = '{{ route("material_kilo.historial_incidencias_devoluciones") }}?' + params.toString();
            });
            
            $('#limpiarFiltros').click(function() {
                window.location.href = '{{ route("material_kilo.historial_incidencias_devoluciones") }}';
            });
        });
    </script>
@endsection

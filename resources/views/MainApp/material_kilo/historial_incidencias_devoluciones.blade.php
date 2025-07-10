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
    
    /* Asegurar que el modal funcione correctamente */
    .modal-backdrop {
        z-index: 1040 !important;
        background-color: rgba(0,0,0,0.5) !important;
    }
    .modal {
        z-index: 1050 !important;
    }
    .modal.fade.show {
        display: block !important;
    }
    .modal-dialog {
        margin: 30px auto !important;
        z-index: 1060 !important;
    }
    .modal-content {
        background-color: #fff !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        z-index: 1070 !important;
    }
    
    /* Forzar visibilidad del modal */
    #modalIncidencias.show, #modalDevoluciones.show {
        display: block !important;
        opacity: 1 !important;
    }
    
    /* Evitar problemas con el backdrop */
    .modal-backdrop.show {
        opacity: 0.5 !important;
    }
    
    /* Asegurar que el contenido del modal esté por encima */
    .modal-header, .modal-body, .modal-footer {
        z-index: 1080 !important;
        position: relative;
    }
    
    /* Botón de cerrar */
    .modal-header .close {
        color: #000 !important;
        opacity: 0.8 !important;
        z-index: 1090 !important;
    }
    .modal-header .close:hover {
        opacity: 1 !important;
    }
    
    /* Evitar que el modal se oculte detrás de otros elementos */
    .modal-xl {
        max-width: 95% !important;
    }
    
    /* Asegurar que los elementos del formulario sean clickeables */
    .modal-body input, .modal-body select, .modal-body textarea, .modal-body button {
        z-index: 1090 !important;
        position: relative;
    }
    
    /* Solución de emergencia para modales problemáticos */
    .modal-debug {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background-color: rgba(0,0,0,0.5) !important;
        z-index: 9999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .modal-debug .modal-content {
        max-width: 95% !important;
        max-height: 95% !important;
        overflow-y: auto !important;
        z-index: 10000 !important;
    }
    
    /* Estilo para filas clickeables */
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
    window.appBaseUrl = '{{ url("/") }}';
    window.guardarIncidenciaUrl = '{{ route("material_kilo.guardar_incidencia") }}';
    window.guardarDevolucionUrl = '{{ route("material_kilo.guardar_devolucion") }}';
    window.obtenerIncidenciasUrl = '{{ route("material_kilo.obtener_incidencias") }}';
    window.obtenerDevolucionesUrl = '{{ route("material_kilo.obtener_devoluciones") }}';
    window.obtenerIncidenciaUrl = '{{ url("material_kilo/obtener-incidencia") }}';
    window.obtenerDevolucionUrl = '{{ url("material_kilo/obtener-devolucion") }}';
    
    // Debug: Mostrar las URLs en consola
    console.log('URLs configuradas:', {
        appBaseUrl: window.appBaseUrl,
        obtenerIncidenciaUrl: window.obtenerIncidenciaUrl,
        obtenerDevolucionUrl: window.obtenerDevolucionUrl
    });
    
    // Variables globales para los filtros actuales
    window.filtroMes = {{ $mes ?? 'null' }};
    window.filtroAño = {{ $año }};
    window.filtroProveedor = '{{ $proveedor }}';
    window.filtroTipo = '{{ $tipo }}';
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
                                <button type="button" id="nuevoRegistro" class="btn btn-success">
                                    <i class="fa fa-plus mr-1"></i>Nuevo
                                </button>
                                <button type="button" id="limpiarModales" class="btn btn-warning ml-2" style="display: none;">
                                    <i class="fa fa-times mr-1"></i>Cerrar Modal
                                </button>
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
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resultados as $registro)
                    <tr class="registro-fila" 
                        data-tipo="{{ $registro->tipo_registro }}" 
                        data-id="{{ $registro->id }}"
                        data-proveedor-id="{{ $registro->tipo_registro == 'incidencia' ? $registro->id_proveedor : $registro->codigo_proveedor }}">
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
                                <span class="badge badge-{{ $registro->clasificacion_incidencia == 'RG1' ? 'danger' : ($registro->clasificacion_incidencia == 'RL1' ? 'warning' : 'info') }}">
                                    {{ $registro->clasificacion_incidencia }}
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
                        <td class="text-center">
                            @if($registro->tipo_registro == 'incidencia')
                                @if($registro->fecha_respuesta_proveedor)
                                    <span class="badge badge-success">Respondido</span>
                                @elseif($registro->fecha_envio_proveedor)
                                    <span class="badge badge-warning">Enviado</span>
                                @else
                                    <span class="badge badge-secondary">Registrado</span>
                                @endif
                            @else
                                @if($registro->abierto == 'No')
                                    <span class="badge badge-success">Cerrado</span>
                                @else
                                    <span class="badge badge-danger">Abierto</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal para seleccionar tipo de nuevo registro -->
    <div class="modal fade" id="modalTipoRegistro" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Tipo de Registro</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" id="btnNuevaIncidencia" class="btn btn-warning btn-lg btn-block">
                                <i class="fa fa-exclamation-triangle fa-2x mb-2"></i><br>
                                Nueva Incidencia
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="btnNuevaDevolucion" class="btn btn-info btn-lg btn-block">
                                <i class="fa fa-undo fa-2x mb-2"></i><br>
                                Nueva Devolución
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Incidencias (copiado del total_kg_por_proveedor) -->
    <div class="modal fade" id="modalIncidencias" tabindex="-1" role="dialog" aria-labelledby="modalIncidenciasLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalIncidenciasLabel">
                        <i class="fa fa-exclamation-triangle mr-2"></i>Gestión de Incidencias de Proveedores
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formIncidencia">
                        @csrf
                        <input type="hidden" id="incidencia_id" name="incidencia_id">
                        <div class="row">
                            <!-- Datos básicos -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proveedor_incidencia">Proveedor:</label>
                                    <select id="proveedor_incidencia" name="id_proveedor" class="form-control" required>
                                        <option value="">Seleccione un proveedor</option>
                                        @foreach ($proveedores_disponibles as $prov)
                                            <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="año_incidencia">Año:</label>
                                    <select id="año_incidencia" name="año" class="form-control" required>
                                        @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ $year == $año ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="mes_incidencia">Mes:</label>
                                    <select id="mes_incidencia" name="mes" class="form-control" required>
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
                            </div>
                        </div>

                        <!-- Resto de campos de incidencia igual que en total_kg_por_proveedor -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="clasificacion_incidencia">Clasificación de Incidencia:</label>
                                    <select id="clasificacion_incidencia" name="clasificacion_incidencia" class="form-control">
                                        <option value="">Seleccione una clasificación</option>
                                        <option value="DEV1">DEV - Rechazos en almacen</option>
                                        <option value="ROK1">ROK - Aceptaciones Condicionales en almacen</option>
                                        <option value="RET1">RET - Retiradas generales de tiendas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="origen">Origen:</label>
                                    <input type="text" id="origen" name="origen" class="form-control" placeholder="Origen de la incidencia">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_incidencia">Fecha Incidencia:</label>
                                    <input type="date" id="fecha_incidencia" name="fecha_incidencia" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero_inspeccion_sap">Nº Inspección SAP:</label>
                                    <input type="text" id="numero_inspeccion_sap" name="numero_inspeccion_sap" class="form-control" placeholder="Número de inspección">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="resolucion_almacen">Resolución Almacén:</label>
                                    <input type="text" id="resolucion_almacen" name="resolucion_almacen" class="form-control" placeholder="Resolución del almacén">
                                </div>
                            </div>
                        </div>

                        <!-- Aquí van todos los demás campos de incidencia igual que en total_kg_por_proveedor -->
                        <!-- Por brevedad, incluyo solo algunos campos principales -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="descripcion_incidencia">Descripción Incidencia:</label>
                                    <textarea id="descripcion_incidencia" name="descripcion_incidencia" class="form-control" rows="3" placeholder="Descripción detallada de la incidencia"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="producto">Producto:</label>
                                    <input type="text" id="producto" name="producto" class="form-control" placeholder="Nombre del producto">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo">Código:</label>
                                    <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Código del producto">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote">Lote:</label>
                                    <input type="text" id="lote" name="lote" class="form-control" placeholder="Lote del producto">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="caducidad">Caducidad:</label>
                                    <input type="date" id="caducidad" name="caducidad" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cantidad_kg">Cantidad KG:</label>
                                    <input type="number" id="cantidad_kg" name="cantidad_kg" class="form-control" step="0.01" placeholder="Cantidad en KG">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cantidad_unidades">Cantidad Unidades:</label>
                                    <input type="number" id="cantidad_unidades" name="cantidad_unidades" class="form-control" placeholder="Cantidad en unidades">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="proveedor_alternativo">Proveedor Alternativo:</label>
                                    <input type="text" id="proveedor_alternativo" name="proveedor_alternativo" class="form-control" placeholder="Proveedor alternativo">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="dias_sin_servicio">Días Sin Servicio:</label>
                                    <input type="number" id="dias_sin_servicio" name="dias_sin_servicio" class="form-control" placeholder="Días sin servicio">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_envio_proveedor">Fecha Envío Proveedor:</label>
                                    <input type="date" id="fecha_envio_proveedor" name="fecha_envio_proveedor" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_respuesta_proveedor">Fecha Respuesta Proveedor:</label>
                                    <input type="date" id="fecha_respuesta_proveedor" name="fecha_respuesta_proveedor" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="comentarios">Comentarios:</label>
                                    <textarea id="comentarios" name="comentarios" class="form-control" rows="3" placeholder="Comentarios adicionales"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="button" id="guardarIncidencia" class="btn btn-warning">
                        <i class="fa fa-save mr-1"></i>Guardar Incidencia
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Devoluciones (copiado del total_kg_por_proveedor) -->
    <div class="modal fade" id="modalDevoluciones" tabindex="-1" role="dialog" aria-labelledby="modalDevolucionesLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDevolucionesLabel">
                        <i class="fa fa-undo mr-2"></i>Gestión de Devoluciones de Proveedores
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formDevolucion">
                        @csrf
                        <input type="hidden" id="devolucion_id" name="devolucion_id">
                        
                        <!-- Campos principales de devolución -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="proveedor_devolucion">Proveedor:</label>
                                    <select id="proveedor_devolucion" name="codigo_proveedor" class="form-control" required>
                                        <option value="">Seleccione un proveedor</option>
                                        @foreach ($proveedores_disponibles as $prov)
                                            <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="año_devolucion">Año:</label>
                                    <select id="año_devolucion" name="año" class="form-control" required>
                                        @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ $year == $año ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="mes_devolucion">Mes:</label>
                                    <select id="mes_devolucion" name="mes" class="form-control" required>
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
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="abierto">Estado:</label>
                                    <select id="abierto" name="abierto" class="form-control" required>
                                        <option value="Sí">Abierto</option>
                                        <option value="No">Cerrado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_devolucion">Fecha Devolución:</label>
                                    <input type="date" id="fecha_devolucion" name="fecha_devolucion" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="motivo_devolucion">Motivo Devolución:</label>
                                    <input type="text" id="motivo_devolucion" name="motivo_devolucion" class="form-control" placeholder="Motivo de la devolución">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo_producto_devolucion">Código Producto:</label>
                                    <input type="text" id="codigo_producto_devolucion" name="codigo_producto" class="form-control" placeholder="Código del producto">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="descripcion_producto_devolucion">Descripción Producto:</label>
                                    <input type="text" id="descripcion_producto_devolucion" name="descripcion_producto" class="form-control" placeholder="Descripción del producto">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote_devolucion">Lote:</label>
                                    <input type="text" id="lote_devolucion" name="lote" class="form-control" placeholder="Lote del producto">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="caducidad_devolucion">Caducidad:</label>
                                    <input type="date" id="caducidad_devolucion" name="caducidad" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cantidad_kg_devolucion">Cantidad KG:</label>
                                    <input type="number" id="cantidad_kg_devolucion" name="cantidad_kg" class="form-control" step="0.01" placeholder="Cantidad en KG">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cantidad_unidades_devolucion">Cantidad Unidades:</label>
                                    <input type="number" id="cantidad_unidades_devolucion" name="cantidad_unidades" class="form-control" placeholder="Cantidad en unidades">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_envio_proveedor_devolucion">Fecha Envío Proveedor:</label>
                                    <input type="date" id="fecha_envio_proveedor_devolucion" name="fecha_envio_proveedor" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_respuesta_proveedor_devolucion">Fecha Respuesta Proveedor:</label>
                                    <input type="date" id="fecha_respuesta_proveedor_devolucion" name="fecha_respuesta_proveedor" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="observaciones_devolucion">Observaciones:</label>
                                    <textarea id="observaciones_devolucion" name="observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="button" id="guardarDevolucion" class="btn btn-info">
                        <i class="fa fa-save mr-1"></i>Guardar Devolución
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('custom_footer')
    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/historial_incidencias_devoluciones.js') }}?v={{ config('app.version') }}"></script>
@endsection

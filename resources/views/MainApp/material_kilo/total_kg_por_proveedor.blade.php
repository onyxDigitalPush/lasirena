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
    }
    .modal-content {
        background-color: #fff !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }
    
    /* Forzar visibilidad del modal */
    #modalIncidencias.show {
        display: block !important;
        opacity: 1 !important;
    }
    
    /* Botón de cerrar */
    .modal-header .close {
        color: #000 !important;
        opacity: 0.8 !important;
    }
    .modal-header .close:hover {
        opacity: 1 !important;
    }
</style>
<script>
    window.appBaseUrl = '{{ url("/") }}';
    window.guardarMetricasUrl = '{{ route("material_kilo.guardar_metricas") }}';
    window.guardarIncidenciaUrl = '{{ route("material_kilo.guardar_incidencia") }}';
    window.guardarDevolucionUrl = '{{ route("material_kilo.guardar_devolucion") }}';
    window.buscarProveedoresUrl = '{{ route("material_kilo.buscar_proveedores") }}';
    window.buscarProductosProveedorUrl = '{{ route("material_kilo.buscar_productos_proveedor") }}';
    window.buscarProductoPorCodigoUrl = '{{ route("material_kilo.buscar_producto_por_codigo") }}';
    window.buscarCodigosProductosUrl = '{{ route("material_kilo.buscar_codigos_productos") }}';
    // Debug temporal
    console.log('Mes filtrado:', {{ $mes }});
    console.log('Año filtrado:', {{ $año }});
</script>
@endsection

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-bar-chart icon-gradient bg-secondary"></i>
            </div>
            <div>Total KG por Proveedor
                <div class="page-title-subheading">
                    Suma total de kilogramos agrupados por proveedor
                </div>
            </div>
        </div>        <div class="page-title-actions text-white">
            <a class="m-2 btn btn-primary" href="{{ route('material_kilo.index') }}">
                <i class="fa fa-list mr-2"></i>Volver a Material Kilos
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
        
        <!-- Filtros por Mes y Año -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-filter mr-2"></i>Filtros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filtrosForm" class="row">                            <div class="col-md-3">
                                <label for="filtro_mes">Mes:</label>
                                <select id="filtro_mes" name="mes" class="form-control" required>
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
                            </div><div class="col-md-3">
                                <label for="filtro_año">Año:</label>
                                <select id="filtro_año" name="año" class="form-control" required>
                                    @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ $year == $año ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="aplicarFiltros" class="btn btn-primary mr-2">
                                    <i class="fa fa-search mr-1"></i>Aplicar Filtros
                                </button>
                                <button type="button" id="limpiarFiltros" class="btn btn-secondary">
                                    <i class="fa fa-times mr-1"></i>Limpiar
                                </button>
                            </div>
                            <div class="col-md-3 d-flex align-items-end justify-content-end">
                                <div class="btn-group" role="group">
                                    <button type="button" id="gestionarIncidencias" class="btn btn-warning">
                                        <i class="fa fa-exclamation-triangle mr-1"></i>Incidencias
                                    </button>
                                    <button type="button" id="gestionarDevoluciones" class="btn btn-info">
                                        <i class="fa fa-undo mr-1"></i>Devoluciones
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
          <!-- Resumen total -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-users mr-2"></i>Total Proveedores
                        </h5>
                        <h3 class="card-text" id="total-proveedores">{{ $totales_por_proveedor->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-weight mr-2"></i>Total KG General
                        </h5>
                        <h3 class="card-text" id="total-kg-general">{{ number_format($totales_por_proveedor->sum('total_kg_proveedor'), 2) }} kg</h3>
                    </div>
                </div>
            </div>
        </div><table id="table_total_kg_proveedor"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">ID Proveedor</th>
                    <th class="text-center">Nombre Proveedor</th>
                    <th class="text-center">Total KG</th>
                    <th class="text-center">Cantidad de Registros</th>
                    <th class="text-center">Porcentaje del Total</th>
                    <th class="text-center bg-warning">RG</th>
                    <th class="text-center bg-warning">RL</th>
                    <th class="text-center bg-warning">DEV</th>
                    <th class="text-center bg-warning">ROK</th>
                    <th class="text-center bg-warning">RET</th>
                </tr>
                <tr>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar ID" /></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Proveedor" /></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>            <tbody>
                @php
                    $total_general = $totales_por_proveedor->sum('total_kg_proveedor');
                @endphp
                @foreach ($totales_por_proveedor as $total)
                    @php
                        $porcentaje = $total_general > 0 ? ($total->total_kg_proveedor / $total_general) * 100 : 0;
                        // Obtener métricas existentes si las hay
                        $metricas = isset($metricas_por_proveedor[$total->id_proveedor]) ? $metricas_por_proveedor[$total->id_proveedor] : null;
                    @endphp
                    <tr data-proveedor-id="{{ $total->id_proveedor }}">
                        <td class="text-center">{{ $total->id_proveedor }}</td>
                        <td class="text-center">{{ $total->nombre_proveedor }}</td>
                        <td class="text-center">
                            <span class="badge badge-success badge-lg">
                                {{ number_format($total->total_kg_proveedor, 2) }} kg
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ number_format($total->cantidad_registros) }} registros
                            </span>
                        </td>                        <td class="text-center">
                            <div class="progress position-relative" style="height: 25px;">
                                <div class="progress-bar 
                                    @if($porcentaje >= 50) bg-success
                                    @elseif($porcentaje >= 25) bg-warning
                                    @else bg-info
                                    @endif" 
                                    role="progressbar" 
                                    style="width: {{ max($porcentaje, 1) }}%;" 
                                    aria-valuenow="{{ $porcentaje }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                </div>
                                <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" 
                                     style="color: {{ $porcentaje >= 50 ? 'white' : '#333' }}; font-weight: bold; font-size: 12px;">
                                    {{ number_format($porcentaje, 1) }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="rg1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->rg1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="rg1"
                                   readonly>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="rl1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->rl1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="rl1"
                                   readonly>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="dev1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->dev1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="dev1"
                                   readonly>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="rok1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->rok1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="rok1"
                                   readonly>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="ret1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->ret1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="ret1"
                                   readonly>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

<!-- Modal de Incidencias - Movido fuera del contenedor principal -->
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
                        <div class="row">
                            <!-- Datos básicos -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proveedor_incidencia">Proveedor:</label>
                                    <select id="proveedor_incidencia" name="id_proveedor" class="form-control" required>
                                        <option value="">Seleccione un proveedor</option>
                                        @foreach ($proveedores_alfabetico as $proveedor)
                                            <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->nombre_proveedor }}</option>
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

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cantidad_devuelta">Cantidad Devuelta:</label>
                                    <input type="number" id="cantidad_devuelta" name="cantidad_devuelta" class="form-control" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kg_un">Kg/un:</label>
                                    <input type="number" id="kg_un" name="kg_un" class="form-control" step="0.0001" placeholder="0.0000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pedido_sap_devolucion">Pedido SAP Devolución:</label>
                                    <input type="text" id="pedido_sap_devolucion" name="pedido_sap_devolucion" class="form-control" placeholder="Número de pedido">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="resolucion_tienda">Resolución Tienda:</label>
                                    <input type="text" id="resolucion_tienda" name="resolucion_tienda" class="form-control" placeholder="Resolución de la tienda">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="retirada_tiendas">¿Retirada Tiendas?:</label>
                                    <select id="retirada_tiendas" name="retirada_tiendas" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Si">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cantidad_afectada">Cantidad Afectada:</label>
                                    <input type="number" id="cantidad_afectada" name="cantidad_afectada" class="form-control" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="descripcion_incidencia">Descripción Incidencia:</label>
                                    <textarea id="descripcion_incidencia" name="descripcion_incidencia" class="form-control" rows="3" placeholder="Descripción detallada de la incidencia"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo">Código:</label>
                                    <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Código del producto">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="producto">Producto:</label>
                                    <input type="text" id="producto" name="producto" class="form-control" placeholder="Nombre del producto">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote_sirena">Lote Sirena:</label>
                                    <input type="text" id="lote_sirena" name="lote_sirena" class="form-control" placeholder="Lote Sirena">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote_proveedor">Lote Proveedor:</label>
                                    <input type="text" id="lote_proveedor" name="lote_proveedor" class="form-control" placeholder="Lote Proveedor">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fcp">FCP:</label>
                                    <input type="date" id="fcp" name="fcp" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="informe_a_proveedor">¿Informe a Proveedor?:</label>
                                    <select id="informe_a_proveedor" name="informe_a_proveedor" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Si">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="numero_informe">Nº de Informe:</label>
                                    <input type="text" id="numero_informe" name="numero_informe" class="form-control" placeholder="Número de informe">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_envio_proveedor">Fecha Envío a Proveedor:</label>
                                    <input type="date" id="fecha_envio_proveedor" name="fecha_envio_proveedor" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_respuesta_proveedor">Fecha Respuesta Proveedor:</label>
                                    <input type="date" id="fecha_respuesta_proveedor" name="fecha_respuesta_proveedor" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="informe_respuesta">Informe Respuesta:</label>
                                    <textarea id="informe_respuesta" name="informe_respuesta" class="form-control" rows="3" placeholder="Informe de respuesta del proveedor"></textarea>
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

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_reclamacion_respuesta1">Fecha Reclamación Respuesta 1:</label>
                                    <input type="date" id="fecha_reclamacion_respuesta1" name="fecha_reclamacion_respuesta1" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_reclamacion_respuesta2">Fecha Reclamación Respuesta 2:</label>
                                    <input type="date" id="fecha_reclamacion_respuesta2" name="fecha_reclamacion_respuesta2" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_decision_destino_producto">Fecha Decisión Destino Producto:</label>
                                    <input type="date" id="fecha_decision_destino_producto" name="fecha_decision_destino_producto" class="form-control">
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
</div>

<!-- Modal de Devoluciones -->
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
                    <div class="row">
                        <!-- Código del producto -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="codigo_producto">Código del Producto:</label>
                                <input type="text" id="codigo_producto" name="codigo_producto" class="form-control" placeholder="Código del producto" required>
                            </div>
                        </div>
                        <!-- Nombre proveedor con select -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="proveedor_devolucion">Proveedor:</label>
                                <select id="proveedor_devolucion" name="id_proveedor" class="form-control" required>
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach ($proveedores_alfabetico as $proveedor)
                                        <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->nombre_proveedor }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Descripción del producto -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="descripcion_producto">Descripción del Producto:</label>
                                <input type="text" id="descripcion_producto" name="descripcion_producto" class="form-control" placeholder="Descripción del producto">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_inicio">Fecha Inicio:</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_fin">Fecha Fin:</label>
                                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="np">NP:</label>
                                <input type="text" id="np" name="np" class="form-control" placeholder="NP">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_reclamacion">Fecha Reclamación:</label>
                                <input type="date" id="fecha_reclamacion" name="fecha_reclamacion" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="año_devolucion">Año:</label>
                                <select id="año_devolucion" name="año" class="form-control" required>
                                    @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ $year == $año ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="clasificacion_incidencia_dev">Clasificación de Incidencia:</label>
                                <select id="clasificacion_incidencia_dev" name="clasificacion_incidencia" class="form-control">
                                    <option value="">Seleccione una clasificación</option>
                                    <option value="RG1">RG - Reclamación General</option>
                                    <option value="RL1">RL - Reclamación Legal</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo_reclamacion">Tipo Reclamación:</label>
                                <select id="tipo_reclamacion" name="tipo_reclamacion" class="form-control">
                                    <option value="">Seleccione tipo</option>
                                    <option value="Leve">Leve</option>
                                    <option value="Grave">Grave</option>
                                    <option value="Crítica">Crítica</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="top100fy2">Top100FY2:</label>
                                <input type="text" id="top100fy2" name="top100fy2" class="form-control" placeholder="Top100FY2">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="descripcion_motivo">Descripción Motivo:</label>
                                <textarea id="descripcion_motivo" name="descripcion_motivo" class="form-control" rows="3" placeholder="Descripción del motivo"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="especificacion_motivo_leve">Especificación Motivo Reclamación Leve:</label>
                                <textarea id="especificacion_motivo_leve" name="especificacion_motivo_reclamacion_leve" class="form-control" rows="3" placeholder="Especificación motivo leve"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="especificacion_motivo_grave">Especificación Motivo Reclamación Grave:</label>
                                <textarea id="especificacion_motivo_grave" name="especificacion_motivo_reclamacion_grave" class="form-control" rows="3" placeholder="Especificación motivo grave"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="recuperamos_objeto_extraño">¿Recuperamos Objeto Extraño?:</label>
                                <select id="recuperamos_objeto_extraño" name="recuperamos_objeto_extraño" class="form-control">
                                    <option value="">Seleccione</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nombre_tienda">Nombre Tienda:</label>
                                <input type="text" id="nombre_tienda" name="nombre_tienda" class="form-control" placeholder="Nombre de la tienda">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="no_queja">No Queja:</label>
                                <input type="text" id="no_queja" name="no_queja" class="form-control" placeholder="Número de queja">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="origen_dev">Origen:</label>
                                <input type="text" id="origen_dev" name="origen" class="form-control" placeholder="Origen">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="descripcion_queja">Descripción Queja:</label>
                                <textarea id="descripcion_queja" name="descripcion_queja" class="form-control" rows="3" placeholder="Descripción de la queja"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lote_sirena_dev">Lote Sirena:</label>
                                <input type="text" id="lote_sirena_dev" name="lote_sirena" class="form-control" placeholder="Lote Sirena">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lote_proveedor_dev">Lote Proveedor:</label>
                                <input type="text" id="lote_proveedor_dev" name="lote_proveedor" class="form-control" placeholder="Lote Proveedor">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="informe_a_proveedor_dev">¿Informe a Proveedor?:</label>
                                <select id="informe_a_proveedor_dev" name="informe_a_proveedor" class="form-control">
                                    <option value="">Seleccione</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="informe_dev">Informe:</label>
                                <textarea id="informe_dev" name="informe" class="form-control" rows="3" placeholder="Informe"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_envio_proveedor_dev">Fecha Envío a Proveedor:</label>
                                <input type="date" id="fecha_envio_proveedor_dev" name="fecha_envio_proveedor" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_respuesta_proveedor_dev">Fecha Respuesta Proveedor:</label>
                                <input type="date" id="fecha_respuesta_proveedor_dev" name="fecha_respuesta_proveedor" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_reclamacion_respuesta">Fecha Reclamación Respuesta:</label>
                                <input type="date" id="fecha_reclamacion_respuesta" name="fecha_reclamacion_respuesta" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="abierto">Abierto:</label>
                                <select id="abierto" name="abierto" class="form-control">
                                    <option value="Si" selected>Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="informe_respuesta_dev">Informe Respuesta:</label>
                                <textarea id="informe_respuesta_dev" name="informe_respuesta" class="form-control" rows="3" placeholder="Informe de respuesta"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comentarios_dev">Comentarios:</label>
                                <textarea id="comentarios_dev" name="comentarios" class="form-control" rows="3" placeholder="Comentarios adicionales"></textarea>
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

@section('custom_footer')
    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/total_kg_proveedor.js') }}?v={{ config('app.version') }}"></script>
@endsection

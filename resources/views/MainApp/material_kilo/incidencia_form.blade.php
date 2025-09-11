@extends('layouts.app')

@section('app_name', config('app.name'))

@section('custom_head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .form-section {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .form-section h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .btn-volver {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }
    
    .btn-volver:hover {
        background-color: #5a6268;
        border-color: #545b62;
        color: white;
    }
</style>

@section('custom_scripts')
<script>
    window.buscarProductoPorCodigoUrl = "{{ url('material_kilo/buscar-producto-por-codigo') }}";
    window.buscarCodigosProductosUrl = "{{ url('material_kilo/buscar-codigos-productos') }}";
</script>
@endsection

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-exclamation-triangle icon-gradient bg-warning"></i>
            </div>
            <div>
                {{ isset($incidencia) ? 'Editar Incidencia' : 'Nueva Incidencia' }}
                <div class="page-title-subheading">
                    Gestión de incidencias de proveedores
                </div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('material_kilo.historial_incidencias_devoluciones') }}" class="btn btn-volver">
                <i class="fa fa-arrow-left mr-2"></i>Volver al Historial
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
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    {{ isset($incidencia) ? 'Editar Incidencia' : 'Nueva Incidencia' }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($incidencia) ? route('material_kilo.actualizar_incidencia', $incidencia->id) : route('material_kilo.guardar_incidencia_completa') }}">
                    @csrf
                    @if(isset($incidencia))
                        @method('PUT')
                    @endif
                    
                    <!-- Datos básicos -->
                    <div class="form-section">
                        <h6><i class="fa fa-info-circle mr-2"></i>Datos Básicos</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proveedor_incidencia">Proveedor:</label>
                                    <select id="proveedor_incidencia" name="id_proveedor" class="form-control" required>
                                        <option value="">Seleccione un proveedor</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id_proveedor }}" {{ (isset($incidencia) && $incidencia->id_proveedor == $proveedor->id_proveedor) ? 'selected' : '' }}>
                                                {{ $proveedor->nombre_proveedor }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="año_incidencia">Año:</label>
                                    <select id="año_incidencia" name="año" class="form-control" required>
                                        @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ (isset($incidencia) && $incidencia->año == $year) ? 'selected' : ($year == now()->year ? 'selected' : '') }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="mes_incidencia">Mes:</label>
                                    <select id="mes_incidencia" name="mes" class="form-control" required>
                                        <option value="1" {{ (isset($incidencia) && $incidencia->mes == 1) ? 'selected' : ($mes == 1 ? 'selected' : '') }}>Enero</option>
                                        <option value="2" {{ (isset($incidencia) && $incidencia->mes == 2) ? 'selected' : ($mes == 2 ? 'selected' : '') }}>Febrero</option>
                                        <option value="3" {{ (isset($incidencia) && $incidencia->mes == 3) ? 'selected' : ($mes == 3 ? 'selected' : '') }}>Marzo</option>
                                        <option value="4" {{ (isset($incidencia) && $incidencia->mes == 4) ? 'selected' : ($mes == 4 ? 'selected' : '') }}>Abril</option>
                                        <option value="5" {{ (isset($incidencia) && $incidencia->mes == 5) ? 'selected' : ($mes == 5 ? 'selected' : '') }}>Mayo</option>
                                        <option value="6" {{ (isset($incidencia) && $incidencia->mes == 6) ? 'selected' : ($mes == 6 ? 'selected' : '') }}>Junio</option>
                                        <option value="7" {{ (isset($incidencia) && $incidencia->mes == 7) ? 'selected' : ($mes == 7 ? 'selected' : '') }}>Julio</option>
                                        <option value="8" {{ (isset($incidencia) && $incidencia->mes == 8) ? 'selected' : ($mes == 8 ? 'selected' : '') }}>Agosto</option>
                                        <option value="9" {{ (isset($incidencia) && $incidencia->mes == 9) ? 'selected' : ($mes == 9 ? 'selected' : '') }}>Septiembre</option>
                                        <option value="10" {{ (isset($incidencia) && $incidencia->mes == 10) ? 'selected' : ($mes == 10 ? 'selected' : '') }}>Octubre</option>
                                        <option value="11" {{ (isset($incidencia) && $incidencia->mes == 11) ? 'selected' : ($mes == 11 ? 'selected' : '') }}>Noviembre</option>
                                        <option value="12" {{ (isset($incidencia) && $incidencia->mes == 12) ? 'selected' : ($mes == 12 ? 'selected' : '') }}>Diciembre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clasificación y origen -->
                    <div class="form-section">
                        <h6><i class="fa fa-tags mr-2"></i>Clasificación y Origen</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="clasificacion_incidencia">Tipo de Incidencia:</label>
                                    <select id="clasificacion_incidencia" name="clasificacion_incidencia" class="form-control">
                                        <option value="">Seleccione una clasificación</option>
                                        <option value="DEV1" {{ old('clasificacion_incidencia', isset($incidencia) ? $incidencia->clasificacion_incidencia : '') == 'DEV1' ? 'selected' : '' }}>Incidencia Almacen</option>
                                        <option value="ROK1" {{ old('clasificacion_incidencia', isset($incidencia) ? $incidencia->clasificacion_incidencia : '') == 'ROK1' ? 'selected' : '' }}>Incidencia Tienda</option>
                                        <option value="RET1" {{ old('clasificacion_incidencia', isset($incidencia) ? $incidencia->clasificacion_incidencia : '') == 'RET1' ? 'selected' : '' }}>Incidencia VAD</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_incidencia">Clasificación de Incidencia:</label>
                                    <select id="tipo_incidencia" name="tipo_incidencia" class="form-control">
                                        <option value="">Seleccione clasificacion de incidencia</option>
                                        <option value="Aspecto" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Aspecto' ? 'selected' : '' }}>Aspecto</option>
                                        <option value="Caducidad" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Caducidad' ? 'selected' : '' }}>Caducidad</option>
                                        <option value="Cata" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Cata' ? 'selected' : '' }}>Cata</option>
                                        <option value="Corte de la rodaja" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Corte de la rodaja' ? 'selected' : '' }}>Corte de la rodaja</option>
                                        <option value="Cuerpos extraños" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Cuerpos extraños' ? 'selected' : '' }}>Cuerpos extraños</option>
                                        <option value="Descongelacion" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Descongelacion' ? 'selected' : '' }}>Descongelacion</option>
                                        <option value="Dimensiones" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Dimensiones' ? 'selected' : '' }}>Dimensiones</option>
                                        <option value="Envase defectuoso" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Envase defectuoso' ? 'selected' : '' }}>Envase defectuoso</option>
                                        <option value="Estado embalaje" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Estado embalaje' ? 'selected' : '' }}>Estado embalaje</option>
                                        <option value="Glaseo" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Glaseo' ? 'selected' : '' }}>Glaseo</option>
                                        <option value="Gramaje" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Gramaje' ? 'selected' : '' }}>Gramaje</option>
                                        <option value="Identificacion" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Identificacion' ? 'selected' : '' }}>Identificacion</option>
                                        <option value="Olor" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Olor' ? 'selected' : '' }}>Olor</option>
                                        <option value="Organoleptico" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Organoleptico' ? 'selected' : '' }}>Organoleptico</option>
                                        <option value="Peso" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Peso' ? 'selected' : '' }}>Peso</option>
                                        <option value="Temperatura" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Temperatura' ? 'selected' : '' }}>Temperatura</option>
                                        <option value="Otros" {{ old('tipo_incidencia', isset($incidencia) ? $incidencia->tipo_incidencia : '') == 'Otros' ? 'selected' : '' }}>Otros</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="origen">Origen:</label>
                                    <select name="origen" id="origen" class="form-control">
                                        <option value="">Seleccione un origen</option>
                                        <option value="Administracion" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Administracion' ? 'selected' : '' }}>Administracion</option>
                                        <option value="Alertas" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Alertas' ? 'selected' : '' }}>Alertas</option>
                                        <option value="Auditoria o Visita" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Auditoria o Visita' ? 'selected' : '' }}>Auditoria o Visita</option>
                                        <option value="Cambio de tapas" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Cambio de tapas' ? 'selected' : '' }}>Cambio de tapas</option>
                                        <option value="Contacto central" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Contacto central' ? 'selected' : '' }}>Contacto central</option>
                                        <option value="Descarga Almacen" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Descarga Almacen' ? 'selected' : '' }}>Descarga Almacen</option>
                                        <option value="Inspeccion - cata" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Inspeccion - cata' ? 'selected' : '' }}>Inspeccion - cata</option>
                                        <option value="Inspeccion - visual" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Inspeccion - visual' ? 'selected' : '' }}>Inspeccion - visual/dimensional</option>
                                        <option value="Laboratorio externo" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Laboratorio externo' ? 'selected' : '' }}>Laboratorio externo</option>
                                        <option value="Maquillas Externas" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Maquillas Externas' ? 'selected' : '' }}>Maquillas Externas</option>
                                        <option value="NPs" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'NPs' ? 'selected' : '' }}>NPs</option>
                                        <option value="Proveedor" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Proveedor' ? 'selected' : '' }}>Proveedor</option>
                                        <option value="Otros" {{ old('origen', isset($incidencia) ? $incidencia->origen : '') == 'Otros' ? 'selected' : '' }}>Otros</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas e inspección -->
                    <div class="form-section">
                        <h6><i class="fa fa-calendar mr-2"></i>Fechas e Inspección</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_incidencia">Fecha Incidencia:</label>
                                    <input type="date" id="fecha_incidencia" name="fecha_incidencia" class="form-control" value="{{ old('fecha_incidencia', isset($incidencia) && $incidencia->fecha_incidencia ? \Carbon\Carbon::parse($incidencia->fecha_incidencia)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero_inspeccion_sap">Nº Inspección SAP:</label>
                                    <input type="text" id="numero_inspeccion_sap" name="numero_inspeccion_sap" class="form-control" placeholder="Número de inspección" value="{{ old('numero_inspeccion_sap', isset($incidencia) ? $incidencia->numero_inspeccion_sap : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="resolucion_almacen">Resolución Almacén:</label>
                                    <select id="resolucion_almacen" name="resolucion_almacen" class="form-control">
                                        <option value="">Seleccione una resolución</option>
                                        <option value="Aceptada" {{ old('resolucion_almacen', isset($incidencia) ? $incidencia->resolucion_almacen : '') == 'Aceptada' ? 'selected' : '' }}>Aceptada</option>
                                        <option value="Aceptado Condicional" {{ old('resolucion_almacen', isset($incidencia) ? $incidencia->resolucion_almacen : '') == 'Aceptado Condicional' ? 'selected' : '' }}>Aceptado Condicional</option>
                                        <option value="Bloqueo de producto" {{ old('resolucion_almacen', isset($incidencia) ? $incidencia->resolucion_almacen : '') == 'Bloqueo de producto' ? 'selected' : '' }}>Bloqueo de producto</option>
                                        <option value="Devolucion a proveedor" {{ old('resolucion_almacen', isset($incidencia) ? $incidencia->resolucion_almacen : '') == 'Devolucion a proveedor' ? 'selected' : '' }}>Devolucion a proveedor</option>
                                        <option value="No aplica" {{ old('resolucion_almacen', isset($incidencia) ? $incidencia->resolucion_almacen : '') == 'No aplica' ? 'selected' : '' }}>No aplica</option>
                                        <option value="Retirado General" {{ old('resolucion_almacen', isset($incidencia) ? $incidencia->resolucion_almacen : '') == 'Retirado General' ? 'selected' : '' }}>Retirado General</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cantidades -->
                    <div class="form-section">
                        <h6><i class="fa fa-weight mr-2"></i>Cantidades</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cantidad_devuelta">Cantidad Devuelta:</label>
                                    <input type="number" id="cantidad_devuelta" name="cantidad_devuelta" class="form-control" step="0.01" placeholder="0.00" value="{{ old('cantidad_devuelta', isset($incidencia) ? $incidencia->cantidad_devuelta : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kg_un">Kg/un:</label>
                                    <select id="kg_un" name="kg_un" class="form-control">
                                        <option value="">Seleccione </option>
                                        <option value="kg" {{ old('kg_un', isset($incidencia) ? $incidencia->kg_un : '') == 'kg' ? 'selected' : '' }}>Kilogramos</option>
                                        <option value="un" {{ old('kg_un', isset($incidencia) ? $incidencia->kg_un : '') == 'un' ? 'selected' : '' }}>Unidades</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pedido_sap_devolucion">Pedido SAP Devolución:</label>
                                    <input type="text" id="pedido_sap_devolucion" name="pedido_sap_devolucion" class="form-control" placeholder="Número de pedido" value="{{ old('pedido_sap_devolucion', isset($incidencia) ? $incidencia->pedido_sap_devolucion : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resolución tienda -->
                    <div class="form-section">
                        <h6><i class="fa fa-store mr-2"></i>Resolución Tienda</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="resolucion_tienda">Resolución Tienda:</label>
                                    <input type="text" id="resolucion_tienda" name="resolucion_tienda" class="form-control" placeholder="Resolución de la tienda" value="{{ old('resolucion_tienda', isset($incidencia) ? $incidencia->resolucion_tienda : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="retirada_tiendas">¿Retirada Tiendas?:</label>
                                    <select id="retirada_tiendas" name="retirada_tiendas" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Si" {{ (isset($incidencia) && $incidencia->retirada_tiendas == 'Si') ? 'selected' : '' }}>Sí</option>
                                        <option value="No" {{ (isset($incidencia) && $incidencia->retirada_tiendas == 'No') ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cantidad_afectada">Cantidad Afectada:</label>
                                    <input type="number" id="cantidad_afectada" name="cantidad_afectada" class="form-control" step="0.01" placeholder="0.00" value="{{ old('cantidad_afectada', isset($incidencia) ? $incidencia->cantidad_afectada : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="form-section">
                        <h6><i class="fa fa-file-text mr-2"></i>Descripción</h6>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="descripcion_incidencia">Descripción Incidencia:</label>
                                    <textarea id="descripcion_incidencia" name="descripcion_incidencia" class="form-control" rows="3" placeholder="Descripción detallada de la incidencia">{{ old('descripcion_incidencia', isset($incidencia) ? $incidencia->descripcion_incidencia : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Producto -->
                    <div class="form-section">
                        <h6><i class="fa fa-cube mr-2"></i>Información del Producto</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo">Código:</label>
                                    <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Código del producto" value="{{ old('codigo', isset($incidencia) ? $incidencia->codigo : '') }}">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="producto">Producto:</label>
                                    <input type="text" id="producto" name="producto" class="form-control" placeholder="Nombre del producto" value="{{ old('producto', isset($incidencia) ? $incidencia->producto : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lotes -->
                    <div class="form-section">
                        <h6><i class="fa fa-barcode mr-2"></i>Lotes</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote_sirena">Lote Sirena:</label>
                                    <input type="text" id="lote_sirena" name="lote_sirena" class="form-control" placeholder="Lote Sirena" value="{{ old('lote_sirena', isset($incidencia) ? $incidencia->lote_sirena : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote_proveedor">Lote Proveedor:</label>
                                    <input type="text" id="lote_proveedor" name="lote_proveedor" class="form-control" placeholder="Lote Proveedor" value="{{ old('lote_proveedor', isset($incidencia) ? $incidencia->lote_proveedor : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fcp">FCP:</label>
                                    <input type="date" id="fcp" name="fcp" class="form-control" value="{{ old('fcp', isset($incidencia) ? $incidencia->fcp : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comunicación con proveedor -->
                    <div class="form-section">
                        <h6><i class="fa fa-envelope mr-2"></i>Comunicación con Proveedor</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="informe_a_proveedor">¿Informe a Proveedor?:</label>
                                    <select id="informe_a_proveedor" name="informe_a_proveedor" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Si" {{ (isset($incidencia) && $incidencia->informe_a_proveedor == 'Si') ? 'selected' : '' }}>Sí</option>
                                        <option value="No" {{ (isset($incidencia) && $incidencia->informe_a_proveedor == 'No') ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="numero_informe">Nº de Informe:</label>
                                    <input type="text" id="numero_informe" name="numero_informe" class="form-control" placeholder="Número de informe" value="{{ old('numero_informe', isset($incidencia) ? $incidencia->numero_informe : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_envio_proveedor">Fecha Envío a Proveedor:</label>
                                    <input type="date" id="fecha_envio_proveedor" name="fecha_envio_proveedor" class="form-control" value="{{ old('fecha_envio_proveedor', isset($incidencia) && $incidencia->fecha_envio_proveedor ? \Carbon\Carbon::parse($incidencia->fecha_envio_proveedor)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_respuesta_proveedor">Fecha Respuesta Proveedor:</label>
                                    <input type="date" id="fecha_respuesta_proveedor" name="fecha_respuesta_proveedor" class="form-control" value="{{ old('fecha_respuesta_proveedor', isset($incidencia) && $incidencia->fecha_respuesta_proveedor ? \Carbon\Carbon::parse($incidencia->fecha_respuesta_proveedor)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informes -->
                    <div class="form-section">
                        <h6><i class="fa fa-file-text-o mr-2"></i>Informes</h6>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="informe_respuesta">Informe Respuesta:</label>
                                    <textarea id="informe_respuesta" name="informe_respuesta" class="form-control" rows="3" placeholder="Informe de respuesta del proveedor">{{ old('informe_respuesta', isset($incidencia) ? $incidencia->informe_respuesta : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="comentarios">Comentarios:</label>
                                    <textarea id="comentarios" name="comentarios" class="form-control" rows="3" placeholder="Comentarios adicionales">{{ old('comentarios', isset($incidencia) ? $incidencia->comentarios : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas de reclamación -->
                    <div class="form-section">
                        <h6><i class="fa fa-clock-o mr-2"></i>Fechas de Reclamación</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_reclamacion_respuesta1">Fecha Reclamación Respuesta 1:</label>
                                    <input type="date" id="fecha_reclamacion_respuesta1" name="fecha_reclamacion_respuesta1" class="form-control" value="{{ old('fecha_reclamacion_respuesta1', isset($incidencia) && $incidencia->fecha_reclamacion_respuesta1 ? \Carbon\Carbon::parse($incidencia->fecha_reclamacion_respuesta1)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_reclamacion_respuesta2">Fecha Reclamación Respuesta 2:</label>
                                    <input type="date" id="fecha_reclamacion_respuesta2" name="fecha_reclamacion_respuesta2" class="form-control" value="{{ old('fecha_reclamacion_respuesta2', isset($incidencia) && $incidencia->fecha_reclamacion_respuesta2 ? \Carbon\Carbon::parse($incidencia->fecha_reclamacion_respuesta2)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_decision_destino_producto">Fecha Decisión Destino Producto:</label>
                                    <input type="date" id="fecha_decision_destino_producto" name="fecha_decision_destino_producto" class="form-control" value="{{ old('fecha_decision_destino_producto', isset($incidencia) ? $incidencia->fecha_decision_destino_producto : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('material_kilo.historial_incidencias_devoluciones') }}" class="btn btn-secondary">
                                    <i class="fa fa-times mr-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-save mr-1"></i>{{ isset($incidencia) ? 'Actualizar' : 'Guardar' }} Incidencia
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

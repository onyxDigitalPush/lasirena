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

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .loading i {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Badges para tipos consistentes con historial */
    .badge-incidencia {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    
    .badge-devolucion {
        background-color: #17a2b8 !important;
        color: #fff !important;
    }
    
    .badge-general {
        background-color: #6c757d !important;
        color: #fff !important;
    }
    
    /* Estilos para botones de archivo */
    .btn-archivo {
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    /* MODAL HISTORIAL FIJO - TAMAÑO VIEWPORT */
    #historialEmailsModal .modal-dialog {
        max-width: 95vw !important;
        width: 95vw !important;
        height: 90vh !important;
        margin: 2.5vh auto !important;
    }
    
    #historialEmailsModal .modal-content {
        height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    #historialEmailsModal .modal-header {
        flex-shrink: 0 !important;
    }
    
    #historialEmailsModal .modal-footer {
        flex-shrink: 0 !important;
    }
    
    #historialEmailsModal .modal-body {
        flex: 1 !important;
        overflow-y: auto !important;
        padding: 15px !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    /* CONTENEDOR DE TABLA CON ALTURA FIJA */
    #tabla_historial_container {
        flex: 1 !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 400px !important;
    }
    
    #historial_emails_table_wrapper {
        flex: 1 !important;
        overflow: auto !important;
    }
    
    /* TABLA CON SCROLL HORIZONTAL Y COLUMNAS FLEXIBLES */
    #historial_emails_table {
        width: 100% !important;
        table-layout: auto !important; /* CAMBIADO DE fixed A auto */
        min-width: 1400px !important; /* MÁS ANCHO PARA MÁS ARCHIVOS */
    }
    
    /* ANCHO MÍNIMO PARA COLUMNAS (NO FIJO) */
    #historial_emails_table th:nth-child(1),
    #historial_emails_table td:nth-child(1) { min-width: 80px !important; }   /* Tipo */
    #historial_emails_table th:nth-child(2),
    #historial_emails_table td:nth-child(2) { min-width: 150px !important; }  /* Remitente */
    #historial_emails_table th:nth-child(3),
    #historial_emails_table td:nth-child(3) { min-width: 150px !important; }  /* Destinatarios */
    #historial_emails_table th:nth-child(4),
    #historial_emails_table td:nth-child(4) { min-width: 100px !important; }  /* BCC */
    #historial_emails_table th:nth-child(5),
    #historial_emails_table td:nth-child(5) { min-width: 200px !important; }  /* Asunto */
    #historial_emails_table th:nth-child(6),
    #historial_emails_table td:nth-child(6) { min-width: 80px !important; }   /* Mensaje */
    #historial_emails_table th:nth-child(7),
    #historial_emails_table td:nth-child(7) { min-width: 200px !important; }  /* MÁS ANCHO PARA ARCHIVOS */
    #historial_emails_table th:nth-child(8),
    #historial_emails_table td:nth-child(8) { min-width: 140px !important; }  /* Fecha */
    
    /* TEXTO QUE SE PUEDE EXPANDIR (SOLO PARA CELDAS NORMALES) */
    #historial_emails_table td:not(.archivos-cell) {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        vertical-align: middle !important;
    }
    
    /* CELDA DE ARCHIVOS CON WRAP PARA MOSTRAR TODOS */
    #historial_emails_table td.archivos-cell {
        white-space: normal !important; /* PERMITE SALTO DE LÍNEA */
        overflow: visible !important;
        text-overflow: initial !important;
        vertical-align: top !important;
        padding: 8px !important;
    }
    
    /* BOTONES DE ARCHIVO MÁS PEQUEÑOS */
    .btn-archivo-download {
        font-size: 10px !important;
        padding: 2px 6px !important;
        margin: 2px !important;
        border-radius: 3px !important;
        display: inline-block !important;
    }
    
    /* PREVIEW MENSAJE FIJO */
    #mensaje_preview_container {
        flex-shrink: 0 !important;
        max-height: 200px !important;
        margin-top: 10px !important;
    }
    
    #mensaje_preview {
        max-height: 150px !important;
        overflow-y: auto !important;
        white-space: pre-wrap !important;
        word-wrap: break-word !important;
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
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <h5 class="card-title mb-0">
                        {{ isset($incidencia) ? 'Editar Incidencia' : 'Nueva Incidencia' }}
                    </h5>
                </div>
            
                @if (isset($incidencia))
                    <div class="text-right">
                        <button type="button"
                            class="btn btn-info btn-sm open-history"
                            data-id="{{ $incidencia->id }}">
                            <i class="fa fa-history"></i> Historial Correos Enviados
                        </button>
                        <button type="button" id="btnAbrirModalCorreo" class="btn btn-primary" data-toggle="modal" data-target="#modalEnviarCorreo">
                            <i class="fas fa-envelope"></i> Enviar Correo
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($incidencia) ? route('material_kilo.actualizar_incidencia', $incidencia->id) : route('material_kilo.guardar_incidencia_completa') }}" enctype="multipart/form-data">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estado">Estado:</label>
                                    <select id="estado" name="estado" class="form-control" required>
                                        <option value="Registrada" {{ (isset($incidencia) && $incidencia->estado == 'Registrada') ? 'selected' : '' }}>Registrada</option>
                                        <option value="Gestionada" {{ (isset($incidencia) && $incidencia->estado == 'Gestionada') ? 'selected' : '' }}>Gestionada</option>
                                        <option value="En Pausa" {{ (isset($incidencia) && $incidencia->estado == 'En Pausa') ? 'selected' : '' }}>En Pausa</option>
                                        <option value="Cerrada" {{ (isset($incidencia) && $incidencia->estado == 'Cerrada') ? 'selected' : '' }}>Cerrada</option>
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
                        
                        <!-- Archivos Relacionados -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="archivos_incidencia">Archivos Relacionados:</label>
                                    <div class="row">
                                        <div class="col-9">
                                            <input type="file" 
                                                   class="form-control-file" 
                                                   id="archivos_incidencia" 
                                                   name="archivos[]" 
                                                   multiple 
                                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                                            <small class="form-text text-muted">
                                                Archivos permitidos: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF (máx. 10MB cada uno)
                                            </small>
                                        </div>
                                        <div class="col-3">
                                            <button type="button" class="btn btn-sm btn-info" id="btn_previsualizar_archivos_incidencia">
                                                <i class="fas fa-eye"></i> Ver Archivos
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Lista de archivos seleccionados -->
                                    <div id="lista_archivos_seleccionados_incidencia" class="mt-2"></div>
                                    <!-- Lista de archivos existentes (en modo edición) -->
                                    <div id="lista_archivos_existentes_incidencia" class="mt-2"></div>
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

    @if (isset($incidencia))
        @php
            $code = old('codigo', $incidencia->codigo ?? '');
            $producto = old('producto', $incidencia->producto ?? '');
            $tipoInc = strtolower(old('tipo_incidencia', $incidencia->tipo_incidencia ?? ''));

            $plantilla_general = <<<EOT
                Buenos días/Buenas tardes,

                Se adjunta informe de incidencia sobre su producto:
                
                {CODE} - {PRODUCT}.
                
                Se ruega contestación a la incidencia en el plazo máximo de 2 días. La respuesta debe ser enviada al siguiente e-mail: calidad.proveedores@lasirena.es; inspcalidad@lasirena.es
                
                El informe de respuesta debe tener el siguiente formato:
                
                Análisis de la incidencia, que incluya:
                · Explicación sobre el posible origen de la incidencia.
                · Plan de control establecido para la característica origen de la incidencia (frecuencia de control, cantidad de muestreo, etc.).
                · Resultados de los controles de producción relacionados con la incidencia.
                
                Medidas correctivas, que incluyan:
                · Descripción de las medidas correctivas adoptadas para eliminar el origen de la incidencia.
                · Descripción de las medidas de seguimiento adoptadas, en caso necesario.
                
                Gracias.
                
                Saludos,
            EOT;
                
            $plantilla_temperatura = <<<EOT
                Buenos días/Buenas tardes,
                
                Se adjunta informe de incidencia sobre su producto:
                
                {CODE} - {PRODUCT}.
                
                Se ha procedido a la devolución de los pallets.
                El lote rechazado por temperatura se bloquea automáticamente y no se permitirá su entrada en un futuro.
                Se ruega contestación a la incidencia en el plazo máximo de 24 horas. La respuesta debe ser enviada al siguiente e-mail: calidad.proveedores@lasirena.es; inspcalidad@lasirena.es
                
                El informe de respuesta debe incluir:
                
                Trazabilidad del producto: Cantidad fabricada / Cantidad servida.
                Temperatura de expedición del producto.
                Temperaturas del viaje y plataformas.
                Explicación sobre el posible origen de la incidencia.
                Plan de control establecido para eliminar el origen de la incidencia.
                Certificado de destino del producto rechazado. En caso de destrucción, documento firmado por el gestor autorizado.
                
                Gracias.
                
                Saludos,
            EOT;

            $mensajePlantilla = (strpos($tipoInc, 'temperatur') !== false) ? $plantilla_temperatura : $plantilla_general;
            $mensajePlantilla = str_replace(['{CODE}','{PRODUCT}'], [($code ?: 'N/D'), ($producto ?: 'producto')], $mensajePlantilla);
            $asuntoDefault = 'Incidencia ' . ($code ? ($code . ' - ') : '') . ($producto ?: 'producto');
        @endphp

        <!-- Modal Enviar Correo (servidor rellena asunto y mensaje) -->
        <div class="modal fade" id="modalEnviarCorreo" tabindex="-1" role="dialog" aria-labelledby="modalCorreoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form id="formEnviarCorreoProveedor" method="POST" enctype="multipart/form-data" action="{{ route('proveedores.emails.enviar') }}">
                        @csrf
                        <input type="hidden" name="id_proveedor" value="{{ $incidencia->proveedor->id_proveedor ?? '' }}">
                        <input type="hidden" name="id_devolucion_proveedor" value="">
                        <input type="hidden" name="id_incidencia_proveedor" value="{{ $incidencia->id ?? '' }}">

                        <div class="modal-header bg-primary text-white">
                            <h4 class="modal-title">Enviar correo al proveedor</h4>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Asunto</label>
                                    <input type="text" class="form-control" name="asunto" id="correo_asunto" placeholder="Asunto del correo" required maxlength="255" value="{{ old('asunto', $asuntoDefault) }}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Correo Remitente</label>
                                    <input type="email" class="form-control" name="email_remitente" id="correo_remitente" value="{{ old('email_remitente', auth()->user()->email) }}" required>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Mensaje</label>
                                    <textarea class="form-control" name="mensaje" id="correo_mensaje" rows="8" placeholder="Escriba el mensaje..." required>{{ old('mensaje', $mensajePlantilla) }}</textarea>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Correo del proveedor</label>
                                    <input type="text" class="form-control" name="emails_destinatarios" id="correo_destinatarios" value="{{ old('emails_destinatarios', $incidencia->proveedor->email_proveedor ?? '') }}" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Copias (BCC, separadas por ;)</label>
                                    <input type="text" class="form-control" name="emails_bcc" id="correo_bcc" placeholder="bcc1@dominio.com;bcc2@dominio.com" value="{{ old('emails_bcc') }}">
                                </div>

                                <!-- Archivos adjuntos al enviar (3 slots) -->
                                <div class="form-group col-md-4">
                                    <label>Archivo 1</label>
                                    <input type="file" class="form-control archivo-input" name="archivo1" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt,.zip,.rar">
                                    <div class="preview mt-2"></div>
                                    <div class="file-actions mt-1" style="display: none;">
                                        <button type="button" class="btn btn-xs btn-danger" onclick="limpiarArchivoModal(1)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Archivo 2</label>
                                    <input type="file" class="form-control archivo-input" name="archivo2" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt,.zip,.rar">
                                    <div class="preview mt-2"></div>
                                    <div class="file-actions mt-1" style="display: none;">
                                        <button type="button" class="btn btn-xs btn-danger" onclick="limpiarArchivoModal(2)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Archivo 3</label>
                                    <input type="file" class="form-control archivo-input" name="archivo3" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt,.zip,.rar">
                                    <div class="preview mt-2"></div>
                                    <div class="file-actions mt-1" style="display: none;">
                                        <button type="button" class="btn btn-xs btn-danger" onclick="limpiarArchivoModal(3)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Enviar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Historial Emails -->
    <div class="modal fade" id="historialEmailsModal" tabindex="-1" role="dialog" aria-labelledby="historialEmailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="historialEmailsModalLabel">
                        <i class="fa fa-envelope mr-2"></i>Historial de Emails de la Incidencia
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- CONTENEDOR TABLA CON ALTURA FIJA -->
                    <div id="tabla_historial_container">
                        <!-- Aquí se insertará la tabla por JavaScript -->
                    </div>

                    <!-- Preview mensaje (FIJO ABAJO) -->
                    <div id="mensaje_preview_container" style="display:none;">
                        <hr>
                        <h6><i class="fa fa-eye mr-1"></i>Vista del mensaje</h6>
                        <div id="mensaje_preview" class="border p-3 bg-light rounded"></div>
                        <div class="mt-2 text-right">
                            <button id="cerrar_preview_mensaje" class="btn btn-sm btn-secondary">
                                <i class="fa fa-times mr-1"></i>Cerrar vista
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mantener solo JS necesario: manejo de archivos y previews (sin plantillas ni apertura forzada)
        var archivosSeleccionadosIncidencia = [];
        var archivosExistentesIncidencia = [];

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function limpiarArchivoModal(numero) {
            var input = document.querySelector('input[name="archivo' + numero + '"]');
            var preview = input.parentElement.querySelector('.preview');
            var actions = input.parentElement.querySelector('.file-actions');
            
            // Limpiar input y preview
            input.value = "";
            input.disabled = false;
            preview.innerHTML = "";
            
            // Ocultar botones de acción
            if (actions) {
                actions.style.display = 'none';
            }
        }

        function mostrarArchivosSeleccionadosIncidencia() {
            var container = $('#lista_archivos_seleccionados_incidencia');
            container.empty();
            if (archivosSeleccionadosIncidencia.length > 0) {
                var html = '<div class="mt-2"><strong>Archivos seleccionados:</strong><ul class="list-group mt-1">';
                archivosSeleccionadosIncidencia.forEach(function(archivo, index) {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center py-1">';
                    html += '<span><i class="fas fa-file"></i> ' + archivo.name + ' (' + formatFileSize(archivo.size) + ')</span>';
                    html += '<button type="button" class="btn btn-sm btn-danger" onclick="removerArchivoSeleccionadoIncidencia(' + index + ')"><i class="fas fa-times"></i></button>';
                    html += '</li>';
                });
                html += '</ul></div>';
                container.html(html);
            }
        }

        function removerArchivoSeleccionadoIncidencia(index) {
            archivosSeleccionadosIncidencia.splice(index, 1);
            actualizarInputArchivosIncidencia();
            mostrarArchivosSeleccionadosIncidencia();
        }

        function actualizarInputArchivosIncidencia() {
            var input = $('#archivos_incidencia')[0];
            if (!input) return;
            var dt = new DataTransfer();
            archivosSeleccionadosIncidencia.forEach(function(archivo) {
                dt.items.add(archivo);
            });
            input.files = dt.files;
        }

        $(document).ready(function() {
            if ($('#modalEnviarCorreo').length) $('#modalEnviarCorreo').appendTo('body');
            if ($('#historialEmailsModal').length) $('#historialEmailsModal').appendTo('body');

            $('#archivos_incidencia').on('change', function() {
                var files = Array.from(this.files || []);
                archivosSeleccionadosIncidencia = files;
                mostrarArchivosSeleccionadosIncidencia();
            });

            $('#btn_previsualizar_archivos_incidencia').on('click', function() {
                if (archivosSeleccionadosIncidencia.length === 0 && (!archivosExistentesIncidencia || archivosExistentesIncidencia.length === 0)) {
                    alert('No hay archivos seleccionados o existentes');
                    return;
                }
                var ventana = window.open('', '_blank', 'width=800,height=600');
                var html = '<html><head><title>Archivos de Incidencia</title></head><body>';
                html += '<h3>Archivos Seleccionados</h3>' + ($('#lista_archivos_seleccionados_incidencia').html() || '<p>No hay</p>');
                html += '<h3>Archivos Existentes</h3>' + ($('#lista_archivos_existentes_incidencia').html() || '<p>No hay</p>');
                html += '</body></html>';
                ventana.document.write(html);
            });

            @if(isset($incidencia) && $incidencia->archivos)
                archivosExistentesIncidencia = @json($incidencia->archivos);
                // Si quieres mostrar archivos existentes aquí, implementa mostrarArchivosExistentesIncidencia similar a la función de arriba.
            @endif

            $(document).on('change', '.archivo-input', function() {
                let preview = $(this).siblings('.preview');
                let actions = $(this).siblings('.file-actions');
                preview.html('');
                actions.hide();
                
                if (this.files && this.files[0]) {
                    let file = this.files[0];
                    let ext = file.name.split('.').pop().toLowerCase();
                    
                    if (['jpg','jpeg','png','bmp','webp'].includes(ext)) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            preview.html('<img src="' + e.target.result + '" style="max-width:80px;max-height:80px;border-radius:4px;border:1px solid #ddd;">');
                        }
                        reader.readAsDataURL(file);
                    } else {
                        let icon = 'fa-file';
                        if (ext === 'pdf') icon = 'fa-file-pdf';
                        else if (['doc','docx'].includes(ext)) icon = 'fa-file-word';
                        else if (['xls','xlsx'].includes(ext)) icon = 'fa-file-excel';
                        else if (['zip','rar'].includes(ext)) icon = 'fa-file-archive';
                        else if (ext === 'txt') icon = 'fa-file-text';
                        preview.html('<i class="fas ' + icon + ' fa-2x text-secondary"></i><div style="font-size:0.85em">' + file.name + '</div>');
                    }
                    
                    // Mostrar botón de eliminar cuando hay archivo
                    actions.show();
                }
            });
        });

        "use strict";

        // --- Funciones auxiliares ---
        function escapeHtml(s) {
          return $("<div>").text(s || "").html();
        }
    
        function nl2br(s) {
          return escapeHtml(s || "").replace(/\r\n|\r|\n/g, "<br>");
        }
    
        function fmtDate(dstr) {
          if (!dstr) return "";
          var d = new Date(dstr);
          if (isNaN(d.getTime())) d = new Date((dstr || "").replace(" ", "T"));
          if (isNaN(d.getTime())) return dstr;
          var p = (n) => (n < 10 ? "0" + n : n);
          return `${p(d.getDate())}-${p(d.getMonth() + 1)}-${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
        }
    
        var loadingHistory = false;
        var historialDataTable = null;
    
        function destroyHistorialTable() {
          try {
            if (historialDataTable) {
              try { historialDataTable.destroy(true); } catch (e) {}
              historialDataTable = null;
            }
            if ($.fn.DataTable && $.fn.DataTable.isDataTable("#historial_emails_table")) {
              try { $("#historial_emails_table").DataTable().destroy(true); } catch (e) {}
            }
            $("#historial_emails_table_wrapper").remove();
            $("#historial_emails_table").remove();
          } catch (e) {}
        }
    
        function createHistorialTable() {
          const html = `
            <table id="historial_emails_table" class="table table-sm table-striped table-bordered">
              <thead class="thead-dark">
                <tr>
                  <th class="text-center">Remitente</th>
                  <th class="text-center">Destinatarios</th>
                  <th class="text-center">BCC</th>
                  <th class="text-center">Asunto</th>
                  <th class="text-center">Mensaje</th>
                  <th class="text-center">Archivos</th>
                  <th class="text-center">Fecha envío</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>`;
          $("#tabla_historial_container").html(html);
        }
    
        function initHistorialTable() {
          try {
            var $table = $("#historial_emails_table");
            if ($table.length === 0) return;
            if (typeof $.fn.DataTable === 'undefined') return;
            setTimeout(function () {
              try {
                var $tbody = $table.find('tbody');
                var rowCount = $tbody.find('tr').length;
                if (rowCount === 1) {
                  var $firstTd = $tbody.find('tr').first().find('td');
                  if ($firstTd.length === 1 && $firstTd.attr('colspan')) return;
                }
                historialDataTable = $table.DataTable({
                  order: [[6, "desc"]],
                  pageLength: 10,
                  lengthMenu: [[10, 25, 50], [10, 25, 50]],
                  columnDefs: [
                    { orderable: false, targets: [4, 5] },
                    { className: "text-center", targets: [0, 4, 5, 6] }
                  ],
                  responsive: true,
                  destroy: true,
                  searching: true,
                  paging: true,
                  info: true,
                  autoWidth: false,
                  language: {
                    emptyTable: "No hay emails registrados para esta incidencia",
                    zeroRecords: "No se encontraron emails coincidentes con la búsqueda",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ emails",
                    infoEmpty: "Mostrando 0 a 0 de 0 emails",
                    infoFiltered: "(filtrado de _MAX_ emails totales)",
                    search: "Buscar emails:",
                    paginate: {
                      first: "Primero",
                      last: "Último",
                      next: "Siguiente",
                      previous: "Anterior",
                    },
                    lengthMenu: "Mostrar _MENU_ emails por página",
                  },
                });
              } catch (e) {}
            }, 150);
          } catch (e) {}
        }
    
        // MODAL HISTORIAL EMAILS
        $(document)
          .off("click.historyModal")
          .on("click.historyModal", ".open-history", manejarClickHistorial);
    
        $(document).ready(function() {
          $(".open-history").off("click.historyModalDirect").on("click.historyModalDirect", manejarClickHistorial);
        });
    
        function manejarClickHistorial(e) {
          e.preventDefault();
          var $btn = $(this);
          if ($btn.hasClass("loading") || loadingHistory) return false;
          $btn.addClass("loading").prop("disabled", true);
          loadingHistory = true;
          var id = $btn.data("id");
          if (!id || id === "" || isNaN(id)) {
            $btn.removeClass("loading").prop("disabled", false);
            loadingHistory = false;
            return alert("Error: ID de la incidencia no válida");
          }
          var $modal = $("#historialEmailsModal");
          if ($modal.length === 0) {
            $btn.removeClass("loading").prop("disabled", false);
            loadingHistory = false;
            return alert("Error: Modal de historial no disponible");
          }
          $("#mensaje_preview").empty();
          $("#mensaje_preview_container").hide();
          destroyHistorialTable();
          createHistorialTable();
          var $tbody = $("#historial_emails_table tbody");
          $tbody.html('<tr><td colspan="7" class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Cargando historial de emails...</td></tr>');
          try { $modal.modal("show"); } catch (error) {
            try {
              $modal.addClass("show").css("display", "block");
              $("body").addClass("modal-open");
              if ($(".modal-backdrop").length === 0) {
                $("body").append('<div class="modal-backdrop fade show"></div>');
              }
            } catch (alternativeError) {
              $btn.removeClass("loading").prop("disabled", false);
              loadingHistory = false;
              return alert("Error al abrir el modal del historial: " + error.message);
            }
          }
          var ajaxUrl = "/material_kilo/" + id + "/historialincidencia";
          $.ajax({
            url: ajaxUrl,
            method: "GET",
            timeout: 15000,
            dataType: "json"
          })
            .done(function (res) {
              $tbody.empty();
              var emails = res && res.data ? res.data : [];
              if (!emails.length) {
                $tbody.html(
                  '<tr><td colspan="7" class="text-center text-muted">' +
                  '<i class="fa fa-inbox"></i><br>' +
                  'No hay emails registrados para esta incidencia</td></tr>'
                );
              } else {
                emails.forEach(function (email) {
                  var remitente = escapeHtml(email.email_remitente || "");
                  var destinatarios = escapeHtml(email.emails_destinatarios || "");
                  var bcc = escapeHtml(email.emails_bcc || "");
                  var asunto = escapeHtml(email.asunto || "");
                  var mensajeEnc = encodeURIComponent(email.mensaje || "");
                  var archivosHtml = "";
                  var archivos = email.archivos_procesados || [];
                  if (archivos && archivos.length) {
                    archivos.forEach(function (archivo, idx) {
                      archivosHtml += `<button type="button" class="btn btn-xs btn-outline-primary btn-archivo-download" 
                        data-url="${archivo.url}" data-nombre="${archivo.nombre}" title="Descargar archivo ${idx + 1}">
                        <i class="fa fa-download"></i> ${idx + 1}</button>`;
                    });
                  } else {
                    archivosHtml = '<span class="text-muted small">Sin archivos</span>';
                  }
                  var fecha = fmtDate(email.created_at || email.fecha_envio_proveedor || "");
                  var row = `
                    <tr>
                      <td>${remitente}</td>
                      <td>${destinatarios}</td>
                      <td>${bcc}</td>
                      <td>${asunto}</td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-outline-secondary btn-ver-mensaje" data-mensaje="${mensajeEnc}">Ver</button>
                      </td>
                      <td class="archivos-cell text-center">${archivosHtml}</td>
                      <td class="text-center">${fecha}</td>
                    </tr>`;
                  $tbody.append(row);
                });
              }
              if (emails.length > 0) {
                setTimeout(function() { initHistorialTable(); }, 100);
              }
            })
            .fail(function (xhr, status, error) {
              var errorMessage = "Error desconocido";
              var errorDetails = "";
              if (xhr.status === 404) {
                errorMessage = "Incidencia no encontrada";
                errorDetails = "La incidencia con ID " + id + " no existe";
              } else if (xhr.status === 500) {
                errorMessage = "Error interno del servidor";
                errorDetails = "Por favor, contacte al administrador";
              } else if (xhr.status === 0) {
                errorMessage = "Error de conexión";
                errorDetails = "Verifique su conexión a internet";
              } else if (status === "timeout") {
                errorMessage = "Tiempo de espera agotado";
                errorDetails = "La consulta tardó demasiado tiempo";
              } else {
                errorMessage = "Error al cargar historial";
                errorDetails = "Código: " + xhr.status + " | " + error;
              }
              $tbody.html(
                '<tr><td colspan="7" class="text-center text-danger p-4">' +
                '<i class="fa fa-exclamation-triangle mb-2" style="font-size: 24px;"></i><br>' +
                '<strong>' + errorMessage + '</strong><br>' +
                '<small>' + errorDetails + '</small><br><br>' +
                '<button class="btn btn-sm btn-outline-secondary" onclick="$(this).closest(\'.modal\').modal(\'hide\')">Cerrar</button>' +
                '</td></tr>'
              );
            })
            .always(function () {
              $btn.removeClass("loading").prop("disabled", false);
              loadingHistory = false;
            });
        }
    
        // BOTÓN VER MENSAJE (delegado)
        $(document)
          .off("click.messagePreview")
          .on("click.messagePreview", ".btn-ver-mensaje", function (e) {
            e.preventDefault();
            var enc = $(this).data("mensaje") || "";
            var msg = "";
            try { msg = decodeURIComponent(enc); } catch (ex) { msg = enc || ""; }
            $("#mensaje_preview").html(nl2br(msg));
            $("#mensaje_preview_container").show();
          });
      
        // BOTÓN CERRAR VISTA (delegado)
        $(document)
          .off("click.closePreview")
          .on("click.closePreview", "#cerrar_preview_mensaje", function (e) {
            e.preventDefault();
            $("#mensaje_preview").empty();
            $("#mensaje_preview_container").hide();
          });
      
        // Función para cerrar el modal historial
        function cerrarModalHistorial() {
          $("#mensaje_preview").empty();
          $("#mensaje_preview_container").hide();
          loadingHistory = false;
          destroyHistorialTable();
          $(".modal-backdrop").remove();
          $("body").removeClass("modal-open");
        }
    
        // Cierre de modal historial (Bootstrap)
        $("#historialEmailsModal").on("hide.bs.modal", cerrarModalHistorial);
    
        // Cierre manual del modal (botón X y backdrop)
        $(document).on("click", "#historialEmailsModal .close, .modal-backdrop", function() {
          var $modal = $("#historialEmailsModal");
          try { $modal.modal("hide"); } catch (e) {
            $modal.removeClass("show").css("display", "none");
            cerrarModalHistorial();
          }
        });
    
        // Forzar descarga directa de archivos (sin abrir nueva pestaña)
        $(document).off("click.descargaArchivo").on("click.descargaArchivo", ".btn-archivo-download", function(e) {
          e.preventDefault();
          var url = $(this).data("url");
          var nombre = $(this).data("nombre") || "archivo";
          var a = document.createElement('a');
          a.href = url;
          a.download = nombre;
          a.style.display = 'none';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        });
    </script>
@endsection

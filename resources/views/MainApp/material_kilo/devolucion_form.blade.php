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

    /* ===== FIX MODAL STACKING =====
       Evita que el modal quede detrás del backdrop o de contenedores con z-index */
    .modal {
        z-index: 1061 !important;
    }
    .modal-backdrop {
        z-index: 1060 !important;
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
@endsection

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-undo icon-gradient bg-info"></i>
            </div>
            <div>
                {{ isset($devolucion) ? 'Editar Devolución' : 'Nueva Devolución' }}
                <div class="page-title-subheading">
                    Gestión de devoluciones de proveedores
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

            <div class="card-header bg-info text-dark d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <h5 class="card-title mb-0">
                        {{ isset($devolucion) ? 'Editar Devolución' : 'Nueva Devolución' }}
                    </h5>
                </div>
            
                @if (isset($devolucion))
                    <div class="text-right">
                        <button type="button"
                            class="btn btn-warning btn-sm open-history"
                            data-id="{{ $devolucion->id }}">
                            <i class="fa fa-history"></i> Historial Correos Enviados
                        </button>
                        <button type="button" id="btnAbrirModalCorreo" class="btn btn-primary" data-toggle="modal" data-target="#modalEnviarCorreo">
                            <i class="fas fa-envelope"></i> Enviar Correo
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($devolucion) ? route('material_kilo.actualizar_devolucion', $devolucion->id) : route('material_kilo.guardar_devolucion_completa') }}" enctype="multipart/form-data">
                    @csrf
                    @if(isset($devolucion))
                        @method('PUT')
                    @endif
                    
                    <!-- Datos del producto -->
                    <div class="form-section">
                        <h6><i class="fa fa-cube mr-2"></i>Información del Producto</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo_producto">Código del Producto:</label>
                                    <input type="text" id="codigo_producto" name="codigo_producto" class="form-control" placeholder="Código del producto" value="{{ old('codigo_producto', isset($devolucion) ? $devolucion->codigo_producto : '') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="proveedor_devolucion">Proveedor:</label>
                                    <select id="proveedor_devolucion" name="codigo_proveedor" class="form-control" required>
                                        <option value="">Seleccione un proveedor</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id_proveedor }}" {{ (isset($devolucion) && $devolucion->codigo_proveedor == $proveedor->id_proveedor) ? 'selected' : '' }}>
                                                {{ $proveedor->nombre_proveedor }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="descripcion_producto">Descripción del Producto:</label>
                                    <input type="text" id="descripcion_producto" name="descripcion_producto" class="form-control" placeholder="Descripción del producto" value="{{ old('descripcion_producto', isset($devolucion) ? $devolucion->descripcion_producto : '') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estado">Estado:</label>
                                    <select id="estado" name="estado" class="form-control" required>
                                        <option value="Registrada" {{ (isset($devolucion) && $devolucion->estado == 'Registrada') ? 'selected' : '' }}>Registrada</option>
                                        <option value="Gestionada" {{ (isset($devolucion) && $devolucion->estado == 'Gestionada') ? 'selected' : '' }}>Gestionada</option>
                                        <option value="En Pausa" {{ (isset($devolucion) && $devolucion->estado == 'En Pausa') ? 'selected' : '' }}>En Pausa</option>
                                        <option value="Cerrada" {{ (isset($devolucion) && $devolucion->estado == 'Cerrada') ? 'selected' : '' }}>Cerrada</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="form-section">
                        <h6><i class="fa fa-calendar mr-2"></i>Fechas</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha Inicio:</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', isset($devolucion) && $devolucion->fecha_inicio ? \Carbon\Carbon::parse($devolucion->fecha_inicio)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha Fin:</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ old('fecha_fin', isset($devolucion) && $devolucion->fecha_fin ? \Carbon\Carbon::parse($devolucion->fecha_fin)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_reclamacion">Fecha Reclamación:</label>
                                    <input type="date" id="fecha_reclamacion" name="fecha_reclamacion" class="form-control" value="{{ old('fecha_reclamacion', isset($devolucion) && $devolucion->fecha_reclamacion ? \Carbon\Carbon::parse($devolucion->fecha_reclamacion)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clasificación -->
                    <div class="form-section">
                        <h6><i class="fa fa-tags mr-2"></i>Clasificación</h6>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="año_devolucion">Año:</label>
                                    <select id="año_devolucion" name="año" class="form-control" required>
                                        @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ (isset($devolucion) && $devolucion->año == $year) ? 'selected' : ($year == now()->year ? 'selected' : '') }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="mes_devolucion">Mes:</label>
                                    <select id="mes_devolucion" name="mes" class="form-control" required>
                                        <option value="1" {{ (isset($devolucion) && $devolucion->mes == 1) ? 'selected' : ($mes == 1 ? 'selected' : '') }}>Enero</option>
                                        <option value="2" {{ (isset($devolucion) && $devolucion->mes == 2) ? 'selected' : ($mes == 2 ? 'selected' : '') }}>Febrero</option>
                                        <option value="3" {{ (isset($devolucion) && $devolucion->mes == 3) ? 'selected' : ($mes == 3 ? 'selected' : '') }}>Marzo</option>
                                        <option value="4" {{ (isset($devolucion) && $devolucion->mes == 4) ? 'selected' : ($mes == 4 ? 'selected' : '') }}>Abril</option>
                                        <option value="5" {{ (isset($devolucion) && $devolucion->mes == 5) ? 'selected' : ($mes == 5 ? 'selected' : '') }}>Mayo</option>
                                        <option value="6" {{ (isset($devolucion) && $devolucion->mes == 6) ? 'selected' : ($mes == 6 ? 'selected' : '') }}>Junio</option>
                                        <option value="7" {{ (isset($devolucion) && $devolucion->mes == 7) ? 'selected' : ($mes == 7 ? 'selected' : '') }}>Julio</option>
                                        <option value="8" {{ (isset($devolucion) && $devolucion->mes == 8) ? 'selected' : ($mes == 8 ? 'selected' : '') }}>Agosto</option>
                                        <option value="9" {{ (isset($devolucion) && $devolucion->mes == 9) ? 'selected' : ($mes == 9 ? 'selected' : '') }}>Septiembre</option>
                                        <option value="10" {{ (isset($devolucion) && $devolucion->mes == 10) ? 'selected' : ($mes == 10 ? 'selected' : '') }}>Octubre</option>
                                        <option value="11" {{ (isset($devolucion) && $devolucion->mes == 11) ? 'selected' : ($mes == 11 ? 'selected' : '') }}>Noviembre</option>
                                        <option value="12" {{ (isset($devolucion) && $devolucion->mes == 12) ? 'selected' : ($mes == 12 ? 'selected' : '') }}>Diciembre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="clasificacion_incidencia_dev">Clasificación de Incidencia:</label>
                                    <select id="clasificacion_incidencia_dev" name="clasificacion_incidencia" class="form-control">
                                        <option value="">Seleccione una clasificación</option>
                                        <option value="RG1" {{ (isset($devolucion) && $devolucion->clasificacion_incidencia == 'RG1') ? 'selected' : '' }}>RG - Reclamación Grave</option>
                                        <option value="RL1" {{ (isset($devolucion) && $devolucion->clasificacion_incidencia == 'RL1') ? 'selected' : '' }}>RL - Reclamación Leve</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Top100FY2 -->
                    {{-- <div class="form-section">
                        <h6><i class="fa fa-star mr-2"></i>Top100FY2</h6>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="top100fy2">Top100:</label>
                                    <input type="text" id="top100fy2" name="top100fy2" class="form-control" placeholder="Top100FY2" value="{{ old('top100fy2', isset($devolucion) ? $devolucion->top100fy2 : '') }}">
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <!-- Descripción motivo -->
                    <div class="form-section">
                        <h6><i class="fa fa-file-text mr-2"></i>Descripción Motivo</h6>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="descripcion_motivo">Descripción Motivo:</label>
                                    <textarea id="descripcion_motivo" name="descripcion_motivo" class="form-control" rows="3" placeholder="Descripción del motivo">{{ old('descripcion_motivo', isset($devolucion) ? $devolucion->descripcion_motivo : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Especificaciones -->
                    <div class="form-section">
                        <h6><i class="fa fa-list mr-2"></i>Especificaciones</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="especificacion_motivo_reclamacion_leve">Especificación Motivo Reclamación Leve:</label>
                                    <textarea id="especificacion_motivo_reclamacion_leve" name="especificacion_motivo_reclamacion_leve" class="form-control" rows="3" placeholder="Especificación motivo leve">{{ old('especificacion_motivo_reclamacion_leve', isset($devolucion) ? $devolucion->especificacion_motivo_reclamacion_leve : '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="especificacion_motivo_reclamacion_grave">Especificación Motivo Reclamación Grave:</label>
                                    <select id="especificacion_motivo_grave" name="especificacion_motivo_reclamacion_grave" class="form-control">
                                        <option value="">Seleccione motivo grave</option>
                                        <option value="Carton/Papel" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Carton/Papel') ? 'selected' : '' }}>Carton/Papel</option>
                                        <option value="Colillas" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Colillas') ? 'selected' : '' }}>Colillas</option>
                                        <option value="Cristales" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Cristales') ? 'selected' : '' }}>Cristales</option>
                                        <option value="Elemento de goma/plastico" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Elemento de goma/plastico') ? 'selected' : '' }}>Elemento de goma/plastico</option>
                                        <option value="Elemento de metalicos" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Elemento de metalicos') ? 'selected' : '' }}>Elemento de metalicos</option>
                                        <option value="Elemento de madera" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Elemento de madera') ? 'selected' : '' }}>Elemento de madera</option>
                                        <option value="Elemento organicos humanos (pelo, etc)" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Elemento organicos humanos (pelo, etc)') ? 'selected' : '' }}>Elemento organicos humanos (pelo, etc)</option>
                                        <option value="Elementos vegetales (hojas, tallos, etc)" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Elementos vegetales (hojas, tallos, etc)') ? 'selected' : '' }}>Elementos vegetales (hojas, tallos, etc)</option>
                                        <option value="Insectos/animales" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Insectos/animales') ? 'selected' : '' }}>Insectos/animales</option>
                                        <option value="Intoxicacion" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Intoxicacion') ? 'selected' : '' }}>Intoxicacion</option>
                                        <option value="Reaccion alergica" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Reaccion alergica') ? 'selected' : '' }}>Reaccion alergica</option>
                                        <option value="Vomitos y nauseas" {{ (isset($devolucion) && $devolucion->especificacion_motivo_reclamacion_grave == 'Vomitos y nauseas') ? 'selected' : '' }}>Vomitos y nauseas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="form-section">
                        <h6><i class="fa fa-info-circle mr-2"></i>Información Adicional</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="recuperamos_objeto_extraño">¿Recuperamos Objeto Extraño?:</label>
                                    <select id="recuperamos_objeto_extraño" name="recuperamos_objeto_extraño" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Si" {{ (isset($devolucion) && $devolucion->recuperamos_objeto_extraño == 'Si') ? 'selected' : '' }}>Sí</option>
                                        <option value="No" {{ (isset($devolucion) && $devolucion->recuperamos_objeto_extraño == 'No') ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nombre_tienda">Nombre Tienda:</label>
                                    <input type="text" id="nombre_tienda" name="nombre_tienda" class="form-control" placeholder="Nombre de la tienda" value="{{ old('nombre_tienda', isset($devolucion) ? $devolucion->nombre_tienda : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="no_queja">No Queja:</label>
                                    <input type="text" id="no_queja" name="no_queja" class="form-control" placeholder="Número de queja" value="{{ old('no_queja', isset($devolucion) ? $devolucion->no_queja : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="origen_dev">Origen:</label>
                                    <input type="text" id="origen_dev" name="origen" class="form-control" placeholder="Origen" value="{{ old('origen', isset($devolucion) ? $devolucion->origen : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción queja -->
                    <div class="form-section">
                        <h6><i class="fa fa-exclamation-circle mr-2"></i>Descripción Queja</h6>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="descripcion_queja">Descripción Queja:</label>
                                    <textarea id="descripcion_queja" name="descripcion_queja" class="form-control" rows="3" placeholder="Descripción de la queja">{{ old('descripcion_queja', isset($devolucion) ? $devolucion->descripcion_queja : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lotes e informes -->
                    <div class="form-section">
                        <h6><i class="fa fa-barcode mr-2"></i>Lotes e Informes</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote_sirena_dev">Lote Sirena:</label>
                                    <input type="text" id="lote_sirena_dev" name="lote_sirena" class="form-control" placeholder="Lote Sirena" value="{{ old('lote_sirena', isset($devolucion) ? $devolucion->lote_sirena : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote_proveedor_dev">Lote Proveedor:</label>
                                    <input type="text" id="lote_proveedor_dev" name="lote_proveedor" class="form-control" placeholder="Lote Proveedor" value="{{ old('lote_proveedor', isset($devolucion) ? $devolucion->lote_proveedor : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="informe_a_proveedor_dev">¿Informe a Proveedor?:</label>
                                    <select id="informe_a_proveedor_dev" name="informe_a_proveedor" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Si" {{ (isset($devolucion) && $devolucion->informe_a_proveedor == 'Si') ? 'selected' : '' }}>Sí</option>
                                        <option value="No" {{ (isset($devolucion) && $devolucion->informe_a_proveedor == 'No') ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informe -->
                    <div class="form-section">
                        <h6><i class="fa fa-file-text-o mr-2"></i>Informe</h6>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="informe_dev">Informe:</label>
                                    <textarea id="informe_dev" name="informe" class="form-control" rows="3" placeholder="Informe">{{ old('informe', isset($devolucion) ? $devolucion->informe : '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Archivos del Informe -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="archivos_informe">Archivos del Informe:</label>
                                    <input type="file" 
                                           class="form-control-file" 
                                           id="archivos_informe" 
                                           name="archivos_informe[]" 
                                           multiple 
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                                    <small class="form-text text-muted">
                                        Archivos permitidos: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF (máx. 10MB cada uno)
                                    </small>
                                    <!-- Lista de archivos seleccionados del informe -->
                                    <div id="lista_archivos_seleccionados_informe" class="mt-2"></div>
                                    <!-- Lista de archivos existentes del informe (en modo edición) -->
                                    <div id="lista_archivos_existentes_informe" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas de respuesta -->
                    <div class="form-section">
                        <h6><i class="fa fa-clock-o mr-2"></i>Fechas de Respuesta</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_envio_proveedor_dev">Fecha Envío a Proveedor:</label>
                                    <input type="date" id="fecha_envio_proveedor_dev" name="fecha_envio_proveedor" class="form-control" value="{{ old('fecha_envio_proveedor', isset($devolucion) && $devolucion->fecha_envio_proveedor ? \Carbon\Carbon::parse($devolucion->fecha_envio_proveedor)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_respuesta_proveedor_dev">Fecha Respuesta Proveedor:</label>
                                    <input type="date" id="fecha_respuesta_proveedor_dev" name="fecha_respuesta_proveedor" class="form-control" value="{{ old('fecha_respuesta_proveedor', isset($devolucion) ? $devolucion->fecha_respuesta_proveedor : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_reclamacion_respuesta">Fecha Reclamación Respuesta:</label>
                                    <input type="date" id="fecha_reclamacion_respuesta" name="fecha_reclamacion_respuesta" class="form-control" value="{{ old('fecha_reclamacion_respuesta', isset($devolucion) ? $devolucion->fecha_reclamacion_respuesta : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="abierto">Abierto:</label>
                                    <select id="abierto" name="abierto" class="form-control">
                                        <option value="Si" {{ (isset($devolucion) && $devolucion->abierto == 'Si') ? 'selected' : 'selected' }}>Sí</option>
                                        <option value="No" {{ (isset($devolucion) && $devolucion->abierto == 'No') ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informes finales -->
                    <div class="form-section">
                        <h6><i class="fa fa-comment mr-2"></i>Informes Finales</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="informe_respuesta_dev">Informe Respuesta:</label>
                                    <textarea id="informe_respuesta_dev" name="informe_respuesta" class="form-control" rows="3" placeholder="Informe de respuesta">{{ old('informe_respuesta', isset($devolucion) ? $devolucion->informe_respuesta : '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="comentarios_dev">Comentarios:</label>
                                    <textarea id="comentarios_dev" name="comentarios" class="form-control" rows="3" placeholder="Comentarios adicionales">{{ old('comentarios', isset($devolucion) ? $devolucion->comentarios : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Archivos Relacionados -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="archivos_devolucion">Archivos Relacionados:</label>
                                    <div class="row">
                                        <div class="col-9">
                                            <input type="file" 
                                                   class="form-control-file" 
                                                   id="archivos_devolucion" 
                                                   name="archivos[]" 
                                                   multiple 
                                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                                            <small class="form-text text-muted">
                                                Archivos permitidos: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF (máx. 10MB cada uno)
                                            </small>
                                        </div>
                                        <div class="col-3">
                                            <button type="button" class="btn btn-sm btn-info" id="btn_previsualizar_archivos_devolucion">
                                                <i class="fas fa-eye"></i> Ver Archivos
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Lista de archivos seleccionados -->
                                    <div id="lista_archivos_seleccionados_devolucion" class="mt-2"></div>
                                    <!-- Lista de archivos existentes (en modo edición) -->
                                    <div id="lista_archivos_existentes_devolucion" class="mt-2"></div>
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
                                <button type="submit" class="btn btn-info">
                                    <i class="fa fa-save mr-1"></i>{{ isset($devolucion) ? 'Actualizar' : 'Guardar' }} Devolución
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (isset($devolucion))
        @php
            $code = old('codigo_producto', $devolucion->codigo_producto ?? '');
            $producto = old('descripcion_producto', $devolucion->descripcion_producto ?? '');
            $plantilla = <<<EOT
                Buenos días/Buenas tardes,

                Adjuntamos una reclamación recibida durante la última semana sobre un producto suministrado por ustedes:

                {$code} - {$producto}

                Se ruega contestación a esta reclamación en un plazo máximo de 2 días.

                El informe de respuesta debe tener el siguiente formato:
                
                Análisis de la incidencia, que incluya:
                · Explicación sobre el posible origen de la incidencia
                · Plan de control establecido para la característica origen de la incidencia
                · Resultados de los controles de producción relacionados con la incidencia
                
                Medidas correctivas, que incluyan:
                · Descripción de las medidas correctivas adoptadas para eliminar el origen de la incidencia
                · Descripción de las medidas de seguimiento adoptadas, en caso necesario
                
                Quedamos a la espera de su respuesta.
                EOT;
        @endphp
        <!-- Modal Enviar Correo -->
        <div class="modal fade" id="modalEnviarCorreo" tabindex="-1" role="dialog" aria-labelledby="modalCorreoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form id="formEnviarCorreoProveedor" method="POST" enctype="multipart/form-data" action="{{ route('proveedores.emails.enviar') }}">
                        @csrf
                        <input type="hidden" name="id_proveedor" value="{{ $devolucion->proveedor->id_proveedor ?? '' }}">
                        <input type="hidden" name="id_devolucion_proveedor" value="{{ $devolucion->id ?? '' }}">
                        <input type="hidden" name="id_incidencia_proveedor" value="">

                        <div class="modal-header bg-primary text-white">
                            <h4 class="modal-title">Enviar correo al proveedor</h4>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body row">
                            <div class="form-group col-md-6">
                                <label>Asunto</label>
                                <input type="text" id="correo_asunto" class="form-control" name="asunto" value="{{ old('asunto', 'Reclamación ' . ($devolucion->codigo_producto ?? '') . (isset($devolucion->descripcion_producto) && $devolucion->descripcion_producto ? ' - ' . $devolucion->descripcion_producto : '')) }}" required maxlength="255">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Correo Remitente</label>
                                <input type="email" class="form-control" name="email_remitente" value="{{ old('email_remitente', auth()->user()->email) }}" required>
                            </div>

                            <div class="form-group col-md-12">
                                <label>Mensaje</label>
                                <textarea id="correo_mensaje" class="form-control" name="mensaje" rows="8" required>{{ old('mensaje', $plantilla) }}</textarea>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Correo del proveedor</label>
                                <input type="text" class="form-control" name="emails_destinatarios" value="{{ old('emails_destinatarios', $devolucion->proveedor->email_proveedor ?? '') }}" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Copias (BCC, separadas por ;)</label>
                                <input type="text" class="form-control" name="emails_bcc" value="{{ old('emails_bcc') }}" placeholder="bcc1@dominio.com;bcc2@dominio.com">
                            </div>

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
                        <i class="fa fa-envelope mr-2"></i>Historial de Emails de la Reclamación
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
        // URL base del proyecto (sin protocolo fijo para evitar problemas con http/https)
        const baseUrl = "{{ rtrim(url('/'), '/') }}".replace('https://', 'http://');
        
        // Variables globales para manejo de archivos de devolución
        var archivosSeleccionadosDevolucion = [];
        var archivosExistentesDevolucion = [];

        // Variables globales para manejo de archivos de informe
        var archivosSeleccionadosInforme = [];
        var archivosExistentesInforme = [];

        // Variables globales para manejo de archivos de informe
        var archivosSeleccionadosInforme = [];
        var archivosExistentesInforme = [];

        // Utilidades
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

        function mostrarArchivosSeleccionadosDevolucion() {
            var container = $('#lista_archivos_seleccionados_devolucion');
            container.empty();
            if (archivosSeleccionadosDevolucion.length > 0) {
                var html = '<div class="mt-2"><strong>Archivos seleccionados:</strong><ul class="list-group mt-1">';
                archivosSeleccionadosDevolucion.forEach(function(archivo, index) {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center py-1">';
                    html += '<span><i class="fas fa-file"></i> ' + archivo.name + ' (' + formatFileSize(archivo.size) + ')</span>';
                    html += '<button type="button" class="btn btn-sm btn-danger" onclick="removerArchivoSeleccionadoDevolucion(' + index + ')"><i class="fas fa-times"></i></button>';
                    html += '</li>';
                });
                html += '</ul></div>';
                container.html(html);
            }
        }

        function removerArchivoSeleccionadoDevolucion(index) {
            archivosSeleccionadosDevolucion.splice(index, 1);
            actualizarInputArchivosDevolucion();
            mostrarArchivosSeleccionadosDevolucion();
        }

        function actualizarInputArchivosDevolucion() {
            var input = $('#archivos_devolucion')[0];
            if (!input) return;
            var dt = new DataTransfer();
            archivosSeleccionadosDevolucion.forEach(function(archivo) {
                dt.items.add(archivo);
            });
            input.files = dt.files;
        }

        function mostrarArchivosExistentesDevolucion() {
            var container = $('#lista_archivos_existentes_devolucion');
            container.empty();
            if (archivosExistentesDevolucion.length > 0) {
                var html = '<div class="mt-2"><strong>Archivos existentes:</strong><ul class="list-group mt-1">';
                archivosExistentesDevolucion.forEach(function(archivo) {
                    if (typeof archivo === 'object' && archivo.nombre_original && archivo.nombre) {
                        html += '<li class="list-group-item d-flex justify-content-between align-items-center py-2">';
                        html += '<div class="d-flex align-items-center">';
                        html += '<i class="fas fa-file text-primary mr-2"></i>';
                        html += '<div>';
                        html += '<strong>' + archivo.nombre_original + '</strong><br>';
                        html += '<small class="text-muted">' + (archivo.fecha_subida || 'Fecha desconocida') + '</small>';
                        html += '</div></div>';
                        html += '<div class="btn-group">';
                        html += '<a href="{{ url("material_kilo/devolucion") }}/{{ $devolucion->id }}/archivo/' + archivo.nombre + '/descargar" target="_blank" class="btn btn-sm btn-info" title="Descargar"><i class="fas fa-download"></i></a>';
                        html += '<button type="button" class="btn btn-sm btn-danger" onclick="eliminarArchivoExistenteDevolucion(\'' + archivo.nombre + '\')" title="Eliminar"><i class="fas fa-times"></i></button>';
                        html += '</div></li>';
                    }
                });
                html += '</ul></div>';
                container.html(html);
            }
        }

        function eliminarArchivoExistenteDevolucion(nombreArchivo) {
            if (!nombreArchivo || !{{ isset($devolucion) ? $devolucion->id : 'null' }}) {
                alert('Error: datos de archivo incompletos');
                return;
            }
            if (!confirm('¿Está seguro de eliminar este archivo?')) return;
        
            $.ajax({
                url: '{{ route("material_kilo.eliminar_archivo_devolucion") }}',
                type: 'DELETE',
                data: {
                    devolucion_id: {{ isset($devolucion) ? $devolucion->id : 'null' }},
                    nombre_archivo: nombreArchivo,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        archivosExistentesDevolucion = archivosExistentesDevolucion.filter(function(archivo) {
                            return archivo.nombre !== nombreArchivo;
                        });
                        mostrarArchivosExistentesDevolucion();
                        alert('Archivo eliminado correctamente');
                    } else {
                        alert('Error al eliminar archivo: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error al eliminar archivo');
                    console.error(xhr);
                }
            });
        }

        $(document).ready(function() {
            // mover modal al body
            if ($('#modalEnviarCorreo').length) $('#modalEnviarCorreo').appendTo('body');
            if ($('#historialEmailsModal').length) $('#historialEmailsModal').appendTo('body');

        
            // abrir modal: NO tocar asunto/mensaje (los pone el servidor mediante Blade)
            $('#btnAbrirModalCorreo').on('click', function() {
                $('#modalEnviarCorreo').modal('show');
            });
        
            // fecha reclamacion -> actualizar año/mes
            $('#fecha_reclamacion').on('change', function() {
                var fecha = $(this).val();
                if (fecha) {
                    var partes = fecha.split('-');
                    var año = partes[0];
                    var mes = parseInt(partes[1], 10);
                    $('#año_devolucion').val(año);
                    $('#mes_devolucion').val(mes);
                }
            });
        
            // archivos principales
            $('#archivos_devolucion').on('change', function() {
                var files = Array.from(this.files || []);
                archivosSeleccionadosDevolucion = files;
                mostrarArchivosSeleccionadosDevolucion();
            });
        
            // previsualizar lista de archivos
            $('#btn_previsualizar_archivos_devolucion').on('click', function() {
                if (archivosSeleccionadosDevolucion.length === 0 && archivosExistentesDevolucion.length === 0) {
                    alert('No hay archivos seleccionados o existentes');
                    return;
                }
                var ventana = window.open('', '_blank', 'width=800,height=600');
                var html = '<html><head><title>Archivos de Devolución</title></head><body>';
                html += '<h3>Archivos Seleccionados</h3>';
                html += $('#lista_archivos_seleccionados_devolucion').html() || '<p>No hay archivos seleccionados</p>';
                html += '<h3>Archivos Existentes</h3>';
                html += $('#lista_archivos_existentes_devolucion').html() || '<p>No hay archivos existentes</p>';
                html += '</body></html>';
                ventana.document.write(html);
                ventana.document.close();
            });
        
            // cargar archivos existentes si hay (modo edición)
            @if(isset($devolucion) && $devolucion->archivos)
                archivosExistentesDevolucion = @json($devolucion->archivos);
                mostrarArchivosExistentesDevolucion();
            @endif
        
            // previews para inputs de archivos del modal
            $(document).on('change', '.archivo-input', function() {
                let preview = $(this).siblings('.preview');
                let actions = $(this).siblings('.file-actions');
                preview.html('');
                actions.hide();
                
                if (this.files && this.files[0]) {
                    let file = this.files[0];
                    let ext = file.name.split('.').pop().toLowerCase();
                    
                    if (['jpg', 'jpeg', 'png', 'bmp', 'webp'].includes(ext)) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            preview.html('<img src="' + e.target.result + '" style="max-width:80px;max-height:80px;border-radius:4px;border:1px solid #ddd;">');
                        };
                        reader.readAsDataURL(file);
                    } else {
                        let icon = 'fa-file';
                        if (ext === 'pdf') icon = 'fa-file-pdf';
                        else if (['doc', 'docx'].includes(ext)) icon = 'fa-file-word';
                        else if (['xls', 'xlsx'].includes(ext)) icon = 'fa-file-excel';
                        else if (['zip', 'rar'].includes(ext)) icon = 'fa-file-archive';
                        else if (ext === 'txt') icon = 'fa-file-text';
                        preview.html('<i class="fas ' + icon + ' fa-2x text-secondary"></i><div style="font-size:0.85em">' + file.name + '</div>');
                    }
                    
                    // Mostrar botón de eliminar cuando hay archivo
                    actions.show();
                }
            });

            // Si estamos en modo edición, cargar archivos del informe existentes
            @if(isset($devolucion) && $devolucion->archivos_informe)
                archivosExistentesInforme = @json($devolucion->archivos_informe);
                mostrarArchivosExistentesInforme();
            @endif
        });

        // ============================================
        // FUNCIONES PARA ARCHIVOS DEL INFORME
        // ============================================

        // Función para mostrar archivos seleccionados del informe
        function mostrarArchivosSeleccionadosInforme() {
            var container = $('#lista_archivos_seleccionados_informe');
            container.empty();
            
            if (archivosSeleccionadosInforme.length > 0) {
                var html = '<div class="mt-2"><strong>Archivos seleccionados:</strong><ul class="list-group mt-1">';
                archivosSeleccionadosInforme.forEach(function(archivo, index) {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center py-1">';
                    html += '<span><i class="fas fa-file"></i> ' + archivo.name + ' (' + formatFileSize(archivo.size) + ')</span>';
                    html += '<button type="button" class="btn btn-sm btn-danger" onclick="removerArchivoSeleccionadoInforme(' + index + ')"><i class="fas fa-times"></i></button>';
                    html += '</li>';
                });
                html += '</ul></div>';
                container.html(html);
            }
        }

        // Función para mostrar archivos existentes del informe
        function mostrarArchivosExistentesInforme() {
            var container = $('#lista_archivos_existentes_informe');
            container.empty();
            
            if (archivosExistentesInforme.length > 0) {
                var html = '<div class="mt-2"><strong>Archivos existentes del informe:</strong><ul class="list-group mt-1">';
                archivosExistentesInforme.forEach(function(archivo, index) {
                    if (typeof archivo === 'object' && archivo.nombre_original && archivo.nombre) {
                        html += '<li class="list-group-item d-flex justify-content-between align-items-center py-2">';
                        html += '<div class="d-flex align-items-center">';
                        html += '<i class="fas fa-file text-info mr-2"></i>';
                        html += '<div>';
                        html += '<strong>' + archivo.nombre_original + '</strong><br>';
                        html += '<small class="text-muted">' + (archivo.fecha_subida || 'Fecha desconocida') + '</small>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="btn-group">';
                        html += '<a href="' + baseUrl + '/storage/devoluciones/archivos_informe/' + archivo.nombre + '" target="_blank" class="btn btn-sm btn-info" title="Ver/Descargar"><i class="fas fa-download"></i></a>';
                        html += '<button type="button" class="btn btn-sm btn-danger" onclick="eliminarArchivoExistenteInforme(\'' + archivo.nombre + '\')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                        html += '</div>';
                        html += '</li>';
                    }
                });
                html += '</ul></div>';
                container.html(html);
            }
        }

        // Función para remover archivo seleccionado del informe
        function removerArchivoSeleccionadoInforme(index) {
            archivosSeleccionadosInforme.splice(index, 1);
            actualizarInputArchivosInforme();
            mostrarArchivosSeleccionadosInforme();
        }

        // Función para eliminar archivo existente del informe
        function eliminarArchivoExistenteInforme(nombreArchivo) {
            if (!nombreArchivo || !{{ isset($devolucion) ? $devolucion->id : 'null' }}) {
                alert('Error: datos de archivo incompletos');
                return;
            }
            
            if (!confirm('¿Está seguro de eliminar este archivo del informe?')) return;
            
            $.ajax({
                url: '{{ route("material_kilo.eliminar_archivo_informe_devolucion") }}',
                type: 'DELETE',
                data: {
                    devolucion_id: {{ isset($devolucion) ? $devolucion->id : 'null' }},
                    nombre_archivo: nombreArchivo,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Remover de la lista de archivos existentes
                        archivosExistentesInforme = archivosExistentesInforme.filter(function(archivo) {
                            return archivo.nombre !== nombreArchivo;
                        });
                        mostrarArchivosExistentesInforme();
                        alert('Archivo eliminado correctamente');
                    } else {
                        alert('Error al eliminar archivo: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error al eliminar archivo');
                    console.error(xhr);
                }
            });
        }

        // Función para actualizar el input de archivos del informe
        function actualizarInputArchivosInforme() {
            var input = $('#archivos_informe')[0];
            var dt = new DataTransfer();
            archivosSeleccionadosInforme.forEach(function(archivo) {
                dt.items.add(archivo);
            });
            input.files = dt.files;
        }

        // Event listener para archivos del informe
        $(document).ready(function() {
            // Manejar selección de archivos del informe
            $('#archivos_informe').on('change', function() {
                var files = Array.from(this.files);
                archivosSeleccionadosInforme = files;
                mostrarArchivosSeleccionadosInforme();
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
                    emptyTable: "No hay emails registrados para esta reclamación",
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
            return alert("Error: ID de la reclamación no válida");
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
          var ajaxUrl = "/material_kilo/" + id + "/historialreclamacion";
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
                  'No hay emails registrados para esta reclamación</td></tr>'
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
                errorMessage = "Reclamación no encontrada";
                errorDetails = "La reclamación con ID " + id + " no existe";
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

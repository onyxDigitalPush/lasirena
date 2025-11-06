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
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fa fa-undo mr-2"></i>
                    {{ isset($devolucion) ? 'Editar Devolución' : 'Nueva Devolución' }}
                </h5>
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

    <script>
        // Variables globales para manejo de archivos de devolución
        var archivosSeleccionadosDevolucion = [];
        var archivosExistentesDevolucion = [];

        // Función para formatear tamaño de archivo
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Función para mostrar archivos seleccionados de devolución
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

        // Función para mostrar archivos existentes de devolución
        function mostrarArchivosExistentesDevolucion() {
            var container = $('#lista_archivos_existentes_devolucion');
            container.empty();
            
            if (archivosExistentesDevolucion.length > 0) {
                var html = '<div class="mt-2"><strong>Archivos existentes:</strong><ul class="list-group mt-1">';
                archivosExistentesDevolucion.forEach(function(archivo, index) {
                    if (typeof archivo === 'object' && archivo.nombre_original && archivo.nombre) {
                        html += '<li class="list-group-item d-flex justify-content-between align-items-center py-2">';
                        html += '<div class="d-flex align-items-center">';
                        html += '<i class="fas fa-file text-primary mr-2"></i>';
                        html += '<div>';
                        html += '<strong>' + archivo.nombre_original + '</strong><br>';
                        html += '<small class="text-muted">' + (archivo.fecha_subida || 'Fecha desconocida') + '</small>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="btn-group">';
                        @if(isset($devolucion))
                        html += '<a href="{{ url("material_kilo/devolucion") }}/{{ $devolucion->id }}/archivo/' + archivo.nombre + '/descargar" target="_blank" class="btn btn-sm btn-info" title="Descargar"><i class="fas fa-download"></i></a>';
                        @else
                        html += '<span class="btn btn-sm btn-secondary disabled" title="Guarde primero para descargar"><i class="fas fa-download"></i></span>';
                        @endif
                        html += '<button type="button" class="btn btn-sm btn-danger" onclick="eliminarArchivoExistenteDevolucion(\'' + archivo.nombre + '\')" title="Eliminar"><i class="fas fa-times"></i></button>';
                        html += '</div>';
                        html += '</li>';
                    }
                });
                html += '</ul></div>';
                container.html(html);
            }
        }

        // Función para remover archivo seleccionado
        function removerArchivoSeleccionadoDevolucion(index) {
            archivosSeleccionadosDevolucion.splice(index, 1);
            actualizarInputArchivosDevolucion();
            mostrarArchivosSeleccionadosDevolucion();
        }

        // Función para eliminar archivo existente
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
                        // Remover de la lista de archivos existentes
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

        // Función para actualizar el input de archivos
        function actualizarInputArchivosDevolucion() {
            var input = $('#archivos_devolucion')[0];
            var dt = new DataTransfer();
            archivosSeleccionadosDevolucion.forEach(function(archivo) {
                dt.items.add(archivo);
            });
            input.files = dt.files;
        }

        // Event listeners
        $(document).ready(function() {
            // Actualizar año y mes automáticamente cuando cambie la fecha de reclamación
            $('#fecha_reclamacion').on('change', function() {
                var fecha = $(this).val();
                if (fecha) {
                    var partes = fecha.split('-');
                    var año = partes[0];
                    var mes = parseInt(partes[1], 10);
                    
                    // Actualizar el select de año
                    $('#año_devolucion').val(año);
                    
                    // Actualizar el select de mes
                    $('#mes_devolucion').val(mes);
                }
            });

            // Manejar selección de archivos
            $('#archivos_devolucion').on('change', function() {
                var files = Array.from(this.files);
                archivosSeleccionadosDevolucion = files;
                mostrarArchivosSeleccionadosDevolucion();
            });

            // Botón para previsualizar archivos
            $('#btn_previsualizar_archivos_devolucion').on('click', function() {
                if (archivosSeleccionadosDevolucion.length === 0 && archivosExistentesDevolucion.length === 0) {
                    alert('No hay archivos seleccionados o existentes');
                    return;
                }
                
                // Mostrar en ventana separada
                var ventana = window.open('', '_blank', 'width=600,height=400');
                var html = '<html><head><title>Archivos de Devolución</title></head><body>';
                html += '<h3>Archivos Seleccionados</h3>';
                html += $('#lista_archivos_seleccionados_devolucion').html();
                html += '<h3>Archivos Existentes</h3>';
                html += $('#lista_archivos_existentes_devolucion').html();
                html += '</body></html>';
                ventana.document.write(html);
            });

            // Si estamos en modo edición, cargar archivos existentes
            @if(isset($devolucion) && $devolucion->archivos)
                archivosExistentesDevolucion = @json($devolucion->archivos);
                mostrarArchivosExistentesDevolucion();
            @endif
        });
    </script>
@endsection

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
                <form method="POST" action="{{ isset($devolucion) ? route('material_kilo.actualizar_devolucion', $devolucion->id) : route('material_kilo.guardar_devolucion_completa') }}">
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
                                    <input type="text" id="codigo_producto" name="codigo_producto" class="form-control" placeholder="Código del producto" value="{{ old('codigo_producto', isset($devolucion) ? $devolucion->codigo_producto : '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_proveedor">Proveedor:</label>
                                    <select id="id_proveedor" name="id_proveedor" class="form-control" required>
                                        <option value="">Seleccione un proveedor</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id_proveedor }}" {{ (isset($devolucion) && $devolucion->id_proveedor == $proveedor->id_proveedor) ? 'selected' : '' }}>
                                                {{ $proveedor->nombre_proveedor }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="descripcion_producto">Descripción del Producto:</label>
                                    <input type="text" id="descripcion_producto" name="descripcion_producto" class="form-control" placeholder="Descripción del producto" value="{{ old('descripcion_producto', isset($devolucion) ? $devolucion->descripcion_producto : '') }}">
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
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', isset($devolucion) ? $devolucion->fecha_inicio : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha Fin:</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ old('fecha_fin', isset($devolucion) ? $devolucion->fecha_fin : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="np">NP:</label>
                                    <input type="text" id="np" name="np" class="form-control" placeholder="NP" value="{{ old('np', isset($devolucion) ? $devolucion->np : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_reclamacion">Fecha Reclamación:</label>
                                    <input type="date" id="fecha_reclamacion" name="fecha_reclamacion" class="form-control" value="{{ old('fecha_reclamacion', isset($devolucion) ? $devolucion->fecha_reclamacion : '') }}">
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
                                    <label for="año">Año:</label>
                                    <select id="año" name="año" class="form-control" required>
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
                                    <label for="mes">Mes:</label>
                                    <select id="mes" name="mes" class="form-control" required>
                                        @php
                                            $meses = [
                                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                            ];
                                        @endphp
                                        @foreach($meses as $numero => $nombre)
                                            <option value="{{ $numero }}" {{ (isset($devolucion) && $devolucion->mes == $numero) ? 'selected' : ($numero == now()->month ? 'selected' : '') }}>
                                                {{ $nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="clasificacion_incidencia">Clasificación de Incidencia:</label>
                                    <select id="clasificacion_incidencia" name="clasificacion_incidencia" class="form-control">
                                        <option value="">Seleccione una clasificación</option>
                                        <option value="RG1" {{ (isset($devolucion) && $devolucion->clasificacion_incidencia == 'RG1') ? 'selected' : '' }}>RG - Reclamación General</option>
                                        <option value="RL1" {{ (isset($devolucion) && $devolucion->clasificacion_incidencia == 'RL1') ? 'selected' : '' }}>RL - Reclamación Legal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_reclamacion">Tipo Reclamación:</label>
                                    <select id="tipo_reclamacion" name="tipo_reclamacion" class="form-control">
                                        <option value="">Seleccione tipo</option>
                                        <option value="Leve" {{ (isset($devolucion) && $devolucion->tipo_reclamacion == 'Leve') ? 'selected' : '' }}>Leve</option>
                                        <option value="Grave" {{ (isset($devolucion) && $devolucion->tipo_reclamacion == 'Grave') ? 'selected' : '' }}>Grave</option>
                                        <option value="Crítica" {{ (isset($devolucion) && $devolucion->tipo_reclamacion == 'Crítica') ? 'selected' : '' }}>Crítica</option>
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
                                    <label for="top100fy2">Top100FY2:</label>
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
                                    <textarea id="especificacion_motivo_reclamacion_grave" name="especificacion_motivo_reclamacion_grave" class="form-control" rows="3" placeholder="Especificación motivo grave">{{ old('especificacion_motivo_reclamacion_grave', isset($devolucion) ? $devolucion->especificacion_motivo_reclamacion_grave : '') }}</textarea>
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
                                    <label for="origen">Origen:</label>
                                    <input type="text" id="origen" name="origen" class="form-control" placeholder="Origen" value="{{ old('origen', isset($devolucion) ? $devolucion->origen : '') }}">
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
                                    <label for="lote_sirena">Lote Sirena:</label>
                                    <input type="text" id="lote_sirena" name="lote_sirena" class="form-control" placeholder="Lote Sirena" value="{{ old('lote_sirena', isset($devolucion) ? $devolucion->lote_sirena : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lote_proveedor">Lote Proveedor:</label>
                                    <input type="text" id="lote_proveedor" name="lote_proveedor" class="form-control" placeholder="Lote Proveedor" value="{{ old('lote_proveedor', isset($devolucion) ? $devolucion->lote_proveedor : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="informe_a_proveedor">¿Informe a Proveedor?:</label>
                                    <select id="informe_a_proveedor" name="informe_a_proveedor" class="form-control">
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
                                    <label for="informe">Informe:</label>
                                    <textarea id="informe" name="informe" class="form-control" rows="3" placeholder="Informe">{{ old('informe', isset($devolucion) ? $devolucion->informe : '') }}</textarea>
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
                                    <label for="fecha_envio_proveedor">Fecha Envío a Proveedor:</label>
                                    <input type="date" id="fecha_envio_proveedor" name="fecha_envio_proveedor" class="form-control" value="{{ old('fecha_envio_proveedor', isset($devolucion) ? $devolucion->fecha_envio_proveedor : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_respuesta_proveedor">Fecha Respuesta Proveedor:</label>
                                    <input type="date" id="fecha_respuesta_proveedor" name="fecha_respuesta_proveedor" class="form-control" value="{{ old('fecha_respuesta_proveedor', isset($devolucion) ? $devolucion->fecha_respuesta_proveedor : '') }}">
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
                                    <label for="informe_respuesta">Informe Respuesta:</label>
                                    <textarea id="informe_respuesta" name="informe_respuesta" class="form-control" rows="3" placeholder="Informe de respuesta">{{ old('informe_respuesta', isset($devolucion) ? $devolucion->informe_respuesta : '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="comentarios">Comentarios:</label>
                                    <textarea id="comentarios" name="comentarios" class="form-control" rows="3" placeholder="Comentarios adicionales">{{ old('comentarios', isset($devolucion) ? $devolucion->comentarios : '') }}</textarea>
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
@endsection

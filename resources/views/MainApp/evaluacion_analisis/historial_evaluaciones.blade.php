@extends('layouts.app')
<!-- Modal Agregar Analítica  -->
<div class="modal fade" id="modalAgregarAnalitica" tabindex="-1" role="dialog"
    aria-labelledby="modalAgregarAnaliticaLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formAgregarAnalitica" method="POST" action="{{ route('evaluacion_analisis.guardar_analitica') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modalAgregarAnaliticaLabel">Agregar Analítica a <span
                            id="nombreTiendaModal"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="num_tienda" id="tienda_id_modal">
                    <input type="hidden" name="modo_edicion" id="modo_edicion_modal" value="agregar">
                    <input type="hidden" name="id_registro" id="id_registro_modal">
                    <div class="form-group">
                        <label for="asesor_externo_empresa">Asesor Externo - Empresa</label>
                        <input type="text" class="form-control" name="asesor_externo_empresa" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_real_analitica">Fecha Teorica de la Analítica</label>
                        <input type="date" class="form-control" name="fecha_real_analitica" required>
                    </div>
                    <div class="form-group">
                        <label for="periodicidad">Periodicidad Temporal</label>
                        <div class="row">
                            <div class="col-9">
                                <select class="form-control" name="periodicidad" id="periodicidad_select" required>
                                    <option value="">-- Seleccionar periodicidad --</option>
                                    <option value="1 mes">1 mes</option>
                                    <option value="3 meses">3 meses</option>
                                    <option value="6 meses">6 meses</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" name="periodicidad_no_procede" id="periodicidad_no_procede" value="1">
                                    <label class="form-check-label" for="periodicidad_no_procede">
                                        No procede
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tipo_analitica">Tipo Analítica</label>
                        <select class="form-control" name="tipo_analitica" id="tipo_analitica_modal" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="Resultados agua">Analitica agua</option>
                            <option value="Tendencias superficie">Analitica de superficie</option>
                            <option value="Tendencias micro">Analitica de microbiologia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="proveedor_id">Proveedor Relacionado</label>
                        <div class="row">
                            <div class="col-9">
                                <select class="form-control" name="proveedor_id" id="proveedor_id_select">
                                    <option value="">-- Seleccionar proveedor --</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" name="proveedor_no_procede" id="proveedor_no_procede" value="1">
                                    <label class="form-check-label" for="proveedor_no_procede">
                                        No procede
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campo para subir archivos -->
                    <div class="form-group">
                        <label for="archivos_analitica">Archivos Relacionados</label>
                        <div class="row">
                            <div class="col-9">
                                <input type="file" 
                                       class="form-control-file" 
                                       id="archivos_analitica" 
                                       name="archivos[]" 
                                       multiple 
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                                <small class="form-text text-muted">
                                    Archivos permitidos: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF (máx. 10MB cada uno)
                                </small>
                            </div>
                            <div class="col-3">
                                <button type="button" class="btn btn-sm btn-info" id="btn_previsualizar_archivos">
                                    <i class="fas fa-eye"></i> Ver Archivos
                                </button>
                            </div>
                        </div>
                        <!-- Lista de archivos seleccionados -->
                        <div id="lista_archivos_seleccionados" class="mt-2"></div>
                        <!-- Lista de archivos existentes (en modo edición) -->
                        <div id="lista_archivos_existentes" class="mt-2"></div>
                    </div>

                    <!-- Campos condicionales según tipo -->
                    <div class="form-group d-none" id="detalle_agua_group">
                        <label for="detalle_agua">Tipo (Resultados agua)</label>
                        <select class="form-control" name="detalle_tipo" id="detalle_agua">
                            <option value="">-- Seleccionar --</option>
                            <option value="Grifo">Grifo</option>
                            <option value="Lavabo">Lavabo</option>
                        </select>
                    </div>

                    <div class="form-group d-none" id="detalle_superficie_group">
                        <label for="detalle_superficie">Tipo (Analitica de superficie)</label>
                        <select class="form-control" name="detalle_tipo" id="detalle_superficie">
                            <option value="">-- Seleccionar --</option>
                            <option value="Mesa">Mesa</option>
                            <option value="Suelo">Suelo</option>
                        </select>
                    </div>

                    <div id="micro_fields" class="d-none">
                        <div class="form-group">
                            <label for="codigo_producto">Código de producto</label>
                            <input type="text" class="form-control" name="codigo_producto" id="codigo_producto_modal">
                        </div>
                        <div class="form-group">
                            <label for="descripcion_producto">Descripción producto</label>
                            <input type="text" class="form-control" name="descripcion_producto" id="descripcion_producto_modal" readonly>
                        </div>
                    </div>
                    <input type="hidden" name="detalle_tipo" id="detalle_tipo_hidden" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Analítica</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@section('title_content')
    {{-- Espacio para el mensaje de success del backend --}}

    <div class="page-title-wrapper">
        <div class="page-title-heading">
@php
    $tipoDisplay = [
        'Resultados agua' => 'Analitica agua',
        'Tendencias superficie' => 'Analitica de superficie',
        'Tendencias micro' => 'Analitica de microbiologia'
    ];
@endphp
<script>
    // Toggle text for Mostrar N más / Mostrar menos
    $(document).on('click', '.toggle-analiticas', function(e){
        e.preventDefault();
        var target = $(this).data('target');
        var $target = $(target);
        var moreText = $(this).data('more') || 'Mostrar más';
        var lessText = $(this).data('less') || 'Mostrar menos';
        $target.collapse('toggle');
        var $link = $(this);
        // wait a tick for collapse to change aria-expanded
        setTimeout(function(){
            var expanded = $target.hasClass('show');
            $link.text(expanded ? lessText : moreText);
        }, 300);
    });

    // Borrar analítica (AJAX DELETE) - usa la ruta nombrada 'evaluacion_analisis.eliminar'
    $(document).on('click', '.btn-borrar-analitica', function(e){
        e.preventDefault();
        if(!confirm('Confirmar borrado de esta analítica?')) return;
        var id = $(this).data('analitica-id');
        var token = '{{ csrf_token() }}';
        var url = '{{ route("evaluacion_analisis.eliminar") }}';
        $.ajax({
            url: url,
            type: 'POST',
            data: { id: id, tipo: 'analitica', _method: 'DELETE', _token: token },
            success: function(resp){
                if(resp.success){
                    alert('Analítica borrada correctamente');
                    location.reload();
                } else {
                    alert('No se pudo borrar: ' + (resp.message || 'error'));
                }
            },
            error: function(xhr){
                var msg = 'Error al borrar la analítica';
                if(xhr.responseJSON && xhr.responseJSON.message) msg += ': ' + xhr.responseJSON.message;
                alert(msg);
            }
        });
    });
</script>
<script>
    (function(){
        // Mostrar/ocultar campos del modal según tipo
        $(document).on('change', '#tipo_analitica_modal', function(){
            var val = $(this).val();
            // ocultar todos
            $('#detalle_agua_group').addClass('d-none');
            $('#detalle_superficie_group').addClass('d-none');
            $('#micro_fields').addClass('d-none');
            // habilitar proveedor por defecto
            $('#proveedor_id_select').prop('required', true).prop('disabled', false);

            if (val === 'Resultados agua') {
                $('#detalle_agua_group').removeClass('d-none');
            } else if (val === 'Tendencias superficie') {
                $('#detalle_superficie_group').removeClass('d-none');
            } else if (val === 'Tendencias micro') {
                $('#micro_fields').removeClass('d-none');
                // proveedor no obligatorio
                $('#proveedor_id_select').prop('required', false).prop('disabled', false);
            }
        });

        // Autocompletar descripcion producto al ingresar codigo (debounce simple)
        var prodTimer = null;
        // URL para buscar productos (usa url() para respetar host/basepath)
        var productosBuscarUrl = '{{ url("api/productos/buscar") }}';
        $(document).on('input', '#codigo_producto_modal', function(){
            var codigo = $(this).val().trim();
            clearTimeout(prodTimer);
            $('#descripcion_producto_modal').val('');
            if (!codigo) return;
            prodTimer = setTimeout(function(){
                $.ajax({
                    url: productosBuscarUrl,
                    method: 'GET',
                    data: { codigo: codigo },
                    success: function(resp){
                        if (resp && resp.success && resp.descripcion) {
                            $('#descripcion_producto_modal').val(resp.descripcion);
                        } else {
                            // permitir editar manualmente si no encontró
                            $('#descripcion_producto_modal').val('');
                        }
                    },
                    error: function(){
                        $('#descripcion_producto_modal').val('');
                    }
                });
            }, 400);
        });
    })();
</script>
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-flask icon-gradient bg-secondary"></i>
            </div>
            <div>Historial de Evaluaciones Analíticas
                <div class="page-title-subheading">
                    Listado de tiendas y analíticas
                </div>
            </div>
        </div>
    </div>
    @if (method_exists($tiendas, 'links'))
        <div class="d-flex justify-content-center">{{ $tiendas->links() }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@endsection
<br><br>
@section('main_content')
    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Num Tienda</th>
                    <th class="text-center">Nombre Tienda</th>
                    <th class="text-center">Dirección</th>
                    <th class="text-center">Responsable</th>
                    <th class="text-center">Agregar Analítica</th>
                    <th class="text-center">ID</th>
                    <th class="text-center">Analítica</th>
                    <th class="text-center">Archivos</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tiendas as $tienda)
                    @if($tienda->analiticas && $tienda->analiticas->count() > 0)
                        @php 
                            $totalAnaliticas = $tienda->analiticas->count();
                            $mostrarBotonExpandir = $totalAnaliticas > 2;
                        @endphp
                        {{-- Si la tienda tiene analíticas, mostrar una fila por cada analítica --}}
                        @foreach($tienda->analiticas as $index => $analitica)
                            <tr class="fila-tienda-{{ $tienda->num_tienda }} {{ $index > 0 ? 'border-top-0' : '' }} 
                                       {{ $index >= 2 ? 'collapse analiticas-extra-' . $tienda->num_tienda : '' }}">
                                {{-- Datos de tienda solo en la primera fila --}}
                                @if($index == 0)
                                    <td class="text-center align-middle celda-tienda-{{ $tienda->num_tienda }}" rowspan="{{ $mostrarBotonExpandir ? 3 : $totalAnaliticas }}">{{ $tienda->num_tienda }}</td>
                                    <td class="text-center align-middle celda-tienda-{{ $tienda->num_tienda }}" rowspan="{{ $mostrarBotonExpandir ? 3 : $totalAnaliticas }}">{{ $tienda->nombre_tienda }}</td>
                                    <td class="text-center align-middle celda-tienda-{{ $tienda->num_tienda }}" rowspan="{{ $mostrarBotonExpandir ? 3 : $totalAnaliticas }}">{{ $tienda->direccion_tienda }}</td>
                                    <td class="text-center align-middle celda-tienda-{{ $tienda->num_tienda }}" rowspan="{{ $mostrarBotonExpandir ? 3 : $totalAnaliticas }}">{{ $tienda->responsable }}</td>
                                    <td class="text-center align-middle celda-tienda-{{ $tienda->num_tienda }}" rowspan="{{ $mostrarBotonExpandir ? 3 : $totalAnaliticas }}">
                                        <a class="btn btn-primary btn-sm btn-agregar-analitica" href="#" data-toggle="modal"
                                            data-target="#modalAgregarAnalitica" data-id="{{ $tienda->num_tienda }}"
                                            data-nombre="{{ $tienda->nombre_tienda }}">
                                            <i class="fas fa-plus mr-1"></i>Agregar
                                        </a>
                                    </td>
                                @endif
                                
                                {{-- ID de la analítica --}}
                                <td class="text-center align-middle">
                                    <span class="badge badge-secondary">#{{ $analitica->id }}</span>
                                </td>
                                
                                {{-- Datos de la analítica --}}
                                <td class="text-left align-middle" style="min-width: 200px;">
                                    <div>
                                        <strong>{{ $tipoDisplay[$analitica->tipo_analitica] ?? $analitica->tipo_analitica }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $analitica->fecha_real_analitica }}</small>
                                        <br>
                                        @if(!empty($analitica->periodicidad_no_procede) && $analitica->periodicidad_no_procede == 1)
                                            <span class="badge badge-secondary">No procede</span>
                                        @else
                                            <small class="text-info">{{ $analitica->periodicidad ?: '-' }}</small>
                                        @endif
                                        @if($analitica->detalle_tipo)
                                            <br><small class="text-muted">{{ $analitica->detalle_tipo }}</small>
                                        @endif
                                    </div>
                                </td>
                                
                                {{-- Archivos de la analítica --}}
                                <td class="text-center align-middle" style="min-width: 180px;">
                                    @if($analitica->hasArchivos())
                                        @php $archivos = $analitica->getArchivosArray(); @endphp
                                        <div class="text-left">
                                            @foreach($archivos as $archivo)
                                                @if(is_array($archivo) && isset($archivo['nombre']) && isset($archivo['nombre_original']))
                                                    <div class="d-flex justify-content-between align-items-center mb-1 p-1 bg-light rounded archivo-item">
                                                        <small class="text-truncate-custom" title="{{ $archivo['nombre_original'] }}">
                                                            <i class="fas fa-file text-primary"></i> {{ Str::limit($archivo['nombre_original'], 12) }}
                                                        </small>
                                                        <a href="{{ route('evaluacion_analisis.descargar_archivo', ['analiticaId' => $analitica->id, 'nombreArchivo' => $archivo['nombre']]) }}" 
                                                           class="btn btn-xs btn-outline-primary" 
                                                           title="Descargar {{ $archivo['nombre_original'] }}" 
                                                           target="_blank">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <small class="text-muted">Sin archivos</small>
                                    @endif
                                </td>
                                
                                {{-- Estado de la analítica --}}
                                <td class="text-center align-middle" style="min-width: 120px;">
                                    @php
                                        $estado = $analitica->estado_analitica ?? 'sin_iniciar';
                                        $fechaCambio = null;
                                        if(!empty($analitica->fecha_cambio_estado)){
                                            try { $fechaCambio = \Carbon\Carbon::parse($analitica->fecha_cambio_estado)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                        } elseif(!empty($analitica->fecha_realizacion)){
                                            try { $fechaCambio = \Carbon\Carbon::parse($analitica->fecha_realizacion)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                        }
                                        $badgeClass = $estado === 'realizada' ? 'badge-success' : ($estado === 'pendiente' ? 'badge-warning' : 'badge-secondary');
                                        $label = $estado === 'realizada' ? 'Realizada' : ($estado === 'pendiente' ? 'Pendiente' : 'Sin iniciar');
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                                    @if($fechaCambio)
                                        <br><small class="text-muted">{{ $fechaCambio }}</small>
                                    @endif
                                </td>
                                
                                {{-- Acciones --}}
                                <td class="text-center align-middle" style="min-width: 100px;">
                                    <div class="btn-group-vertical btn-group-sm">
                                        <button class="btn btn-warning btn-sm btn-editar-analitica mb-1" 
                                            data-analitica-id="{{ $analitica->id }}"
                                            data-tienda-id="{{ $tienda->num_tienda }}"
                                            data-tienda-nombre="{{ $tienda->nombre_tienda }}"
                                            title="Editar analítica">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-borrar-analitica" 
                                            data-analitica-id="{{ $analitica->id }}"
                                            data-tienda-id="{{ $tienda->num_tienda }}"
                                            title="Eliminar analítica">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        
                        {{-- Fila con botón "Ver más" / "Ver menos" si hay más de 2 analíticas --}}
                        @if($mostrarBotonExpandir)
                            <tr class="fila-expandir-{{ $tienda->num_tienda }}">
                                <td colspan="5" class="text-center py-2">
                                    <button class="btn btn-sm btn-outline-info btn-toggle-analiticas" 
                                            data-target=".analiticas-extra-{{ $tienda->num_tienda }}"
                                            data-tienda="{{ $tienda->num_tienda }}"
                                            data-total="{{ $totalAnaliticas - 2 }}">
                                        <i class="fas fa-chevron-down mr-1"></i>
                                        Ver {{ $totalAnaliticas - 2 }} más
                                    </button>
                                </td>
                            </tr>
                        @endif
                    @else
                        {{-- Si la tienda no tiene analíticas, mostrar una fila vacía --}}
                        <tr>
                            <td class="text-center">{{ $tienda->num_tienda }}</td>
                            <td class="text-center">{{ $tienda->nombre_tienda }}</td>
                            <td class="text-center">{{ $tienda->direccion_tienda }}</td>
                            <td class="text-center">{{ $tienda->responsable }}</td>
                            <td class="text-center">
                                <a class="btn btn-primary btn-sm btn-agregar-analitica" href="#" data-toggle="modal"
                                    data-target="#modalAgregarAnalitica" data-id="{{ $tienda->num_tienda }}"
                                    data-nombre="{{ $tienda->nombre_tienda }}">
                                    <i class="fas fa-plus mr-1"></i>Agregar
                                </a>
                            </td>
                            <td class="text-center text-muted" colspan="5">Sin analíticas registradas</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        {{-- Paginación si usas ->paginate() en el controlador --}}

    </div>
@endsection
<!-- jQuery y Bootstrap JS solo para esta vista -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<style>
    .btn-xs {
        padding: 0.125rem 0.25rem;
        font-size: 0.75rem;
        line-height: 1.2;
        border-radius: 0.15rem;
    }
    .archivo-item {
        transition: background-color 0.2s;
    }
    .archivo-item:hover {
        background-color: #f8f9fa !important;
    }
    .text-truncate-custom {
        max-width: 120px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
    
    /* Estilos para el botón de expandir/colapsar */
    .btn-toggle-analiticas {
        transition: all 0.3s ease;
        border: 1px dashed #17a2b8;
        background-color: #f8f9fa;
    }
    .btn-toggle-analiticas:hover {
        background-color: #17a2b8;
        color: white;
    }
    
    /* Animación suave para las filas que se muestran/ocultan */
    .collapse {
        display: none;
    }
    .collapse.show {
        display: table-row;
    }
    
    /* Bordes suaves para las filas expandidas */
    .analiticas-extra-1:first-of-type,
    .analiticas-extra-2:first-of-type,
    .analiticas-extra-3:first-of-type,
    .analiticas-extra-4:first-of-type,
    .analiticas-extra-5:first-of-type {
        border-top: 1px solid #dee2e6 !important;
    }
</style>
<script>
    // Variables globales para manejo de archivos
    var archivosSeleccionados = [];
    var archivosExistentes = [];
    var analiticaIdActual = null;

    // Función para toggle de analíticas extras
    $(document).on('click', '.btn-toggle-analiticas', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var target = $btn.data('target');
        var tienda = $btn.data('tienda');
        var total = $btn.data('total');
        var $rows = $(target);
        
        if ($rows.hasClass('show')) {
            // Colapsar
            $rows.removeClass('show');
            $btn.html('<i class="fas fa-chevron-down mr-1"></i>Ver ' + total + ' más');
            
            // Ajustar rowspan de las celdas de tienda a 3 (2 analíticas + 1 fila de botón)
            $('.celda-tienda-' + tienda).attr('rowspan', 3);
        } else {
            // Expandir
            $rows.addClass('show');
            $btn.html('<i class="fas fa-chevron-up mr-1"></i>Ver menos');
            
            // Calcular el rowspan total: todas las analíticas + 1 fila de botón
            var totalAnaliticas = $('.fila-tienda-' + tienda).length;
            var rowspanTotal = totalAnaliticas + 1;
            $('.celda-tienda-' + tienda).attr('rowspan', rowspanTotal);
        }
    });

    // Función para mostrar archivos seleccionados
    function mostrarArchivosSeleccionados() {
        var container = $('#lista_archivos_seleccionados');
        container.empty();
        
        if (archivosSeleccionados.length > 0) {
            var html = '<div class="mt-2"><strong>Archivos seleccionados:</strong><ul class="list-group mt-1">';
            archivosSeleccionados.forEach(function(archivo, index) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center py-1">';
                html += '<span><i class="fas fa-file"></i> ' + archivo.name + ' (' + formatFileSize(archivo.size) + ')</span>';
                html += '<button type="button" class="btn btn-sm btn-danger" onclick="removerArchivoSeleccionado(' + index + ')"><i class="fas fa-times"></i></button>';
                html += '</li>';
            });
            html += '</ul></div>';
            container.html(html);
        }
    }

    // Función para mostrar archivos existentes
    function mostrarArchivosExistentes() {
        var container = $('#lista_archivos_existentes');
        container.empty();
        
        if (archivosExistentes.length > 0) {
            var html = '<div class="mt-2"><strong>Archivos existentes:</strong><ul class="list-group mt-1">';
            archivosExistentes.forEach(function(archivo, index) {
                // Validar que el archivo tenga la estructura esperada
                if (typeof archivo === 'object' && archivo.nombre_original && archivo.nombre && archivo.tamano) {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center py-2">';
                    html += '<div class="d-flex align-items-center">';
                    html += '<i class="fas fa-file text-primary mr-2"></i>';
                    html += '<div>';
                    html += '<strong>' + archivo.nombre_original + '</strong><br>';
                    html += '<small class="text-muted">' + formatFileSize(archivo.tamano) + ' - ' + (archivo.fecha_subida || 'Fecha desconocida') + '</small>';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="btn-group">';
                    html += '<a href="' + (archivo.ruta || '#') + '" target="_blank" class="btn btn-sm btn-info" title="Descargar"><i class="fas fa-download"></i></a>';
                    html += '<button type="button" class="btn btn-sm btn-danger" onclick="eliminarArchivoExistente(\'' + archivo.nombre + '\')" title="Eliminar"><i class="fas fa-times"></i></button>';
                    html += '</div>';
                    html += '</li>';
                }
            });
            html += '</ul></div>';
            container.html(html);
        }
    }

    // Función para formatear tamaño de archivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Función para remover archivo seleccionado
    function removerArchivoSeleccionado(index) {
        archivosSeleccionados.splice(index, 1);
        actualizarInputArchivos();
        mostrarArchivosSeleccionados();
    }

    // Función para actualizar el input de archivos
    function actualizarInputArchivos() {
        var input = $('#archivos_analitica')[0];
        var dt = new DataTransfer();
        archivosSeleccionados.forEach(function(archivo) {
            dt.items.add(archivo);
        });
        input.files = dt.files;
    }

    // Función para eliminar archivo existente
    function eliminarArchivoExistente(nombreArchivo) {
        if (!nombreArchivo || !analiticaIdActual) {
            alert('Error: datos de archivo incompletos');
            return;
        }
        
        if (!confirm('¿Está seguro de eliminar este archivo?')) return;
        
        $.ajax({
            url: '{{ route("evaluacion_analisis.eliminar_archivo") }}',
            type: 'DELETE',
            data: {
                analitica_id: analiticaIdActual,
                nombre_archivo: nombreArchivo,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Remover de la lista de archivos existentes
                    archivosExistentes = archivosExistentes.filter(function(archivo) {
                        return archivo.nombre !== nombreArchivo;
                    });
                    mostrarArchivosExistentes();
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

    // Event listeners
    $(document).ready(function() {
        // Manejar selección de archivos
        $('#archivos_analitica').on('change', function() {
            var files = Array.from(this.files);
            archivosSeleccionados = files;
            mostrarArchivosSeleccionados();
        });

        // Botón para previsualizar archivos
        $('#btn_previsualizar_archivos').on('click', function() {
            if (archivosSeleccionados.length === 0 && archivosExistentes.length === 0) {
                alert('No hay archivos seleccionados o existentes');
                return;
            }
            
            var modal = $('#modalAgregarAnalitica');
            if (modal.hasClass('show')) {
                // Expandir modal o mostrar en ventana separada
                var ventana = window.open('', '_blank', 'width=600,height=400');
                var html = '<html><head><title>Archivos de Analítica</title></head><body>';
                html += '<h3>Archivos Seleccionados</h3>';
                html += $('#lista_archivos_seleccionados').html();
                html += '<h3>Archivos Existentes</h3>';
                html += $('#lista_archivos_existentes').html();
                html += '</body></html>';
                ventana.document.write(html);
            }
        });
    });
</script>
<script>
    // Verifica si jQuery está cargado
    console.log('jQuery version:', typeof $);
    if (typeof $ === 'undefined') {
        console.warn('jQuery NO está cargado. El script de la modal no funcionará.');
    } else {
        // Delegación de eventos para asegurar que funcione aunque la tabla se recargue
        $(document).on('click', '.btn-agregar-analitica', function(e) {
            e.preventDefault();
            console.log('Click en btn-agregar-analitica');
            var tiendaId = $(this).attr('data-id');
            var tiendaNombre = $(this).attr('data-nombre');
            
            // Limpiar el formulario y configurar para agregar
            $('#formAgregarAnalitica')[0].reset();
            $('#modo_edicion_modal').val('agregar');
            $('#id_registro_modal').val('');
            $('#modalAgregarAnaliticaLabel').text('Agregar Analítica a ' + tiendaNombre);
            
            // Limpiar archivos
            archivosSeleccionados = [];
            archivosExistentes = [];
            analiticaIdActual = null;
            $('#lista_archivos_seleccionados').empty();
            $('#lista_archivos_existentes').empty();
            $('#archivos_analitica').val('');
            
            $('#tienda_id_modal').val(tiendaId);
            $('#nombreTiendaModal').text(tiendaNombre);
            $('#modalAgregarAnalitica').modal('show');
        });

        // Nuevo manejador para editar analíticas
        $(document).on('click', '.btn-editar-analitica', function(e) {
            e.preventDefault();
            var analiticaId = $(this).attr('data-analitica-id');
            var tiendaId = $(this).attr('data-tienda-id');
            var tiendaNombre = $(this).attr('data-tienda-nombre');
            
            // Limpiar archivos y configurar ID actual
            archivosSeleccionados = [];
            archivosExistentes = [];
            analiticaIdActual = analiticaId;
            $('#lista_archivos_seleccionados').empty();
            $('#lista_archivos_existentes').empty();
            $('#archivos_analitica').val('');
            
            // Configurar modal para edición
            $('#modalAgregarAnaliticaLabel').text('Editar Analítica de ' + tiendaNombre);
            $('#modo_edicion_modal').val('editar');
            $('#id_registro_modal').val(analiticaId);
            $('#tienda_id_modal').val(tiendaId);
            $('#nombreTiendaModal').text(tiendaNombre);
            
            // Cargar datos de la analítica
            $.ajax({
                url: '{{ route("evaluacion_analisis.obtener_datos") }}',
                type: 'GET',
                data: { id: analiticaId },
                success: function(data) {
                    console.log('=== DEBUG CARGA DE DATOS ===');
                    console.log('Datos recibidos del servidor:', data);
                    
                    if (!data || !data.success) {
                        alert('Error al cargar los datos: ' + (data && data.message ? data.message : 'error'));
                        return;
                    }
                    var analitica = data.analitica || data.data;
                    console.log('Objeto analitica final:', analitica);
                    
                    // Si la analítica ya está realizada, no permitir edición
                    if (analitica.realizada) {
                        alert('No se puede editar: la analítica ya fue realizada o tiene resultados asociados.');
                        return;
                    }

                    // Llenar el formulario con los datos para edición
                    console.log('=== LLENANDO FORMULARIO ===');
                    $('input[name="fecha_real_analitica"]').val(analitica.fecha_real_analitica);
                    $('input[name="asesor_externo_empresa"]').val(analitica.asesor_externo_empresa);
                    // Tipo analítica y campos condicionales
                    $('select[name="tipo_analitica"]').val(analitica.tipo_analitica);
                    // Rellenar valores específicos de detalle/código/descr para editar
                    // detalle_tipo puede estar en cualquiera de los selects; asignamos ambos y mostramos el correcto
                    if (analitica.detalle_tipo) {
                        $('#detalle_agua').val(analitica.detalle_tipo);
                        $('#detalle_superficie').val(analitica.detalle_tipo);
                    } else {
                        $('#detalle_agua').val('');
                        $('#detalle_superficie').val('');
                    }
                    // Campos de microbiología: intentar varias propiedades que puedan venir según origen
                    var codigoProd = analitica.codigo_producto || analitica.codigo || analitica.product_cod || analitica.codigo_producto || analitica.codigo_producto || '';
                    var descripcionProd = analitica.descripcion_producto || analitica.nombre_producto || analitica.product_description || analitica.nombre || analitica.descripcion || '';
                    $('#codigo_producto_modal').val(codigoProd);
                    $('#descripcion_producto_modal').val(descripcionProd);
                    // Disparar el change del select para que el comportamiento de mostrar/ocultar se aplique
                    $('#tipo_analitica_modal').trigger('change');
                    // Ajustar required del proveedor si corresponde
                    if (analitica.tipo_analitica === 'Tendencias micro') {
                        $('#proveedor_id_select').prop('required', false);
                    } else {
                        $('#proveedor_id_select').prop('required', true);
                    }
                    
                    // Manejar proveedor y checkbox "no procede"
                    if (analitica.proveedor_no_procede == 1) {
                        $('#proveedor_no_procede').prop('checked', true);
                        $('#proveedor_id_select').prop('disabled', true).val('');
                    } else {
                        $('#proveedor_no_procede').prop('checked', false);
                        $('#proveedor_id_select').prop('disabled', false).val(analitica.proveedor_id);
                    }
                    
                    // Manejar periodicidad y checkbox "no procede"
                    if (analitica.periodicidad_no_procede == 1) {
                        $('#periodicidad_no_procede').prop('checked', true);
                        $('#periodicidad_select').prop('disabled', true).val('');
                    } else {
                        $('#periodicidad_no_procede').prop('checked', false);
                        $('#periodicidad_select').prop('disabled', false).val(analitica.periodicidad);
                    }
                    // Disparar cambios para aplicar lógica de habilitado/deshabilitado
                    $('#proveedor_no_procede').trigger('change');
                    $('#periodicidad_no_procede').trigger('change');
                    
                    // Cargar archivos existentes
                    if (analitica.archivos && Array.isArray(analitica.archivos) && analitica.archivos.length > 0) {
                        archivosExistentes = analitica.archivos.filter(function(archivo) {
                            return typeof archivo === 'object' && archivo.nombre && archivo.nombre_original;
                        });
                        mostrarArchivosExistentes();
                    }
                    
                    $('#modalAgregarAnalitica').modal('show');
                },
                error: function() {
                    alert('Error al cargar los datos de la analítica');
                }
            });
        });

        // Manejar checkboxes "No procede" para proveedor
        $(document).on('change', '#proveedor_no_procede', function() {
            if ($(this).is(':checked')) {
                $('#proveedor_id_select').prop('disabled', true).val('');
            } else {
                $('#proveedor_id_select').prop('disabled', false);
            }
        });

        // Manejar checkboxes "No procede" para periodicidad
        $(document).on('change', '#periodicidad_no_procede', function() {
            if ($(this).is(':checked')) {
                $('#periodicidad_select').prop('disabled', true).val('').prop('required', false);
            } else {
                $('#periodicidad_select').prop('disabled', false).prop('required', true);
            }
        });

        // Antes de enviar, asegurar que detalle_tipo se envíe correctamente
        $('#formAgregarAnalitica').on('submit', function(e){
            // Si el grupo agua está visible, tomar su valor
            var detalle = '';
            if (!$('#detalle_agua_group').hasClass('d-none')) {
                detalle = $('#detalle_agua').val() || '';
            } else if (!$('#detalle_superficie_group').hasClass('d-none')) {
                detalle = $('#detalle_superficie').val() || '';
            }
            $('#detalle_tipo_hidden').val(detalle);

            // Algunos campos pueden estar disabled; re-enable them so browsers submit their values
            $(this).find(':disabled').prop('disabled', false);
            return true; // continuar con el submit
        });
    }
</script>

@extends('layouts.app')
<!-- Modal Agregar Analítica  -->
<div class="modal fade" id="modalAgregarAnalitica" tabindex="-1" role="dialog"
    aria-labelledby="modalAgregarAnaliticaLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formAgregarAnalitica" method="POST" action="{{ route('evaluacion_analisis.guardar_analitica') }}">
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
                    <th class="text-center">Analíticas Existentes</th>
                    <th class="text-center">Estado Analítica</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tiendas as $tienda)
                    <tr>
                        <td class="text-center">{{ $tienda->num_tienda }}</td>
                        <td class="text-center">{{ $tienda->nombre_tienda }}</td>
                        <td class="text-center">{{ $tienda->direccion_tienda }}</td>
                        <td class="text-center">{{ $tienda->responsable }}</td>
                        <td class="text-center">
                            <a class="m-2 btn btn-primary btn-agregar-analitica" href="#" data-toggle="modal"
                                data-target="#modalAgregarAnalitica" data-id="{{ $tienda->num_tienda }}"
                                data-nombre="{{ $tienda->nombre_tienda }}">
                                <i class="metismenu-icon fa fa-plus mr-2"></i>Agregar Analítica
                            </a>
                        </td>
                        <td class="text-center text-right" style="min-width: 260px;">
                            {{-- Analíticas existentes: compactas, mostrar 2 y colapsar el resto --}}
                            @if($tienda->analiticas && $tienda->analiticas->count() > 0)
                                @php $totalAnal = $tienda->analiticas->count(); @endphp
                                @foreach($tienda->analiticas->take(2) as $analitica)
                                    <div class="d-flex justify-content-end align-items-center mb-1">
                                        <div class="mr-2 text-right" style="min-width:160px;">
                                            <strong style="display:block">{{ $tipoDisplay[$analitica->tipo_analitica] ?? $analitica->tipo_analitica }}</strong>
                                            <small style="display:block">{{ $analitica->fecha_real_analitica }}</small>
                                            @if(!empty($analitica->periodicidad_no_procede) && $analitica->periodicidad_no_procede == 1)
                                                <small style="display:block"><span class="badge badge-secondary">No procede</span></small>
                                            @else
                                                <small style="display:block">{{ $analitica->periodicidad ?: '-' }}</small>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            @php
                                                // Mostrar estado basado en campo estado_analitica
                                                $estado = $analitica->estado_analitica ?? 'sin_iniciar';
                                                $fechaCambio = null;
                                                if(!empty($analitica->fecha_cambio_estado)){
                                                    try { $fechaCambio = \Carbon\Carbon::parse($analitica->fecha_cambio_estado)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                                } elseif(!empty($analitica->fecha_realizacion)){
                                                    try { $fechaCambio = \Carbon\Carbon::parse($analitica->fecha_realizacion)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                                }
                                                $badgeClass = $estado === 'realizada' ? 'badge-success' : ($estado === 'pendiente' ? 'badge-warning' : 'badge-secondary');
                                                $label = $estado === 'realizada' ? 'Realizada' . ($fechaCambio ? ' el ' . $fechaCambio : '') : ($estado === 'pendiente' ? 'Pendiente' : 'Sin iniciar');
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                                            <button class="btn btn-sm btn-warning btn-editar-analitica ml-2" 
                                                data-analitica-id="{{ $analitica->id }}"
                                                data-tienda-id="{{ $tienda->num_tienda }}"
                                                data-tienda-nombre="{{ $tienda->nombre_tienda }}">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-borrar-analitica ml-2" 
                                                data-analitica-id="{{ $analitica->id }}"
                                                data-tienda-id="{{ $tienda->num_tienda }}">
                                                Borrar
                                            </button>
                                        </div>
                                    </div>
                                @endforeach

                                @if($totalAnal > 2)
                                    <div class="collapse mt-2" id="collapseAnaliticas{{ $tienda->num_tienda }}">
                                        @foreach($tienda->analiticas->slice(2) as $analitica)
                                            <div class="d-flex justify-content-end align-items-center mb-1">
                                                <div class="mr-2 text-right" style="min-width:160px;">
                                                    <strong style="display:block">{{ $tipoDisplay[$analitica->tipo_analitica] ?? $analitica->tipo_analitica }}</strong>
                                                    <small style="display:block">{{ $analitica->fecha_real_analitica }}</small>
                                                    @if(!empty($analitica->periodicidad_no_procede) && $analitica->periodicidad_no_procede == 1)
                                                        <small style="display:block"><span class="badge badge-secondary">No procede</span></small>
                                                    @else
                                                        <small style="display:block">{{ $analitica->periodicidad ?: '-' }}</small>
                                                    @endif
                                                </div>
                                                <div class="text-right">
                                                    @php
                                                        // Mostrar estado basado en campo estado_analitica
                                                        $estado = $analitica->estado_analitica ?? 'sin_iniciar';
                                                        $fechaCambio = null;
                                                        if(!empty($analitica->fecha_cambio_estado)){
                                                            try { $fechaCambio = \Carbon\Carbon::parse($analitica->fecha_cambio_estado)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                                        } elseif(!empty($analitica->fecha_realizacion)){
                                                            try { $fechaCambio = \Carbon\Carbon::parse($analitica->fecha_realizacion)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                                        }
                                                        $badgeClass = $estado === 'realizada' ? 'badge-success' : ($estado === 'pendiente' ? 'badge-warning' : 'badge-secondary');
                                                        $label = $estado === 'realizada' ? 'Realizada' . ($fechaCambio ? ' el ' . $fechaCambio : '') : ($estado === 'pendiente' ? 'Pendiente' : 'Sin iniciar');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                                                    <button class="btn btn-sm btn-warning btn-editar-analitica ml-2" 
                                                        data-analitica-id="{{ $analitica->id }}"
                                                        data-tienda-id="{{ $tienda->num_tienda }}"
                                                        data-tienda-nombre="{{ $tienda->nombre_tienda }}">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger btn-borrar-analitica ml-2" 
                                                        data-analitica-id="{{ $analitica->id }}"
                                                        data-tienda-id="{{ $tienda->num_tienda }}">
                                                        Borrar
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <a class="btn btn-sm btn-link toggle-analiticas" href="#" data-target="#collapseAnaliticas{{ $tienda->num_tienda }}" data-more="Mostrar {{ $totalAnal - 2 }} más" data-less="Mostrar menos" role="button" aria-expanded="false">Mostrar {{ $totalAnal - 2 }} más</a>
                                @endif
                            @else
                                <span class="text-muted">Sin analíticas</span>
                            @endif
                        </td>
                        <td class="text-right align-middle" style="min-width: 220px;">
                            {{-- Mostrar estado por cada analítica, compactado (2 visibles + colapsable) --}}
                            @if($tienda->analiticas && $tienda->analiticas->count() > 0)
                                @php $totalAnalEstado = $tienda->analiticas->count(); @endphp
                                @foreach($tienda->analiticas->take(2) as $anal)
                                    <div class="d-flex justify-content-end align-items-center mb-1">
                                        <div class="mr-2 text-right" style="min-width:140px;">
                                            <small style="display:block">{{ $tipoDisplay[$anal->tipo_analitica] ?? $anal->tipo_analitica }}</small>
                                            <small style="display:block">{{ $anal->fecha_real_analitica }}</small>
                                        </div>
                                        <div class="text-right">
                                            @php
                                                // Mostrar estado basado en campo estado_analitica
                                                $estado = $anal->estado_analitica ?? 'sin_iniciar';
                                                $fechaCambio = null;
                                                if(!empty($anal->fecha_cambio_estado)){
                                                    try { $fechaCambio = \Carbon\Carbon::parse($anal->fecha_cambio_estado)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                                } elseif(!empty($anal->fecha_realizacion)){
                                                    try { $fechaCambio = \Carbon\Carbon::parse($anal->fecha_realizacion)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                                }
                                                $badgeClass = $estado === 'realizada' ? 'badge-success' : ($estado === 'pendiente' ? 'badge-warning' : 'badge-secondary');
                                                $label = $estado === 'realizada' ? 'Realizada' . ($fechaCambio ? ' el ' . $fechaCambio : '') : ($estado === 'pendiente' ? 'Pendiente' : 'Sin iniciar');
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                                        </div>
                                    </div>
                                @endforeach

                                @if($totalAnalEstado > 2)
                                    <div class="collapse mt-2" id="collapseEstadoAnaliticas{{ $tienda->num_tienda }}">
                                        @foreach($tienda->analiticas->slice(2) as $anal)
                                            <div class="d-flex justify-content-end align-items-center mb-1">
                                                <div class="mr-2 text-right" style="min-width:140px;">
                                                    <small style="display:block">{{ $tipoDisplay[$anal->tipo_analitica] ?? $anal->tipo_analitica }}</small>
                                                    <small style="display:block">{{ $anal->fecha_real_analitica }}</small>
                                                </div>
                                                <div class="text-right">
                                                    @php
                                                        // Mostrar estado basado en campo estado_analitica
                                                        $estado = $anal->estado_analitica ?? 'sin_iniciar';
                                                        $fechaCambio = null;
                                                        if(!empty($anal->fecha_cambio_estado)){
                                                            try { $fechaCambio = \Carbon\Carbon::parse($anal->fecha_cambio_estado)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                                        } elseif(!empty($anal->fecha_realizacion)){
                                                            try { $fechaCambio = \Carbon\Carbon::parse($anal->fecha_realizacion)->format('d/m/Y'); } catch(\Exception $e) { $fechaCambio = null; }
                                                        }
                                                        $badgeClass = $estado === 'realizada' ? 'badge-success' : ($estado === 'pendiente' ? 'badge-warning' : 'badge-secondary');
                                                        $label = $estado === 'realizada' ? 'Realizada' . ($fechaCambio ? ' el ' . $fechaCambio : '') : ($estado === 'pendiente' ? 'Pendiente' : 'Sin iniciar');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <a class="btn btn-sm btn-link toggle-analiticas" href="#" data-target="#collapseEstadoAnaliticas{{ $tienda->num_tienda }}" data-more="Mostrar {{ $totalAnalEstado - 2 }} más" data-less="Mostrar menos" role="button" aria-expanded="false">Mostrar {{ $totalAnalEstado - 2 }} más</a>
                                @endif
                            @else
                                <span class="text-muted">Sin analíticas</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- Paginación si usas ->paginate() en el controlador --}}

    </div>
@endsection
<!-- jQuery y Bootstrap JS solo para esta vista -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

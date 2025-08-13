@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#table_tiendas').DataTable({
            pageLength: 25,
            lengthMenu: [ [25, 50, 100, -1], [25, 50, 100, 'Todos'] ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [],
        });

        function renderEditAction(id) {
            return '<a href="#" class="btn btn-primary open-modal" data-url="' + '{{ url('tiendas') }}/' + id + '/edit' + '"><i class="metismenu-icon fa fa-pencil"></i></a>';
        }
        function renderDeleteAction(id) {
            return '<form action="{{ route('tiendas.delete') }}" method="POST" style="display:inline-block; margin-left:5px;" onsubmit="return confirm(\'¿Estás seguro de que deseas eliminar esta tienda?\');">@csrf<input type="hidden" name="id" value="' + id + '"><button type="submit" class="btn btn-danger mt-2"><i class="fa fa-trash"></i></button></form>';
        }

        $('#btnBuscarTiendas').on('click', function() {
            var num = $('#filtro_num_tienda').val();
            var nombre = $('#filtro_nombre_tienda').val();
            var responsable = $('#filtro_responsable').val();
            $.ajax({
                url: '{{ route('tiendas.buscar') }}',
                method: 'GET',
                data: {
                    num_tienda: num,
                    nombre_tienda: nombre,
                    responsable: responsable
                },
                success: function(response) {
                    table.clear();
                    response.data.forEach(function(t) {
                        table.row.add([
                            t.num_tienda,
                            t.nombre_tienda,
                            t.direccion_tienda,
                            t.responsable,
                            t.email_responsable,
                            t.telefono,
                            renderEditAction(t.id),
                            renderDeleteAction(t.id)
                        ]);
                    });
                    table.draw();
                    // Reasignar eventos a los nuevos botones de editar
                    $('.open-modal').off('click').on('click', function() {
                        var url = $(this).data('url');
                        $.get(url, function(data) {
                            $('#tienda_id_edit').val(data.id);
                            $('#num_tienda_edit').val(data.num_tienda);
                            $('#nombre_tienda_edit').val(data.nombre_tienda);
                            $('#direccion_tienda_edit').val(data.direccion_tienda);
                            $('#responsable_edit').val(data.responsable);
                            $('#email_responsable_edit').val(data.email_responsable);
                            $('#telefono_edit').val(data.telefono);
                            $('#editTiendaModal').modal('show');
                        });
                    });
                },
                error: function() {
                    alert('Error al buscar tiendas.');
                }
            });
        });
        // Limpiar filtro y recargar todos
        $('#btnLimpiarFiltro').on('click', function() {
            $('#filtro_num_tienda').val('');
            $('#filtro_nombre_tienda').val('');
            $('#filtro_responsable').val('');
            $('#btnBuscarTiendas').click();
        });
    });
</script>
@endpush
@extends('layouts.app')

@section('app_name', config('app.name'))

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-building icon-gradient bg-secondary"></i>
            </div>
            <div>Tiendas
                <div class="page-title-subheading">
                    Lista de Tiendas
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <a class="m-2 btn btn-primary" href="#" data-toggle="modal" data-target="#createTiendaModal">
                <i class="metismenu-icon fa fa-plus mr-2"></i>Crear Tienda
            </a>
        </div>
    </div>
@endsection
<br><br><br><br><br>
{{-- Mensajes de éxito y error --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@section('main_content')
    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        <!-- Filtros personalizados -->
         <form id="filtroTiendasForm" class="form-inline mb-3" method="GET" action="{{ route('tiendas.index') }}">
            <input type="text" class="form-control mr-2 mb-2" name="num_tienda" value="{{ request('num_tienda') }}" placeholder="Num Tienda">
            <input type="text" class="form-control mr-2 mb-2" name="nombre_tienda" value="{{ request('nombre_tienda') }}" placeholder="Nombre Tienda">
            <input type="text" class="form-control mr-2 mb-2" name="responsable" value="{{ request('responsable') }}" placeholder="Responsable">
            <button type="submit" class="btn btn-info mb-2"><i class="fa fa-search"></i> Buscar</button>
            <a href="{{ route('tiendas.index') }}" class="btn btn-secondary mb-2 ml-2"><i class="fa fa-eraser"></i> Limpiar</a>
        </form>
        <table id="table_tiendas"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Num Tienda</th>
                    <th class="text-center">Nombre Tienda</th>
                    <th class="text-center">Dirección</th>
                    <th class="text-center">Responsable</th>
                    <th class="text-center">Email Responsable</th>
                    <th class="text-center">Teléfono</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Eliminar</th>
                </tr>
                <!-- DataTables search bar will be used instead of manual filters -->
            </thead>
            <tbody>
                @foreach ($tiendas as $tienda)
                    <tr>
                        <td class="text-center">{{ $tienda->num_tienda }}</td>
                        <td class="text-center">{{ $tienda->nombre_tienda }}</td>
                        <td class="text-center">{{ $tienda->direccion_tienda }}</td>
                        <td class="text-center">{{ $tienda->responsable }}</td>
                        <td class="text-center">{{ $tienda->email_responsable }}</td>
                        <td class="text-center">{{ $tienda->telefono }}</td>
                        <td class="text-center">
                            <a href="#" class="btn btn-primary open-modal" data-url="{{ url('tiendas/' . $tienda->id . '/edit') }}">
                                <i class="metismenu-icon fa fa-pencil"></i>
                            </a>
                        </td>
                        <td class="text-center d-flex justify-content-center">
                            <form action="{{ route('tiendas.delete') }}" method="POST" style="display:inline-block;"
                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta tienda?');">
                                @csrf
                                <input type="hidden" name="id" value="{{ $tienda->id }}">
                                <button type="submit" class="btn btn-danger mt-2">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

<!-- Modal Crear Tienda -->
<div class="modal fade" id="createTiendaModal" tabindex="-1" role="dialog" aria-labelledby="createTiendaModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('tiendas.store') }}">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="createTiendaModalLabel">Crear Tienda</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="num_tienda">Num Tienda</label>
                        <input type="text" class="form-control" id="num_tienda" name="num_tienda" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre_tienda">Nombre Tienda</label>
                        <input type="text" class="form-control" id="nombre_tienda" name="nombre_tienda" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion_tienda">Dirección Tienda</label>
                        <input type="text" class="form-control" id="direccion_tienda" name="direccion_tienda" required>
                    </div>
                    <div class="form-group">
                        <label for="responsable">Responsable</label>
                        <input type="text" class="form-control" id="responsable" name="responsable" required>
                    </div>
                    <div class="form-group">
                        <label for="email_responsable">Email Responsable</label>
                        <input type="email" class="form-control" id="email_responsable" name="email_responsable" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Crear</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Edición Tienda -->
<div class="modal fade" id="editTiendaModal" tabindex="-1" role="dialog" aria-labelledby="editTiendaModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="editTiendaModalLabel">Editar Tienda</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editTiendaForm" method="POST" action="{{ route('tiendas.update') }}">
                    @csrf
                    <input type="hidden" id="tienda_id_edit" name="id">
                    <div class="form-group">
                        <label for="num_tienda_edit">Num Tienda</label>
                        <input type="text" class="form-control" id="num_tienda_edit" name="num_tienda">
                    </div>
                    <div class="form-group">
                        <label for="nombre_tienda_edit">Nombre Tienda</label>
                        <input type="text" class="form-control" id="nombre_tienda_edit" name="nombre_tienda">
                    </div>
                    <div class="form-group">
                        <label for="direccion_tienda_edit">Dirección Tienda</label>
                        <input type="text" class="form-control" id="direccion_tienda_edit" name="direccion_tienda">
                    </div>
                    <div class="form-group">
                        <label for="responsable_edit">Responsable</label>
                        <input type="text" class="form-control" id="responsable_edit" name="responsable">
                    </div>
                    <div class="form-group">
                        <label for="email_responsable_edit">Email Responsable</label>
                        <input type="email" class="form-control" id="email_responsable_edit" name="email_responsable">
                    </div>
                    <div class="form-group">
                        <label for="telefono_edit">Teléfono</label>
                        <input type="text" class="form-control" id="telefono_edit" name="telefono">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@section('custom_footer')
<script>
    // Mostrar nombre del archivo seleccionado
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    // Resetear modal de importación
    function resetModal() {
        $('#formContent').show();
        $('#loadingContent').hide();
        $('#submitBtn').prop('disabled', false).html('<i class="fa fa-upload mr-2"></i>Importar Archivo');
        $('#cancelBtn').prop('disabled', false).text('Cancelar');
        $('#importForm')[0].reset();
        $('.custom-file-label').html('Elegir archivo...');
        $('#importarArchivoModal').removeData('processing');
        var modalConfig = $('#importarArchivoModal').data('bs.modal');
        if (modalConfig && modalConfig._config) {
            modalConfig._config.backdrop = true;
            modalConfig._config.keyboard = true;
        }
    }
    $('#importarArchivoModal').on('show.bs.modal', function () {
        resetModal();
    });
    // Loader durante importación
    $('#importForm').on('submit', function(e) {
        if (!$('#archivo').val()) {
            e.preventDefault();
            alert('Por favor selecciona un archivo antes de continuar.');
            return false;
        }
        $('#formContent').hide();
        $('#loadingContent').show();
        $('#submitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Procesando...');
        $('#cancelBtn').prop('disabled', true).text('Procesando...');
        $('#importarArchivoModal').data('processing', true);
        var modalConfig = $('#importarArchivoModal').data('bs.modal');
        if (modalConfig) {
            modalConfig._config.backdrop = 'static';
            modalConfig._config.keyboard = false;
        }
        return true;
    });
    $('#importarArchivoModal').on('hidden.bs.modal', function () {
        resetModal();
    });
    $('#cancelBtn').on('click', function() {
        var isProcessing = $('#importarArchivoModal').data('processing');
        if (isProcessing) {
            return false;
        }
        $('#importarArchivoModal').modal('hide');
    });
    $('#importarArchivoModal').on('hide.bs.modal', function(e) {
        var isProcessing = $('#importarArchivoModal').data('processing');
        if (isProcessing) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        return true;
    });
    // Cargar datos en modal de edición
    $('.open-modal').on('click', function() {
        var url = $(this).data('url');
        $.get(url, function(data) {
            $('#tienda_id_edit').val(data.id);
            $('#num_tienda_edit').val(data.num_tienda);
            $('#nombre_tienda_edit').val(data.nombre_tienda);
            $('#direccion_tienda_edit').val(data.direccion_tienda);
            $('#responsable_edit').val(data.responsable);
            $('#email_responsable_edit').val(data.email_responsable);
            $('#telefono_edit').val(data.telefono);
            $('#editTiendaModal').modal('show');
        });
    });
</script>
@endsection

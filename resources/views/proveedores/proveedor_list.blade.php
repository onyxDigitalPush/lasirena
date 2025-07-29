@extends('layouts.app')

@section('app_name', config('app.name'))


@section('custom_head')

@endsection

@section('title_content')

    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-users icon-gradient bg-secondary"></i>
            </div>
            <div>Proveedores
                <div class="page-title-subheading">
                    Lista de Proveedores
                </div>
            </div>        </div>

        <div class="page-title-actions text-white">
            <input type="hidden" value="0" name="tab_orders" id="tab_orders">

            <a class="m-2 btn btn-success" href="#" data-toggle="modal" data-target="#importarArchivoModal">
                <i class="metismenu-icon fa fa-upload mr-2"></i>Importar Archivo
            </a>

            <a class="m-2 btn btn-primary" href="#" data-toggle="modal" data-target="#createUserModal">
                <i class="metismenu-icon fa fa-user mr-2"></i>Crear Proveedor
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
        <table id="table_proveedores"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Codigo Proveedor</th>
                    <th class="text-center">Nombre Proveedor</th>
                    <th class="text-center">Ver Articulos</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Eliminar</th>
                </tr>
                <tr>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Código" /></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Nombre" /></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>


            <tbody>
                @foreach ($array_proveedores as $proveedor)
                    <tr>
                        <td class="text-center">{{ $proveedor->id_proveedor }}</td>
                        <td class="text-center">{{ $proveedor->nombre_proveedor }}</td>
                        <td class="text-center">
                            <a href="{{ url('material/' . (int) $proveedor->id_proveedor . '/list') }}"
                                class="btn btn-primary">
                                <i class="metismenu-icon fa fa-eye"></i>
                            </a>

                        </td>
                        <td class="text-center">
                            <a href="#" class="btn btn-primary open-modal"
                                data-url="{{ url('proveedor/' . $proveedor->id_proveedor . '/edit') }}">
                                <i class="metismenu-icon fa fa-pencil"></i>
                            </a>
                        </td>
                        <td class="text-center d-flex justify-content-center">
                            <form action="{{ route('proveedores.delete') }}" method="POST" style="display:inline-block;"
                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este proveedor?');">
                                @csrf
                                 <input type="hidden" name="id" value="{{ $proveedor->id_proveedor }}">
                                <button type="submit" class="btn btn-danger mt-2">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>    @endsection    <!-- Modal Importar Archivo -->
    <div class="modal fade" id="importarArchivoModal" tabindex="-1" role="dialog" aria-labelledby="importarArchivoModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('importar.archivo') }}" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="importarArchivoModalLabel">
                            <i class="fa fa-upload mr-2"></i>Importar Archivo CSV/XLSX
                        </h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <!-- Contenido normal del formulario -->
                        <div id="formContent">
                            <div class="form-group">
                                <label for="archivo">Seleccionar Archivo</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="archivo" name="archivo" 
                                           accept=".csv,.txt,.xlsx" required>
                                    <label class="custom-file-label" for="archivo">Elegir archivo...</label>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> 
                                    Formatos soportados: CSV, TXT, XLSX
                                    <br>
                                    • Para CSV: Las cabeceras deben estar en la fila 4
                                    <br>
                                    • Para XLSX: Las cabeceras deben estar en la fila 4
                                </small>
                            </div>
                        </div>

                        <!-- Loader (oculto por defecto) -->
                        <div id="loadingContent" style="display: none;">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <h5 class="mt-3">Procesando archivo...</h5>
                                <p class="text-muted">Por favor espere, esto puede tomar varios minutos dependiendo del tamaño del archivo.</p>
                                <div class="progress mt-3">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" style="width: 100%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer" id="modalFooter">
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="fa fa-upload mr-2"></i>Importar Archivo
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelBtn">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Crear Usuario -->
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('proveedores.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="createUserModalLabel">Crear Proveedor</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="codigo_proveedor">Codigo Proveedor</label>
                            <input type="number" class="form-control" id="id_proveedor" name="id_proveedor" required>
                        </div>

                        <div class="form-group">
                            <label for="nombre_proveedor">Nombre Proveedor</label>
                            <input type="text" class="form-control" id="nombre_proveedor" name="nombre_proveedor"
                                required>
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
    <!-- Modal Edicion -->
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="userModalLabel">Editar Proveedor</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Aquí se cargarán los datos del usuario con AJAX -->
                    <form id="editUserForm" method="POST" action="{{ route('proveedores.update') }}">
                        @csrf
                        <div class="form-group">
                            <label for="codigo_proveedor">Codigo Proveedor</label>
                            <input type="number" class="form-control" id="codigo_proveedor_edit"
                                name="id_proveedor">
                        </div>
                        <input type="hidden" id="codigo_proveedor_old" name="codigo_proveedor_old">
                        <div class="form-group">
                            <label for="nombre_proveedor_edit">Nombre Proveedor</label>
                            <input type="text" class="form-control" id="nombre_proveedor_edit"
                                name="nombre_proveedor_edit">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar
                            Cambios</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>    @section('custom_footer')        <script>
            // Script para mostrar el nombre del archivo seleccionado
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });

            // Función para resetear completamente el modal
            function resetModal() {
                $('#formContent').show();
                $('#loadingContent').hide();
                $('#submitBtn').prop('disabled', false).html('<i class="fa fa-upload mr-2"></i>Importar Archivo');
                $('#cancelBtn').prop('disabled', false).text('Cancelar');
                $('#importForm')[0].reset();
                $('.custom-file-label').html('Elegir archivo...');
                $('#importarArchivoModal').removeData('processing');
                
                // Restaurar configuración del modal
                var modalConfig = $('#importarArchivoModal').data('bs.modal');
                if (modalConfig && modalConfig._config) {
                    modalConfig._config.backdrop = true;
                    modalConfig._config.keyboard = true;
                }
            }

            // Resetear modal al abrirlo (para casos donde pueda quedar en estado inconsistente)
            $('#importarArchivoModal').on('show.bs.modal', function () {
                resetModal();
            });// Script para manejar el loader durante la importación
            $('#importForm').on('submit', function(e) {
                // Verificar que se haya seleccionado un archivo
                if (!$('#archivo').val()) {
                    e.preventDefault();
                    alert('Por favor selecciona un archivo antes de continuar.');
                    return false;
                }

                // Mostrar el loader y ocultar el formulario
                $('#formContent').hide();
                $('#loadingContent').show();
                
                // Deshabilitar botones y cambiar textos
                $('#submitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Procesando...');
                $('#cancelBtn').prop('disabled', true).text('Procesando...');
                
                // Marcar que está procesando para prevenir cierre del modal
                $('#importarArchivoModal').data('processing', true);
                
                // Prevenir que se cierre el modal durante el procesamiento
                var modalConfig = $('#importarArchivoModal').data('bs.modal');
                if (modalConfig) {
                    modalConfig._config.backdrop = 'static';
                    modalConfig._config.keyboard = false;
                }

                // El formulario se enviará normalmente
                return true;
            });            // Resetear el modal cuando se cierre
            $('#importarArchivoModal').on('hidden.bs.modal', function () {
                resetModal();
            });// Manejar clic en el botón cancelar
            $('#cancelBtn').on('click', function() {
                var isProcessing = $('#importarArchivoModal').data('processing');
                if (isProcessing) {
                    // Si está procesando, no hacer nada (el botón ya está deshabilitado)
                    return false;
                }
                // Si no está procesando, cerrar modal normalmente
                $('#importarArchivoModal').modal('hide');
            });

            // Prevenir el cierre del modal durante el procesamiento
            $('#importarArchivoModal').on('hide.bs.modal', function(e) {
                var isProcessing = $('#importarArchivoModal').data('processing');
                if (isProcessing) {
                    // Si está procesando, prevenir el cierre
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                // Si no está procesando, permitir el cierre
                return true;
            });
        </script>

        <script type="text/javascript"
            src="{{ URL::asset('' . DIR_JS . '/main_app/proveedor_list.js') }}?v={{ config('app.version') }}"></script>
    @endsection

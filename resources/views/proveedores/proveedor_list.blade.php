@extends('layouts.app')

@section('app_name', config('app.name'))


@section('custom_head')
<style>
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
    
    /* 🔥 MODAL HISTORIAL FIJO - TAMAÑO VIEWPORT */
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
    
    /* 🔥 CONTENEDOR DE TABLA CON ALTURA FIJA */
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
    
    /* 🔥 TABLA CON SCROLL HORIZONTAL Y COLUMNAS FLEXIBLES */
    #historial_emails_table {
        width: 100% !important;
        table-layout: auto !important; /* 🔥 CAMBIADO DE fixed A auto */
        min-width: 1400px !important; /* 🔥 MÁS ANCHO PARA MÁS ARCHIVOS */
    }
    
    /* 🔥 ANCHO MÍNIMO PARA COLUMNAS (NO FIJO) */
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
    #historial_emails_table td:nth-child(7) { min-width: 200px !important; }  /* 🔥 MÁS ANCHO PARA ARCHIVOS */
    #historial_emails_table th:nth-child(8),
    #historial_emails_table td:nth-child(8) { min-width: 140px !important; }  /* Fecha */
    
    /* 🔥 TEXTO QUE SE PUEDE EXPANDIR (SOLO PARA CELDAS NORMALES) */
    #historial_emails_table td:not(.archivos-cell) {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        vertical-align: middle !important;
    }
    
    /* 🔥 CELDA DE ARCHIVOS CON WRAP PARA MOSTRAR TODOS */
    #historial_emails_table td.archivos-cell {
        white-space: normal !important; /* 🔥 PERMITE SALTO DE LÍNEA */
        overflow: visible !important;
        text-overflow: initial !important;
        vertical-align: top !important;
        padding: 8px !important;
    }
    
    /* 🔥 BOTONES DE ARCHIVO MÁS PEQUEÑOS */
    .btn-archivo-download {
        font-size: 10px !important;
        padding: 2px 6px !important;
        margin: 2px !important;
        border-radius: 3px !important;
        display: inline-block !important;
    }
    
    /* 🔥 PREVIEW MENSAJE FIJO */
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
            <i class="metismenu-icon fa fa-users icon-gradient bg-secondary"></i>
        </div>
        <div>Proveedores
            <div class="page-title-subheading">
                Lista de Proveedores
            </div>
        </div>
    </div>

    <div class="page-title-actions text-white">
        <input type="hidden" value="0" name="tab_orders" id="tab_orders">

        <a class="m-2 btn btn-warning" href="#" data-toggle="modal" data-target="#importarArchivoModal" data-import-type="sin_fconversion">
            <i class="metismenu-icon fa fa-upload mr-2"></i>Importar Prov. Art. sin Fconversion
        </a>
        <a class="m-2 btn btn-success" href="#" data-toggle="modal" data-target="#importarArchivoModal" data-import-type="general">
            <i class="metismenu-icon fa fa-upload mr-2"></i>Importar Proveedores y Articulos
        </a>
        <a class="m-2 btn btn-info" href="#" data-toggle="modal" data-target="#importarArchivoModal" data-import-type="proveedores">
            <i class="metismenu-icon fa fa-upload mr-2"></i>Importar Proveedores
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
                <th class="text-center">Email Proveedor</th>
                <th class="text-center">Ver Historial Emails</th>
                <th class="text-center">Ver Articulos</th>
                <th class="text-center">Editar</th>
                <th class="text-center">Eliminar</th>
            </tr>
            <tr>
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Código" /></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Nombre" /></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Email" /></th>
                <th></th>
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
                <td class="text-center">{{ $proveedor->email_proveedor }}</td>
                <td class="text-center">
                    <button type="button"
                        class="btn btn-info btn-sm open-history"
                        data-id="{{ $proveedor->id_proveedor }}"
                        data-nombre="{{ $proveedor->nombre_proveedor }}">
                        <i class="fa fa-envelope"></i>
                    </button>
                </td>
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
    </table> @endsection <!-- Modal Importar Archivo -->
    <div class="modal fade" id="importarArchivoModal" tabindex="-1" role="dialog" aria-labelledby="importarArchivoModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('importar.archivo') }}" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <input type="hidden" name="import_type" id="import_type_input" value="general">
                    <div class="modal-header">
                        <h4 class="modal-title" id="importarArchivoModalLabel">
                            <i class="fa fa-upload mr-2"></i>Importar Archivo CSV/XLSX
                        </h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <!-- Contenido normal del formulario -->
                        <div id="formContent">
                            <div class="mb-3" id="download_format_general" style="display: none;">
                                <a href="{{ route('proveedores.descargar_formato_proveedores_entradas') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-download mr-1"></i>Descargar Formato de Ejemplo (.xlsx)
                                </a>
                                <small class="form-text text-muted d-inline-block ml-2">
                                    Cabeceras en la fila 4; los datos (filas) deben comenzar abajo (fila 5 en general). Para importación de proveedores (botón "Importar Proveedores"), el import utiliza la fila 10 como inicio de datos.
                                </small>
                            </div>
                            <div class="mb-3" id="download_format_sin_fconversion" style="display: none;">
                                <a href="{{ route('proveedores.descargar_formato_sin_fconversion') }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fa fa-download mr-1"></i>Descargar Formato de Ejemplo (.xlsx)
                                </a>
                                <small class="form-text text-muted d-inline-block ml-2">
                                    Formato sin factor de conversión. Cabeceras en la fila 1; los datos desde la fila 2.
                                    Columnas: ML, Material, Jerarquía product., Descripción de material, Proveedor, Nombre del proveedor, Ce., Mes (formato: 9.2025), Ctd. EM-DEV, UMB, Valor EM-DEV
                                </small>
                            </div>
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
                                    • Para PROVEEDORES: Las cabeceras deben estar en la fila 10
                                    <br>
                                    • Para PROVEEDORES Y ARTICULOS: Las cabeceras deben estar en la fila 4
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

                        <div class="form-group">
                            <label for="familia">Familia</label>
                            <select class="form-control" id="familia" name="familia">
                                <option value="">Seleccionar...</option>
                                <option value="ELABORADOS">ELABORADOS</option>
                                <option value="PRODUCTOS DEL MAR">PRODUCTOS DEL MAR</option>
                                <option value="CONSUMIBLES">CONSUMIBLES</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subfamilia">Subfamilia</label>
                            <select class="form-control" id="subfamilia" name="subfamilia">
                                <option value="">Seleccionar...</option>
                                <option value="Ambient">Ambient</option>
                                <option value="Carne">Carne</option>
                                <option value="Consumible">Consumible</option>
                                <option value="Helados y Postres">Helados y Postres</option>
                                <option value="Marisco">Marisco</option>
                                <option value="Pescado">Pescado</option>
                                <option value="Pescado y Marisco">Pescado y Marisco</option>
                                <option value="Plato preparado">Plato preparado</option>
                                <option value="postres">postres</option>
                                <option value="precocinados">precocinados</option>
                                <option value="precocinado y plato preparados">precocinado y plato preparados</option>
                                <option value="Repostería">Repostería</option>
                                <option value="servicios">servicios</option>
                                <option value="Verdura">Verdura</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email_proveedor">Email Proveedor</label>
                            <input type="email" class="form-control" id="email_proveedor" name="email_proveedor"
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
                        <div class="form-group">
                            <label for="email_proveedor_edit">Email Proveedor</label>
                            <input type="email" class="form-control" id="email_proveedor_edit"
                                name="email_proveedor_edit" placeholder="ejemplo@correo.com">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar
                            Cambios</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Historial Emails -->
    <div class="modal fade" id="historialEmailsModal" tabindex="-1" role="dialog" aria-labelledby="historialEmailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="historialEmailsModalLabel">
                        <i class="fa fa-envelope mr-2"></i>Historial de Emails del Proveedor
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Info del proveedor -->
                    <div class="mb-3" style="flex-shrink: 0;">
                        <strong>Proveedor:</strong> <span id="hist_proveedor_nombre" class="badge badge-info"></span>
                    </div>

                    <!-- 🔥 CONTENEDOR TABLA CON ALTURA FIJA -->
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

    @section('custom_footer') <script>
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
            // Ocultar ambos botones de descarga por defecto
            $('#download_format_general').hide();
            $('#download_format_sin_fconversion').hide();

            // Restaurar configuración del modal
            var modalConfig = $('#importarArchivoModal').data('bs.modal');
            if (modalConfig && modalConfig._config) {
                modalConfig._config.backdrop = true;
                modalConfig._config.keyboard = true;
            }
        }

        // Resetear modal al abrirlo (para casos donde pueda quedar en estado inconsistente)
        $('#importarArchivoModal').on('show.bs.modal', function(event) {
            resetModal();
            // Determinar el tipo de importación según el botón que abrió el modal
            var button = $(event.relatedTarget); // Button that triggered the modal
            var importType = button.data('import-type') || 'general';
            $('#import_type_input').val(importType);
            // Mostrar el botón de descarga apropiado según el tipo de importación
            if (importType === 'general') {
                $('#download_format_general').show();
            } else if (importType === 'sin_fconversion') {
                $('#download_format_sin_fconversion').show();
            } else {
                $('#download_format_general').hide();
                $('#download_format_sin_fconversion').hide();
            }
        }); // Script para manejar el loader durante la importación
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
        }); // Resetear el modal cuando se cierre
        $('#importarArchivoModal').on('hidden.bs.modal', function() {
            resetModal();
        }); // Manejar clic en el botón cancelar
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

    <!-- 🔥 SCRIPT DE DIAGNÓSTICO PARA EL SERVIDOR 🔥 -->
    <script>
        console.log('=== DIAGNÓSTICO DEL SERVIDOR ===');
        
        // 1. Verificar librerías básicas
        console.log('jQuery disponible:', typeof jQuery !== 'undefined' ? '✓ v' + jQuery.fn.jquery : '✗ NO');
        console.log('Bootstrap disponible:', typeof bootstrap !== 'undefined' ? '✓ SÍ' : '✗ NO');
        console.log('Bootstrap modal fn:', typeof jQuery.fn.modal !== 'undefined' ? '✓ SÍ' : '✗ NO');
        
        // 2. Verificar elementos del DOM
        $(document).ready(function() {
            console.log('DOM cargado correctamente');
            console.log('Botones .open-history encontrados:', $('.open-history').length);
            console.log('Modal historial existe:', $('#historialEmailsModal').length > 0 ? '✓ SÍ' : '✗ NO');
            
            // 3. Test de evento click directo
            console.log('Vinculando evento de prueba...');
            $('.open-history').off('click.testDiag').on('click.testDiag', function(e) {
                console.log('🔥 CLICK DETECTADO EN BOTÓN HISTORIAL!');
                console.log('- ID del proveedor:', $(this).data('id'));
                console.log('- Nombre del proveedor:', $(this).data('nombre'));
                console.log('- Elemento que disparó:', this);
                
                // Prevenir comportamiento por defecto TEMPORALMENTE para testing
                e.preventDefault();
                e.stopPropagation();
                
                // Test básico de modal
                console.log('Intentando abrir modal de test...');
                try {
                    var $modal = $('#historialEmailsModal');
                    if ($modal.length === 0) {
                        console.error('✗ Modal no encontrado en DOM');
                        return;
                    }
                    
                    console.log('Modal encontrado, intentando abrir...');
                    $modal.modal('show');
                    console.log('✓ Comando modal.show() ejecutado');
                    
                    // Verificar si se abrió
                    setTimeout(function() {
                        var isVisible = $modal.hasClass('show') || $modal.is(':visible');
                        console.log('Modal visible después de 500ms:', isVisible ? '✓ SÍ' : '✗ NO');
                        
                        if (isVisible) {
                            console.log('🎉 ¡MODAL SE ABRE CORRECTAMENTE!');
                            // Cerrar después de 2 segundos
                            setTimeout(function() {
                                $modal.modal('hide');
                                console.log('Modal cerrado automáticamente');
                            }, 2000);
                        } else {
                            console.error('❌ EL MODAL NO SE ABRE - PROBLEMA IDENTIFICADO');
                            console.log('Clases del modal:', $modal.attr('class'));
                            console.log('Display del modal:', $modal.css('display'));
                        }
                    }, 500);
                    
                } catch (error) {
                    console.error('❌ ERROR AL ABRIR MODAL:', error.message);
                }
                
                return false; // Prevenir comportamiento normal TEMPORALMENTE
            });
            
            console.log('Evento de diagnóstico vinculado correctamente');
            console.log('=== FIN DIAGNÓSTICO - PRUEBA CLICKEANDO UN BOTÓN DE HISTORIAL ===');
        });
        
        // 4. Capturar errores JavaScript
        window.addEventListener('error', function(e) {
            console.error('❌ ERROR JS DETECTADO:', e.message, 'en', e.filename, 'línea', e.lineno);
        });
        
        // 5. Monitorear eventos de modal
        $(document).on('show.bs.modal', '#historialEmailsModal', function(e) {
            console.log('🟡 Evento show.bs.modal disparado');
        });
        
        $(document).on('shown.bs.modal', '#historialEmailsModal', function(e) {
            console.log('🟢 Evento shown.bs.modal disparado - Modal completamente abierto');
        });
        
        $(document).on('hide.bs.modal', '#historialEmailsModal', function(e) {
            console.log('🟡 Evento hide.bs.modal disparado');
        });
        
        $(document).on('hidden.bs.modal', '#historialEmailsModal', function(e) {
            console.log('🔴 Evento hidden.bs.modal disparado - Modal completamente cerrado');
        });
    </script>

    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/proveedor_list.js') }}?v={{ config('app.version') }}"></script>
    @endsection
@extends('layouts.app')

@section('app_name', config('app.name'))

@section('custom_head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .btn-group .btn {
        margin-left: 5px;
    }
    .btn-group .btn:first-child {
        margin-left: 0;
    }
    .card-header .btn-group .btn {
        border: 1px solid rgba(255,255,255,0.3);
    }
    .card-header .btn-group .btn:hover {
        background-color: rgba(255,255,255,0.1);
    }
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    /* IMPORTANTE: Evitar propagación en columna de botones */
    .table tbody tr .td-historial {
        cursor: default;
    }
    .badge-incidencia {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    .badge-devolucion {
        background-color: #17a2b8 !important;
        color: #fff !important;
    }
    
    /* TABLA CON TAMAÑO FIJO Y OVERFLOW */
    #tabla_historial_respuestas_container {
        height: 400px;
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    
    #tabla_historial_respuestas {
        width: 100% !important;
        margin: 0 !important;
    }
    
    #tabla_historial_respuestas thead {
        position: sticky;
        top: 0;
        background: #343a40 !important;
        z-index: 10;
    }
    
    #tabla_historial_respuestas thead th {
        background: #343a40 !important;
        color: white !important;
        border-color: #454d55 !important;
        white-space: nowrap;
    }
    
    /* Hacer las columnas más compactas */
    #tabla_historial_respuestas td,
    #tabla_historial_respuestas th {
        padding: 0.5rem 0.4rem !important;
        font-size: 0.875rem;
        vertical-align: middle;
    }
    
    /* Ancho fijo para columnas específicas */
    #tabla_historial_respuestas .col-fecha { width: 100px; }
    #tabla_historial_respuestas .col-persona { width: 150px; }
    #tabla_historial_respuestas .col-telefono { width: 120px; }
    #tabla_historial_respuestas .col-email { width: 180px; }
    #tabla_historial_respuestas .col-archivos { width: 100px; }
    #tabla_historial_respuestas .col-acciones { width: 120px; }
    
    /* Descripción con scroll interno */
    .descripcion-cell {
        max-width: 200px;
        max-height: 60px;
        overflow-y: auto;
        overflow-x: hidden;
        word-wrap: break-word;
        font-size: 0.8rem;
    }
</style>
<script>
    // VARIABLES GLOBALES
    window.appBaseUrl = '{{ url("/") }}';
    window.guardarIncidenciaUrl = '{{ route("material_kilo.guardar_incidencia") }}';
    window.guardarDevolucionUrl = '{{ route("material_kilo.guardar_devolucion") }}';
    window.obtenerIncidenciasUrl = '{{ route("material_kilo.obtener_incidencias") }}';
    window.obtenerDevolucionesUrl = '{{ route("material_kilo.obtener_devoluciones") }}';
    window.obtenerIncidenciaUrl = '{{ url("material_kilo/obtener-incidencia") }}';
    window.obtenerDevolucionUrl = '{{ url("material_kilo/obtener-devolucion") }}';
    window.filtroMes = {{ $mes ?? 'null' }};
    window.filtroAño = {{ $año }};
    window.filtroProveedor = '{{ $proveedor }}';
    window.filtroTipo = '{{ $tipo }}';

    var historialEstadosTable = null;

    // FUNCIÓN PARA EDITAR (FUERA DEL READY)
    function editarRegistro(tipo, id) {
        if (tipo === 'incidencia') {
            window.location.href = '{{ url("material_kilo/incidencia/editar") }}/' + id;
        } else if (tipo === 'devolucion') {
            window.location.href = '{{ url("material_kilo/devolucion/editar") }}/' + id;
        }
    }

    // FUNCIONES PARA MODAL HISTORIAL
    function destroyHistorialEstadosTable() {
        try {
            if (historialEstadosTable) {
                historialEstadosTable.destroy(true);
                historialEstadosTable = null;
            }
            if ($.fn.DataTable && $.fn.DataTable.isDataTable("#tabla_historial_estados")) {
                $("#tabla_historial_estados").DataTable().destroy(true);
            }
            $("#tabla_historial_estados_wrapper").remove();
            $("#tabla_historial_estados").remove();
        } catch (e) {
            // Silently handle table destruction errors
        }
    }

    function createHistorialEstadosTable() {
        const html = `
            <table id="tabla_historial_estados" class="table table-sm table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">Usuario</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>`;
        $("#tabla_historial_estados_container").html(html);
    }

    function initHistorialEstadosTable() {
        try {
            var $table = $("#tabla_historial_estados");
            if ($table.length === 0) return;

            setTimeout(function() {
                try {
                    historialEstadosTable = $table.DataTable({
                        order: [[2, "desc"]],
                        pageLength: 10,
                        lengthMenu: [[5, 10, 25], [5, 10, 25]],
                        responsive: true,
                        destroy: true,
                        searching: true, // Activa el buscador
                        paging: true,
                        info: true,
                        autoWidth: false,
                        language: {
                            emptyTable: "No hay cambios de estado registrados",
                            zeroRecords: "No se encontraron registros",
                            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                            infoEmpty: "Mostrando 0 a 0 de 0 registros",
                            search: "Buscar:", // Etiqueta del buscador
                            paginate: {
                                first: "Primero",
                                last: "Último",
                                next: "Siguiente",
                                previous: "Anterior"
                            },
                            lengthMenu: "Mostrar _MENU_ registros"
                        }
                    });
                } catch (e) {
                    // Silently handle DataTable creation errors
                }
            }, 100);
        } catch (e) {
            // Silently handle table initialization errors
        }
    }

    // DOCUMENT READY
    $(document).ready(function() {
        // Configurar CSRF token para todas las peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // DATATABLE PRINCIPAL
        try {
            if ($.fn.DataTable.isDataTable('#table_historial')) {
                $('#table_historial').DataTable().destroy();
            }
            
            $('#table_historial').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "pageLength": 25,
                "order": [[5, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 9 }
                ]
            });
        } catch (e) {
            // Silently handle DataTable errors
        }

        // EVENTO CLICK EN FILAS (EDITAR)
        $('#table_historial tbody').on('click', 'tr', function(e) {
            // Si el clic fue en la columna del botón Historial, NO hacer nada
            if ($(e.target).closest('.td-historial').length > 0) {
                return;
            }
            
            var tipo = $(this).data('tipo');
            var id = $(this).data('id');
            
            if (tipo && id) {
                editarRegistro(tipo, id);
            }
        });

        // EVENTOS FILTROS
        $('#aplicarFiltros').click(function() {
            var params = new URLSearchParams();
            if ($('#filtro_mes').val()) params.append('mes', $('#filtro_mes').val());
            if ($('#filtro_año').val()) params.append('año', $('#filtro_año').val());
            if ($('#filtro_proveedor').val()) params.append('proveedor', $('#filtro_proveedor').val());
            if ($('#filtro_tipo').val()) params.append('tipo', $('#filtro_tipo').val());
            if ($('#filtro_codigo_proveedor').val()) params.append('codigo_proveedor', $('#filtro_codigo_proveedor').val());
            if ($('#filtro_codigo_producto').val()) params.append('codigo_producto', $('#filtro_codigo_producto').val());
            if ($('#filtro_gravedad').val()) params.append('gravedad', $('#filtro_gravedad').val());
            if ($('#filtro_no_queja').val()) params.append('no_queja', $('#filtro_no_queja').val());
            window.location.href = '{{ route("material_kilo.historial_incidencias_devoluciones") }}?' + params.toString();
        });

        $('#limpiarFiltros').click(function() {
            window.location.href = '{{ route("material_kilo.historial_incidencias_devoluciones") }}';
        });
    });

    // EVENTO BOTÓN "VER HISTORIAL"
    $(document).on("click", ".btn-ver-historial-estados", function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        if ($btn.hasClass("loading")) return;
        $btn.addClass("loading").prop("disabled", true);

        var tipo = $btn.data("tipo");
        var id = $btn.data("id");
        var codigo = $btn.data("codigo");
        var producto = $btn.data("producto");

        if (!tipo || !id) {
            alert("Datos incompletos");
            $btn.removeClass("loading").prop("disabled", false);
            return;
        }

        $("#hist_tipo_registro").text(tipo === 'incidencia' ? 'Incidencia' : 'Reclamación')
            .removeClass('badge-warning badge-info')
            .addClass(tipo === 'incidencia' ? 'badge-warning' : 'badge-info');
        $("#hist_codigo_producto").text(codigo || 'N/A');
        $("#hist_descripcion_producto").text(producto || 'N/A');

        destroyHistorialEstadosTable();
        createHistorialEstadosTable();

        var $tbody = $("#tabla_historial_estados tbody");
        $tbody.html('<tr><td colspan="3" class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

        $("#modalHistorialEstados").modal("show");

        var url = "{{ url('material_kilo/historial-estados') }}/" + tipo + "/" + id;

        $.get(url)
            .done(function(res) {
                $tbody.empty();
                var estados = res && res.data ? res.data : [];

                if (!estados.length) {
                    $tbody.html('<tr><td colspan="3" class="text-center">No hay cambios de estado registrados</td></tr>');
                } else {
                    estados.forEach(function(estado) {
                        var usuario = estado.user_name || 'Usuario desconocido';
                        var estadoNombre = estado.estado || 'Sin estado';
                        var fecha = estado.created_at || '';

                        var fechaFormateada = '';
                        if (fecha) {
                            var d = new Date(fecha);
                            if (!isNaN(d.getTime())) {
                                var pad = (n) => (n < 10 ? '0' + n : n);
                                fechaFormateada = pad(d.getDate()) + '-' +
                                    pad(d.getMonth() + 1) + '-' +
                                    d.getFullYear() + ' ' +
                                    pad(d.getHours()) + ':' +
                                    pad(d.getMinutes()) + ':' +
                                    pad(d.getSeconds());
                            }
                        }

                        var estadoBadge = '';
                        if (estadoNombre === 'Registrada') {
                            estadoBadge = '<span class="badge badge-secondary">Registrada</span>';
                        } else if (estadoNombre === 'Gestionada') {
                            estadoBadge = '<span class="badge badge-success">Gestionada</span>';
                        } else if (estadoNombre === 'En Pausa') {
                            estadoBadge = '<span class="badge badge-warning">En Pausa</span>';
                        } else if (estadoNombre === 'Cerrada') {
                            estadoBadge = '<span class="badge badge-danger">Cerrada</span>';
                        } else {
                            estadoBadge = '<span class="badge badge-light">' + estadoNombre + '</span>';
                        }

                        var row = `
                            <tr>
                                <td class="text-center">${usuario}</td>
                                <td class="text-center">${estadoBadge}</td>
                                <td class="text-center">${fechaFormateada}</td>
                            </tr>`;
                        $tbody.append(row);
                    });
                }

                initHistorialEstadosTable();
            })
            .fail(function(xhr, status, error) {
                $tbody.html('<tr><td colspan="3" class="text-center text-danger">Error al cargar el historial</td></tr>');
            })
            .always(function() {
                $btn.removeClass("loading").prop("disabled", false);
            });
    });

    // LIMPIAR AL CERRAR MODAL
    $("#modalHistorialEstados").on("hide.bs.modal", function() {
        $("#hist_tipo_registro").text("");
        $("#hist_codigo_producto").text("");
        $("#hist_descripcion_producto").text("");
        destroyHistorialEstadosTable();
    });

    // ========== MODAL RESPUESTAS ==========
    var historialRespuestasTable = null;

    function destroyHistorialRespuestasTable() {
        try {
            if (historialRespuestasTable) {
                try {
                    historialRespuestasTable.destroy(true);
                } catch (e) {
                    // Silently handle table destruction errors
                }
                historialRespuestasTable = null;
            }
            if ($.fn.DataTable && $.fn.DataTable.isDataTable("#tabla_historial_respuestas")) {
                try {
                    $("#tabla_historial_respuestas").DataTable().destroy(true);
                } catch (e) {
                    // Silently handle DataTable destruction errors
                }
            }
            // Limpiar DOM completamente
            $("#tabla_historial_respuestas_wrapper").remove();
            $("#tabla_historial_respuestas").remove();
        } catch (e) {
            // Silently handle table destruction errors
        }
    }

    function createHistorialRespuestasTable() {
        const html = `
            <table id="tabla_historial_respuestas" class="table table-sm table-striped table-bordered" style="width:100%">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center col-fecha">Fecha</th>
                        <th class="text-center">Descripción</th>
                        <th class="text-center col-persona">Persona</th>
                        <th class="text-center col-telefono">Teléfono</th>
                        <th class="text-center col-email">Email</th>
                        <th class="text-center col-archivos">Archivos</th>
                        <th class="text-center col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>`;
        $("#tabla_historial_respuestas_container").html(html);
    }

    function initHistorialRespuestasTable() {
        try {
            var $table = $("#tabla_historial_respuestas");
            if ($table.length === 0) {
                return;
            }

            setTimeout(function() {
                try {
                    historialRespuestasTable = $table.DataTable({
                        order: [[0, "desc"]],
                        pageLength: 5,
                        lengthMenu: [[5, 10, 25], [5, 10, 25]],
                        columnDefs: [
                            { orderable: false, targets: [5, 6] },
                            { width: "12%", targets: [0] },
                            { width: "32%", targets: [1] },
                            { width: "15%", targets: [2] },
                            { width: "12%", targets: [3] },
                            { width: "12%", targets: [4] },
                            { width: "8%", targets: [5] },
                            { width: "9%", targets: [6] }
                        ],
                        responsive: true,
                        destroy: true,
                        autoWidth: false,
                        language: {
                            emptyTable: "No hay respuestas registradas",
                            zeroRecords: "No se encontraron respuestas",
                            info: "Mostrando _START_ a _END_ de _TOTAL_ respuestas",
                            infoEmpty: "Mostrando 0 a 0 de 0 respuestas",
                            infoFiltered: "(filtrado de _MAX_ respuestas totales)",
                            search: "Buscar:",
                            paginate: {
                                first: "Primero",
                                last: "Último", 
                                next: "Siguiente",
                                previous: "Anterior"
                            },
                            lengthMenu: "Mostrar _MENU_ respuestas"
                        }
                    });
                } catch (e) {
                    // Silently handle DataTable creation errors
                }
            }, 150);
        } catch (e) {
            // Silently handle table initialization errors
        }
    }

    // EVENTO BOTÓN "VER RESPUESTAS"
    $(document).on("click", ".btn-ver-respuestas", function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        if ($btn.hasClass("loading")) return;
        $btn.addClass("loading").prop("disabled", true);

        var tipo = $btn.data("tipo");
        var id = $btn.data("id");
        var codigo = $btn.data("codigo");
        var producto = $btn.data("producto");

        if (!tipo || !id) {
            alert("Datos incompletos");
            $btn.removeClass("loading").prop("disabled", false);
            return;
        }

        // Configurar info del modal
        $("#resp_tipo_registro").text(tipo === 'incidencia' ? 'Incidencia' : 'Reclamación')
            .removeClass('badge-warning badge-info')
            .addClass(tipo === 'incidencia' ? 'badge-warning' : 'badge-info');
        $("#resp_codigo_producto").text(codigo || 'N/A');
        $("#resp_descripcion_producto").text(producto || 'N/A');
        
        // Configurar formulario
        $("#resp_tipo").val(tipo);
        $("#resp_id").val(id);

        // Mostrar modal y ir al tab historial para cargar datos
        $("#modalRespuestas").modal("show");
        $("#historial-respuestas-tab").tab("show");

        cargarHistorialRespuestas(tipo, id);

        $btn.removeClass("loading").prop("disabled", false);
    });

    function cargarHistorialRespuestas(tipo, id) {
        // Variables globales para usar en la tabla
        window.tipoActual = tipo;
        window.referenciaIdActual = id;
        
        destroyHistorialRespuestasTable();
        createHistorialRespuestasTable();

        var $tbody = $("#tabla_historial_respuestas tbody");
        $tbody.html('<tr><td colspan="7" class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</td></tr>');

        var url = "{{ url('material_kilo/historial-respuestas') }}/" + tipo + "/" + id;

        $.get(url)
            .done(function(res) {
                $tbody.empty();
                var respuestas = res && res.data ? res.data : [];

                if (!respuestas.length) {
                    $tbody.html('<tr><td colspan="7" class="text-center">No hay respuestas registradas</td></tr>');
                } else {
                    respuestas.forEach(function(resp) {
                        var archivosHtml = "";
                        if (resp.archivos && resp.archivos.length) {
                            archivosHtml = resp.archivos.map(function(archivo, index) {
                                var downloadUrl = "{{ url('material_kilo/descargar-archivo-respuesta') }}/" + resp.id + "/" + encodeURIComponent(archivo.nombre_original);
                                var btnClass = archivo.es_imagen ? 'btn-success' : 'btn-primary';
                                var numeroSlot = index + 1;
                                return '<button type="button" class="btn btn-xs ' + btnClass + ' mr-1 mb-1 btn-descargar-archivo" ' +
                                    'data-url="' + downloadUrl + '" data-nombre="' + archivo.nombre_original + '" title="' + archivo.nombre_original + '">' +
                                    numeroSlot + '</button>';
                            }).join('');
                        } else {
                            archivosHtml = '<span class="text-muted">Sin archivos</span>';
                        }

                        var descripcionCorta = (resp.descripcion || '').length > 80 ? 
                            (resp.descripcion || '').substring(0, 80) + '...' : 
                            (resp.descripcion || '');

                        var accionesHtml = '<div class="btn-group">' +
                            '<button type="button" class="btn btn-xs btn-warning btn-editar-respuesta" ' +
                            'data-id="' + resp.id + '" ' +
                            'data-tipo="' + window.tipoActual + '" ' +
                            'data-referencia-id="' + window.referenciaIdActual + '" ' +
                            'title="Editar respuesta">' +
                            '<i class="fa fa-edit"></i></button>' +
                            '<button type="button" class="btn btn-xs btn-danger btn-eliminar-respuesta" ' +
                            'data-id="' + resp.id + '" ' +
                            'title="Eliminar respuesta">' +
                            '<i class="fa fa-trash"></i></button>' +
                            '</div>';

                        var row = '<tr>' +
                            '<td class="text-center">' + (resp.fecha_respuesta || '') + '</td>' +
                            '<td class="descripcion-cell" title="' + (resp.descripcion || '') + '">' + (resp.descripcion || '') + '</td>' +
                            '<td class="text-center">' + (resp.persona_contacto || '') + '</td>' +
                            '<td class="text-center">' + (resp.telefono || '-') + '</td>' +
                            '<td class="text-center">' + (resp.email || '-') + '</td>' +
                            '<td class="text-center">' + archivosHtml + '</td>' +
                            '<td class="text-center">' + accionesHtml + '</td>' +
                            '</tr>';
                        $tbody.append(row);
                    });
                }

                initHistorialRespuestasTable();
            })
            .fail(function(xhr, status, error) {
                $tbody.html('<tr><td colspan="7" class="text-center text-danger">Error al cargar el historial</td></tr>');
            });
    }

    // SUBMIT FORMULARIO RESPUESTA
    $(document).on("submit", "#formNuevaRespuesta", function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        
        if ($btn.hasClass("loading")) return;
        
        // Validar que al menos la descripción esté llena
        var descripcion = $("#descripcion").val().trim();
        var persona = $("#persona_contacto").val().trim();
        
        if (!descripcion || descripcion.length < 10) {
            alert("La descripción debe tener al menos 10 caracteres");
            return;
        }
        
        if (!persona) {
            alert("La persona de contacto es requerida");
            return;
        }

        $btn.addClass("loading").prop("disabled", true);
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        var formData = new FormData(this);
        
        // Añadir datos de edición si está en modo edición
        var modoEdicion = $("#modalRespuestas").data("modo") === "editar";
        if (modoEdicion) {
            formData.append("respuesta_id", $("#modalRespuestas").data("respuesta-id"));
        }
        
        // Form data prepared for submission

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var url = modoEdicion ? 
            "{{ url('material_kilo/respuesta') }}/" + $("#modalRespuestas").data("respuesta-id") + "/actualizar" :
            "{{ route('material_kilo.guardar_respuesta') }}";

        $.ajax({
            url: url,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    var mensaje = modoEdicion ? "Respuesta actualizada correctamente" : "Respuesta guardada correctamente";
                    alert(mensaje);
                    limpiarFormularioRespuesta();
                    
                    // *** RESETEAR INTERFAZ DESPUÉS DE ÉXITO ***
                    $("#modalRespuestas").removeData("modo");
                    $("#modalRespuestas").removeData("respuesta-id");
                    
                    // Restaurar interfaz a modo creación
                    $("#modalRespuestasHeader").removeClass("bg-warning").addClass("bg-success");
                    $("#modalRespuestasLabel").html('<i class="fa fa-reply mr-2"></i>Gestión de Respuestas');
                    $("#modoIndicador").hide();
                    
                    $("#tabIcon").removeClass("fa-edit").addClass("fa-plus");
                    $("#tabTexto").text("Nueva Respuesta");
                    $("#nueva-respuesta-tab").removeClass("text-warning").addClass("text-primary");
                    
                    $("#btnGuardarRespuesta").html('<i class="fa fa-save"></i> Guardar Respuesta')
                        .removeClass("btn-warning").addClass("btn-success");
                    $("#btnCancelarEdicion").hide();
                    
                    // Recargar historial
                    var tipo = $("#resp_tipo").val();
                    var id = $("#resp_id").val();
                    cargarHistorialRespuestas(tipo, id);
                    
                    // Ir al tab historial
                    $("#historial-respuestas-tab").tab("show");
                } else {
                    alert("Error: " + (res.message || "Error desconocido"));
                }
            },
            error: function(xhr) {
                
                var modoTexto = modoEdicion ? "actualizar" : "guardar";
                var mensaje = "Error al " + modoTexto + " la respuesta";
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        // Errores de validación
                        var errors = "";
                        Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                            var fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            errors += "• " + fieldName + ": " + xhr.responseJSON.errors[key].join(", ") + "\\n";
                        });
                        mensaje = "Errores de validación:\\n" + errors;
                    } else if (xhr.responseJSON.message) {
                        // Error específico del servidor
                        mensaje = xhr.responseJSON.message;
                    }
                } else if (xhr.status === 404) {
                    mensaje = "Respuesta no encontrada";
                } else if (xhr.status === 422) {
                    mensaje = "Datos inválidos - verifique los campos";
                } else if (xhr.status === 500) {
                    mensaje = "Error interno del servidor";
                } else {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText || "Error desconocido";
                    mensaje = "Error al " + modoTexto + ": " + message;
                }
                
                alert(mensaje);
            },
            complete: function() {
                $btn.removeClass("loading").prop("disabled", false);
                
                // Restaurar texto correcto del botón según el modo
                if (modoEdicion) {
                    $btn.html('<i class="fa fa-save"></i> Actualizar Respuesta');
                } else {
                    $btn.html('<i class="fa fa-save"></i> Guardar Respuesta');
                }
            }
        });
    });

    // PREVIEW ARCHIVOS INDIVIDUALES CON VISTA PREVIA REAL DE IMÁGENES
    function previewArchivo(numero) {
        var input = document.getElementById('archivo' + numero);
        var preview = $('#preview' + numero);
        preview.empty();

        if (input.files && input.files[0]) {
            var file = input.files[0];
            var fileSize = (file.size / 1024 / 1024).toFixed(2);
            
            // Validar tamaño
            if (fileSize > 10) {
                alert("El archivo " + file.name + " excede los 10MB permitidos");
                input.value = "";
                preview.empty();
                return;
            }

            // Validar formato
            var allowedTypes = ['application/pdf', 'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg', 'image/jpg', 'image/png', 'text/plain'];
            
            if (!allowedTypes.includes(file.type)) {
                alert("Formato no permitido. Use: PDF, DOC, DOCX, JPG, JPEG, PNG, TXT");
                input.value = "";
                preview.empty();
                return;
            }

            var fileName = file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name;
            var ext = file.name.split('.').pop().toLowerCase();
            
            // Para imágenes, mostrar preview real
            if (['jpg','jpeg','png','bmp','webp'].includes(ext)) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.html(
                        '<div class="text-center">' +
                        '<img src="' + e.target.result + '" style="max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 5px;" onclick="mostrarImagenCompleta(\'' + e.target.result + '\', \'' + file.name + '\')">' +
                        '<br><small class="text-success">' + fileName + '<br>(' + fileSize + ' MB)</small>' +
                        '<br><button type="button" class="btn btn-xs btn-danger mt-1" onclick="limpiarArchivo(' + numero + ')">' +
                        '<i class="fa fa-trash"></i></button>' +
                        '</div>'
                    );
                };
                reader.readAsDataURL(file);
            } else {
                // Para otros archivos, mostrar icono
                var iconClass = "fa-file";
                if (file.type.includes("pdf")) iconClass = "fa-file-pdf-o";
                else if (file.type.includes("word")) iconClass = "fa-file-word-o";
                else if (file.type.includes("excel")) iconClass = "fa-file-excel-o";
                
                preview.html(
                    '<div class="text-success text-center">' +
                    '<i class="fa ' + iconClass + ' fa-2x"></i><br>' +
                    '<small>' + fileName + '<br>(' + fileSize + ' MB)</small>' +
                    '<br><button type="button" class="btn btn-xs btn-danger mt-1" onclick="limpiarArchivo(' + numero + ')">' +
                    '<i class="fa fa-trash"></i></button>' +
                    '</div>'
                );
            }
        }
    }

    // Función para mostrar imagen en modal completo
    function mostrarImagenCompleta(src, nombre) {
        var modal = $('<div class="modal fade" id="modalImagenPreview" tabindex="-1">' +
            '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title">Vista Previa: ' + nombre + '</h5>' +
            '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
            '</div>' +
            '<div class="modal-body text-center">' +
            '<img src="' + src + '" style="max-width: 100%; height: auto;">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>');
        
        $('body').append(modal);
        modal.modal('show');
        
        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    }

    function limpiarArchivo(numero) {
        var input = document.getElementById('archivo' + numero);
        var preview = $('#preview' + numero);
        
        // Limpiar input y preview
        input.value = "";
        preview.empty();
        
        // Asegurar que el input está habilitado
        input.disabled = false;
    }

    function limpiarFormularioRespuesta() {
        $("#formNuevaRespuesta")[0].reset();
        
        // Limpiar todos los slots de archivos CORRECTAMENTE
        for (var i = 1; i <= 3; i++) {
            var input = document.getElementById('archivo' + i);
            var preview = $('#preview' + i);
            
            // Resetear completamente el input
            if (input) {
                input.value = '';
                input.disabled = false;
            }
            
            // Limpiar preview
            preview.empty();
        }
        
        // Limpiar fecha manual si existe
        if ($("#fecha_respuesta_manual").length) {
            $("#fecha_respuesta_manual").val('');
        }
    }

    // EVENTO EDITAR RESPUESTA
    $(document).on("click", ".btn-editar-respuesta", function(e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.hasClass("loading")) return;
        
        var respuestaId = $btn.data("id");
        var tipo = $btn.data("tipo");
        var referenciaId = $btn.data("referencia-id");
        
        // Loading response for editing
        
        $btn.addClass("loading").prop("disabled", true);
        
        // Cargar datos de la respuesta para edición
        $.get("{{ url('material_kilo/obtener-datos-respuesta') }}/" + respuestaId)
            .done(function(respuesta) {
                // Response data loaded successfully
                
                // Cambiar a modo edición
                $("#modalRespuestas").data("modo", "editar");
                $("#modalRespuestas").data("respuesta-id", respuestaId);
                
                // Rellenar formulario con datos existentes
                $("#fecha_respuesta").val(respuesta.fecha_respuesta);
                $("#descripcion").val(respuesta.descripcion);
                $("#persona_contacto").val(respuesta.persona_contacto);
                $("#telefono").val(respuesta.telefono);
                $("#email_respuesta").val(respuesta.email); // CORREGIDO: usar el ID correcto
                
                // *** INDICADORES VISUALES DE MODO EDICIÓN ***
                // Cambiar header a modo edición
                $("#modalRespuestasHeader").removeClass("bg-success").addClass("bg-warning");
                $("#modalRespuestasLabel").html('<i class="fa fa-edit mr-2"></i>Editando Respuesta #' + respuestaId);
                $("#modoIndicador").show().html('<i class="fa fa-edit"></i> MODO EDICIÓN').removeClass("text-success").addClass("text-warning");
                
                // Cambiar tab a modo edición
                $("#tabIcon").removeClass("fa-plus").addClass("fa-edit");
                $("#tabTexto").text("Editar Respuesta");
                $("#nueva-respuesta-tab").removeClass("text-primary").addClass("text-warning");
                
                // Cambiar botones
                $("#btnGuardarRespuesta").html('<i class="fa fa-save"></i> Actualizar Respuesta')
                    .removeClass("btn-success").addClass("btn-warning");
                $("#btnCancelarEdicion").show();
                
                // Ir al tab de nueva respuesta para editar
                $("#nueva-respuesta-tab").tab("show");
                
                // Mostrar archivos existentes si los hay
                mostrarArchivosExistentesEnEdicion(respuesta.archivos || []);
            })
            .fail(function(xhr, status, error) {
                alert("Error al cargar los datos de la respuesta");
            })
            .always(function() {
                $btn.removeClass("loading").prop("disabled", false);
            });
    });
    
    function mostrarArchivosExistentesEnEdicion(archivos) {
        // Show existing files in edit mode
        
        // Limpiar todos los previews
        for (var i = 1; i <= 3; i++) {
            $('#preview' + i).empty();
            // También limpiar los inputs para evitar conflictos
            $('#archivo' + i).val('');
        }
        
        // Procesar archivos existentes
        if (Array.isArray(archivos) && archivos.length > 0) {
            archivos.forEach(function(archivo, index) {
                if (archivo && archivo.nombre_original) {
                    // Usar el índice del array como slot (1-based)
                    var slot = index + 1;
                    if (slot >= 1 && slot <= 3) {
                        mostrarArchivoEnSlot(archivo, slot, archivo.nombre_original);
                    }
                }
            });
        }
    }
    
    function mostrarArchivoEnSlot(archivo, slot, nombreArchivo) {
        if (!archivo || slot < 1 || slot > 3) return;
        
        var preview = $('#preview' + slot);
        var input = $('#archivo' + slot)[0];
        
        // Deshabilitar el input para indicar que hay un archivo existente
        input.disabled = true;
        
        // Extraer información del archivo (nuevo sistema usa URL completa)
        var fileName = archivo.nombre_original || nombreArchivo || basename(archivo.url || archivo.ruta_completa || 'archivo_existente');
        var fileUrl = archivo.url || "{{ url('storage') }}/" + (archivo.ruta_completa || '');
        var esImagen = archivo.es_imagen || false;
        var extension = archivo.extension || fileName.toLowerCase().split('.').pop();
        
        if (esImagen && ['jpg','jpeg','png','gif','bmp','webp'].includes(extension)) {
            preview.html(
                '<div class="text-center">' +
                '<img src="' + fileUrl + '" style="max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 5px; cursor: pointer;" ' +
                'onclick="mostrarImagenCompleta(\'' + fileUrl + '\', \'' + fileName + '\')">' +
                '<br><small class="text-info"><strong>Existente</strong><br>' + fileName + '</small>' +
                '<br><button type="button" class="btn btn-xs btn-danger mt-1" onclick="eliminarArchivoExistente(' + slot + ', \'' + fileName + '\')" title="Eliminar archivo">' +
                '<i class="fa fa-trash"></i></button>' +
                '</div>'
            );
        } else {
            var iconClass = "fa-file";
            if (extension === 'pdf') iconClass = "fa-file-pdf-o";
            else if (['doc','docx'].includes(extension)) iconClass = "fa-file-word-o";
            else if (['xls','xlsx'].includes(extension)) iconClass = "fa-file-excel-o";
            
            preview.html(
                '<div class="text-info text-center">' +
                '<i class="fa ' + iconClass + ' fa-2x"></i><br>' +
                '<small><strong>Existente</strong><br>' + fileName + '</small>' +
                '<br><button type="button" class="btn btn-xs btn-danger mt-1" onclick="eliminarArchivoExistente(' + slot + ', \'' + fileName + '\')" title="Eliminar archivo">' +
                '<i class="fa fa-trash"></i></button>' +
                '</div>'
            );
        }
    }

    // Función auxiliar para obtener nombre del archivo de una ruta
    function basename(str) {
        if (!str) return 'archivo';
        var parts = str.split('/');
        return parts[parts.length - 1];
    }
    
    function eliminarArchivoExistente(numeroSlot, nombreArchivo) {
        if (!confirm("¿Está seguro de eliminar este archivo?")) return;
        
        var respuestaId = $("#modalRespuestas").data("respuesta-id");
        
        if (!respuestaId) {
            alert("Error: No se puede identificar la respuesta");
            return;
        }
        
        // Mostrar indicador de carga en el slot
        $('#preview' + numeroSlot).html(
            '<div class="text-center text-muted">' +
            '<i class="fa fa-spinner fa-spin fa-2x"></i><br>' +
            '<small>Eliminando...</small>' +
            '</div>'
        );
        
        $.ajax({
            url: "{{ url('material_kilo/eliminar-archivo-respuesta') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'respuesta_id': respuestaId,
                'nombre_archivo': nombreArchivo
            },
            success: function(response) {
                
                if (response.success) {
                    // Limpiar el slot
                    $('#preview' + numeroSlot).empty();
                    
                    // Mostrar mensaje de éxito
                    $('#preview' + numeroSlot).html(
                        '<div class="text-center text-success">' +
                        '<i class="fa fa-check-circle fa-2x"></i><br>' +
                        '<small>Eliminado</small>' +
                        '</div>'
                    );
                    
                    // Limpiar después de 2 segundos
                    setTimeout(function() {
                        $('#preview' + numeroSlot).empty();
                    }, 2000);
                    
                    // Recargar historial después de eliminar
                    setTimeout(function() {
                        cargarHistorialRespuestas(window.tipoActual, window.referenciaIdActual);
                    }, 1000);
                } else {
                    alert("Error: " + (response.message || "No se pudo eliminar el archivo"));
                    $('#preview' + numeroSlot).empty();
                }
            },
            error: function(xhr, status, error) {
                
                var mensaje = "Error al eliminar el archivo";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    mensaje = "Archivo no encontrado";
                } else if (xhr.status === 500) {
                    mensaje = "Error interno del servidor";
                }
                
                alert(mensaje);
                $('#preview' + numeroSlot).empty();
            }
        });
    }

    // FUNCIÓN CANCELAR EDICIÓN - VOLVER A MODO CREAR
    function cancelarEdicion() {
        if (confirm("¿Está seguro de cancelar la edición? Se perderán los cambios no guardados.")) {
            // Limpiar formulario
            limpiarFormularioRespuesta();
            
            // Resetear modo
            $("#modalRespuestas").removeData("modo");
            $("#modalRespuestas").removeData("respuesta-id");
            
            // Restaurar interfaz a modo creación
            $("#modalRespuestasHeader").removeClass("bg-warning").addClass("bg-success");
            $("#modalRespuestasLabel").html('<i class="fa fa-reply mr-2"></i>Gestión de Respuestas');
            $("#modoIndicador").hide();
            
            $("#tabIcon").removeClass("fa-edit").addClass("fa-plus");
            $("#tabTexto").text("Nueva Respuesta");
            $("#nueva-respuesta-tab").removeClass("text-warning").addClass("text-primary");
            
            $("#btnGuardarRespuesta").html('<i class="fa fa-save"></i> Guardar Respuesta')
                .removeClass("btn-warning").addClass("btn-success");
            $("#btnCancelarEdicion").hide();
        }
    }

    // EVENTO ELIMINAR RESPUESTA
    $(document).on("click", ".btn-eliminar-respuesta", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var respuestaId = $(this).data("id");
        
        if (!respuestaId) {
            alert("ID de respuesta no válido");
            return;
        }
        
        if (!confirm("¿Está seguro de eliminar esta respuesta? Esta acción eliminará la respuesta y todos sus archivos asociados de forma permanente.")) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: "{{ url('material_kilo/eliminar-respuesta') }}/" + respuestaId,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.success) {
                    // Recargar tabla de respuestas
                    cargarHistorialRespuestas(window.tipoActual, window.referenciaIdActual);
                    alert("Respuesta eliminada correctamente");
                } else {
                    alert("Error: " + (res.message || "No se pudo eliminar la respuesta"));
                }
            },
            error: function(xhr) {
                var mensaje = "Error al eliminar la respuesta";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    mensaje = "Respuesta no encontrada";
                } else if (xhr.status === 500) {
                    mensaje = "Error interno del servidor";
                }
                alert(mensaje);
            },
            complete: function() {
                $btn.prop("disabled", false).html('<i class="fa fa-trash"></i>');
            }
        });
    });

    // LIMPIAR AL CERRAR MODAL RESPUESTAS
    $("#modalRespuestas").on("hide.bs.modal", function() {
        limpiarFormularioRespuesta();
        destroyHistorialRespuestasTable();
        $("#nueva-respuesta-tab").tab("show"); // Volver al primer tab
        
        // Limpiar datos del modal
        $("#resp_tipo_registro").text("");
        $("#resp_codigo_producto").text("");
        $("#resp_descripcion_producto").text("");
        $("#resp_tipo").val("");
        $("#resp_id").val("");
        
        // *** RESETEAR INTERFAZ AL MODO CREACIÓN ***
        $(this).removeData("modo");
        $(this).removeData("respuesta-id");
        
        // Restaurar header original
        $("#modalRespuestasHeader").removeClass("bg-warning").addClass("bg-success");
        $("#modalRespuestasLabel").html('<i class="fa fa-reply mr-2"></i>Gestión de Respuestas');
        $("#modoIndicador").hide();
        
        // Restaurar tab original
        $("#tabIcon").removeClass("fa-edit").addClass("fa-plus");
        $("#tabTexto").text("Nueva Respuesta");
        $("#nueva-respuesta-tab").removeClass("text-warning").addClass("text-primary");
        
        // Restaurar botones originales
        $("#btnGuardarRespuesta").html('<i class="fa fa-save"></i> Guardar Respuesta')
            .removeClass("btn-warning").addClass("btn-success");
        $("#btnCancelarEdicion").hide();
    });

    $(document).on('click', '.btn-descargar-archivo', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var nombre = $(this).data('nombre') || 'archivo';
        var a = document.createElement('a');
        a.href = url;
        a.download = nombre;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });
</script>
@endsection

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-history icon-gradient bg-secondary"></i>
            </div>
            <div>Historial de Incidencias y Devoluciones
                <div class="page-title-subheading">
                    Gestión y seguimiento de incidencias y devoluciones de proveedores
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <a class="m-2 btn btn-primary" href="{{ route('material_kilo.total_kg_proveedor') }}">
                <i class="fa fa-bar-chart mr-2"></i>Total KG por Proveedor
            </a>
            <a class="m-2 btn btn-success" href="{{ route('material_kilo.evaluacion_continua_proveedores') }}">
                <i class="fa fa-line-chart mr-2"></i>Evaluación Continua
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
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-filter mr-2"></i>Filtros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filtrosForm" class="row">
                            <div class="col-md-2">
                                <label for="filtro_mes">Mes:</label>
                                <select id="filtro_mes" name="mes" class="form-control">
                                    <option value="" {{ !$mes ? 'selected' : '' }}>Todos los meses</option>
                                    <option value="1" {{ $mes == 1 ? 'selected' : '' }}>Enero</option>
                                    <option value="2" {{ $mes == 2 ? 'selected' : '' }}>Febrero</option>
                                    <option value="3" {{ $mes == 3 ? 'selected' : '' }}>Marzo</option>
                                    <option value="4" {{ $mes == 4 ? 'selected' : '' }}>Abril</option>
                                    <option value="5" {{ $mes == 5 ? 'selected' : '' }}>Mayo</option>
                                    <option value="6" {{ $mes == 6 ? 'selected' : '' }}>Junio</option>
                                    <option value="7" {{ $mes == 7 ? 'selected' : '' }}>Julio</option>
                                    <option value="8" {{ $mes == 8 ? 'selected' : '' }}>Agosto</option>
                                    <option value="9" {{ $mes == 9 ? 'selected' : '' }}>Septiembre</option>
                                    <option value="10" {{ $mes == 10 ? 'selected' : '' }}>Octubre</option>
                                    <option value="11" {{ $mes == 11 ? 'selected' : '' }}>Noviembre</option>
                                    <option value="12" {{ $mes == 12 ? 'selected' : '' }}>Diciembre</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_año">Año:</label>
                                <select id="filtro_año" name="año" class="form-control" required>
                                    @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ $year == $año ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_proveedor">Proveedor:</label>
                                <select id="filtro_proveedor" name="proveedor" class="form-control">
                                    <option value="">Todos los proveedores</option>
                                    @foreach($proveedores_disponibles as $prov)
                                        <option value="{{ $prov->nombre_proveedor }}" {{ $proveedor == $prov->nombre_proveedor ? 'selected' : '' }}>
                                            {{ $prov->nombre_proveedor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_tipo">Tipo:</label>
                                <select id="filtro_tipo" name="tipo" class="form-control">
                                    <option value="" {{ $tipo == '' ? 'selected' : '' }}>Todos</option>
                                    <option value="incidencia" {{ $tipo == 'incidencia' ? 'selected' : '' }}>Solo Incidencias</option>
                                    <option value="devolucion" {{ $tipo == 'devolucion' ? 'selected' : '' }}>Solo Reclamaciones</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros adicionales -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-search mr-2"></i>Filtros Avanzados
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filtrosAvanzadosForm" class="row">
                            <div class="col-md-2">
                                <label for="filtro_codigo_proveedor">Código Proveedor:</label>
                                <input type="text" id="filtro_codigo_proveedor" name="codigo_proveedor" 
                                       class="form-control" placeholder="Ej: 12345" 
                                       value="{{ $codigo_proveedor ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_codigo_producto">Código Producto:</label>
                                <input type="text" id="filtro_codigo_producto" name="codigo_producto" 
                                       class="form-control" placeholder="Ej: PROD123" 
                                       value="{{ $codigo_producto ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_no_queja">No. Queja:</label>
                                <input type="text" id="filtro_no_queja" name="no_queja" 
                                       class="form-control" placeholder="Número de queja" 
                                       value="{{ $no_queja ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_gravedad">Gravedad:</label>
                                <select id="filtro_gravedad" name="gravedad" class="form-control">
                                    <option value="" {{ !isset($gravedad) || $gravedad == '' ? 'selected' : '' }}>Todas</option>
                                    <option value="grave" {{ isset($gravedad) && $gravedad == 'grave' ? 'selected' : '' }}>Solo Graves </option>
                                    <option value="leve" {{ isset($gravedad) && $gravedad == 'leve' ? 'selected' : '' }}>Solo Leves </option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" id="aplicarFiltros" class="btn btn-primary mr-2">
                                    <i class="fa fa-search mr-1"></i>Aplicar Filtros
                                </button>
                                <button type="button" id="limpiarFiltros" class="btn btn-secondary mr-2">
                                    <i class="fa fa-times mr-1"></i>Limpiar
                                </button>
                                <button type="button" id="exportarExcel" class="btn btn-success mr-2">
                                    <i class="fa fa-file-excel-o mr-1"></i>Exportar Excel
                                </button>
                                <div class="btn-group mr-2">
                                    {{-- <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-plus mr-1"></i>Nuevo
                                    </button> --}}
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('material_kilo.crear_incidencia') }}">
                                            <i class="fa fa-exclamation-triangle mr-2 text-warning"></i>Nueva Incidencia
                                        </a>
                                        <a class="dropdown-item" href="{{ route('material_kilo.crear_devolucion') }}">
                                            <i class="fa fa-undo mr-2 text-info"></i>Nueva Devolución
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Resumen de contadores -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-exclamation-triangle mr-2"></i>Total Incidencias
                        </h5>
                        <h3 class="card-text">{{ $total_incidencias }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-undo mr-2"></i>Total Reclamaciones de clientes
                        </h5>
                        <h3 class="card-text">{{ $total_devoluciones }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-list mr-2"></i>Total Registros
                        </h5>
                        <h3 class="card-text">{{ $total_incidencias + $total_devoluciones }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-calendar mr-2"></i>Período
                        </h5>
                        <h3 class="card-text">
                            @php
                                $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            @endphp
                            @if($mes)
                                {{ $meses[$mes] }} {{ $año }}
                            @else
                                Todo {{ $año }}
                            @endif
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de historial -->
        <table id="table_historial"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">ID Proveedor</th>
                    <th class="text-center">Nombre Proveedor</th>
                    <th class="text-center">Código Producto</th>
                    <th class="text-center">No. Queja</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Mes/Año</th>
                    <th class="text-center">Clasificación</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Historial cambios de estado</th>
                    <th class="text-center">Respuestas</th>
                    <th class="text-center">Descripción/Producto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resultados as $registro)
                    <tr class="registro-fila" 
                        data-tipo="{{ $registro->tipo_registro }}" 
                        data-id="{{ $registro->id }}"
                        data-proveedor-id="{{ $registro->tipo_registro == 'incidencia' ? $registro->id_proveedor : $registro->codigo_proveedor }}">
                        <td class="text-center">
                            @if($registro->tipo_registro == 'incidencia')
                                <span class="badge badge-incidencia">
                                    <i class="fa fa-exclamation-triangle mr-1"></i>Incidencia
                                </span>
                            @else
                                <span class="badge badge-devolucion">
                                    <i class="fa fa-undo mr-1"></i>Reclamacion
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $registro->tipo_registro == 'incidencia' ? $registro->id_proveedor : $registro->codigo_proveedor }}
                        </td>
                        <td class="text-center">{{ $registro->nombre_proveedor }}</td>
                        <td class="text-center">
                            @if($registro->tipo_registro == 'incidencia')
                                <span class="badge badge-light">{{ $registro->codigo ?? 'N/A' }}</span>
                            @else
                                <span class="badge badge-light">{{ $registro->codigo_producto ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($registro->tipo_registro == 'devolucion' && $registro->no_queja)
                                <span class="badge badge-warning">{{ $registro->no_queja }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($registro->fecha_principal)
                                {{ \Carbon\Carbon::parse($registro->fecha_principal)->format('d/m/Y') }}
                            @else
                                <span class="text-muted">Sin fecha</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-secondary">
                                @php
                                    $meses_cortos = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                @endphp
                                {{ $meses_cortos[$registro->mes] ?? 'N/A' }}/{{ $registro->año }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($registro->clasificacion_incidencia)
                                @php
                                    $clase = $registro->clasificacion_incidencia == 'RG1' ? 'danger' : ($registro->clasificacion_incidencia == 'RL1' ? 'warning' : 'info');
                                    $texto = preg_replace('/1$/', '', $registro->clasificacion_incidencia);
                                    
                                    if ($registro->clasificacion_incidencia == 'RG1') {
                                        $descripcion_clase = 'Reclamación Grave';
                                    } elseif ($registro->clasificacion_incidencia == 'RL1') {
                                        $descripcion_clase = 'Reclamación Leve';
                                    } else {
                                        $descripcion_clase = $texto;
                                    }
                                @endphp
                                <span class="badge badge-{{ $clase }}" title="{{ $descripcion_clase }}">
                                    {{ $texto }}
                                </span>
                            @else
                                <span class="text-muted">Sin clasificar</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $registro->estado ?? 'Registrada' }}
                        </td>
                        <td class="text-center td-historial">
                            <button type="button"
                                class="btn btn-sm btn-outline-info btn-ver-historial-estados"
                                data-tipo="{{ $registro->tipo_registro }}"
                                data-id="{{ $registro->id }}"
                                data-codigo="{{ $registro->codigo ?? $registro->codigo_producto ?? 'N/A' }}"
                                data-producto="{{ $registro->producto ?? $registro->producto ?? 'N/A' }}"
                                title="Ver historial de cambios de estado">
                                <i class="fa fa-history"></i> Historial
                            </button>
                        </td>
                        <td class="text-center td-historial">
                            <button type="button"
                                class="btn btn-sm btn-outline-success btn-ver-respuestas"
                                data-tipo="{{ $registro->tipo_registro }}"
                                data-id="{{ $registro->id }}"
                                data-codigo="{{ $registro->codigo ?? $registro->codigo_producto ?? 'N/A' }}"
                                data-producto="{{ $registro->producto ?? $registro->producto ?? 'N/A' }}"
                                title="Ver y gestionar respuestas">
                                <i class="fa fa-reply"></i> Respuestas
                            </button>
                        </td>
                        <td class="text-center">
                            @if($registro->tipo_registro == 'incidencia')
                                {{ $registro->descripcion_incidencia ?? $registro->producto ?? 'Sin descripción' }}
                            @else
                                {{ $registro->descripcion_producto ?? $registro->codigo_producto ?? 'Sin descripción' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

<!-- MODAL HISTORIAL DE ESTADOS -->
<div class="modal fade" id="modalHistorialEstados" tabindex="-1" role="dialog" aria-labelledby="modalHistorialEstadosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalHistorialEstadosLabel">
                    <i class="fa fa-history mr-2"></i>Historial de Cambios de Estado
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Tipo:</strong> <span id="hist_tipo_registro" class="badge"></span>
                    <strong class="ml-3">Código:</strong> <span id="hist_codigo_producto" class="badge badge-light"></span>
                    <strong class="ml-3">Producto:</strong> <span id="hist_descripcion_producto" class="badge badge-light"></span>
                </div>
                <div id="tabla_historial_estados_container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RESPUESTAS -->
<div class="modal fade" id="modalRespuestas" tabindex="-1" role="dialog" aria-labelledby="modalRespuestasLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white" id="modalRespuestasHeader">
                <h5 class="modal-title" id="modalRespuestasLabel">
                    <i class="fa fa-reply mr-2"></i>Gestión de Respuestas
                </h5>
                <div class="ml-auto mr-3">
                    <span id="modoIndicador" class="badge badge-light text-success" style="display: none;">
                        <i class="fa fa-edit"></i> MODO EDICIÓN
                    </span>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Info del registro -->
                <div class="mb-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Tipo:</strong> <span id="resp_tipo_registro" class="badge"></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Código:</strong> <span id="resp_codigo_producto" class="badge badge-light"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Producto:</strong> <span id="resp_descripcion_producto" class="badge badge-light"></span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs" id="respuestaTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="nueva-respuesta-tab" data-toggle="tab" href="#nueva-respuesta" role="tab">
                            <i class="fa fa-plus" id="tabIcon"></i> <span id="tabTexto">Nueva Respuesta</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="historial-respuestas-tab" data-toggle="tab" href="#historial-respuestas" role="tab">
                            <i class="fa fa-list"></i> Historial
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="respuestaTabContent">
                    <!-- Tab Nueva Respuesta -->
                    <div class="tab-pane fade show active" id="nueva-respuesta" role="tabpanel">
                        <form id="formNuevaRespuesta" class="mt-3" enctype="multipart/form-data">
                            <input type="hidden" id="resp_tipo" name="tipo">
                            <input type="hidden" id="resp_id" name="id">
                            
                            <div class="form-group">
                                <label for="descripcion">Descripción de la respuesta *</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                    placeholder="Describa la respuesta proporcionada..." 
                                    minlength="10" maxlength="2000" required></textarea>
                                <small class="form-text text-muted">Mínimo 10 caracteres, máximo 2000</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="persona_contacto">Persona de contacto *</label>
                                <input type="text" class="form-control" id="persona_contacto" name="persona_contacto" 
                                    placeholder="Nombre de la persona responsable" maxlength="255" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fecha_respuesta">Fecha de respuesta</label>
                                        <input type="date" class="form-control" id="fecha_respuesta" name="fecha_respuesta">
                                        <small class="form-text text-muted">Si no selecciona, se usará la fecha actual</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" 
                                            placeholder="Teléfono de contacto" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="email_respuesta">Email</label>
                                        <input type="email" class="form-control" id="email_respuesta" name="email" 
                                            placeholder="Email de contacto" maxlength="255">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card border-light">
                                            <div class="card-header bg-light text-center">
                                                <strong>Archivo 1</strong>
                                            </div>
                                            <div class="card-body text-center">
                                                <input type="file" class="form-control-file archivo-input" 
                                                    id="archivo1" name="archivo1" 
                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"
                                                    onchange="previewArchivo(1)">
                                                <div id="preview1" class="mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-light">
                                            <div class="card-header bg-light text-center">
                                                <strong>Archivo 2</strong>
                                            </div>
                                            <div class="card-body text-center">
                                                <input type="file" class="form-control-file archivo-input" 
                                                    id="archivo2" name="archivo2" 
                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"
                                                    onchange="previewArchivo(2)">
                                                <div id="preview2" class="mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-light">
                                            <div class="card-header bg-light text-center">
                                                <strong>Archivo 3</strong>
                                            </div>
                                            <div class="card-body text-center">
                                                <input type="file" class="form-control-file archivo-input" 
                                                    id="archivo3" name="archivo3" 
                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"
                                                    onchange="previewArchivo(3)">
                                                <div id="preview3" class="mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group text-right">
                                <button type="button" class="btn btn-secondary" onclick="limpiarFormularioRespuesta()">
                                    <i class="fa fa-eraser"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="btnCancelarEdicion" style="display: none;" onclick="cancelarEdicion()">
                                    <i class="fa fa-times"></i> Cancelar Edición
                                </button>
                                <button type="submit" class="btn btn-success" id="btnGuardarRespuesta">
                                    <i class="fa fa-save"></i> Guardar Respuesta
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab Historial -->
                    <div class="tab-pane fade" id="historial-respuestas" role="tabpanel">
                        <div class="mt-3">
                            <div id="tabla_historial_respuestas_container"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@section('custom_footer')
    <script>
        // Script inline simple para manejo de eventos
        $(document).ready(function() {
            $('#aplicarFiltros').click(function() {
                var params = new URLSearchParams();
                
                if ($('#filtro_mes').val()) params.append('mes', $('#filtro_mes').val());
                if ($('#filtro_año').val()) params.append('año', $('#filtro_año').val());
                if ($('#filtro_proveedor').val()) params.append('proveedor', $('#filtro_proveedor').val());
                if ($('#filtro_tipo').val()) params.append('tipo', $('#filtro_tipo').val());
                if ($('#filtro_codigo_proveedor').val()) params.append('codigo_proveedor', $('#filtro_codigo_proveedor').val());
                if ($('#filtro_codigo_producto').val()) params.append('codigo_producto', $('#filtro_codigo_producto').val());
                if ($('#filtro_gravedad').val()) params.append('gravedad', $('#filtro_gravedad').val());
                if ($('#filtro_no_queja').val()) params.append('no_queja', $('#filtro_no_queja').val());
                
                window.location.href = '{{ route("material_kilo.historial_incidencias_devoluciones") }}?' + params.toString();
            });
            
            $('#limpiarFiltros').click(function() {
                window.location.href = '{{ route("material_kilo.historial_incidencias_devoluciones") }}';
            });

            $('#exportarExcel').click(function() {
                var $btn = $(this);
                if ($btn.hasClass('loading')) return;
                
                // Preparar filtros
                var filtros = {};
                if ($('#filtro_mes').val()) filtros.mes = $('#filtro_mes').val();
                if ($('#filtro_año').val()) filtros.año = $('#filtro_año').val();
                if ($('#filtro_proveedor').val()) filtros.proveedor = $('#filtro_proveedor').val();
                if ($('#filtro_tipo').val()) filtros.tipo = $('#filtro_tipo').val();
                if ($('#filtro_codigo_proveedor').val()) filtros.codigo_proveedor = $('#filtro_codigo_proveedor').val();
                if ($('#filtro_codigo_producto').val()) filtros.codigo_producto = $('#filtro_codigo_producto').val();
                if ($('#filtro_gravedad').val()) filtros.gravedad = $('#filtro_gravedad').val();
                if ($('#filtro_no_queja').val()) filtros.no_queja = $('#filtro_no_queja').val();
                
                // Iniciar exportación inteligente
                iniciarExportacionInteligente(filtros, $btn);
            });
            
            // Funciones auxiliares
            function restaurarBoton($btn) {
                $btn.removeClass('loading').prop('disabled', false);
                $btn.html('<i class="fa fa-file-excel-o mr-1"></i>Exportar Excel');
            }
            
            function mostrarError(mensaje, $btn) {
                console.error('Error exportación:', mensaje);
                alert('Error: ' + mensaje);
                restaurarBoton($btn);
            }
            
            function mostrarExito(mensaje) {
                console.log('Éxito:', mensaje);
            }

            // Función para exportación inteligente
            function iniciarExportacionInteligente(filtros, $btn) {
                $btn.addClass('loading').prop('disabled', true);
                $btn.html('<i class="fa fa-spinner fa-spin mr-1"></i>Preparando exportación...');
                
                // Agregar CSRF token a los filtros
                filtros._token = $('meta[name="csrf-token"]').attr('content');
                
                // Iniciar exportación por lotes
                $.post('{{ route("material_kilo.iniciar_exportacion_lotes") }}', filtros)
                    .done(function(response) {
                        if (response.success) {
                            // Si hay pocos registros, usar método directo
                            if (response.total_registros <= 5000) {
                                $btn.html('<i class="fa fa-spinner fa-spin mr-1"></i>Generando Excel directo...');
                                exportarDirecto(filtros, $btn);
                            } else {
                                // Usar método por lotes para archivos grandes
                                $btn.html('<i class="fa fa-spinner fa-spin mr-1"></i>Procesando lotes...');
                                procesarPorLotes(response.job_id, response.lotes_totales, $btn);
                            }
                        } else {
                            mostrarError('Error al iniciar exportación: ' + response.error, $btn);
                        }
                    })
                    .fail(function(xhr) {
                        var error = xhr.responseJSON ? xhr.responseJSON.error : 'Error desconocido';
                        mostrarError(error, $btn);
                    });
            }
            
            // Exportación directa para archivos pequeños
            function exportarDirecto(filtros, $btn) {
                var params = new URLSearchParams();
                Object.keys(filtros).forEach(key => {
                    params.append(key, filtros[key]);
                });
                
                var exportUrl = '{{ route("material_kilo.exportar_historial_excel") }}?' + params.toString();
                window.location.href = exportUrl;
                
                setTimeout(function() {
                    restaurarBoton($btn);
                }, 3000);
            }
            
            // Procesamiento por lotes para archivos grandes
            function procesarPorLotes(jobId, lotesTotales, $btn) {
                var loteActual = 0;
                
                function procesarSiguienteLote() {
                    $btn.html('<i class="fa fa-spinner fa-spin mr-1"></i>Procesando lote ' + (loteActual + 1) + '/' + lotesTotales);
                    
                    $.post('{{ route("material_kilo.procesar_siguiente_lote") }}', { 
                        job_id: jobId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                        .done(function(response) {
                            if (response.success) {
                                loteActual = response.progreso.lote_actual;
                                
                                // Actualizar progreso
                                var porcentaje = response.progreso.porcentaje;
                                $btn.html('<i class="fa fa-spinner fa-spin mr-1"></i>' + porcentaje + '% - Lote ' + loteActual + '/' + lotesTotales);
                                
                                if (response.completado) {
                                    // Generar Excel final
                                    $btn.html('<i class="fa fa-spinner fa-spin mr-1"></i>Generando Excel final...');
                                    generarExcelFinal(jobId, $btn);
                                } else {
                                    // Procesar siguiente lote después de un pequeño delay
                                    setTimeout(procesarSiguienteLote, 100);
                                }
                            } else {
                                mostrarError('Error procesando lote: ' + response.error, $btn);
                            }
                        })
                        .fail(function(xhr) {
                            var error = xhr.responseJSON ? xhr.responseJSON.error : 'Error procesando lote';
                            mostrarError(error, $btn);
                        });
                }
                
                procesarSiguienteLote();
            }
            
            // Generar Excel final
            function generarExcelFinal(jobId, $btn) {
                $.post('{{ route("material_kilo.generar_excel_final") }}', { 
                    job_id: jobId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                    .done(function(response) {
                        if (response.success) {
                            $btn.html('<i class="fa fa-check mr-1"></i>¡Excel generado! Descargando...');
                            
                            // Descargar archivo
                            window.location.href = '{{ url("material_kilo/descargar-excel-generado") }}/' + jobId;
                            
                            setTimeout(function() {
                                restaurarBoton($btn);
                                mostrarExito('Excel generado y descargado exitosamente');
                            }, 2000);
                        } else {
                            mostrarError('Error generando Excel: ' + response.error, $btn);
                        }
                    })
                    .fail(function(xhr) {
                        var error = xhr.responseJSON ? xhr.responseJSON.error : 'Error generando Excel';
                        mostrarError(error, $btn);
                    });
            }
        });
    </script>
@endsection

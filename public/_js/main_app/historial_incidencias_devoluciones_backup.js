$(document).ready(function () {
    console.log('Iniciando historial incidencias y devoluciones...');
    
    // Limpiar modales al inicio
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
    
    var table = $('#table_historial').DataTable({
        paging: true,
        pageLength: 25,
        info: true,
        ordering: true,
        searching: true,
        orderCellsTop: true,
        fixedHeader: false,
        order: [[3, 'desc']], // Ordenar por fecha descendente
        scrollX: true,
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        columnDefs: [
            {
                targets: [0, 1, 2, 4, 5, 6, 7],
                orderable: true,
                searchable: true
            },
            {
                targets: [3],
                orderable: true,
                searchable: false,
                type: 'date'
            }
        ],
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles en la tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered": "(filtrado de _MAX_ entradas totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": activar para ordenar la columna ascendente",
                "sortDescending": ": activar para ordenar la columna descendente"
            }
        }
    });

    // Hacer filas clickeables para editar
    $('#table_historial tbody').on('click', 'tr.registro-fila', function() {
        var tipo = $(this).data('tipo');
        var id = $(this).data('id');
        var proveedorId = $(this).data('proveedor-id');
        
        console.log('Click en fila:', {tipo: tipo, id: id, proveedorId: proveedorId});
        
        if (tipo === 'incidencia') {
            abrirModalIncidencia(id);
        } else if (tipo === 'devolucion') {
            abrirModalDevolucion(id);
        }
    });
    
    // Funcionalidad para filtros
    $('#aplicarFiltros').on('click', function() {
        var mes = $('#filtro_mes').val();
        var año = $('#filtro_año').val();
        var proveedor = $('#filtro_proveedor').val();
        var tipo = $('#filtro_tipo').val();
        
        // Validar que al menos año esté seleccionado
        if (!año) {
            alert('Por favor seleccione un año');
            return;
        }
        
        // Construir URL con parámetros
        var url = new URL(window.location.href);
        
        // Limpiar parámetros existentes
        url.searchParams.delete('mes');
        url.searchParams.delete('año');
        url.searchParams.delete('proveedor');
        url.searchParams.delete('tipo');
        
        // Agregar nuevos parámetros
        if (mes) {
            url.searchParams.set('mes', mes);
        }
        
        if (año) {
            url.searchParams.set('año', año);
        }
        
        if (proveedor) {
            url.searchParams.set('proveedor', proveedor);
        }
        
        if (tipo) {
            url.searchParams.set('tipo', tipo);
        }
        
        // Mostrar mensaje de carga
        $('#aplicarFiltros').html('<i class="fa fa-spinner fa-spin mr-1"></i>Aplicando...');
        
        // Recargar página con nuevos filtros
        window.location.href = url.toString();
    });
    
    // Limpiar filtros
    $('#limpiarFiltros').on('click', function() {
        if (confirm('¿Está seguro que desea limpiar todos los filtros?')) {
            var url = new URL(window.location.href);
            url.searchParams.delete('mes');
            url.searchParams.delete('año');
            url.searchParams.delete('proveedor');
            url.searchParams.delete('tipo');
            
            // Mantener solo el año actual
            url.searchParams.set('año', new Date().getFullYear());
            
            $('#limpiarFiltros').html('<i class="fa fa-spinner fa-spin mr-1"></i>Limpiando...');
            window.location.href = url.toString();
        }
    });
    
    // Botón nuevo registro
    $('#nuevoRegistro').on('click', function() {
        $('#modalTipoRegistro').modal('show');
    });
    
    // Botones del modal de tipo de registro
    $('#btnNuevaIncidencia').on('click', function() {
        $('#modalTipoRegistro').modal('hide');
        setTimeout(function() {
            abrirModalIncidencia(null); // null para nuevo registro
        }, 300);
    });
    
    $('#btnNuevaDevolucion').on('click', function() {
        $('#modalTipoRegistro').modal('hide');
        setTimeout(function() {
            abrirModalDevolucion(null); // null para nuevo registro
        }, 300);
    });

    // Función para abrir modal de incidencia - NUEVA VERSIÓN SIMPLIFICADA
    // NUEVA FUNCIÓN PARA ABRIR MODAL DE INCIDENCIA - VERSIÓN MEJORADA
    function abrirModalIncidencia(incidenciaId) {
        console.log('=== ABRIENDO MODAL DE INCIDENCIA ===');
        console.log('ID:', incidenciaId);
        
        // Paso 1: Cerrar cualquier modal abierto
        $('.modal').modal('hide');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        
        // Paso 2: Esperar para asegurar que el anterior se cierre
        setTimeout(function() {
            // Paso 3: Limpiar y resetear formulario
            $('#formIncidencia')[0].reset();
            $('#incidencia_id').val('');
            
            // Paso 4: FORZAR HABILITACIÓN DE TODOS LOS CAMPOS
            forceEnableAllFields('#modalIncidencias');
            
            // Paso 5: Configurar título según el tipo de operación
            if (incidenciaId) {
                $('#modalIncidenciasLabel').html('<i class="fa fa-exclamation-triangle mr-2"></i>Editar Incidencia');
                $('#guardarIncidencia').html('<i class="fa fa-save mr-1"></i>Actualizar Incidencia');
                $('#incidencia_id').val(incidenciaId);
            } else {
                $('#modalIncidenciasLabel').html('<i class="fa fa-exclamation-triangle mr-2"></i>Nueva Incidencia');
                $('#guardarIncidencia').html('<i class="fa fa-save mr-1"></i>Guardar Incidencia');
                
                // Precargar valores por defecto
                $('#año_incidencia').val(window.filtroAño || new Date().getFullYear());
                if (window.filtroMes) {
                    $('#mes_incidencia').val(window.filtroMes);
                }
            }
            
            // Paso 6: Mostrar modal con configuración específica
            $('#modalIncidencias').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
            
            // Paso 7: Asegurar campos habilitados después de mostrar el modal
            setTimeout(function() {
                forceEnableAllFields('#modalIncidencias');
                
                // Paso 8: Cargar datos si es edición
                if (incidenciaId) {
                    cargarDatosIncidencia(incidenciaId);
                }
                
                console.log('Modal de incidencia abierto correctamente');
            }, 300);
            
        }, 100);
    }

    // NUEVA FUNCIÓN PARA ABRIR MODAL DE DEVOLUCIÓN - VERSIÓN MEJORADA
    function abrirModalDevolucion(devolucionId) {
        console.log('=== ABRIENDO MODAL DE DEVOLUCIÓN ===');
        console.log('ID:', devolucionId);
        
        // Paso 1: Cerrar cualquier modal abierto
        $('.modal').modal('hide');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        
        // Paso 2: Esperar para asegurar que el anterior se cierre
        setTimeout(function() {
            // Paso 3: Limpiar y resetear formulario
            $('#formDevolucion')[0].reset();
            $('#devolucion_id').val('');
            
            // Paso 4: FORZAR HABILITACIÓN DE TODOS LOS CAMPOS
            forceEnableAllFields('#modalDevoluciones');
            
            // Paso 5: Configurar título según el tipo de operación
            if (devolucionId) {
                $('#modalDevolucionesLabel').html('<i class="fa fa-undo mr-2"></i>Editar Devolución');
                $('#guardarDevolucion').html('<i class="fa fa-save mr-1"></i>Actualizar Devolución');
                $('#devolucion_id').val(devolucionId);
            } else {
                $('#modalDevolucionesLabel').html('<i class="fa fa-undo mr-2"></i>Nueva Devolución');
                $('#guardarDevolucion').html('<i class="fa fa-save mr-1"></i>Guardar Devolución');
                
                // Precargar valores por defecto
                $('#año_devolucion').val(window.filtroAño || new Date().getFullYear());
                if (window.filtroMes) {
                    $('#mes_devolucion').val(window.filtroMes);
                }
            }
            
            // Paso 6: Mostrar modal con configuración específica
            $('#modalDevoluciones').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
            
            // Paso 7: Asegurar campos habilitados después de mostrar el modal
            setTimeout(function() {
                forceEnableAllFields('#modalDevoluciones');
                
                // Paso 8: Cargar datos si es edición
                if (devolucionId) {
                    cargarDatosDevolucion(devolucionId);
                }
                
                console.log('Modal de devolución abierto correctamente');
            }, 300);
            
        }, 100);
    }

    // FUNCIÓN NUEVA PARA FORZAR HABILITACIÓN DE CAMPOS
    function forceEnableAllFields(modalSelector) {
        console.log('Forzando habilitación de campos en:', modalSelector);
        
        $(modalSelector + ' input, ' + modalSelector + ' select, ' + modalSelector + ' textarea, ' + modalSelector + ' button').each(function() {
            var $element = $(this);
            
            // Remover atributos que puedan deshabilitar el campo
            $element.prop('disabled', false)
                   .prop('readonly', false)
                   .removeAttr('disabled')
                   .removeAttr('readonly');
            
            // Forzar estilos CSS para que no se vea deshabilitado
            $element.css({
                'background-color': '#fff',
                'color': '#495057',
                'border': '1px solid #ced4da',
                'cursor': 'auto',
                'opacity': '1',
                'pointer-events': 'auto'
            });
            
            // Remover clases que puedan hacer que se vea deshabilitado
            $element.removeClass('disabled', 'readonly');
        });
        
        // Asegurar que el contenedor del modal también esté habilitado
        $(modalSelector).css('pointer-events', 'auto');
        $(modalSelector + ' .modal-content').css('pointer-events', 'auto');
        $(modalSelector + ' .modal-body').css('pointer-events', 'auto');
        
        console.log('Campos habilitados forzadamente en:', modalSelector);
    }
        
        // Cerrar cualquier modal abierto primero
        $('.modal').modal('hide');
        $('.modal-backdrop').remove();
        
        // Esperar un momento para que se cierre el anterior
        setTimeout(function() {
            // Limpiar formulario
            $('#formDevolucion')[0].reset();
            $('#devolucion_id').val('');
            
            // Asegurar que todos los campos estén habilitados
            $('#formDevolucion input, #formDevolucion select, #formDevolucion textarea').prop('disabled', false).prop('readonly', false);
            
            if (devolucionId) {
                // Cargar datos existentes
                $('#devolucion_id').val(devolucionId);
                cargarDatosDevolucion(devolucionId);
                $('#modalDevolucionesLabel').html('<i class="fa fa-undo mr-2"></i>Editar Devolución');
                $('#guardarDevolucion').html('<i class="fa fa-save mr-1"></i>Actualizar Devolución');
            } else {
                // Nuevo registro
                $('#modalDevolucionesLabel').html('<i class="fa fa-undo mr-2"></i>Nueva Devolución');
                $('#guardarDevolucion').html('<i class="fa fa-save mr-1"></i>Guardar Devolución');
                
                // Precargar valores por defecto
                $('#año_devolucion').val(window.filtroAño || new Date().getFullYear());
                if (window.filtroMes) {
                    $('#mes_devolucion').val(window.filtroMes);
                }
                if (window.filtroProveedor) {
                    $('#proveedor_devolucion option').each(function() {
                        if ($(this).text().includes(window.filtroProveedor)) {
                            $(this).prop('selected', true);
                            return false;
                        }
                    });
                }
            }
            
            // Mostrar modal
            $('#modalDevoluciones').modal('show');
            
        }, 200);
    }
    
    // Función para cargar datos de incidencia
    function cargarDatosIncidencia(incidenciaId) {
        console.log('Cargando datos de incidencia ID:', incidenciaId);
        $('#incidencia_id').val(incidenciaId);
        
        // Mostrar indicador de carga
        $('#modalIncidencias .modal-body').append('<div id="loading-indicator" class="text-center"><i class="fa fa-spinner fa-spin mr-2"></i>Cargando datos...</div>');
        
        // Hacer llamada AJAX para obtener datos completos
        $.ajax({
            url: window.obtenerIncidenciaUrl + '/' + incidenciaId,
            type: 'GET',
            success: function(data) {
                console.log('Respuesta del servidor:', data);
                
                if (data.success) {
                    var incidencia = data.incidencia;
                    
                    // Llenar todos los campos del formulario de incidencia
                    $('#proveedor_incidencia').val(incidencia.codigo_proveedor || incidencia.id_proveedor || '');
                    $('#año_incidencia').val(incidencia.año || '');
                    $('#mes_incidencia').val(incidencia.mes || '');
                    $('#codigo_producto_incidencia').val(incidencia.codigo_producto || '');
                    $('#descripcion_producto_incidencia').val(incidencia.descripcion_producto || '');
                    $('#fecha_inicio_incidencia').val(incidencia.fecha_inicio || '');
                    $('#fecha_fin_incidencia').val(incidencia.fecha_fin || '');
                    $('#np_incidencia').val(incidencia.np || '');
                    $('#fecha_reclamacion_incidencia').val(incidencia.fecha_reclamacion || '');
                    $('#clasificacion_incidencia_inc').val(incidencia.clasificacion_incidencia || '');
                    $('#tipo_reclamacion_incidencia').val(incidencia.tipo_reclamacion || '');
                    $('#top100fy2_incidencia').val(incidencia.top100fy2 || '');
                    $('#descripcion_motivo_incidencia').val(incidencia.descripcion_motivo || '');
                    $('#especificacion_motivo_leve_incidencia').val(incidencia.especificacion_motivo_reclamacion_leve || '');
                    $('#especificacion_motivo_grave_incidencia').val(incidencia.especificacion_motivo_reclamacion_grave || '');
                    $('#recuperamos_objeto_extraño_incidencia').val(incidencia.recuperamos_objeto_extraño || '');
                    $('#nombre_tienda_incidencia').val(incidencia.nombre_tienda || '');
                    $('#no_queja_incidencia').val(incidencia.no_queja || '');
                    $('#origen_incidencia').val(incidencia.origen || '');
                    $('#descripcion_queja_incidencia').val(incidencia.descripcion_queja || '');
                    $('#lote_sirena_incidencia').val(incidencia.lote_sirena || '');
                    $('#lote_proveedor_incidencia').val(incidencia.lote_proveedor || '');
                    $('#informe_a_proveedor_incidencia').val(incidencia.informe_a_proveedor || '');
                    $('#informe_incidencia').val(incidencia.informe || '');
                    $('#fecha_envio_proveedor_incidencia').val(incidencia.fecha_envio_proveedor || '');
                    $('#fecha_respuesta_proveedor_incidencia').val(incidencia.fecha_respuesta_proveedor || '');
                    $('#fecha_reclamacion_respuesta_incidencia').val(incidencia.fecha_reclamacion_respuesta || '');
                    $('#abierto_incidencia').val(incidencia.abierto || 'Si');
                    $('#informe_respuesta_incidencia').val(incidencia.informe_respuesta || '');
                    $('#comentarios_incidencia').val(incidencia.comentarios || '');
                    
                    // Asegurar que todos los campos estén habilitados
                    $('#modalIncidencias input, #modalIncidencias select, #modalIncidencias textarea').prop('disabled', false);
                    
                    console.log('Datos de incidencia cargados correctamente');
                } else {
                    console.error('Error al cargar datos de incidencia:', data.message);
                    showAlert('error', 'Error al cargar los datos de la incidencia: ' + (data.message || 'Error desconocido'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar incidencia:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                showAlert('error', 'Error al cargar los datos de la incidencia. Verifique la conexión.');
            },
            complete: function() {
                // Remover indicador de carga
                $('#loading-indicator').remove();
            }
        });
    }
    
    // Función para cargar datos de devolución
    function cargarDatosDevolucion(devolucionId) {
        console.log('Cargando datos de devolución ID:', devolucionId);
        $('#devolucion_id').val(devolucionId);
        
        // Mostrar indicador de carga
        $('#modalDevoluciones .modal-body').append('<div id="loading-indicator" class="text-center"><i class="fa fa-spinner fa-spin mr-2"></i>Cargando datos...</div>');
        
        // Hacer llamada AJAX para obtener datos completos
        $.ajax({
            url: window.obtenerDevolucionUrl + '/' + devolucionId,
            type: 'GET',
            success: function(data) {
                console.log('Respuesta del servidor:', data);
                
                if (data.success) {
                    var devolucion = data.devolucion;
                    
                    // Llenar todos los campos del formulario de devolución
                    $('#proveedor_devolucion').val(devolucion.codigo_proveedor || devolucion.id_proveedor || '');
                    $('#año_devolucion').val(devolucion.año || '');
                    $('#mes_devolucion').val(devolucion.mes || '');
                    $('#codigo_producto_devolucion').val(devolucion.codigo_producto || '');
                    $('#descripcion_producto_devolucion').val(devolucion.descripcion_producto || '');
                    $('#fecha_inicio').val(devolucion.fecha_inicio || '');
                    $('#fecha_fin').val(devolucion.fecha_fin || '');
                    $('#np').val(devolucion.np || '');
                    $('#fecha_reclamacion').val(devolucion.fecha_reclamacion || '');
                    $('#clasificacion_incidencia_dev').val(devolucion.clasificacion_incidencia || '');
                    $('#tipo_reclamacion').val(devolucion.tipo_reclamacion || '');
                    $('#top100fy2').val(devolucion.top100fy2 || '');
                    $('#descripcion_motivo').val(devolucion.descripcion_motivo || '');
                    $('#especificacion_motivo_leve').val(devolucion.especificacion_motivo_reclamacion_leve || '');
                    $('#especificacion_motivo_grave').val(devolucion.especificacion_motivo_reclamacion_grave || '');
                    $('#recuperamos_objeto_extraño').val(devolucion.recuperamos_objeto_extraño || '');
                    $('#nombre_tienda').val(devolucion.nombre_tienda || '');
                    $('#no_queja').val(devolucion.no_queja || '');
                    $('#origen_dev').val(devolucion.origen || '');
                    $('#descripcion_queja').val(devolucion.descripcion_queja || '');
                    $('#lote_sirena_dev').val(devolucion.lote_sirena || '');
                    $('#lote_proveedor_dev').val(devolucion.lote_proveedor || '');
                    $('#informe_a_proveedor_dev').val(devolucion.informe_a_proveedor || '');
                    $('#informe_dev').val(devolucion.informe || '');
                    $('#fecha_envio_proveedor_devolucion').val(devolucion.fecha_envio_proveedor || '');
                    $('#fecha_respuesta_proveedor_devolucion').val(devolucion.fecha_respuesta_proveedor || '');
                    $('#fecha_reclamacion_respuesta').val(devolucion.fecha_reclamacion_respuesta || '');
                    $('#abierto').val(devolucion.abierto || 'Si');
                    $('#informe_respuesta_dev').val(devolucion.informe_respuesta || '');
                    $('#comentarios_devolucion').val(devolucion.comentarios || '');
                    
                    // Asegurar que todos los campos estén habilitados
                    $('#modalDevoluciones input, #modalDevoluciones select, #modalDevoluciones textarea').prop('disabled', false);
                    
                    console.log('Datos de devolución cargados correctamente');
                } else {
                    console.error('Error al cargar datos de devolución:', data.message);
                    showAlert('error', 'Error al cargar los datos de la devolución: ' + (data.message || 'Error desconocido'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar devolución:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                showAlert('error', 'Error al cargar los datos de la devolución. Verifique la conexión.');
            },
            complete: function() {
                // Remover indicador de carga
                $('#loading-indicator').remove();
            }
        });
    }
    
    // Guardar incidencia
    $('#guardarIncidencia').on('click', function() {
        var formData = new FormData($('#formIncidencia')[0]);
        var isEdit = $('#incidencia_id').val() !== '';
        
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Guardando...');
        
        $.ajax({
            url: window.guardarIncidenciaUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', isEdit ? 'Incidencia actualizada correctamente' : 'Incidencia guardada correctamente');
                    $('#modalIncidencias').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', response.message || 'Error al guardar la incidencia');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al guardar la incidencia';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('error', errorMessage);
            },
            complete: function() {
                $('#guardarIncidencia').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Guardar Incidencia');
            }
        });
    });
    
    // Guardar devolución
    $('#guardarDevolucion').on('click', function() {
        var formData = new FormData($('#formDevolucion')[0]);
        var isEdit = $('#devolucion_id').val() !== '';
        
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Guardando...');
        
        $.ajax({
            url: window.guardarDevolucionUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', isEdit ? 'Devolución actualizada correctamente' : 'Devolución guardada correctamente');
                    $('#modalDevoluciones').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', response.message || 'Error al guardar la devolución');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al guardar la devolución';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('error', errorMessage);
            },
            complete: function() {
                $('#guardarDevolucion').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Guardar Devolución');
            }
        });
    });
    
    // Función para mostrar alertas
    function showAlert(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        var alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            '<i class="fa ' + icon + ' mr-2"></i>' + message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>');
        
        $('.col-12.bg-white').prepend(alert);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(function() {
            alert.fadeOut();
        }, 5000);
    }
    
    // Limpiar formularios cuando se cierran los modales
    $('#modalIncidencias').on('hidden.bs.modal', function() {
        $('#formIncidencia')[0].reset();
        $('#incidencia_id').val('');
        $('#loading-indicator').remove();
        // Limpiar cualquier backdrop residual
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });
    
    $('#modalDevoluciones').on('hidden.bs.modal', function() {
        $('#formDevolucion')[0].reset();
        $('#devolucion_id').val('');
        $('#loading-indicator').remove();
        // Limpiar cualquier backdrop residual
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });
    
    // Eventos específicos para asegurar que los modales funcionen correctamente
    $('#modalIncidencias').on('shown.bs.modal', function() {
        console.log('Modal incidencias mostrado correctamente');
        // Asegurar que todos los campos estén habilitados
        $('#formIncidencia input, #formIncidencia select, #formIncidencia textarea').prop('disabled', false);
        $(this).find('input, select, textarea').first().focus();
    });
    
    $('#modalDevoluciones').on('shown.bs.modal', function() {
        console.log('Modal devoluciones mostrado correctamente');
        // Asegurar que todos los campos estén habilitados
        $('#formDevolucion input, #formDevolucion select, #formDevolucion textarea').prop('disabled', false);
        $(this).find('input, select, textarea').first().focus();
    });
    
    // Mejorar manejo de eventos de teclado
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if ($('#modalIncidencias').hasClass('show')) {
                $('#modalIncidencias').modal('hide');
            }
            if ($('#modalDevoluciones').hasClass('show')) {
                $('#modalDevoluciones').modal('hide');
            }
        }
    });
    
    // Asegurar que hacer clic fuera del modal lo cierre pero no interfiera con el contenido
    $(document).on('click', '.modal', function(e) {
        if (e.target === this) {
            $(this).modal('hide');
        }
    });
    
    // ============================================================================
    // FUNCIÓN ESPECIAL PARA FORZAR HABILITACIÓN DE CAMPOS - SOLUCIÓN DEFINITIVA
    // ============================================================================
    function forceEnableAllModalFields() {
        // Función que se ejecuta cada vez que se abre un modal
        setTimeout(function() {
            // Para modal de incidencias
            $('#modalIncidencias input, #modalIncidencias select, #modalIncidencias textarea').each(function() {
                var $this = $(this);
                
                // Remover todos los atributos que puedan deshabilitar
                $this.removeAttr('disabled')
                     .removeAttr('readonly')
                     .prop('disabled', false)
                     .prop('readonly', false);
                
                // Forzar estilos CSS para que se vea habilitado
                $this.css({
                    'background-color': '#ffffff !important',
                    'color': '#495057 !important',
                    'border': '1px solid #ced4da !important',
                    'cursor': 'text !important',
                    'opacity': '1 !important',
                    'pointer-events': 'auto !important'
                });
                
                // Remover clases que puedan hacer que se vea deshabilitado
                $this.removeClass('disabled').removeClass('readonly');
            });
            
            // Para modal de devoluciones
            $('#modalDevoluciones input, #modalDevoluciones select, #modalDevoluciones textarea').each(function() {
                var $this = $(this);
                
                // Remover todos los atributos que puedan deshabilitar
                $this.removeAttr('disabled')
                     .removeAttr('readonly')
                     .prop('disabled', false)
                     .prop('readonly', false);
                
                // Forzar estilos CSS para que se vea habilitado
                $this.css({
                    'background-color': '#ffffff !important',
                    'color': '#495057 !important',
                    'border': '1px solid #ced4da !important',
                    'cursor': 'text !important',
                    'opacity': '1 !important',
                    'pointer-events': 'auto !important'
                });
                
                // Remover clases que puedan hacer que se vea deshabilitado
                $this.removeClass('disabled').removeClass('readonly');
            });
            
            console.log('🔧 Todos los campos forzados a estar habilitados');
        }, 100);
    }
    
    // Ejecutar la función cada vez que se abre un modal
    $('#modalIncidencias').on('shown.bs.modal', function() {
        forceEnableAllModalFields();
    });
    
    $('#modalDevoluciones').on('shown.bs.modal', function() {
        forceEnableAllModalFields();
    });
    
    // También ejecutar cuando se hace clic en cualquier campo
    $(document).on('click', '#modalIncidencias input, #modalIncidencias select, #modalIncidencias textarea', function() {
        forceEnableAllModalFields();
    });
    
    $(document).on('click', '#modalDevoluciones input, #modalDevoluciones select, #modalDevoluciones textarea', function() {
        forceEnableAllModalFields();
    });
    
    // Ejecutar cada 2 segundos mientras el modal esté abierto
    setInterval(function() {
        if ($('#modalIncidencias').hasClass('show') || $('#modalDevoluciones').hasClass('show')) {
            forceEnableAllModalFields();
        }
    }, 2000);
});

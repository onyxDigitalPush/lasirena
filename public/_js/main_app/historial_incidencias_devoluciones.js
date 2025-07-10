$(document).ready(function () {
    // Función para limpiar completamente los modales
    function limpiarModales() {
        $('.modal').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
        $('#limpiarModales').hide();
    }
    
    // Limpiar modales al cargar la página
    limpiarModales();
    
    // Botón de emergencia para limpiar modales
    $('#limpiarModales').on('click', function() {
        limpiarModales();
        console.log('Modales limpiados manualmente');
    });
    
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

    // Función para abrir modal de incidencia
    function abrirModalIncidencia(incidenciaId) {
        console.log('Abriendo modal incidencia para ID:', incidenciaId);
        
        // Cerrar cualquier modal que esté abierto
        $('.modal').modal('hide');
        
        // Esperar a que se cierre antes de abrir el nuevo
        setTimeout(function() {
            // Limpiar formulario
            $('#formIncidencia')[0].reset();
            $('#incidencia_id').val('');
            
            if (incidenciaId) {
                // Cargar datos de la incidencia existente
                cargarDatosIncidencia(incidenciaId);
                $('#modalIncidenciasLabel').html('<i class="fa fa-exclamation-triangle mr-2"></i>Editar Incidencia');
                $('#guardarIncidencia').html('<i class="fa fa-save mr-1"></i>Actualizar Incidencia');
            } else {
                // Nuevo registro
                $('#modalIncidenciasLabel').html('<i class="fa fa-exclamation-triangle mr-2"></i>Nueva Incidencia');
                $('#guardarIncidencia').html('<i class="fa fa-save mr-1"></i>Guardar Incidencia');
                
                // Precargar año y mes actuales
                $('#año_incidencia').val(window.filtroAño || new Date().getFullYear());
                if (window.filtroMes) {
                    $('#mes_incidencia').val(window.filtroMes);
                }
            }
            
            // Limpiar cualquier backdrop que pueda haber quedado
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Mostrar modal con configuración específica
            $('#modalIncidencias').modal({
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Mostrar botón de emergencia
            $('#limpiarModales').show();
            
            // Forzar el foco en el modal
            setTimeout(function() {
                $('#modalIncidencias').focus();
                // Asegurar que el modal esté completamente visible
                $('#modalIncidencias').css({
                    'display': 'block',
                    'opacity': '1'
                });
            }, 100);
        }, 300);
    }
    
    // Función para abrir modal de devolución
    function abrirModalDevolucion(devolucionId) {
        console.log('Abriendo modal devolución para ID:', devolucionId);
        
        // Cerrar cualquier modal que esté abierto
        $('.modal').modal('hide');
        
        // Esperar a que se cierre antes de abrir el nuevo
        setTimeout(function() {
            // Limpiar formulario
            $('#formDevolucion')[0].reset();
            $('#devolucion_id').val('');
            
            if (devolucionId) {
                // Cargar datos de la devolución existente
                cargarDatosDevolucion(devolucionId);
                $('#modalDevolucionesLabel').html('<i class="fa fa-undo mr-2"></i>Editar Devolución');
                $('#guardarDevolucion').html('<i class="fa fa-save mr-1"></i>Actualizar Devolución');
            } else {
                // Nuevo registro
                $('#modalDevolucionesLabel').html('<i class="fa fa-undo mr-2"></i>Nueva Devolución');
                $('#guardarDevolucion').html('<i class="fa fa-save mr-1"></i>Guardar Devolución');
                
                // Precargar año y mes actuales
                $('#año_devolucion').val(window.filtroAño || new Date().getFullYear());
                if (window.filtroMes) {
                    $('#mes_devolucion').val(window.filtroMes);
                }
            }
            
            // Limpiar cualquier backdrop que pueda haber quedado
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Mostrar modal con configuración específica
            $('#modalDevoluciones').modal({
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Mostrar botón de emergencia
            $('#limpiarModales').show();
            
            // Forzar el foco en el modal
            setTimeout(function() {
                $('#modalDevoluciones').focus();
                // Asegurar que el modal esté completamente visible
                $('#modalDevoluciones').css({
                    'display': 'block',
                    'opacity': '1'
                });
            }, 100);
        }, 300);
    }
    
    // Función para cargar datos de incidencia
    function cargarDatosIncidencia(incidenciaId) {
        console.log('Cargando datos de incidencia ID:', incidenciaId);
        $('#incidencia_id').val(incidenciaId);
        
        // Buscar datos en la tabla actual
        var fila = $('tr[data-tipo="incidencia"][data-id="' + incidenciaId + '"]');
        if (fila.length > 0) {
            // Extraer datos de la fila si están disponibles
            var proveedorId = fila.data('proveedor-id');
            
            // Precargar proveedor si está disponible
            if (proveedorId) {
                $('#proveedor_incidencia').val(proveedorId);
            }
            
            // Hacer llamada AJAX para obtener datos completos
            $.ajax({
                url: window.obtenerIncidenciaUrl + '/' + incidenciaId,
                type: 'GET',
                success: function(data) {
                    if (data.success) {
                        var incidencia = data.incidencia;
                        
                        // Llenar todos los campos del formulario
                        $('#proveedor_incidencia').val(incidencia.id_proveedor || '');
                        $('#año_incidencia').val(incidencia.año || '');
                        $('#mes_incidencia').val(incidencia.mes || '');
                        $('#clasificacion_incidencia').val(incidencia.clasificacion_incidencia || '');
                        $('#origen').val(incidencia.origen || '');
                        $('#fecha_incidencia').val(incidencia.fecha_incidencia || '');
                        $('#numero_inspeccion_sap').val(incidencia.numero_inspeccion_sap || '');
                        $('#resolucion_almacen').val(incidencia.resolucion_almacen || '');
                        $('#descripcion_incidencia').val(incidencia.descripcion_incidencia || '');
                        $('#producto').val(incidencia.producto || '');
                        $('#lote').val(incidencia.lote || '');
                        $('#caducidad').val(incidencia.caducidad || '');
                        $('#cantidad_kg').val(incidencia.cantidad_kg || '');
                        $('#cantidad_unidades').val(incidencia.cantidad_unidades || '');
                        $('#proveedor_alternativo').val(incidencia.proveedor_alternativo || '');
                        $('#dias_sin_servicio').val(incidencia.dias_sin_servicio || '');
                        $('#fecha_envio_proveedor').val(incidencia.fecha_envio_proveedor || '');
                        $('#fecha_respuesta_proveedor').val(incidencia.fecha_respuesta_proveedor || '');
                        $('#comentarios').val(incidencia.comentarios || '');
                        
                        console.log('Datos de incidencia cargados correctamente');
                    } else {
                        console.error('Error al cargar datos de incidencia:', data.message);
                        showAlert('error', 'Error al cargar los datos de la incidencia');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al cargar incidencia:', error);
                    showAlert('error', 'Error al cargar los datos de la incidencia');
                }
            });
        } else {
            console.error('No se encontró la fila en la tabla');
        }
    }
    
    // Función para cargar datos de devolución
    function cargarDatosDevolucion(devolucionId) {
        console.log('Cargando datos de devolución ID:', devolucionId);
        $('#devolucion_id').val(devolucionId);
        
        // Buscar datos en la tabla actual
        var fila = $('tr[data-tipo="devolucion"][data-id="' + devolucionId + '"]');
        if (fila.length > 0) {
            // Extraer datos de la fila si están disponibles
            var proveedorId = fila.data('proveedor-id');
            
            // Precargar proveedor si está disponible
            if (proveedorId) {
                $('#proveedor_devolucion').val(proveedorId);
            }
            
            // Hacer llamada AJAX para obtener datos completos
            $.ajax({
                url: window.obtenerDevolucionUrl + '/' + devolucionId,
                type: 'GET',
                success: function(data) {
                    if (data.success) {
                        var devolucion = data.devolucion;
                        
                        // Llenar todos los campos del formulario
                        $('#proveedor_devolucion').val(devolucion.codigo_proveedor || '');
                        $('#año_devolucion').val(devolucion.año || '');
                        $('#mes_devolucion').val(devolucion.mes || '');
                        $('#fecha_devolucion').val(devolucion.fecha_devolucion || '');
                        $('#motivo_devolucion').val(devolucion.motivo_devolucion || '');
                        $('#codigo_producto_devolucion').val(devolucion.codigo_producto || '');
                        $('#descripcion_producto_devolucion').val(devolucion.descripcion_producto || '');
                        $('#lote_devolucion').val(devolucion.lote || '');
                        $('#caducidad_devolucion').val(devolucion.caducidad || '');
                        $('#cantidad_kg_devolucion').val(devolucion.cantidad_kg || '');
                        $('#cantidad_unidades_devolucion').val(devolucion.cantidad_unidades || '');
                        $('#observaciones_devolucion').val(devolucion.observaciones || '');
                        $('#fecha_envio_proveedor_devolucion').val(devolucion.fecha_envio_proveedor || '');
                        $('#fecha_respuesta_proveedor_devolucion').val(devolucion.fecha_respuesta_proveedor || '');
                        $('#abierto').val(devolucion.abierto || 'Sí');
                        
                        console.log('Datos de devolución cargados correctamente');
                    } else {
                        console.error('Error al cargar datos de devolución:', data.message);
                        showAlert('error', 'Error al cargar los datos de la devolución');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al cargar devolución:', error);
                    showAlert('error', 'Error al cargar los datos de la devolución');
                }
            });
        } else {
            console.error('No se encontró la fila en la tabla');
        }
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
        // Limpiar cualquier backdrop residual
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
    
    $('#modalDevoluciones').on('hidden.bs.modal', function() {
        $('#formDevolucion')[0].reset();
        $('#devolucion_id').val('');
        // Limpiar cualquier backdrop residual
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
    
    // Eventos específicos para asegurar que los modales funcionen correctamente
    $('#modalIncidencias').on('shown.bs.modal', function() {
        console.log('Modal incidencias mostrado correctamente');
        $(this).focus();
    });
    
    $('#modalDevoluciones').on('shown.bs.modal', function() {
        console.log('Modal devoluciones mostrado correctamente');
        $(this).focus();
    });
    
    // Asegurar que el ESC cierre el modal
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.modal').modal('hide');
        }
    });
    
    // Asegurar que hacer clic fuera del modal lo cierre
    $(document).on('click', '.modal-backdrop', function() {
        $('.modal').modal('hide');
    });
});

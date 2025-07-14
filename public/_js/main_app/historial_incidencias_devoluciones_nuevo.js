$(document).ready(function () {
    console.log('Iniciando historial incidencias y devoluciones...');
    
    // Limpiar modales al inicio
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
    
    // Configurar DataTable
    var table = $('#table_historial').DataTable({
        paging: true,
        pageLength: 25,
        searching: true,
        ordering: true,
        info: true,
        responsive: true,
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron coincidencias",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });
    
    // ============================================================================
    // FUNCIÓN ESPECIAL PARA FORZAR HABILITACIÓN DE CAMPOS - SOLUCIÓN DEFINITIVA
    // ============================================================================
    function forceEnableAllModalFields() {
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
    
    // Event listeners para filas de la tabla
    $(document).on('click', '.registro-fila', function() {
        var tipo = $(this).data('tipo');
        var id = $(this).data('id');
        
        if (tipo === 'incidencia') {
            abrirModalIncidencia(id);
        } else if (tipo === 'devolucion') {
            abrirModalDevolucion(id);
        }
    });
    
    // Botones para nuevo registro
    $('#nuevoRegistro').on('click', function() {
        $('#modalTipoRegistro').modal('show');
    });
    
    $('#btnNuevaIncidencia').on('click', function() {
        $('#modalTipoRegistro').modal('hide');
        setTimeout(function() {
            abrirModalIncidencia(null);
        }, 300);
    });
    
    $('#btnNuevaDevolucion').on('click', function() {
        $('#modalTipoRegistro').modal('hide');
        setTimeout(function() {
            abrirModalDevolucion(null);
        }, 300);
    });
    
    // ============================================================================
    // FUNCIONES PRINCIPALES PARA ABRIR MODALES
    // ============================================================================
    function abrirModalIncidencia(incidenciaId) {
        console.log('=== ABRIENDO MODAL DE INCIDENCIA ===');
        console.log('ID:', incidenciaId);
        
        // Cerrar cualquier modal abierto
        $('.modal').modal('hide');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        
        setTimeout(function() {
            // Limpiar formulario
            $('#formIncidencia')[0].reset();
            $('#incidencia_id').val('');
            
            // Configurar título
            if (incidenciaId) {
                $('#modalIncidenciasLabel').html('<i class="fa fa-exclamation-triangle mr-2"></i>Editar Incidencia');
                $('#incidencia_id').val(incidenciaId);
            } else {
                $('#modalIncidenciasLabel').html('<i class="fa fa-exclamation-triangle mr-2"></i>Nueva Incidencia');
            }
            
            // Mostrar modal
            $('#modalIncidencias').modal('show');
            
            // Forzar habilitación de campos
            setTimeout(function() {
                forceEnableAllModalFields();
                
                // Cargar datos si es edición
                if (incidenciaId) {
                    cargarDatosIncidencia(incidenciaId);
                }
            }, 300);
            
        }, 100);
    }
    
    function abrirModalDevolucion(devolucionId) {
        console.log('=== ABRIENDO MODAL DE DEVOLUCIÓN ===');
        console.log('ID:', devolucionId);
        
        // Cerrar cualquier modal abierto
        $('.modal').modal('hide');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        
        setTimeout(function() {
            // Limpiar formulario
            $('#formDevolucion')[0].reset();
            $('#devolucion_id').val('');
            
            // Configurar título
            if (devolucionId) {
                $('#modalDevolucionesLabel').html('<i class="fa fa-undo mr-2"></i>Editar Devolución');
                $('#devolucion_id').val(devolucionId);
            } else {
                $('#modalDevolucionesLabel').html('<i class="fa fa-undo mr-2"></i>Nueva Devolución');
            }
            
            // Mostrar modal
            $('#modalDevoluciones').modal('show');
            
            // Forzar habilitación de campos
            setTimeout(function() {
                forceEnableAllModalFields();
                
                // Cargar datos si es edición
                if (devolucionId) {
                    cargarDatosDevolucion(devolucionId);
                }
            }, 300);
            
        }, 100);
    }
    
    // ============================================================================
    // FUNCIONES DE CARGA DE DATOS
    // ============================================================================
    function cargarDatosIncidencia(incidenciaId) {
        console.log('Cargando datos de incidencia ID:', incidenciaId);
        
        var url = window.obtenerIncidenciaUrl + '/' + incidenciaId;
        console.log('URL:', url);
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    var incidencia = data.incidencia;
                    
                    // Llenar todos los campos del formulario de incidencia
                    $('#proveedor_incidencia').val(incidencia.codigo_proveedor || incidencia.id_proveedor || '');
                    $('#año_incidencia').val(incidencia.año || '');
                    $('#mes_incidencia').val(incidencia.mes || '');
                    $('#clasificacion_incidencia').val(incidencia.clasificacion_incidencia || '');
                    $('#origen').val(incidencia.origen || '');
                    $('#fecha_incidencia').val(incidencia.fecha_incidencia || '');
                    $('#numero_inspeccion_sap').val(incidencia.numero_inspeccion_sap || '');
                    $('#resolucion_almacen').val(incidencia.resolucion_almacen || '');
                    $('#descripcion_incidencia').val(incidencia.descripcion_incidencia || '');
                    $('#producto').val(incidencia.producto || '');
                    $('#codigo').val(incidencia.codigo || '');
                    $('#lote').val(incidencia.lote || '');
                    $('#caducidad').val(incidencia.caducidad || '');
                    $('#cantidad_kg').val(incidencia.cantidad_kg || '');
                    $('#cantidad_unidades').val(incidencia.cantidad_unidades || '');
                    $('#proveedor_alternativo').val(incidencia.proveedor_alternativo || '');
                    $('#dias_sin_servicio').val(incidencia.dias_sin_servicio || '');
                    $('#fecha_envio_proveedor').val(incidencia.fecha_envio_proveedor || '');
                    $('#fecha_respuesta_proveedor').val(incidencia.fecha_respuesta_proveedor || '');
                    $('#comentarios').val(incidencia.comentarios || '');
                    
                    // Forzar habilitación después de cargar datos
                    setTimeout(function() {
                        forceEnableAllModalFields();
                    }, 200);
                    
                    console.log('Datos de incidencia cargados correctamente');
                } else {
                    console.error('Error al cargar datos de incidencia:', data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar incidencia:', error);
            }
        });
    }
    
    function cargarDatosDevolucion(devolucionId) {
        console.log('Cargando datos de devolución ID:', devolucionId);
        
        var url = window.obtenerDevolucionUrl + '/' + devolucionId;
        console.log('URL:', url);
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                console.log('Datos recibidos:', data);
                
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
                    
                    // Forzar habilitación después de cargar datos
                    setTimeout(function() {
                        forceEnableAllModalFields();
                    }, 200);
                    
                    console.log('Datos de devolución cargados correctamente');
                } else {
                    console.error('Error al cargar datos de devolución:', data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar devolución:', error);
            }
        });
    }
    
    // ============================================================================
    // EVENT LISTENERS PARA MODALES
    // ============================================================================
    
    // Ejecutar cuando se muestran los modales
    $('#modalIncidencias').on('shown.bs.modal', function() {
        forceEnableAllModalFields();
    });
    
    $('#modalDevoluciones').on('shown.bs.modal', function() {
        forceEnableAllModalFields();
    });
    
    // Ejecutar cuando se hace clic en cualquier campo
    $(document).on('click', '#modalIncidencias input, #modalIncidencias select, #modalIncidencias textarea', function() {
        forceEnableAllModalFields();
    });
    
    $(document).on('click', '#modalDevoluciones input, #modalDevoluciones select, #modalDevoluciones textarea', function() {
        forceEnableAllModalFields();
    });
    
    // Ejecutar periódicamente mientras los modales estén abiertos
    setInterval(function() {
        if ($('#modalIncidencias').hasClass('show') || $('#modalDevoluciones').hasClass('show')) {
            forceEnableAllModalFields();
        }
    }, 2000);
    
    // ============================================================================
    // FILTROS
    // ============================================================================
    
    $('#aplicarFiltros').on('click', function() {
        var form = $('#filtrosForm');
        var baseUrl = window.location.href.split('?')[0];
        var params = form.serialize();
        
        if (params) {
            window.location.href = baseUrl + '?' + params;
        } else {
            window.location.href = baseUrl;
        }
    });
    
    $('#limpiarFiltros').on('click', function() {
        $('#filtrosForm')[0].reset();
        var baseUrl = window.location.href.split('?')[0];
        window.location.href = baseUrl;
    });
    
    console.log('JavaScript de historial incidencias y devoluciones cargado correctamente');
});

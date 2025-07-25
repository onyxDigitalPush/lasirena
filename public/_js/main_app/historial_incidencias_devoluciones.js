$(document).ready(function () {
    console.log('Iniciando historial incidencias y devoluciones...');
    
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
    
    // Event listeners para filas de la tabla
    $(document).on('click', '.registro-fila', function() {
        var tipo = $(this).data('tipo');
        var id = $(this).data('id');
        
        console.log('Clic en fila:', tipo, id);
        
        if (tipo === 'incidencia') {
            abrirModalIncidencia(id);
        } else if (tipo === 'devolucion') {
            abrirModalDevolucion(id);
        }
    });
    
    // Botones para nuevo registro
    $('#nuevoRegistro').on('click', function() {
        console.log('Botón nuevo registro');
        $('#modalTipoRegistro').modal('show');
    });
    
    $('#btnNuevaIncidencia').on('click', function() {
        console.log('Nueva incidencia');
        $('#modalTipoRegistro').modal('hide');
        setTimeout(function() {
            abrirModalIncidencia(null);
        }, 300);
    });
    
    $('#btnNuevaDevolucion').on('click', function() {
        console.log('Nueva devolución');
        $('#modalTipoRegistro').modal('hide');
        setTimeout(function() {
            abrirModalDevolucion(null);
        }, 300);
    });
    
    // Funciones para abrir modales
    function abrirModalIncidencia(incidenciaId) {
        console.log('Abriendo modal incidencia:', incidenciaId);
        
        try {
            // Cerrar todos los modales primero
            $('.modal').modal('hide');
            
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
            
            // Mostrar modal con configuración específica
            $('#modalIncidencias').modal({
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Cargar datos después de que el modal se haya mostrado
            $('#modalIncidencias').on('shown.bs.modal', function() {
                console.log('Modal incidencia mostrado');
                if (incidenciaId) {
                    cargarDatosIncidencia(incidenciaId);
                }
            });
            
            console.log('Modal incidencia configurado');
            
        } catch (error) {
            console.error('Error al abrir modal incidencia:', error);
        }
    }
    
    function abrirModalDevolucion(devolucionId) {
        console.log('Abriendo modal devolución:', devolucionId);
        
        try {
            // Cerrar todos los modales primero
            $('.modal').modal('hide');
            
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
            
            // Mostrar modal con configuración específica
            $('#modalDevoluciones').modal({
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Cargar datos después de que el modal se haya mostrado
            $('#modalDevoluciones').on('shown.bs.modal', function() {
                console.log('Modal devolución mostrado');
                if (devolucionId) {
                    cargarDatosDevolucion(devolucionId);
                }
            });
            
            console.log('Modal devolución configurado');
            
        } catch (error) {
            console.error('Error al abrir modal devolución:', error);
        }
    }
    
    // Funciones para cargar datos
    function cargarDatosIncidencia(incidenciaId) {
        console.log('Cargando datos de incidencia ID:', incidenciaId);
        
        if (!incidenciaId) return;
        
        var url = window.obtenerIncidenciaUrl + '/' + incidenciaId;
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    var incidencia = data.incidencia;
                    
                    // Llenar campos del formulario
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
        
        if (!devolucionId) return;
        
        var url = window.obtenerDevolucionUrl + '/' + devolucionId;
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    var devolucion = data.devolucion;
                    
                    // Llenar campos del formulario
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
    
    // Filtros
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
    
    // Guardar incidencia
    $('#guardarIncidencia').on('click', function() {
        console.log('Guardando incidencia...');
        
        var form = $('#formIncidencia');
        var formData = form.serialize();
        
        $.ajax({
            url: window.guardarIncidenciaUrl,
            method: 'POST',
            data: formData,
            success: function(response) {
                console.log('Respuesta guardar incidencia:', response);
                if (response.success) {
                    alert('Incidencia guardada correctamente');
                    $('#modalIncidencias').modal('hide');
                    location.reload();
                } else {
                    alert('Error al guardar la incidencia: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                alert('Error al guardar la incidencia');
            }
        });
    });
    
    // Guardar devolución
    $('#guardarDevolucion').on('click', function() {
        console.log('Guardando devolución...');
        
        var form = $('#formDevolucion');
        var formData = form.serialize();
        
        $.ajax({
            url: window.guardarDevolucionUrl,
            method: 'POST',
            data: formData,
            success: function(response) {
                console.log('Respuesta guardar devolución:', response);
                if (response.success) {
                    alert('Devolución guardada correctamente');
                    $('#modalDevoluciones').modal('hide');
                    location.reload();
                } else {
                    alert('Error al guardar la devolución: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                alert('Error al guardar la devolución');
            }
        });
    });
    
    // Hacer funciones disponibles globalmente para depuración
    window.abrirModalIncidencia = abrirModalIncidencia;
    window.abrirModalDevolucion = abrirModalDevolucion;
    
    // Event listeners adicionales para asegurar que los modales funcionen
    $('.modal').on('hidden.bs.modal', function () {
        console.log('Modal cerrado');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
    
    // Asegurar que los botones de cerrar funcionen
    $('.modal .close, .modal [data-dismiss="modal"]').on('click', function() {
        console.log('Botón cerrar clickeado');
        $(this).closest('.modal').modal('hide');
    });
    
    // Cerrar modal con ESC
    $(document).keyup(function(e) {
        if (e.keyCode === 27) { // ESC key
            $('.modal').modal('hide');
        }
    });
    
    console.log('JavaScript de historial incidencias y devoluciones cargado correctamente');
});

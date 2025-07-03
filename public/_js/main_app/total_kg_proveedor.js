$(document).ready(function () {
    console.log('jQuery y DataTables cargados correctamente');
    
    // Verificar que Bootstrap esté cargado
    if (typeof $.fn.modal === 'undefined') {
        console.error('Bootstrap modal no está cargado');
    } else {
        console.log('Bootstrap modal disponible');
    }
    
    var table = $('#table_total_kg_proveedor').DataTable({
        paging: true,
        pageLength: 25,
        info: true,
        ordering: true,
        searching: true,
        orderCellsTop: false, // Cambiado para headers complejos
    
    // ...existing code...ra headers complejos
        fixedHeader: false, // Deshabilitado temporalmente
        order: [[2, 'desc']], // Ordenar por Total KG descendente por defecto
        columnDefs: [
            {
                targets: [0, 1], // ID Proveedor y Nombre Proveedor - searchable
                orderable: true,
                searchable: true
            },
            {
                targets: [2, 3, 4], // Columnas Total KG, Cantidad Registros y Porcentaje
                orderable: true,
                searchable: false
            },
            {
                targets: [5, 6, 7, 8, 9], // Columnas de métricas (RG1, RL1, DEV1, ROK1, RET1)
                orderable: false,
                searchable: false
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
    });    // Función para actualizar totales basado en filas filtradas
    function actualizarTotales() {
        var filasVisibles = table.rows({ filter: 'applied' }).data();
        var totalProveedores = filasVisibles.length;
        var totalKg = 0;
        
        // Calcular suma de KG de las filas visibles
        filasVisibles.each(function(data, index) {
            // La columna 2 contiene el total KG (necesitamos extraer el número del badge)
            var kgText = $(data[2]).text() || data[2];
            var kgValue = parseFloat(kgText.replace(/[^\d.-]/g, '')) || 0;
            totalKg += kgValue;
        });
        
        // Actualizar los elementos en la interfaz usando los IDs específicos
        $('#total-proveedores').text(totalProveedores);
        $('#total-kg-general').text(new Intl.NumberFormat('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(totalKg) + ' kg');
        
        // Actualizar porcentajes de las filas visibles
        actualizarPorcentajes(totalKg);
    }
    
    // Función para actualizar porcentajes basado en el nuevo total
    function actualizarPorcentajes(totalKgFiltrado) {
        if (totalKgFiltrado <= 0) return;
        
        table.rows({ filter: 'applied' }).every(function() {
            var data = this.data();
            var node = this.node();
            
            // Extraer el valor de KG de la fila
            var kgText = $(data[2]).text() || data[2];
            var kgValue = parseFloat(kgText.replace(/[^\d.-]/g, '')) || 0;
            
            // Calcular nuevo porcentaje
            var nuevoPorcentaje = (kgValue / totalKgFiltrado) * 100;
            
            // Actualizar la celda de porcentaje (columna 4)
            var $progressContainer = $(node).find('td:eq(4) .progress');
            var $progressBar = $progressContainer.find('.progress-bar');
            var $progressText = $progressContainer.find('.position-absolute');
            
            // Actualizar la barra de progreso
            var anchoMinimo = Math.max(nuevoPorcentaje, 1);
            $progressBar.css('width', anchoMinimo + '%');
            $progressBar.attr('aria-valuenow', nuevoPorcentaje);
            
            // Actualizar el color de la barra según el porcentaje
            $progressBar.removeClass('bg-success bg-warning bg-info');
            if (nuevoPorcentaje >= 50) {
                $progressBar.addClass('bg-success');
                $progressText.css('color', 'white');
            } else if (nuevoPorcentaje >= 25) {
                $progressBar.addClass('bg-warning');
                $progressText.css('color', '#333');
            } else {
                $progressBar.addClass('bg-info');
                $progressText.css('color', '#333');
            }
            
            // Actualizar el texto del porcentaje
            $progressText.text(nuevoPorcentaje.toFixed(1) + '%');
        });
    }    // Aplica los filtros de las celdas con inputs (búsqueda directa)
    $('#table_total_kg_proveedor thead input').each(function() {
        var $input = $(this);
        var columnIndex = $input.closest('th').index();
        
        console.log('Configurando filtro para columna:', columnIndex, 'Placeholder:', $input.attr('placeholder'));
        
        $input.on('keyup change', function() {
            var searchValue = this.value.trim();
            console.log('Filtrando columna', columnIndex, 'con valor:', searchValue);
            
            if (table.column(columnIndex).search() !== searchValue) {
                table.column(columnIndex).search(searchValue).draw();
                
                // Actualizar totales después de filtrar
                setTimeout(function() {
                    actualizarTotales();
                }, 100);
            }
        });
    });
      // También actualizar totales cuando se use el buscador general
    table.on('search.dt', function() {
        actualizarTotales();
    });
    
    // Actualizar totales cuando se redibuje la tabla
    table.on('draw.dt', function() {
        actualizarTotales();
    });
    
    // Actualizar totales al cargar la página inicialmente
    actualizarTotales();
      // Funcionalidad para filtros
    $('#aplicarFiltros').on('click', function() {
        var mes = $('#filtro_mes').val();
        var año = $('#filtro_año').val();
        
        // Construir URL con parámetros
        var url = new URL(window.location.href);
        
        if (mes) {
            url.searchParams.set('mes', mes);
        } else {
            url.searchParams.delete('mes');
        }
        
        if (año) {
            url.searchParams.set('año', año);
        } else {
            url.searchParams.delete('año');
        }
        
        // Recargar página con nuevos filtros
        window.location.href = url.toString();
    });
    
    // Función para controlar la visibilidad del botón Guardar Métricas
    function controlarBotonGuardarMetricas() {
        var mes = $('#filtro_mes').val();
        var $botonGuardar = $('#guardarMetricas');
        
        if (!mes) {
            $botonGuardar.prop('disabled', true);
            $botonGuardar.attr('title', 'Debe seleccionar un mes específico para guardar métricas');
            $botonGuardar.removeClass('btn-success').addClass('btn-secondary');
        } else {
            $botonGuardar.prop('disabled', false);
            $botonGuardar.attr('title', 'Guardar Métricas');
            $botonGuardar.removeClass('btn-secondary').addClass('btn-success');
        }
    }
    
    // Controlar el botón al cambiar el mes
    $('#filtro_mes').on('change', controlarBotonGuardarMetricas);
    
    // Controlar el botón al cargar la página
    controlarBotonGuardarMetricas();
    
    $('#limpiarFiltros').on('click', function() {
        $('#filtro_mes').val('');
        $('#filtro_año').val('');
        
        // Remover parámetros de URL y recargar
        var url = new URL(window.location.href);
        url.searchParams.delete('mes');
        url.searchParams.delete('año');
        window.location.href = url.toString();
    });
      // Funcionalidad para guardar métricas
    $('#guardarMetricas').on('click', function() {
        var mes = $('#filtro_mes').val();
        var año = $('#filtro_año').val();
        
        if (!año) {
            Swal.fire({
                icon: 'warning',
                title: 'Año requerido',
                text: 'Debe seleccionar el año.',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        if (!mes) {
            Swal.fire({
                icon: 'warning',
                title: 'Mes requerido para guardar métricas',
                text: 'Debe seleccionar un mes específico para guardar las métricas. No es posible guardar métricas para "Todos los meses".',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // Recopilar datos de métricas
        var metricas = {};
        var hasData = false;
        
        $('.metrica-input').each(function() {
            var $input = $(this);
            var proveedorId = $input.data('proveedor');
            var metrica = $input.data('metrica');
            var valor = $input.val();
            
            if (!metricas[proveedorId]) {
                metricas[proveedorId] = {};
            }
            
            if (valor && valor.trim() !== '') {
                metricas[proveedorId][metrica] = parseFloat(valor);
                hasData = true;
            } else {
                metricas[proveedorId][metrica] = null;
            }        });
        
        if (!hasData) {
            $.confirm({
                title: 'Sin datos',
                content: 'No hay métricas para guardar.',
                type: 'blue',
                buttons: {
                    entendido: {
                        text: 'Entendido',
                        btnClass: 'btn-blue'
                    }
                }
            });
            return;
        }
        
        // Mostrar loading
        var loadingDialog = $.confirm({
            title: 'Guardando métricas...',
            content: 'Por favor espere',
            type: 'blue',
            buttons: false,
            closeIcon: false
        });        // Enviar datos al servidor
        $.ajax({
            url: window.guardarMetricasUrl,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                metricas: metricas,
                mes: mes,
                año: año            },
            success: function(response) {
                loadingDialog.close();
                $.confirm({
                    title: 'Éxito',
                    content: response.message || 'Métricas guardadas correctamente',
                    type: 'green',
                    buttons: {
                        entendido: {
                            text: 'Entendido',
                            btnClass: 'btn-green'
                        }
                    }
                });
            },
            error: function(xhr) {
                loadingDialog.close();
                var errorMessage = 'Error al guardar las métricas';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                $.confirm({
                    title: 'Error',
                    content: errorMessage,
                    type: 'red',
                    buttons: {
                        entendido: {
                            text: 'Entendido',
                            btnClass: 'btn-red'
                        }
                    }
                });
            }
        });
    });
    
    // Inicializar filtros desde URL
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('mes')) {
        $('#filtro_mes').val(urlParams.get('mes'));
    }
    if (urlParams.has('año')) {
        $('#filtro_año').val(urlParams.get('año'));
    }
    
    // Validación de inputs numéricos
    $('.metrica-input').on('input', function() {
        var value = $(this).val();
        if (value && !/^\d*\.?\d*$/.test(value)) {
            $(this).val(value.slice(0, -1));
        }
    });
    
    // Manejar apertura del modal de incidencias
    $('#gestionarIncidencias').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Click en botón incidencias detectado');
        
        // Limpiar cualquier modal backdrop existente
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        
        try {
            // Intentar usar Bootstrap modal
            $('#modalIncidencias').modal({
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            console.log('Modal Bootstrap ejecutado');
        } catch (error) {
            console.error('Error con Bootstrap modal, usando fallback:', error);
            
            // Fallback manual
            $('#modalIncidencias').show().addClass('show').css({
                'display': 'block',
                'padding-right': '15px'
            });
            
            $('body').addClass('modal-open').css('padding-right', '15px');
            
            // Crear backdrop manualmente
            if ($('.modal-backdrop').length === 0) {
                $('<div class="modal-backdrop fade show"></div>').appendTo('body');
            }
            
            console.log('Fallback manual ejecutado');
        }
    });
    
    // Función para cerrar modal manualmente
    function cerrarModal() {
        $('#modalIncidencias').hide().removeClass('show').css({
            'display': 'none',
            'padding-right': ''
        });
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    }
    
    // Event listeners para cerrar el modal
    $(document).on('click', '#modalIncidencias .close, #modalIncidencias [data-dismiss="modal"]', function() {
        console.log('Cerrando modal...');
        try {
            $('#modalIncidencias').modal('hide');
        } catch (error) {
            cerrarModal();
        }
    });
    
    // Cerrar con Escape
    $(document).on('keyup', function(e) {
        if (e.key === 'Escape' && $('#modalIncidencias').is(':visible')) {
            try {
                $('#modalIncidencias').modal('hide');
            } catch (error) {
                cerrarModal();
            }
        }
    });
    
    // Cerrar haciendo clic en el backdrop
    $(document).on('click', '.modal-backdrop, #modalIncidencias', function(e) {
        if (e.target === this) {
            try {
                $('#modalIncidencias').modal('hide');
            } catch (error) {
                cerrarModal();
            }
        }
    });
    
    // Asegurar que el modal se puede cerrar
    $('#modalIncidencias').on('shown.bs.modal', function() {
        console.log('Modal mostrado correctamente');
    });
    
    $('#modalIncidencias').on('hidden.bs.modal', function() {
        console.log('Modal cerrado correctamente');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });
    
    // Verificar que el modal existe
    if ($('#modalIncidencias').length) {
        console.log('Modal de incidencias encontrado en el DOM');
    } else {
        console.error('Modal de incidencias NO encontrado en el DOM');
    }
    
    // Manejar envío del formulario de incidencias
    $('#guardarIncidencia').on('click', function() {
        var formData = new FormData(document.getElementById('formIncidencia'));
        
        $.ajax({
            url: window.appBaseUrl + '/material_kilo/guardar-incidencia',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#guardarIncidencia').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Guardando...');
            },
            success: function(response) {
                if (response.success) {
                    $.confirm({
                        title: 'Éxito',
                        content: 'Incidencia guardada correctamente. Las métricas se han actualizado automáticamente.',
                        type: 'green',
                        buttons: {
                            aceptar: {
                                text: 'Aceptar',
                                btnClass: 'btn-green',
                                action: function() {
                                    // Cerrar modal y recargar página para actualizar métricas
                                    $('#modalIncidencias').modal('hide');
                                    location.reload();
                                }
                            }
                        }
                    });
                    
                    // Limpiar formulario
                    document.getElementById('formIncidencia').reset();
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al guardar la incidencia';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                $.confirm({
                    title: 'Error',
                    content: errorMessage,
                    type: 'red',
                    buttons: {
                        entendido: {
                            text: 'Entendido',
                            btnClass: 'btn-red'
                        }
                    }
                });
            },
            complete: function() {
                $('#guardarIncidencia').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Guardar Incidencia');
            }
        });
    });
    
    // Auto-calcular días de respuesta cuando se cambian las fechas
    $('#fecha_envio_proveedor, #fecha_respuesta_proveedor').on('change', function() {
        var fechaEnvio = $('#fecha_envio_proveedor').val();
        var fechaRespuesta = $('#fecha_respuesta_proveedor').val();
        
        if (fechaEnvio && fechaRespuesta) {
            var envio = new Date(fechaEnvio);
            var respuesta = new Date(fechaRespuesta);
            var diferencia = Math.ceil((respuesta - envio) / (1000 * 60 * 60 * 24));
            
            // Mostrar información calculada (opcional)
            console.log('Días de respuesta calculados:', diferencia);
        }
    });
    
    // Auto-calcular días de respuesta cuando se cambian las fechas en devoluciones
    $('#fecha_envio_proveedor_dev, #fecha_respuesta_proveedor_dev').on('change', function() {
        var fechaEnvio = $('#fecha_envio_proveedor_dev').val();
        var fechaRespuesta = $('#fecha_respuesta_proveedor_dev').val();
        
        if (fechaEnvio && fechaRespuesta) {
            var envio = new Date(fechaEnvio);
            var respuesta = new Date(fechaRespuesta);
            var diferencia = Math.ceil((respuesta - envio) / (1000 * 60 * 60 * 24));
            
            // Mostrar información calculada (opcional)
            console.log('Días de respuesta calculados para devolución:', diferencia);
        }
    });
    
    // Auto-llenar proveedor cuando se abre el modal desde una fila específica
    $(document).on('click', '[data-target="#modalIncidencias"]', function() {
        var proveedorId = $(this).closest('tr').data('proveedor-id');
        if (proveedorId) {
            $('#proveedor_incidencia').val(proveedorId);
        }
    });
    
    // Limpiar formulario cuando se cierra el modal
    $('#modalIncidencias').on('hidden.bs.modal', function() {
        document.getElementById('formIncidencia').reset();
    });
    
    // Gestión de devoluciones
    $('#gestionarDevoluciones').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Abriendo modal de devoluciones...');
        
        // Limpiar cualquier modal backdrop existente
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        
        try {
            $('#modalDevoluciones').modal({
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            console.log('Modal devoluciones ejecutado');
        } catch (error) {
            console.error('Error con Bootstrap modal, usando fallback:', error);
            
            // Fallback manual
            $('#modalDevoluciones').show().addClass('show').css({
                'display': 'block',
                'padding-right': '15px'
            });
            
            $('body').addClass('modal-open').css('padding-right', '15px');
            
            if ($('.modal-backdrop').length === 0) {
                $('<div class="modal-backdrop fade show"></div>').appendTo('body');
            }
        }
    });
    
    // Autocompletado para nombre de proveedor
    $('#nombre_proveedor_dev').on('input', function() {
        var term = $(this).val();
        if (term.length >= 2) {
            $.ajax({
                url: window.buscarProveedoresUrl,
                data: { term: term },
                success: function(data) {
                    var suggestions = '';
                    data.forEach(function(proveedor) {
                        suggestions += '<option value="' + proveedor.nombre + '" data-codigo="' + proveedor.codigo + '">';
                    });
                    
                    if ($('#proveedores-datalist').length === 0) {
                        $('<datalist id="proveedores-datalist"></datalist>').appendTo('body');
                    }
                    $('#proveedores-datalist').html(suggestions);
                    $('#nombre_proveedor_dev').attr('list', 'proveedores-datalist');
                }
            });
        }
    });
    
    // Cuando se selecciona un proveedor, obtener su código
    $('#nombre_proveedor_dev').on('change', function() {
        var selectedName = $(this).val();
        $('#proveedores-datalist option').each(function() {
            if ($(this).val() === selectedName) {
                $('#codigo_proveedor').val($(this).data('codigo'));
                return false;
            }
        });
    });
    
    // Autocompletado para productos cuando se selecciona un proveedor
    $('#descripcion_producto').on('input', function() {
        var term = $(this).val();
        var codigo_proveedor = $('#codigo_proveedor').val();
        
        if (term.length >= 2 && codigo_proveedor) {
            $.ajax({
                url: window.buscarProductosProveedorUrl,
                data: { 
                    term: term,
                    codigo_proveedor: codigo_proveedor
                },
                success: function(data) {
                    var suggestions = '';
                    data.forEach(function(producto) {
                        suggestions += '<option value="' + producto.descripcion + '" data-codigo="' + producto.codigo + '">';
                    });
                    
                    if ($('#productos-datalist').length === 0) {
                        $('<datalist id="productos-datalist"></datalist>').appendTo('body');
                    }
                    $('#productos-datalist').html(suggestions);
                    $('#descripcion_producto').attr('list', 'productos-datalist');
                }
            });
        }
    });
    
    // Cuando se selecciona un producto, obtener su código
    $('#descripcion_producto').on('change', function() {
        var selectedDesc = $(this).val();
        $('#productos-datalist option').each(function() {
            if ($(this).val() === selectedDesc) {
                $('#codigo_producto').val($(this).data('codigo'));
                return false;
            }
        });
    });
    
    // Manejar envío del formulario de devoluciones
    $('#guardarDevolucion').on('click', function() {
        var formData = new FormData(document.getElementById('formDevolucion'));
        
        $.ajax({
            url: window.guardarDevolucionUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#guardarDevolucion').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Guardando...');
            },
            success: function(response) {
                if (response.success) {
                    $.confirm({
                        title: 'Éxito',
                        content: 'Devolución guardada correctamente.',
                        type: 'green',
                        buttons: {
                            aceptar: {
                                text: 'Aceptar',
                                btnClass: 'btn-green',
                                action: function() {
                                    $('#modalDevoluciones').modal('hide');
                                    document.getElementById('formDevolucion').reset();
                                }
                            }
                        }
                    });
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al guardar la devolución';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                $.confirm({
                    title: 'Error',
                    content: errorMessage,
                    type: 'red',
                    buttons: {
                        entendido: {
                            text: 'Entendido',
                            btnClass: 'btn-red'
                        }
                    }
                });
            },
            complete: function() {
                $('#guardarDevolucion').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Guardar Devolución');
            }
        });
    });
    
    // Limpiar formulario cuando se cierra el modal de devoluciones
    $('#modalDevoluciones').on('hidden.bs.modal', function() {
        document.getElementById('formDevolucion').reset();
    });
    
    // Event listeners para cerrar el modal de devoluciones
    $(document).on('click', '#modalDevoluciones .close, #modalDevoluciones [data-dismiss="modal"]', function() {
        console.log('Cerrando modal devoluciones...');
        try {
            $('#modalDevoluciones').modal('hide');
        } catch (error) {
            $('#modalDevoluciones').hide().removeClass('show').css({
                'display': 'none',
                'padding-right': ''
            });
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        }
    });
    
    // Cerrar con Escape
    $(document).on('keyup', function(e) {
        if (e.key === 'Escape' && $('#modalDevoluciones').is(':visible')) {
            try {
                $('#modalDevoluciones').modal('hide');
            } catch (error) {
                $('#modalDevoluciones').hide().removeClass('show').css({
                    'display': 'none',
                    'padding-right': ''
                });
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            }
        }
    });
    
    // Cerrar haciendo clic en el backdrop
    $(document).on('click', '.modal-backdrop, #modalDevoluciones', function(e) {
        if (e.target === this) {
            try {
                $('#modalDevoluciones').modal('hide');
            } catch (error) {
                $('#modalDevoluciones').hide().removeClass('show').css({
                    'display': 'none',
                    'padding-right': ''
                });
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            }
        }
    });
    
    // Asegurar que el modal se puede cerrar
    $('#modalDevoluciones').on('shown.bs.modal', function() {
        console.log('Modal de devoluciones mostrado correctamente');
    });
    
    $('#modalDevoluciones').on('hidden.bs.modal', function() {
        console.log('Modal de devoluciones cerrado correctamente');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });
    
    // Verificar que el modal existe
    if ($('#modalDevoluciones').length) {
        console.log('Modal de devoluciones encontrado en el DOM');
    } else {
        console.error('Modal de devoluciones NO encontrado en el DOM');
    }
});

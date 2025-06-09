$(document).ready(function () {
    var table = $('#table_total_kg_proveedor').DataTable({
        paging: true,
        pageLength: 25,
        info: true,
        ordering: true,
        searching: true,
        orderCellsTop: true,
        fixedHeader: true,
        order: [[2, 'desc']], // Ordenar por Total KG descendente por defecto
        columnDefs: [
            {
                targets: [2, 3, 4], // Columnas Total KG, Cantidad Registros y Porcentaje
                orderable: true
            },
            {
                targets: [5, 6, 7, 8, 9], // Columnas de métricas (RG1, RL1, DEV1, ROK1, RET1)
                orderable: false
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

    // Aplica los filtros de las celdas del segundo thead (por columna)
    $('#table_total_kg_proveedor thead tr:eq(1) th').each(function (i) {
        var input = $(this).find('input');
        if (input.length) {
            input.on('keyup change', function () {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        }
    });
    
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
        
        if (!mes || !año) {
            Swal.fire({
                icon: 'warning',
                title: 'Filtros requeridos',
                text: 'Debe seleccionar mes y año antes de guardar las métricas.',
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
        });
        
        // Enviar datos al servidor
        $.ajax({
            url: '/material_kilo/guardar-metricas',
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
});

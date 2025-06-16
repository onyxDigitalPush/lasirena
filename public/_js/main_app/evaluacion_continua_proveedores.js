$(document).ready(function () {
    var table = $('#table_evaluacion_continua').DataTable({
        paging: true,
        pageLength: 25,
        info: true,
        ordering: true,
        searching: true,
        orderCellsTop: false, // Cambiado a false para headers complejos
        fixedHeader: true,
        order: [[2, 'desc']], // Ordenar por Total KG descendente por defecto
        scrollX: true, // Permitir scroll horizontal para tantas columnas
        columnDefs: [
            {
                targets: [0, 1], // ID Proveedor y Nombre Proveedor - permitir filtrado
                orderable: true
            },
            {
                targets: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], // Todas las columnas numéricas
                orderable: true
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

    // Función para actualizar totales basado en filas filtradas
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
    }    // Aplica los filtros de las celdas del tercer thead (por columna) - tr:eq(2) es la tercera fila (índice 2)
    $('#table_evaluacion_continua thead tr:eq(2) th').each(function (i) {
        var input = $(this).find('input');
        if (input.length) {
            console.log('Configurando filtro para columna:', i, 'Placeholder:', input.attr('placeholder'));
            input.on('keyup change', function () {
                var searchValue = this.value;
                console.log('Filtrando columna', i, 'con valor:', searchValue);
                if (table.column(i).search() !== searchValue) {
                    table
                        .column(i)
                        .search(searchValue)
                        .draw();
                    
                    // Actualizar totales después de filtrar
                    actualizarTotales();
                }
            });
        }
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
    
    // Funcionalidad para filtros por mes y año
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
        // Redireccionar sin parámetros de filtro
        var url = new URL(window.location.href);
        url.searchParams.delete('mes');
        url.searchParams.delete('año');
        window.location.href = url.toString();
    });
    
    // Inicializar filtros desde URL
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('mes')) {
        $('#filtro_mes').val(urlParams.get('mes'));
    }
    if (urlParams.has('año')) {
        $('#filtro_año').val(urlParams.get('año'));
    }
});

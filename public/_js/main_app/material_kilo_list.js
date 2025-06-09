$(document).ready(function () {
    var table = $('#table_material_kilo').DataTable({
        paging: false,       // 🚫 Desactiva paginación de DataTables
        info: false,         // 🚫 Desactiva resumen tipo "Mostrando X de Y"
        ordering: false,     // (opcional) Desactiva ordenamiento si no lo usas
        searching: false,    // 🚫 Desactiva el buscador general (usamos por columna)
        orderCellsTop: true,
        fixedHeader: true
    });

    // Aplica los filtros de las celdas del segundo thead (por columna)
    $('#table_material_kilo thead tr:eq(1) th').each(function (i) {
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
});

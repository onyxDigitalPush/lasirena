$(document).ready(function () {
    var table = $('#table_material_kilo').DataTable({
        orderCellsTop: true,
        fixedHeader: true
    });

    // Aplica los filtros de las celdas del segundo thead
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

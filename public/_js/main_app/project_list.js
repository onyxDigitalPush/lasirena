var oTable_admin_users;
$(document).ready(function () {
    $('#shippingReferenceTbl thead tr')
        .clone(true)
        .addClass('filters')
        .appendTo('#shippingReferenceTbl thead');

    oTable_admin_users = $('#shippingReferenceTbl').DataTable({
        "orderCellsTop": true,
        "stateSave": true,
        "scrollX": true,
        "scrollY": 600,
        "scroller": {
            "loadingIndicator": true
        },
        "language": { "url": http_web_root + '_js/datatables/es.datatables.json' },
        "lengthMenu": [5, 10, 15, 20, 50, 100, 500],
        "pageLength": 15,
        "order": [[0, 'desc']],
        "autoWidth": false,
        "processing": true,
        "bServerSide": false,
        "columnDefs": [
            { "targets": 0, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "40px" }, //project_id
            { "targets": 1, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "138px" }, //name
            { "targets": 2, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "138px" }, //subject
            { "targets": 3, "visible": true, "searchable": true, "bSortable": false, "sClass": "", "width": "138px" }, //ccs
            { "targets": 4, "visible": true, "searchable": true, "bSortable": false, "sClass": "", "width": "138px" }, //document
            { "targets": 5, "visible": true, "searchable": true, "bSortable": false, "sClass": "", "width": "110px" }, //date creation
            { "targets": 6, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "70px" },
            { "targets": 7, "visible": true, "searchable": true, "bSortable": false, "sClass": "", "width": "30px" }, //project_id



        ],
        initComplete: function () {
            var api = this.api();
            //hacemos un primer bucle para los th que no tienen filtros que se quite el texto ya que arriba hacemos un clone
            api
                .columns()
                .eq(0)
                .each(function (colIdx) {
                    var cell_clear = $('.filters th').eq(
                        $(api.column(colIdx).header()).index()
                    );
                    $(cell_clear).text('');
                });
            // For each column
            api
                .columns([1, 2, 5])
                .eq(0)
                .each(function (colIdx) {

                    // Set the header cell to contain the input element
                    var cell = $('.filters th').eq(
                        $(api.column(colIdx).header()).index()
                    );
                    var title = $(cell).text();

                    if ($(api.column(colIdx).header()).index() >= 0) {
                        $(cell).html('<input class="form-control" type="text" placeholder="' + title + '"/>');
                    }


                    // On every keypress in this input
                    $(
                        'input',
                        $('.filters th').eq($(api.column(colIdx).header()).index())
                    )
                        .off('keyup change')
                        .on('keyup change', function (e) {
                            e.stopPropagation();

                            // Get the search value
                            $(this).attr('title', $(this).val());
                            var regexr = '({search})'; //$(this).parents('th').find('select').val();

                            var cursorPosition = this.selectionStart;
                            // Search the column for that value
                            api
                                .column(colIdx)
                                .search(
                                    this.value != ''
                                        ? regexr.replace('{search}', '(((' + this.value + ')))')
                                        : '',
                                    this.value != '',
                                    this.value == ''
                                )
                                .draw();

                            $(this)
                                .focus()[0]
                                .setSelectionRange(cursorPosition, cursorPosition);
                        });


                });
        },

        "drawCallback": function (settings) {
            $('#shippingReferenceTbl [data-toggle="tooltip"]').tooltip();
        }
    });

});

//Upload excel code


var parseSheetName = false;
var parseFile = true;

$(document).on('click', '#excelParse', function () {
    $('#excel_table_body').empty();
    parseSheetName = false;
    parseFile = true;
    $('#rowError,#rowData').removeClass('d-flex').addClass('d-none');
    $('.card-body', '#rowError').html('');
    if ($('input[name="excelFile"]').val() == '') {
        $('input[name="excelFile"]').addClass('is-invalid');
        $('#confirm_button').prop('disabled', true);

        console.error("You have to choose a file.");
        return;

    }
    else {
        $('#excelParse').hide();
        $('#spinner').show();
      
    }
    $('#confirm_button').prop('disabled', false);
    parseExcel();

});

$(document).on('click', 'input[name=excelType]', function () {
    parseSheetName = false;
    parseFile = true;
    $('#rowError,#rowData').removeClass('d-flex').addClass('d-none');
    $('.card-body', '#rowError').html('');
    $('input[name=excelType]').removeClass('is-invalid');
    $('input[name=excelType]').parent().removeClass('is-invalid');
});

function parseExcel() {
    var selectedFile = $('input[name="excelFile"]')[0].files[0];
    var reader = new FileReader();
    rABS = typeof FileReader !== "undefined" && (FileReader.prototype || {}).readAsBinaryString;

    reader.onload = function (event) {
        var data = event.target.result;
        if (!rABS) /** Read as binary string it is not avalible on IE 11 */ {
            data = new Uint8Array(data);
        }
        var workbook = XLSX.read(data, { type: rABS ? 'binary' : 'array', cellDates: true, cellNF: false, cellText: false });
       
        workbook.SheetNames.forEach(function (sheetName) {
            var ws = workbook.Sheets[sheetName];
            if (!ws.hasOwnProperty('!ref')) {
                $('.card-body', '#rowError').html(
                    '<div class="alert alert-danger" role="alert"> File could have correct sheetName.!</div>');
                $('#rowError').removeClass('d-none').addClass('d-flex');
                console.error('File must have some data.');
                $('#confirm_button').prop('disabled', true);
                return;
            }
            /* Validar num. col y rows */
            if (!parseFileColRows(ws, 2)) {

                parseFile = false;
            }
            /** Validar config este completo.  */
            var ws_config = [
                { A1: 'familia' }, //Necesario
                { B1: 'codprov' },//Necesario	
                { C1: 'nombreproveedor' },//Necesario	
                { D1: 'prod' },//Necesario	
                { E1: 'descripcionproducto' },//Necesario	
                { F1: 'dto' },//Necesario	
                { G1: 'fechainicio' }, //Necesario
                { H1: 'fechafinal' }, //Necesario añadir fecha
                { I1: 'iniciosap' }, //Necesario
                { J1: 'finsap' }, //Necesario
                { K1: 'previsones' },//Necesario	
                { L1: 'idioma' },//Necesario	
                { M1: 'email' },  //Necesario		
                { N1: 'redencion' },  //Necesario	
                { O1: 'enviarmail' },  //Necesario	
                { P1: 'previsiones(desdeelenviodelacomunicacionhastafinalizarlaoferta)' }				
            ];

            if (!parseFileConfig(ws, ws_config)) {
                parseFile = false;
            }

            if (parseFile) {
                let emailsJson = createEmailsJson(ws);

                //$('#excel_table_body').html(createEmailsTable(ws));
                var datatable = $("#projectExcelTbl").DataTable();
                $('#str_projects').val(JSON.stringify(emailsJson));
                datatable.clear();
                console.log(emailsJson);
                for (json_row of emailsJson) {

                    let span_color_red = '<span style="color:red;">[content]</span>';
                    let span_element_void = '<span style="color:red;">Este campo no puede estar vacío</span>';
                    let can_send_mail = true;
                    let style_send_email = '';
                    let style_email = '';
                    if (!validateEmail(json_row.email) || json_row.email == null) {
                        style_email = ' style = "color:red"';
                        can_send_mail = false;
                    }
                    if (json_row.send_email == "false") {
                        style_send_email = ' style = "color:red"';
                        can_send_mail = false;
                    }

                    if (json_row.cod_prov == '' || json_row.family == '' || json_row.provider_name == '' || json_row.prod == '' || json_row.product_description == '' || json_row.dto == '' || json_row.start_date == '' || json_row.end_date == '' || isNaN(json_row.cod_prov) || isNaN(json_row.prod) || isNaN(json_row.dto)) {
                        can_send_mail = false;
                    }
                    let tr_class = (can_send_mail) ? 'table-success' : 'table-danger';
                    let html = '<tr class="' + tr_class + '">';
                    html += '<td>';
                    html += json_row.family != '' ? json_row.family : span_element_void;
                    //let cod_prov_check = isInteger(json_row.cod_prov) ? '' : '';
                    html += '</td>';
                    html += '<td>';
                    html += json_row.cod_prov == '' ? span_element_void : isNaN(json_row.cod_prov) ? span_color_red.replace('[content]', json_row.cod_prov) : json_row.cod_prov;
                    html += '</td>';
                    html += '<td>';
                    html += json_row.provider_name != '' ? json_row.provider_name : span_element_void;
                    html += '</td>';
                    html += '<td>';
                    html += json_row.prod == '' ? span_element_void : isNaN(json_row.prod) ? span_color_red.replace('[content]', json_row.prod) : json_row.prod;
                    html += '</td>';
                    html += '<td>';
                    html += json_row.product_description != '' ? json_row.product_description : span_element_void;
                    html += '</td>';
                    html += '<td>';
                    html += json_row.dto == '' ? span_element_void : isNaN(json_row.dto) ? span_color_red.replace('[content]', 'Debe de ser un número') : parseFloat(json_row.dto).toFixed(2) + ' %';
                    html += '</td>';
                    html += '<td>';
                    html += json_row.start_date != '' ? json_row.start_date : span_element_void;
                    html += '</td>';
                    html += '<td>';
                    html += json_row.end_date != '' ? json_row.end_date : span_element_void;
                    html += '</td>';
                    html += '<td>' + json_row.start_sap + '</td>';
                    html += '<td>' + json_row.end_sap + '</td>';
                    html += '<td>' + json_row.forecast + '</td>';
                    html += '<td>' + json_row.language + '</td>';
                    html += '<td ' + style_email + '>' + json_row.email + '</td>';
                    html += '<td>' + json_row.redemption + '</td>';
                    html += '<td ' + style_send_email + '>' + json_row.send_email + '</td>';
                    html += '<td>' + json_row.prevision + '</td>';

                    html += '</tr>';

                    datatable.row.add($(html)[0]).draw();
                }
                $('#spinner').hide();
                $('#excelParse').show();
            }

        });
       
    };

    reader.onerror = function (event) {
        $('.card-body', '#rowError').html(
            '<div class="alert alert-danger" role="alert"> File could not be read! Code ' + event.target.error.code + '!</div>');
        $('#rowError').removeClass('d-none').addClass('d-flex');
        console.error("File could not be read! Code " + event.target.error.code);
        $('#confirm_button').prop('disabled', true);
    };

    if (rABS) {
        reader.readAsBinaryString(selectedFile);
    }
    else {
        reader.readAsArrayBuffer(selectedFile);
    }
}

function parseFileColRows(ws, min_rows) {
    var ok = true;
    /* Validar num. col y rows */
    var range = XLSX.utils.decode_range(ws['!ref']);
    var ncols = range.e.c - range.s.c + 1;
    var nrows = range.e.r - range.s.r + 1;
    if (nrows < min_rows) {
        $('.card-body', '#rowError').append(
            '<div class="alert alert-danger" role="alert">File must have minim ' + min_rows + ' row.</div>');
        $('#rowError').removeClass('d-none').addClass('d-flex');
        console.error('File must have minim ' + min_rows + ' row.');
        $('#confirm_button').prop('disabled', true);
        ok = false;
    }
    return ok;
}

function parseFileConfig(ws, ws_config) {

    var ok = true;

    for (var config_element in ws_config) {
        for (var cell in ws_config[config_element]) {
            var cell_name = ws_config[config_element][cell];
            let normalized_cell = ws[cell].v.replaceAll('_', '').replaceAll(' ', '').replaceAll('.', '').replaceAll(':', '').toLowerCase();

            let parsed_cell = normalized_cell.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            if (!ws.hasOwnProperty(cell)) {
                $('.card-body', '#rowError').append(
                    '<div class="alert alert-danger" role="alert">The Cell "' + cell + '" column must be edited</div>');
                $('#rowError').removeClass('d-none').addClass('d-flex');
                console.error('The Cell "' + cell + '" column must be edited');
                $('#confirm_button').prop('disabled', true);
                ok = false;
            } else if (parsed_cell != cell_name) {

                $('.card-body', '#rowError').append(
                    '<div class="alert alert-danger" role="alert">Error: El header de ' + cell + ' no puede ser "' + parsed_cell + '" ' + 'debería de ser "' + cell_name + '" </div>');
                $('#rowError').removeClass('d-none').addClass('d-flex');
                $('#spinner').hide();
                $('#excelParse').show();
                $('#confirm_button').prop('disabled', true);
                console.error('El header de ' + cell + ' no es "' + cell_name + '"');
                ok = false;
            }
        }
    }

    return ok;
}




function createEmailsJson(ws) {
    var range = XLSX.utils.decode_range(ws['!ref']);
    var nrows = range.e.r - range.s.r + 1;
    
    html = '';
    let json_parsed_emails = [];
    for (var row = 2; row <= nrows; row++) {
        if (getValue(ws, 'A' + row) == '' && getValue(ws, 'B' + row) == '' && getValue(ws, 'C' + row) == '' && getValue(ws, 'D' + row) == '' && getValue(ws, 'E' + row) == '' && getValue(ws, 'F' + row) == '' && getValue(ws, 'G' + row) == '' && getValue(ws, 'H' + row) == '' && getValue(ws, 'I' + row) == '' && getValue(ws, 'J' + row) == '' && getValue(ws, 'K' + row) == '' && getValue(ws, 'L' + row) == '' && getValue(ws, 'M' + row) == '' && getValue(ws, 'N' + row) == '' && getValue(ws, 'O' + row) == '') {
            return json_parsed_emails;
        }
        else {

            array_emails = getValue(ws, 'M' + row);
            array_emails = array_emails.split(';');
            for (email of array_emails) {
               
                var language = getValue(ws, 'L' + row);


                if (language != '' && language != 'es' && language != 'ca') {
                    language = 'es';
                }


                let data = {

                    "family": getValue(ws, 'A' + row),
                    "cod_prov": getValue(ws, 'B' + row),
                    "provider_name": getValue(ws, 'C' + row),
                    "prod": getValue(ws, 'D' + row),
                    "product_description": getValue(ws, 'E' + row),
                    "dto": parseFloat(getValue(ws, 'F' + row) * 100).toFixed(2),
                    "start_date": format_date(getValue(ws, 'G' + row)),
                    "end_date": format_date(getValue(ws, 'H' + row)),
                    "start_sap": format_date(getValue(ws, 'I' + row)),
                    "end_sap": format_date(getValue(ws, 'J' + row)),
                    "forecast": getValue(ws, 'K' + row),
                    "language": language,
                    "email": email.replace(/\s/g, '', ''),
                    "redemption": getValue(ws, 'N' + row),
                    "send_email": getValue(ws, 'O' + row),
                    "prevision" : getValue (ws,'P'+row)
                };
                

                json_parsed_emails.push(data);
            }

        }
    }

    return json_parsed_emails;
}


function getValue(ws, key) {
    if (!ws.hasOwnProperty(key)) {
        return '';
    }
    var text = ws[key].v;
    var result_text = text.toString().replaceAll('"', '\\"');
    return (result_text);
}

function format_date(value) {
    let fecha = value;

    if (fecha != '') {
        let date = new Date(fecha);
		let year = date.toLocaleString('en', { year: 'numeric' });
        var month = date.toLocaleString('en', { month: '2-digit' });
        let day = date.toLocaleString('en', { day: '2-digit' });
		
        fecha = year + '-' + month + '-' + day;
    }
    return fecha;
}




//data table code


var oTable_emails;
$(document).ready(function () {


    oTable_emails = $('#projectExcelTbl').DataTable({

        "stateSave": true,
        "scrollX": true,
        "scroller": {
            "loadingIndicator": true
        },
        "lengthMenu": [5, 10, 15, 20, 50, 100, 500],
        "pageLength": 20,
        "language": { "url": http_web_root + '/_js/datatables/es.datatables.json' },
        "autoWidth": false,
        "processing": true,
        "bServerSide": false,
        "columns": [
            { data: 'familia' }, //Necesario
            { data: 'cod_prov' },//Necesario	
            { data: 'nombre_proveedor' },//Necesario	
            { data: 'prod' },//Necesario	
            { data: 'descripcion_producto' },//Necesario	
            { data: 'dto' },//Necesario	
            { data: 'fecha_inicio' }, //Necesario
            { data: 'fecha_final' }, //Necesario añadir fecha
            { data: 'inicio_sap' }, //Necesario
            { data: 'fin_sap' }, //Necesario
            { data: 'previsiones' },//Necesario	
            { data: 'idioma' },//Necesario	
            { data: 'email' },  //Necesario		
            { data: 'redencion' },  //Necesario	
            { data: 'enviar_mail' },  //Necesario
            { data: 'prevision' },
        ],
        "columnDefs": [
            { "targets": 0, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "69px" },
            { "targets": 1, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "69px" },
            { "targets": 2, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "69px" },
            { "targets": 3, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "69px" },
            { "targets": 4, "visible": true, "searchable": false, "bSortable": true, "sClass": "", "width": "69px" },
            { "targets": 5, "visible": true, "searchable": false, "bSortable": true, "sClass": "", "width": "69px" },
            { "targets": 6, "visible": true, "searchable": false, "bSortable": true, "sClass": "", "width": "69px" },
            { "targets": 7, "visible": true, "searchable": false, "bSortable": false, "sClass": "", "width": "69px" },
            { "targets": 8, "visible": true, "searchable": false, "bSortable": false, "sClass": "", "width": "69px" },
            { "targets": 9, "visible": true, "searchable": false, "bSortable": false, "sClass": "", "width": "69px" },
            { "targets": 10, "visible": true, "searchable": false, "bSortable": false, "sClass": "", "width": "69px" },
            { "targets": 11, "visible": true, "searchable": false, "bSortable": false, "sClass": "", "width": "69px" },
            { "targets": 12, "visible": true, "searchable": false, "bSortable": false, "sClass": "", "width": "69px" },
            { "targets": 13, "visible": true, "searchable": false, "bSortable": false, "sClass": "", "width": "69px" },
            { "targets": 14, "visible": true, "searchable": true, "bSortable": true, "sClass": "", "width": "69px" }
        ],


        "drawCallback": function (settings) {
            $('#projectExcelTbl [data-toggle="tooltip"]').tooltip();
        }
    });



});

function validateEmail(value) {

    if ($.trim(value))
        return valida_patron(value, "^[a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$");
    return false;

}

var valida_patron = function (valor, patron) {
    var regex = new RegExp(patron, "i");
    return regex.test(valor);
}

$(document).on('click', '#confirm_button', function (e) {

    var form_excel = $("#form_upload_excel");
    form_excel.validacion();



    e.preventDefault();
    if (!form_excel.valida()) {
        return false;
    } else {
        $.confirm({
            title: 'Enviar Emails',
            content: 'Estas seguro que quieres enviar los emails?',
            buttons: {
                cancelar: {
                    text: 'Cancelar',
                    btnClass: 'btn-red',
                },
                confirmar: {
                    text: 'Enviar',
                    btnClass: 'btn-dark',
                    action: function () {
                        e.preventDefault();
                        $("#form_upload_excel").submit();
                    }
                }
            }
        });
    }


});


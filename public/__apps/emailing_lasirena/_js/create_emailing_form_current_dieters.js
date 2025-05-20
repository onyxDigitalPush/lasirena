var parseSheetName = false;
var parseFile = true;
var num_destinatarios = 0;
var form_inv_data = [];

var clipboard_plus = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-clipboard-plus" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path fill-rule="evenodd" d="M9.5 1h-3a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3zM8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7z"/></svg>';
$(document).ready(function () {

    $(document).on('click', '#excelParse', function () {
        parseSheetName = false;
        parseFile = true;
        $('#rowError,#rowData,#rowSendData').removeClass('d-flex').addClass('d-none');
        $('.card-body', '#rowError').html('');

        if ($('input[name="subject"]').val() == '' && $('input[name="excelType"]').val() == '' && $('input[name="excelFile"]').val() == '') {
            $('input[name="subject"]').addClass('is-invalid');
            $('input[name="excelType"]').addClass('is-invalid');
            $('input[name="excelFile"]').addClass('is-invalid');
            console.error("the fields is mandatory");
            return;
        }
        if ($('input[name="subject"]').val() == '' && $('input[name="excelType"]').val() == '') {
            $('input[name="subject"]').addClass('is-invalid');
            $('input[name="excelType"]').addClass('is-invalid');
            console.error("the fields is mandatory");
            return;
        }
        if ($('input[name="excelFile"]').val() == '') {
            $('input[name="excelFile"]').addClass('is-invalid');
            console.error("You have to choose a file.");
            return;
        }
        if ($('input[name="subject"]').val() == '') {
            $('input[name="subject"]').addClass('is-invalid');
            console.error("the field is mandatory");
            return;
        }

        if ($('input[name="excelType"]').val() == '') {
            $('input[name="excelType"]').addClass('is-invalid');
            console.error("the field is mandatory");
            return;
        }

        if ($('input[name="excelType"]').val() != '' && $('input[name="subject"]').val() != '') {
            parseFileOptifastFormInv();
        }

    });

    $(document).on('click', 'input[name=excelType]', function () {
        parseSheetName = false;
        parseFile = true;
        $('#rowError,#rowData,#rowSendData').removeClass('d-flex').addClass('d-none');
        $('.card-body', '#rowError').html('');
        $('#excelData').html('');
        $('input[name=excelType]').removeClass('is-invalid');
        $('input[name=excelType]').parent().removeClass('is-invalid');
    });

    $(document).on('click', 'input[name=excelFile]', function () {
        parseSheetName = false;
        parseFile = true;
        $('#rowError,#rowData,#rowSendData').removeClass('d-flex').addClass('d-none');
        $('.card-body', '#rowError').html('');
        $('#excelData').html('');
        $('input[name=excelFile]').removeClass('is-invalid');

    });

    $(document).on('click', 'input[name=subject]', function () {
        parseSheetName = false;
        parseFile = true;
        $('#rowError,#rowData,#rowSendData').removeClass('d-flex').addClass('d-none');
        $('.card-body', '#rowError').html('');
        $('#excelData').html('');
        $('input[name=subject]').removeClass('is-invalid');

    });

    $(document).on('click', '#send_data', function () {

        console.log(form_inv_data);
        //var json_data = JSON.stringify(form_inv_data);
        $.confirm({
            title: '¿Datos correctos?',
            content: '¿Confirma que el asunto: '+ $('#subject').val() + ' y referencia: ' + $('#reference').val() + ' son correctos?',
            buttons: {
                cancelar: {
                    text: 'Cancelar',
                    btnClass: 'btn-red',
                    action: function () {
                        $(this).confirm("close");
                    }
                },
                confirmar: {
                    text: 'Confirmar',
                    btnClass: 'btn-dark',
                    action: function () {
                        $.ajax({
                            type: 'POST',
                            async: true,
                            data: {
                                'form_data': JSON.stringify(form_inv_data),
                                'ref': $('#reference').val(),
                                'subject' : $('#subject').val(),
                                'send': false
                            },
                            dataType: 'json',
                            success: function (result) {
                                if (result.status === 'success')
                                {
                                    $('#rowError').removeClass('d-flex').addClass('d-none');
                                    $('#send_data').prop("disabled", true);
                                    $('.card-body', '#rowError').html('');
                                    $('#recipients_response').html('Destinatarios (' + result.data.length + '):<br>' + JSON.stringify(result.data));
                                    $('#rowSendEmail').removeClass('d-none').addClass('d-flex');

                                } else
                                {
                                    $('.card-body', '#rowError').html(
                                            '<div class="alert alert-danger" role="alert">' + result.message + '</div>');
                                    $('#rowError').removeClass('d-none').addClass('d-flex');
                                }
                            }
                        });
                    }
                }
            }
        });

    });

    $(document).on('click', '#send_email', function () {
        //var json_data = JSON.stringify(form_inv_data);
        $.ajax({
            type: 'POST',
            async: true,
            data: {
                'form_data': JSON.stringify(form_inv_data),
                'ref': $('#excelType').val(),
                'send': true
            },
            dataType: 'json',
            success: function (result) {
                if (result.status === 'success')
                {
                    $('#rowError').removeClass('d-flex').addClass('d-none');
                    $('#send_email').prop("disabled", true);
                    $('.card-body', '#rowError').html('');
                    alert('Añadido a la cola');

                } else
                {
                    $('.card-body', '#rowError').html(
                            '<div class="alert alert-danger" role="alert">' + result.message + '</div>');
                    $('#rowError').removeClass('d-none').addClass('d-flex');
                    alert('KO');
                }
            }
        });
    });

});


function parseFileOptifastFormInv()
{
    var selectedFile = $('input[name="excelFile"]')[0].files[0];
    var reader = new FileReader();
    rABS = typeof FileReader !== "undefined" && (FileReader.prototype || {}).readAsBinaryString;

    reader.onload = function (event) {
        var data = event.target.result;
        if (!rABS) /** Read as binary string it is not avalible on IE 11 */
        {
            data = new Uint8Array(data);
        }
        var workbook = XLSX.read(data, {type: rABS ? 'binary' : 'array', cellDates: true, cellNF: false, cellText: false});

        workbook.SheetNames.forEach(function (sheetName) {
            var ws = workbook.Sheets[sheetName];
            if (!ws.hasOwnProperty('!ref')) {
                $('.card-body', '#rowError').html(
                        '<div class="alert alert-danger" role="alert"> File could have correct sheetName.!</div>');
                $('#rowError').removeClass('d-none').addClass('d-flex');
                console.error('File must have some data.');
                return;
            }

            /* Validar num. col y rows */
            /* TODO ABF perquè min rows? */
            /*if (!parseFileColRows(ws, 27)) {
             
             parseFile = false;
             }*/

            /** Validar config este completo.  */
            var ws_config = [
                {A1: 'nestle_user_id'},
                {B1: 'creation_web_id'},
                {C1: 'check_code'},
                {D1: 'name'},
                {E1: 'surnames'},
                {F1: 'nif'},
                {G1: 'email'},
                {H1: 'phone'},
                {I1: 'consent_flag'},
                {J1: 'legal_text_confirmation'},
                {K1: 'is_email_subscribed'}
            ];

            if (!parseFileConfig(ws, ws_config)) {
                parseFile = false;
            }

            if (parseFile) {
                $('#excelData').html(createOptifastFormInvData(ws));
                $('#num_destinatarios').html('(' + parseInt(num_destinatarios) + ')');
                //$('#insertData').html(createOptifastFormInvInsertData(ws));
                $('#rowData').removeClass('d-none').addClass('d-flex');
                $('#rowSendData').removeClass('d-none').addClass('d-flex');
            }

        });
    };

    reader.onerror = function (event) {
        $('.card-body', '#rowError').html(
                '<div class="alert alert-danger" role="alert"> File could not be read! Code ' + event.target.error.code + '!</div>');
        $('#rowError').removeClass('d-none').addClass('d-flex');
        console.error("File could not be read! Code " + event.target.error.code);
    };

    if (rABS)
    {
        reader.readAsBinaryString(selectedFile);
    } else
    {
        reader.readAsArrayBuffer(selectedFile);
    }
}


function createOptifastFormInvData(ws) {
    var html = '';
    var range = XLSX.utils.decode_range(ws['!ref']);
    var nrows = range.e.r - range.s.r + 1;
    num_destinatarios = 0;
    html += '<table class="table table-hover" style="max-width:1400px">';
    html += '<thead class="thead-dark">';
    html += '<tr>';
    html += '<th>nestle_user_id</th>';
    html += '<th>creation_web_id</th>';
    html += '<th>check_code</th>';
    html += '<th>name</th>';
    html += '<th>surnames</th>';
    html += '<th>nif</th>';
    html += '<th>email</th>';
    html += '<th>phone</th>';
    html += '<th>consent_flag</th>';
    html += '<th>legal_text_confirmation</th>';
    html += '<th>is_email_subscribed</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    for (var row = 2; row <= nrows; row++) {
        var test_email = getValue(ws, 'G' + row);
        var patron = /^[^@]*@[^@]*$/;
        var regex = new RegExp(patron, 'i');
        if (regex.test(test_email) === false)
        {
            $('.card-body', '#rowError').append(
                    '<div class="alert alert-danger" role="alert">No se ha importado la línea ' + row + ' email no válido ' + test_email + '</div>');
            $('#rowError').removeClass('d-none').addClass('d-flex');
            console.error('No se ha importado la línea ' + row + ' email no válido ' + test_email + '');
            continue;
        }
        num_destinatarios++;
        html += '<tr>';
        html += '<td>' + getValue(ws, 'A' + row) + '</td>';
        html += '<td>' + getValue(ws, 'B' + row) + '</td>';
        html += '<td>' + getValue(ws, 'C' + row) + '</td>';
        html += '<td>' + getValue(ws, 'D' + row) + '</td>';
        html += '<td>' + getValue(ws, 'E' + row) + '</td>';
        html += '<td>' + getValue(ws, 'F' + row) + '</td>';
        html += '<td>' + getValue(ws, 'G' + row) + '</td>';
        html += '<td>' + getValue(ws, 'H' + row) + '</td>';
        html += '<td>' + getValue(ws, 'I' + row) + '</td>';
        html += '<td>' + getValue(ws, 'J' + row) + '</td>';
        html += '<td>' + getValue(ws, 'K' + row) + '</td>';

        html += '</tr>';

        //Array objects                    
        form_inv_data.push(
                {
                    nestle_user_id: getValue(ws, 'A' + row),
                    creation_web_id: getValue(ws, 'B' + row),
                    check_code: getValue(ws, 'C' + row),
                    name: getValue(ws, 'D' + row),
                    surnames: getValue(ws, 'E' + row),
                    nif: getValue(ws, 'F' + row),
                    email: getValue(ws, 'G' + row),
                    phone: getValue(ws, 'H' + row),
                    consent_flag: getValue(ws, 'I' + row),
                    legal_text_confirmation: getValue(ws, 'J' + row),
                    is_email_subscribed: getValue(ws, 'K' + row)
                }
        );
    }
    html += '</tbody>';
    html += '</table>';
    return html;
}

function getValue(ws, key)
{
    if (!ws.hasOwnProperty(key))
    {
        return '';
    }

    return $.trim(ws[key].v);

}

function parseFileColRows(ws, min_rows) {
    var ok = true;
    /* Validar num. col y rows */
    var range = XLSX.utils.decode_range(ws['!ref']);
    var ncols = range.e.c - range.s.c + 1;
    var nrows = range.e.r - range.s.r + 1;
    if (nrows < min_rows)
    {
        $('.card-body', '#rowError').append(
                '<div class="alert alert-danger" role="alert">File must have minim ' + min_rows + ' row.</div>');
        $('#rowError').removeClass('d-none').addClass('d-flex');
        console.error('File must have minim ' + min_rows + ' row.');
        ok = false;
    }
    return ok;
}

function parseFileConfig(ws, ws_config) {

    var ok = true;

    for (var config_element in ws_config)
    {
        for (var cell in ws_config[config_element])
        {
            var cell_name = ws_config[config_element][cell];
            if (!ws.hasOwnProperty(cell))
            {
                $('.card-body', '#rowError').append(
                        '<div class="alert alert-danger" role="alert">The Cell "' + cell + '" column must be edited</div>');
                $('#rowError').removeClass('d-none').addClass('d-flex');
                console.error('The Cell "' + cell + '" column must be edited');
                ok = false;
            } else if ($.trim(ws[cell].v) != cell_name)
            {
                $('.card-body', '#rowError').append(
                        '<div class="alert alert-danger" role="alert">El header de ' + cell + ' no es "' + cell_name + '"</div>');
                $('#rowError').removeClass('d-none').addClass('d-flex');
                console.error('El header de ' + cell + ' no es "' + cell_name + '"');
                ok = false;
            }
        }
    }
    return ok;
}


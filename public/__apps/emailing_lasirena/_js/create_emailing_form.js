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
        if ($('input[name="excelFile"]').val() == '') {
            $('input[name="excelFile"]').addClass('is-invalid');
            console.error("You have to choose a file.");
            return;
        }
        switch ($('input[name=excelType]:checked').val()) {
            case 'NHS_OPTIFAST_FORM_INV':
                parseFileOptifastFormInv();
                break;
            default:
                $('input[name=excelType]').parent().addClass('is-invalid');
                $('input[name=excelType]').addClass('is-invalid');
                console.error("You have to choose a file type.");
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

    $(document).on('click', '#send_data', function () {
        console.log(form_inv_data);
        //var json_data = JSON.stringify(form_inv_data);
        $.ajax({
            type: 'POST',
            async: true,
            data: {
                'form_data': JSON.stringify(form_inv_data),
                'ref': 'NHS_OPTIFAST_FORM_INV',
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
    });

    $(document).on('click', '#send_email', function () {
        //var json_data = JSON.stringify(form_inv_data);
        $.ajax({
            type: 'POST',
            async: true,
            data: {
                'form_data': JSON.stringify(form_inv_data),
                'ref':
                        'NHS_OPTIFAST_FORM_INV',
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
                {A1: 'Nombre'}, //Necesario
                {B1: 'Apellido 1'},
                {C1: 'Apellido 2'},
                {D1: 'Email'} //Necesario			
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
    html += '<th>Nombre</th>';
    html += '<th>Apellido 1</th>';
    html += '<th>Apellido 2</th>';
    html += '<th>Email</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    for (var row = 2; row <= nrows; row++) {
        var test_email = getValue(ws, 'D' + row);
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

        html += '</tr>';

        //Array objects                    
        form_inv_data.push(
                {name: getValue(ws, 'A' + row),
                    surname1: getValue(ws, 'B' + row),
                    surname2: getValue(ws, 'C' + row),
                    email: getValue(ws, 'D' + row)}
        );
    }
    html += '</tbody>';
    html += '</table>';
    return html;
}

/*function createOptifastFormInvInsertData(ws)
 {
 var html = '';
 var range = XLSX.utils.decode_range(ws['!ref']);
 var nrows = range.e.r - range.s.r + 1;
 html += '<table class="table table-hover" style="min-width:1400px">';
 html += '<thead class="thead-dark">';
 html += '<tr><th>penm_email_recipients</th><th>penm_newsletter_recipients</th></tr>';
 html += '</thead>';
 html += '<tbody>';
 for (var row = 2; row <= nrows; row++) {
 var test_email = getValue(ws, 'D' + row);
 var patron = /^[^@]*@[^@]*$/;
 var regex = new RegExp(patron, 'i');
 var email_recipients_query = '';
 var newsletter_recipients_query = '';
 if (regex.test(test_email) === false)
 {
 $('.card-body', '#rowError').append(
 '<div class="alert alert-danger" role="alert">No se ha importado la línea ' + row + ' email no válido ' + test_email + '</div>');
 $('#rowError').removeClass('d-none').addClass('d-flex');
 console.error('No se ha importado la línea ' + row + ' email no válido ' + test_email + '');
 continue;
 }
 
 html += '<tr>';
 html += '<td>';
 
 email_recipients_query += 'INSERT INTO penm_email_recipients (name, surname1, surname2, email_recipient, creation_app_id, is_email_subscribed, register_date) VALUES (';
 email_recipients_query += '"' + getValue(ws, 'A' + row) + '",';   // name
 email_recipients_query += '"' + getValue(ws, 'B' + row) + '",';   // surname 1
 email_recipients_query += '"' + getValue(ws, 'C' + row) + '",';    // surname 1
 email_recipients_query += '"' + getValue(ws, 'D' + row) + '",';  // email
 email_recipients_query += '1,';                                   // creation_app_id
 email_recipients_query += '1,';                                   // is_email_subscribed
 email_recipients_query += 'NOW()';                                // register_date
 
 email_recipients_query += ') ON DUPLICATE KEY UPDATE ';
 email_recipients_query += 'name = "' + getValue(ws, 'A' + row) + '", ';
 email_recipients_query += 'surname1 = "' + getValue(ws, 'B' + row) + '", ';
 email_recipients_query += 'surname2 = "' + getValue(ws, 'C' + row) + '";';
 
 //Add to html
 html += email_recipients_query;
 
 html += '</td>';
 
 html += '<td>';
 newsletter_recipients_query += 'INSERT INTO penm_newsletter_recipients (email_recipient_id, newsletter_reference) ';
 newsletter_recipients_query += 'SELECT email_recipient_id, "NHS_OPTIFAST_FORM_INV" AS email_reference ';   // Email reference
 newsletter_recipients_query += 'FROM penm_email_recipients WHERE ';
 newsletter_recipients_query += 'is_email_subscribed = 1 AND ';    // surname 1
 newsletter_recipients_query += 'email_recipient = "' + getValue(ws, 'D' + row) + '";';  // email   
 //    
 //Add to html
 html += newsletter_recipients_query;
 
 html += '</td>';
 html += '</tr>';
 }
 html += '</tbody>';
 html += '</table>';
 return html;
 }*/

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
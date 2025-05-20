$(document).ready(function () {
    var formulario = $("#legal_acceptation");

    formulario.validacion();

    // Validación del precio
    $.Validacion_formulario.incluye_opcion('check_recaptcha', {
        verifica: function (input) {
            if ($(input).val() == '')
                return true;

            return ($(input).val() == 1) ? true : false;
        },
        alerta: 'Confirma el captcha'
    });

    $('#send_form').click(function (e) {
        if (formulario.valida())
        {
            document.getElementById('legal_acceptation').submit();
        }
        e.preventDefault();
    });
    $('#localizador_yes').click(function () {
        $('#question_1').val('1').trigger('change');
        $('#localizador_yes').removeClass('inactive');
        $('#localizador_alert').show();
        $('#localizador_content').show();
        $('#localizacion_form').show();
        $('.legal_extended_options_view').addClass('show');
        $('#legal_localization_label').show();
        $('#localizador_no').addClass('inactive');

        $(".legal_extended_options_view :input").each(function () {
            if ($(this).attr("name") == 'postal_code')
            {
                $(this).attr("data-validacion", "obligatorio numerico");
            } else if ($(this).attr("name") == 'email_localization')
            {
                $(this).attr("data-validacion", "obligatorio email");
            } else
            {
                $(this).attr("data-validacion", "obligatorio");
            }
        });
        $('#legal_localization_label input').attr("data-validacion", "check_obligatorio");
        formulario.validacion();
    });
    $('#localizador_no').click(function () {
        $('#question_1').val('2').trigger('change');
        $('#localizador_no').removeClass('inactive');
        $('#localizador_alert').hide();
        $('#localizador_content').hide();
        $('#localizacion_form').hide();
        $('.legal_extended_options_view').removeClass('show');
        $('#legal_localization_label').hide();
        $('#localizador_yes').addClass('inactive');

        $("#legal_localization_label input").prop("checked", false);
        $(".legal_extended_options_view :input").each(function () {
            if ($(this).attr("name") == 'postal_code')
            {
                $(this).attr("data-validacion", "numerico");
            } else if ($(this).attr("name") == 'email_localization')
            {
                $(this).attr("data-validacion", "email");
            } else
            {
                $(this).attr("data-validacion", "");
            }
            $(this).val('');
        });
        $('#legal_localization_label input').attr("data-validacion", "");
        formulario.validacion();
    });

    $('#teleconsulta_yes').click(function () {
        $('#question_2').val('1').trigger('change');
        $('#teleconsulta_yes').removeClass('inactive');
        $('#teleconsulta_alert').show();
        $('#teleconsulta_check').show();
        $('#teleconsulta_no').addClass('inactive');
        $('#teleconsulta_check input').attr("data-validacion", "check_obligatorio");
        formulario.validacion();
    });

    $('#teleconsulta_no').click(function () {
        $('#question_2').val('2').trigger('change');
        $('#teleconsulta_no').removeClass('inactive');
        $('#teleconsulta_alert').hide();
        $('#teleconsulta_check').hide();
        $('#teleconsulta_yes').addClass('inactive');
        $('#teleconsulta_check input').attr("data-validacion", "");

        $("#teleconsulta_check input").prop("checked", false);

        formulario.validacion();
    });

    $(document).on('click', '.open_p1', function () {
        $('#popup_1').fadeIn('fast');
    });
    $(document).on('click', '.open_p2', function () {
        $('#popup_2').fadeIn('fast');
    });

    $(document).on('click', '.rgpd_next, .popup_close', function () {
        $('.popup_background').fadeOut('fast');
    });

});

function recaptcha_callback() {
    var response = grecaptcha.getResponse();
    response = (response.length !== 0) ? 1 : 0;
    $('#check_recaptcha').val(response).trigger('change');
}
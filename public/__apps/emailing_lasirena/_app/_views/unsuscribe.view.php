<?php
/**
 * View include
 */
/**
 * View include
 * @filename: unsuscribe.view.php
 * Location: _app/_views
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20190806 RBM Created
 */
PHP_EOL;/** PhpDoc Fix */
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width; initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="robots" content="noindex, nofollow">
        <title>Unsuscribe</title>
        <link rel="shortcut icon" href="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_img/favicon.png?v=<?php AntiXSS::show((int) APP_VERSION); ?>">
        <link rel="stylesheet" type="text/css" href="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/<?php AntiXSS::show(DIR_CSS) ?>unsuscribe.css" media="screen">
        <script src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/<?php AntiXSS::show(DIR_JS) ?>jquery-3.4.1.min.js"></script>
        <script src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/<?php AntiXSS::show(DIR_JS) ?>jquery.validacion-formulario-3.5.js"></script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>
    <body>


        <main>
            <div class="container">
                <div class="unsuscribe_div">
                    <div class="header_img">
                        <img src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?><?php AntiXSS::show(DIR_IMG) ?>header-unsubscribe-form.jpg" alt="Cabecera">
                    </div>
                    <h1>¿Seguro que desea cancelar su suscripción para la dirección de correo <?php AntiXSS::show($email); ?>?</h1>

                    <form id="unsuscribe_form" method="post" autocomplete="off">
                        <label class="check">
                            <input type="checkbox" name="unsuscribe" value="1" data-validacion="check_obligatorio">
                            Sí, deseo cancelar mi suscripción
                        </label>

                        <div class="captcha_container">
                            <div class="captcha">
                                <?php include VIEWS_INC_DIR . '/recaptcha.inc.php'; ?>
                            </div>
                            <input type="hidden" id="check_recaptcha" value="0" data-validacion="obligatorio check_recaptcha">
                        </div>

                        <div class="button_container">
                            <a href="javascript:void(0);" title="Confirmar" id="send_form">Confirmar</a>
                        </div>


                        <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo AntiXSS::xssFilter($_SESSION["csrf_token"]); ?>">
                    </form>
                </div>
            </div>
        </main>

        <?php
        $no_cookies = true;
        require VIEWS_INC_DIR . '/footer.inc.php';
        ?>

        <script type="text/javascript">

            $( document ).ready( function () {
                var formulario = $( "#unsuscribe_form" );

                formulario.validacion();

                // Validación del precio
                $.Validacion_formulario.incluye_opcion( 'check_recaptcha', {
                    verifica: function ( input ) {
                        if ( $( input ).val() == '' )
                            return true;

                        return ($( input ).val() == 1) ? true : false;
                    },
                    alerta: 'Confirma el captcha'
                } );

                $( '#send_form' ).click( function ( e ) {

                    if ( formulario.valida() )
                    {
                        document.getElementById( 'unsuscribe_form' ).submit();
                    }

                    e.preventDefault();
                } );



            } );

            function recaptcha_callback() {
                var response = grecaptcha.getResponse();
                response = (response.length !== 0) ? 1 : 0;
                $( '#check_recaptcha' ).val( response ).trigger( 'change' );
            }

        </script>
    </body>
</html>
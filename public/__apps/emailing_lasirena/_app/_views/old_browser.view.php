<?php
/**
 * View
 */
/**
 * @filename: old_browser.view.php
 * Location: _app/_views
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
PHP_EOL;/** PhpDoc Fix */
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>Navegador no soportado</title>
        <meta name="robots" content="noindex, nofollow">
        <link rel="shortcut icon" href="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_img/favicon.png?v=<?php AntiXSS::show((int) APP_VERSION); ?>">
        <style type="text/css">
            .contenido{
                display:block;
                margin: auto;
                width:600px;
                padding:8% 3%;
            }
            .titulo{
                width:100%;
                font-family:Arial, Helvetica, sans-serif;
                font-size:20px;
                text-align: left;
                font-weight: bold;
                padding-bottom: 20px;
                color: #dd4b39;
            }
            .mensaje{
                width:100%;
                font-family:Arial, Helvetica, sans-serif;
                font-size:16px;
                text-align: left;
                color:#444444;
            }
            .boton{
                color: #444444;
                text-decoration:underline;				
            }
            .boton:hover{
                text-decoration:none;
            }
        </style>
    </head>

    <body>
        <div class="contenido">
            <div class="titulo">
                Parece que estás usando un navegador antiguo
            </div>
            <div class="mensaje">
                Los navegadores antiguos pueden poner en peligro tu seguridad, son lentos y no funcionan con las funciones más nuevas.
                Te recomendamos que actualices a un navegador más moderno<br><br>
                <a href="javascript: window.history.back();" class="boton">Continuar navegando de todos modos</a>
            </div>
        </div>

    </body>
</html>
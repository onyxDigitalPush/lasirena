<?php
/**
 * View
 */
/**
 * View
 * @filename: see_you_soon.view.php
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
        <title>Gracias</title>
        <link rel="shortcut icon" href="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_img/favicon.png?v=<?php AntiXSS::show((int) APP_VERSION); ?>">
        <link rel="stylesheet" type="text/css" href="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?><?php AntiXSS::show(DIR_CSS) ?>notifications.css" media="screen">
    </head>
    <body>


        <main>
            <div class="content_container">
                <div class="title">Gracias por tu confianza</div>

                <div class="text">Te confirmamos que te hemos desactivado el envío de los correos. Esperamos volver a verte pronto.</div>

            </div>
        </main>

        <?php
        $no_cookies = true;
        require VIEWS_INC_DIR . '/footer.inc.php';
        ?>
    </body>
</html>
<?php
/**
 * View
 */
/**
 * View
 * @filename: error.view.php
 * Location: _app/_views
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
PHP_EOL;/** PhpDoc Fix */
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?php AntiXSS::show($meta_title); ?></title>
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width; initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="robots" content="noindex, nofollow">
        <link rel="shortcut icon" href="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_img/favicon.png?v=<?php AntiXSS::show((int) APP_VERSION); ?>">
        <link rel="stylesheet" type="text/css" href="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?><?php AntiXSS::show(DIR_CSS) ?>notifications.css" media="screen">
    </head>
    <body>


        <main>
            <div class="container">
                <div class="content_container">

                    <div class="header_img">
                        <img src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?><?php AntiXSS::show(DIR_IMG) ?>header-web.jpg" alt="Cabecera">
                    </div>

                    <div class="title"><?php AntiXSS::show(nl2br($title), 'UTF-8', false, '<p><strong><em><ul><li><ol><a><img><br>'); ?></div>

                    <div class="text"><?php AntiXSS::show(nl2br($text), 'UTF-8', false, '<p><strong><em><ul><li><ol><a><img><br>'); ?></div>

                    <div class="button_container">
                        <a href="<?php AntiXSS::show($GLOBALS['HTTP_WEB_MAIN_URL']); ?>" class="boton" title="Aceptar">Aceptar</a>
                    </div>
                </div>
            </div>
        </main>

        <?php
        $no_cookies = true;
        require VIEWS_INC_DIR . '/footer.inc.php';
        ?>

    </body>
</html>
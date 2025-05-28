<?php

/**
 * Controller
 */
/**
 * @filename: error.cntrl.php
 * Location: _app/_controllers
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
require_once __DIR__ . '/../_conf/general_conf.inc.php';
error_reporting(E_ALL & ~(E_STRICT | E_DEPRECATED | E_NOTICE));

if (!function_exists('http_response_code'))
{

    /**
     * http response code
     * @param int $code
     * @return int
     */
    function http_response_code($code = NULL)
    {

        if ($code !== NULL)
        {
            switch ($code)
            {
                case 100: $text = 'Continue';
                    break;
                case 101: $text = 'Switching Protocols';
                    break;
                case 200: $text = 'OK';
                    break;
                case 201: $text = 'Created';
                    break;
                case 202: $text = 'Accepted';
                    break;
                case 203: $text = 'Non-Authoritative Information';
                    break;
                case 204: $text = 'No Content';
                    break;
                case 205: $text = 'Reset Content';
                    break;
                case 206: $text = 'Partial Content';
                    break;
                case 300: $text = 'Multiple Choices';
                    break;
                case 301: $text = 'Moved Permanently';
                    break;
                case 302: $text = 'Moved Temporarily';
                    break;
                case 303: $text = 'See Other';
                    break;
                case 304: $text = 'Not Modified';
                    break;
                case 305: $text = 'Use Proxy';
                    break;
                case 400: $text = 'Bad Request';
                    break;
                case 401: $text = 'Unauthorized';
                    break;
                case 402: $text = 'Payment Required';
                    break;
                case 403: $text = 'Forbidden';
                    break;
                case 404: $text = 'Not Found';
                    break;
                case 405: $text = 'Method Not Allowed';
                    break;
                case 406: $text = 'Not Acceptable';
                    break;
                case 407: $text = 'Proxy Authentication Required';
                    break;
                case 408: $text = 'Request Time-out';
                    break;
                case 409: $text = 'Conflict';
                    break;
                case 410: $text = 'Gone';
                    break;
                case 411: $text = 'Length Required';
                    break;
                case 412: $text = 'Precondition Failed';
                    break;
                case 413: $text = 'Request Entity Too Large';
                    break;
                case 414: $text = 'Request-URI Too Large';
                    break;
                case 415: $text = 'Unsupported Media Type';
                    break;
                case 500: $text = 'Internal Server Error';
                    break;
                case 501: $text = 'Not Implemented';
                    break;
                case 502: $text = 'Bad Gateway';
                    break;
                case 503: $text = 'Service Unavailable';
                    break;
                case 504: $text = 'Gateway Time-out';
                    break;
                case 505: $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        }
        else
        {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }
        return $code;
    }

}

//AntiXss
require_once  '../_models/_class/xss_filter.class.php';

/** These scripts are applied to avoid problems arising from server dependencies */
require_once  '../_models/_includes/host_normalization.inc.php';


/** Standard messaje shouldn't contain html tags */
/** If necessary break line must be done including nl2br function inside the view */
if (isset($_GET['error_404']))
{
    http_response_code(404);
    $meta_title = _('Error 404');
    $title = 'PÁGINA NO ENCONTRADA';
    $text = 'La página solicitada no se encuentra en este servidor.';
}
elseif (isset($_GET['error_403']))
{
    http_response_code(403);
    $meta_title = _('Error 403');
    $title = 'ERROR 403';
    $text = 'Se ha detectado un acceso fraudulento o envío de información no permitida.';
}
elseif (isset($_GET['error_lock_account']))
{
    $meta_title = 'Cuenta bloqueada';
    $title = 'Cuenta de usuario bloqueada';
    $text = 'Hemos detectado un intento de acceso fraudulento a tu cuenta de usuario.' . PHP_EOL . 'Por motivos de seguridad, tu cuenta ha sido bloqueada temporalmente.' . PHP_EOL . 'Puedes intentar acceder más tarde, disculpa las molestias.';
}
elseif (isset($_GET['error_multiple_login']))
{
    $meta_title = 'Acceso simultáneo';
    $title = 'ACCESO SIMULTÁNEO' . PHP_EOL . 'A CUENTA DE USUARIO';
    $text = 'Se ha detectado un acceso simultáneo a tu cuenta  desde otro' . PHP_EOL . 'navegador y se ha cerrado la sesión actual por motivos de seguridad.' . PHP_EOL . 'Si sospechas que se trata de un robo de identidad, te recomendamos modificar tu contraseña de acceso.';
}
elseif (isset($_GET['error_enlace_password']))
{
    $meta_title = 'El enlace ha caducado';
    $title = 'EL ENLACE HA CADUCADO';
    $text = 'Por motivos de seguridad este enlace ha caducado.' . PHP_EOL . 'Por favor, vuelve a iniciar el proceso de recuperar tu contraseña.';
}
elseif (isset($_GET['input_file_not_supported']))
{
    $meta_title = 'Tu dispositivo móvil no permite subir imágenes';
    $title = 'OPSSS... TU DISPOSITIVO MÓVIL NO PERMITE SUBIR IMÁGENES';
    $text = 'La versión de tu sistema operativo no es compatible con nuestro sistema de subida de imágenes.' . PHP_EOL . 'Te recomendamos que pruebes desde otro dispositivo o que accedas desde un ordenador.';
}
elseif (isset($_GET['android_fb_app_detect'])) /** Browsing under the native browser of facebook */
{
    $meta_title = 'Tu dispositivo móvil no permite subir imágenes';
    $title = 'OPSSS... ESTÁS ACCEDIENDO DESDE LA APP DE FACEBOOK CON UN DISPOSITIVO ANDROID';
    $text = 'Hemos detectado que estás intentando acceder directamente desde la App de Facebook con tu dispositivo Android.' . PHP_EOL . 'Actualmente Facebook para Android no es compatible con la subida de imágenes, por lo que te recomendamos que abras la página desde tu navegador web siguiendo estos dos sencillos pasos:' . PHP_EOL . PHP_EOL . '1- Pulsa sobre el menú de opciones:' . PHP_EOL . PHP_EOL . '2- Pulsa sobre la opción "Abrir en Internet":' . PHP_EOL . PHP_EOL . '<img alt="Tips" src="_img/error_fb_app_browser.jpg">' . PHP_EOL . PHP_EOL . 'Lamentamos las molestias.';
}
elseif (isset($_GET['android_tw_app_detect'])) /** Browsing under the native browser of twitter */
{
    $meta_title = 'Tu dispositivo móvil no permite subir imágenes';
    $title = 'OPSSS... ESTÁS ACCEDIENDO DESDE LA APP DE TWITTER CON UN DISPOSITIVO ANDROID';
    $text = 'Hemos detectado que estás intentando acceder directamente desde la App de Twitter con tu dispositivo Android.' . PHP_EOL . 'Actualmente Twitter para Android no es compatible con la subida de imágenes, por lo que te recomendamos que abras la página desde tu navegador web siguiendo estos dos sencillos pasos:' . PHP_EOL . PHP_EOL . '1- Pulsa sobre el menú de opciones:' . PHP_EOL . PHP_EOL . '2- Pulsa sobre la opción "Abrir en Internet":' . '<img alt="Tips" src="_img/error_fb_app_browser.jpg">' . PHP_EOL . PHP_EOL . 'Lamentamos las molestias.';
}
else
{
    $meta_title = 'Error inesperado';
    $title = '¡OPSSS, SE HA PRODUCIDO' . PHP_EOL . 'UN ERROR INESPERADO!';
    $text = 'Por favor, vuelve a intentarlo más tarde.';
}


/** We redirect to the homepage users who don't have the upload available and are not browsing under the native browser of facebook, previously we show an alert as the upload doesn't work */
if (isset($_GET['android_fb_app_detect']) && !preg_match('/(?=.*?(Android).*(Mobile).*(FB_IAB))/', $_SERVER['HTTP_USER_AGENT']))
{
    header('location:' . $GLOBALS['HTTP_WEB_ROOT'] . '/?fb_redirect=1');
    exit();
}

/** We redirect to the homepage users who don't have the upload available and are not browsing under the native browser of twitter, previously we show an alert as the upload doesn't work */
if (isset($_GET['android_tw_app_detect']) && !preg_match('/(?=.*?(TwitterAndroid))/', $_SERVER['HTTP_USER_AGENT']))
{
    header('location:' . $GLOBALS['HTTP_WEB_ROOT'] . '/?tw_redirect=1');
    exit();
}

include VIEWS_DIR . '/error.view.php';
exit();
?>
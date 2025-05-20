<?php

/**
 * App configuration
 */
/**
 * @filename: general_conf.inc.php
 * Location: _app/_conf
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
/** App version for force client contents reload */
define('APP_VERSION', 1);

/** APP identifier */
define('APP_ID', 'laSirena.emailing.20220407.app');

$GLOBALS['HTTP_WEB_MAIN_URL'] = 'https://lasirena.gestionproyectos.online';
/** To redirect */
$production_url = 'lasirena.gestionproyectos.online';
/** Production host */
$local_development_host = '192.168.2.175';
/** Development host */
$app_relative_path = '/__apps/emailing_lasirena'; //Phisical app path /** This value must start with "/" */
$url_relative_path = '/emailing-lasirena'; //http url path
$url_relative_main_path = ''; //http url path main domain

$GLOBALS['SESSION_NAME'] = 'PHPSESSID';

/** Set path constants */
if (is_int(strpos($_SERVER['HTTP_HOST'], $local_development_host)))
{
    // DEVELOPMENT
    $app_relative_path = '/projects/lasirena/public/__apps/emailing_lasirena';
    /** This value must start with "/" */
    $url_relative_path = '/lasirena/public/emailing-lasirena';
    $url_relative_main_path = '';

    define('IN_PRODUCTION', false);
    define('USE_SSL', true);
    define('USE_GTM', false);

    define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . $app_relative_path);
    define('MAIN_APP_ROOT', '');

    define('HTTP_WEB_ROOT', 'http://' . $local_development_host . $url_relative_path);
    define('HTTPS_WEB_ROOT', 'http://' . $local_development_host . $url_relative_path);
    define('HTTP_MAIN_WEB_ROOT', 'http://' . $local_development_host . $url_relative_main_path);
    define('HTTPS_MAIN_WEB_ROOT', 'http://' . $local_development_host . $url_relative_main_path);

    /** DATABASE CONNECTION */
    define('DATABASE', 'lasirena');
    define('DATABASE_HOST', '127.0.0.1');
    define('DATABASE_USER', 'novaigrup_lasirena');
    define('DATABASE_PASSWORD', 'H4q!y35o');
    define('DATABASE_PORT', 3306);

    /** EMAIL configuration */
    define('EMAIL_HOST', 'smtp-relay.sendinblue.com');
    define('EMAIL_SENDER_ADDRESS', 'no-reply@lasirena.gestionproyectos.online');
    define('EMAIL_SENDER_NAME', 'LaSirena');
    define('EMAIL_SMTP_AUTH', true);
    define('EMAIL_SMTP_AUTH_USER', 'sblue-lasirena@scsolutions.es');
    define('EMAIL_SMTP_AUTH_PASSWORD', 'xsmtpsib-4226e09c02a710b4b929df0500ea278528f1ba65184c7edccf2ca2c2843338fd-Ir4PtX6WUDFzphVj');
    define('EMAIL_PORT', 587);



    $GLOBALS['DEBUG_SQL'] = false;
    $GLOBALS['DEBUG_SQL_QUERIES'] = array();
}
else
{
    // PRODUCTION WWW
    define('IN_PRODUCTION', true);
    define('USE_SSL', true);
    define('USE_GTM', true);

    define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . $app_relative_path);
    define('MAIN_APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . $app_relative_path);

    define('HTTP_WEB_ROOT', 'http://' . $production_url . $url_relative_path);
    define('HTTPS_WEB_ROOT', 'https://' . $production_url . $url_relative_path);
    define('HTTP_MAIN_WEB_ROOT', 'http://' . $production_url . $url_relative_main_path);
    define('HTTPS_MAIN_WEB_ROOT', 'https://' . $production_url . $url_relative_main_path);

    /** DATABASE CONNECTION */
    define('DATABASE', 'lasirena');
    define('DATABASE_HOST', '127.0.0.1');
    define('DATABASE_USER', 'novaigrup_lasirena');
    define('DATABASE_PASSWORD', 'H4q!y35o');
    define('DATABASE_PORT', 3306);

    /** EMAIL configuration */
    define('EMAIL_HOST', 'smtp-relay.sendinblue.com');
    define('EMAIL_SENDER_ADDRESS', 'no-reply@lasirena.gestionproyectos.online');
    define('EMAIL_SENDER_NAME', 'LaSirena');
    define('EMAIL_SMTP_AUTH', true);
    define('EMAIL_SMTP_AUTH_USER', 'sblue-lasirena@scsolutions.es');
    define('EMAIL_SMTP_AUTH_PASSWORD', 'xsmtpsib-4226e09c02a710b4b929df0500ea278528f1ba65184c7edccf2ca2c2843338fd-Ir4PtX6WUDFzphVj');
    define('EMAIL_PORT', 587);
}

$GLOBALS['CHECK_TOUT_SESSION'] = (isset($_POST['check_session'])) ? true : false;

/** RESOURCES PATHS */
define('VIEWS_DIR', APP_ROOT . '/_app/_views');
define('VIEWS_INC_DIR', APP_ROOT . '/_app/_views/_includes');
define('CONTROLLERS_DIR', APP_ROOT . '/_app/_controller');
define('MODELS_CLASS_DIR', APP_ROOT . '/_app/_models/_class');
define('MODELS_INC_DIR', APP_ROOT . '/_app/_models/_includes');
define('PLUGINS_DIR', APP_ROOT . '/_app/_plugins');
define('TEMP_DIR', APP_ROOT . '/_app/_temp');

define('DIR_CSS', '/_css/');
define('DIR_JS', '/_js/');
define('DIR_IMG', '/_img/');

/** APP SECURITY CONSTANTS */
define('GOOGLE_RECAPTCHA_SITE_KEY', '6LcmSxIUAAAAAKCOBwjqMSyp07jgDQlQAVFzIpio'); //Need google re-captcha account
define('GOOGLE_RECAPTCHA_SECRET_KEY', '6LcmSxIUAAAAALPJP1bZ1VefZQ4S-pckZphnuu-K'); //Need google re-captcha account

define('APP_SALT', 'xXob+4duoBLCcIOLEIbUHA4qboWthhAyVwDH6ltYld11veULU64/ljqUfk85A8KrbHZhDVkyYtfpQeZJf/76AQ==');
define('APP_PBKDF2_ITERATIONS', 4096);
define('APP_PBKDF2_KEY_LENGTH', 20);
define('APP_PBKDF2_ALGORITHM', 'sha1');

define('PASSWORD_EXPIRATION_TIME', 7776000); //Seconds for password expiration Set 0 to disable [7776000 -> 3 months]
define('SESSION_INACTIVE_TIME_OUT', 1200); //Seconds Tout for session inactive user [1200 -> 20 min]
define('SESSION_ABSOLUTE_TIME_OUT', 43200); //Seconds absolute Tout for session [43200 -> 12 Hours]



/** DEBUG MODE */
require_once MODELS_CLASS_DIR . '/ip.class.php';
$debug = false;

$array_private_debug_ips = array(
    array('10.0.0.0', '10.255.255.255'),
    array('172.16.0.0', '172.31.255.255'),
    array('192.168.0.0', '192.168.255.255'),
    array('169.254.0.0', '169.254.255.255')
);
foreach ($array_private_debug_ips as $ip_range)
{
    if (ip2long(Ip::getIp()) <= ip2long($ip_range[1]) && ip2long($ip_range[0]) <= ip2long(Ip::getIp()))
    {
        $debug = true;
    }
}

$array_public_debug_ips = array(
    'Novaigrup 1' => '188.119.218.21',
    'Novaigrup 2' => '80.28.245.19'
);
if (in_array(Ip::getIp(), $array_public_debug_ips))
{
    $debug = true;
}

define('DEBUG', $debug);

$GLOBALS['CREATE_TABLES'] = true;

$GLOBALS['HTTP_WEB_ROOT'] = (USE_SSL) ? HTTPS_WEB_ROOT : HTTP_WEB_ROOT;
$GLOBALS['HTTP_MAIN_WEB_ROOT'] = (USE_SSL) ? HTTPS_MAIN_WEB_ROOT : HTTP_MAIN_WEB_ROOT;
$GLOBALS['DATABASE_PREFIX'] = 'gp_ls_';
$GLOBALS['DATABASE_ENGINE'] = 'MyISAM';

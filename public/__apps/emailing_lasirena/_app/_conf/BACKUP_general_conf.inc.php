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
use Drupal\Core\Database\Database;

$database_info = Database::getConnectionInfo('default');

/** App version for force client contents reload */
define('APP_VERSION', 5);

/** APP identifier */
define('APP_ID', 'nhs.emailing-nhs.20190806.app');

$GLOBALS['HTTP_WEB_MAIN_URL'] = 'https://www.nestlehealthscience.es';/** To redirect */
$production_url = 'www.clickcumple.nestlehealthscience.es';/** Production host */
$preproduction_url = 'prod.clickcumple.nestlehealthscience.es';/** Production host */
$stage_url = 'dev-72813-nestlehealthscience-clickcumple-spain.pantheonsite.io';/** Staging host */
$test_url = 'test-72813-nestlehealthscience-clickcumple-spain.pantheonsite.io';/** Test host */
$live_preproduction_url = 'live-72813-nestlehealthscience-clickcumple-spain.pantheonsite.io';/** live host */
$local_development_host = '192.168.2.56:8888';/** Development host */
//$app_relative_path = '/__apps/_emailing_nhs'; //Phisical app path /** This value must start with "/" */
//$url_relative_path = '/emailing-nhs'; //http url path
//$url_relative_main_path = ''; //http url path main domain

$app_relative_path = '/modules/custom/emailing_nhs/src/assets/emailing_nhs';/** This value must start with "/" */
$url_relative_path = '/emailing-nhs';
$url_relative_main_path = '/emailing-nhs';
$url_resources_relative_path = '/modules/custom/emailing_nhs/src/assets/emailing_nhs';

$GLOBALS['SESSION_NAME'] = 'PHPSESSID';

/** Set path constants */
if (is_int(strpos($_SERVER['HTTP_HOST'], $stage_url)))
{
    //STAGING (Development)
//    $app_relative_path = '/modules/custom/emailing_nhs/src/assets/emailing_nhs';/** This value must start with "/" */
//    $url_relative_path = '/emailing-nhs';
//    $url_relative_main_path = '/emailing-nhs';
//    $url_resources_relative_path = '/modules/custom/emailing_nhs/src/assets/emailing_nhs';

    define('IN_PRODUCTION', false);
    define('USE_SSL', true);
    define('USE_GTM', false);

    define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . $app_relative_path);
    define('MAIN_APP_ROOT', '');

    define('HTTP_WEB_ROOT', 'http://' . $stage_url . $url_relative_path);
    define('HTTPS_WEB_ROOT', 'https://' . $stage_url . $url_relative_path);
    define('HTTP_MAIN_WEB_ROOT', 'http://' . $stage_url . $url_relative_main_path);
    define('HTTPS_MAIN_WEB_ROOT', 'https://' . $stage_url . $url_relative_main_path);

    define('HTTP_RESOURCES_WEB_ROOT', 'http://' . $stage_url . $url_resources_relative_path);
    define('HTTPS_RESOURCES_WEB_ROOT', 'https://' . $stage_url . $url_resources_relative_path);

    /** DATABASE CONNECTION */
    define('DATABASE', $database_info['default']['database']);
    define('DATABASE_HOST', $database_info['default']['host']);
    define('DATABASE_USER', $database_info['default']['username']);
    define('DATABASE_PASSWORD', $database_info['default']['password']);
    define('DATABASE_PORT', $database_info['default']['port']);
}
elseif (is_int(strpos($_SERVER['HTTP_HOST'], $test_url)))
{
    define('IN_PRODUCTION', false);
    define('USE_SSL', true);
    define('USE_GTM', false);

    define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . $app_relative_path);
    define('MAIN_APP_ROOT', '');

    define('HTTP_WEB_ROOT', 'http://' . $test_url . $url_relative_path);
    define('HTTPS_WEB_ROOT', 'https://' . $test_url . $url_relative_path);
    define('HTTP_MAIN_WEB_ROOT', 'http://' . $test_url . $url_relative_main_path);
    define('HTTPS_MAIN_WEB_ROOT', 'https://' . $test_url . $url_relative_main_path);

    define('HTTP_RESOURCES_WEB_ROOT', 'http://' . $test_url . $url_resources_relative_path);
    define('HTTPS_RESOURCES_WEB_ROOT', 'https://' . $test_url . $url_resources_relative_path);

    /** DATABASE CONNECTION */
    define('DATABASE', $database_info['default']['database']);
    define('DATABASE_HOST', $database_info['default']['host']);
    define('DATABASE_USER', $database_info['default']['username']);
    define('DATABASE_PASSWORD', $database_info['default']['password']);
    define('DATABASE_PORT', $database_info['default']['port']);
}
elseif (is_int(strpos($_SERVER['HTTP_HOST'], $live_preproduction_url)))
{
    define('IN_PRODUCTION', true);
    define('USE_SSL', true);
    define('USE_GTM', true);

    define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . $app_relative_path);
    define('MAIN_APP_ROOT', '');

    define('HTTP_WEB_ROOT', 'http://' . $live_preproduction_url . $url_relative_path);
    define('HTTPS_WEB_ROOT', 'https://' . $live_preproduction_url . $url_relative_path);
    define('HTTP_MAIN_WEB_ROOT', 'http://' . $live_preproduction_url . $url_relative_main_path);
    define('HTTPS_MAIN_WEB_ROOT', 'https://' . $live_preproduction_url . $url_relative_main_path);

    define('HTTP_RESOURCES_WEB_ROOT', 'http://' . $live_preproduction_url . $url_resources_relative_path);
    define('HTTPS_RESOURCES_WEB_ROOT', 'https://' . $live_preproduction_url . $url_resources_relative_path);

    /** DATABASE CONNECTION */
    define('DATABASE', $database_info['default']['database']);
    define('DATABASE_HOST', $database_info['default']['host']);
    define('DATABASE_USER', $database_info['default']['username']);
    define('DATABASE_PASSWORD', $database_info['default']['password']);
    define('DATABASE_PORT', $database_info['default']['port']);
}
elseif (is_int(strpos($_SERVER['HTTP_HOST'], $local_development_host)))
{
    // DEVELOPMENT
    $app_relative_path = '/72813-nestlehealthscience-clickcumple-spain/modules/custom/emailing_nhs/src/assets/emailing_nhs';/** This value must start with "/" */
    $url_relative_path = '/72813-nestlehealthscience-clickcumple-spain/emailing-nhs';
    $url_relative_main_path = '/72813-nestlehealthscience-clickcumple-spain/emailing-nhs';
    $url_resources_relative_path = '/72813-nestlehealthscience-clickcumple-spain/modules/custom/emailing_nhs/src/assets/emailing_nhs';

    define('IN_PRODUCTION', false);
    define('USE_SSL', false);
    define('USE_GTM', false);

    define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . $app_relative_path);
    define('MAIN_APP_ROOT', '');

    define('HTTP_WEB_ROOT', 'http://' . $local_development_host . $url_relative_path);
    define('HTTPS_WEB_ROOT', 'https://' . $local_development_host . $url_relative_path);
    define('HTTP_MAIN_WEB_ROOT', 'http://' . $local_development_host . $url_relative_main_path);
    define('HTTPS_MAIN_WEB_ROOT', 'https://' . $local_development_host . $url_relative_main_path);

    define('HTTP_RESOURCES_WEB_ROOT', 'http://' . $local_development_host . $url_resources_relative_path);
    define('HTTPS_RESOURCES_WEB_ROOT', 'https://' . $local_development_host . $url_resources_relative_path);

    /** DATABASE CONNECTION */
    define('DATABASE', $database_info['default']['database']);
    define('DATABASE_HOST', $database_info['default']['host']);
    define('DATABASE_USER', $database_info['default']['username']);
    define('DATABASE_PASSWORD', $database_info['default']['password']);
    define('DATABASE_PORT', $database_info['default']['port']);

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

    define('HTTP_RESOURCES_WEB_ROOT', 'http://' . $production_url . $url_resources_relative_path);
    define('HTTPS_RESOURCES_WEB_ROOT', 'https://' . $production_url . $url_resources_relative_path);

    /** DATABASE CONNECTION */
    define('DATABASE', $database_info['default']['database']);
    define('DATABASE_HOST', $database_info['default']['host']);
    define('DATABASE_USER', $database_info['default']['username']);
    define('DATABASE_PASSWORD', $database_info['default']['password']);
    define('DATABASE_PORT', $database_info['default']['port']);
}

$GLOBALS['CHECK_TOUT_SESSION'] = (isset($_POST['check_session']) ) ? true : false;

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


/** @MSA - 20210609 - Replaced with old email due to SARENET issues */
/** EMAIL configuration */
//define('EMAIL_HOST', 'correo.sarenet.es');
//define('EMAIL_SENDER_ADDRESS', 'nhs@noreply.nestlehealthscience.es');
//define('EMAIL_SENDER_NAME', 'Nestlé Health Science');
//define('EMAIL_SMTP_AUTH', true);
//define('EMAIL_SMTP_AUTH_USER', 'clickcumple@noreply.nestlehealthscience.es');
//define('EMAIL_SMTP_AUTH_PASSWORD', 'R7gb*U(9');

/** EMAIL configuration */
define('EMAIL_HOST', 'correo.sarenet.es');
define('EMAIL_SENDER_ADDRESS', 'nhs@noreply.nestlehealthscience.es');
define('EMAIL_SENDER_NAME', 'Nestlé Health Science');
define('EMAIL_SMTP_AUTH', true);
define('EMAIL_SMTP_AUTH_USER', 'mail@noreply.nestle.es');
define('EMAIL_SMTP_AUTH_PASSWORD', 'LFS4mcG7');


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
$GLOBALS['HTTP_RESOURCES_WEB_ROOT'] = (USE_SSL) ? HTTPS_RESOURCES_WEB_ROOT : HTTP_RESOURCES_WEB_ROOT;
$GLOBALS['DATABASE_PREFIX'] = 'penm_';
$GLOBALS['DATABASE_ENGINE'] = 'MyISAM';

//TRACKING URL NHS PLATFORM
$GLOBALS['TRACKING_URL_NHS_PLATFORM_REPLACE_STRING'] = '[-[nhs_tracking_url]-]';
$GLOBALS['TRACKING_URL_NHS_PLATFORM'] = 'https://clickcumple.nestlehealthscience.es/emailing-nhs/link.html?newsletter_reference=[-[newsletter_reference]-]&email_recipient=[-[email_recipient]-]';

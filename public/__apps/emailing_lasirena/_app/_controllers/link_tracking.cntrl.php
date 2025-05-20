<?php

/**
 * Controller
 */
/**
 * @filename: link_tracking.cntrl.php
 * Location: _app/_controllers
 * @Creator: MA. Sanchez (MSA) <info@novaigrup.com>
 *  20201028 MSA Created
 */
require_once 'common.cntrl.inc.php';

/** Click tracking system */
require_once MODELS_CLASS_DIR . '/emailing_system.class.php';
$obj_emailing = new EmailingSystem();

$user_agent = ( isset($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : '-';
$click_id = AntiXSS::xssFilter($_GET['click_id']);
$newsletter_reference = AntiXSS::xssFilter($_GET['newsletter_reference']);
$email_recipient = AntiXSS::xssFilter($_GET['email_recipient']);

if ($obj_emailing->isEmailInRecipientList($email_recipient))
{
    $obj_emailing->linkTracking($click_id, $newsletter_reference, $email_recipient, $user_agent);
}
else
{
    $GLOBALS['obj_log_event']->saveLogEvent(LogEvent::LOG_EVENT_APPLICATION, 'Click on email ' . $newsletter_reference . ' from email ' . $email_recipient . ' that does not belong to email recipients.', 0);
}

/** Destination */
$array_url = parse_url($_GET['url']);

/** Platform tracking */
$platform_tracking = '';
if (strpos($array_url['path'], 'baja.html') || isset($array_url['query']))
{
    $platform_tracking = 'email=' . AntiXSS::xssFilter($_GET['email_recipient']) . '&token=' . AntiXSS::xssFilter($_GET['token']) . '&newsletter_reference=' . $newsletter_reference;
}

//Seguimiento analytics
$analytics_tracking = '';
if (isset($_GET['utm_content']))
{
    $analytics_tracking = '&utm_source=' . AntiXSS::xssFilter($_GET['utm_source']) . '&utm_medium=' . AntiXSS::xssFilter($_GET['utm_medium']) . '&utm_term=' . AntiXSS::xssFilter($_GET['utm_term']) . '&utm_content=' . AntiXSS::xssFilter($_GET['utm_content']) . '&utm_campaign=' . AntiXSS::xssFilter($_GET['utm_campaign']);
}

//Si en la URL nos han indicado algún parámetro OJO los parámetros de analytics no se tienen que pasar
if ($array_url['query'] != '')
{
    header('Location: ' . AntiXSS::xssFilter($_GET['url']) . '&' . $platform_tracking . $analytics_tracking);
}
else
{
    if ($platform_tracking == '' && $analytics_tracking == '')
    {
        header('Location: ' . AntiXSS::xssFilter($_GET['url']));
    }
    else
    {
        header('Location: ' . AntiXSS::xssFilter($_GET['url']) . '?' . $platform_tracking . $analytics_tracking);
    }
}

exit();

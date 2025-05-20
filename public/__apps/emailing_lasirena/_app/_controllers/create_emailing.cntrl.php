<?php

/**
 * Controller
 */
/**
 * @filename: create_emailing_test.cntrl.php
 * Location: _app/_controllers
 * @Creator: MA. Sanchez (MAS) <info@novaigrup.com>
 *  20200921 MAS Created
 */
require_once 'common.cntrl.inc.php';

set_time_limit(0);

/** Emailing system */
require_once MODELS_CLASS_DIR . '/emailing_system.class.php';

$newsletter_reference = 'NHS_20220405_23';
//$newsletter_reference = 'NHS_20220406_20';
//$newsletter_reference = 'NHS_20220406_28';
$subject = '¿Conoces las enzymas sistémicas de Wobecare?';
//$subject = 'Descubre la nueva IMAGEN de WeAreNutrition';
//$subject = 'Descubre WeAreNutrition: la nueva plataforma digital experta en nutrición';

/** TODO: revisar si el fichero existe, lo hace getHtmlFromFile?? */
$newsletter_html_file = $GLOBALS['HTTP_WEB_ROOT'] . '/emails/' . $newsletter_reference . '/email.html';

$obj_emailing = new EmailingSystem();

if (($html_source = $obj_emailing->getHtmlFromFile($newsletter_html_file)) === false)
{
    NotificationsHandler::notify('101', false);
    exit;
}

/** Email historic insert */ 
/*if ($obj_emailing->saveHistoric($newsletter_reference, $subject, $html_source) === false)
{
    NotificationsHandler::notify('100', false);
    exit;
}*/

/** Email recipient list */
//$email_list = $obj_emailing->getEmailRecipients($newsletter_reference);

if ($obj_emailing->fillSendingQueue($email_list) === false)
{
    NotificationsHandler::notify('100', false);
    exit;
}

echo '<h1>Email ' . $newsletter_reference . ' creado!</h1>';
echo '<p>Comprueba la tabla <b>penm_historic</b> y <b>penm_sending_queue</b>.</p>';
exit;

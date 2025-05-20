<?php

/**
 * Controller
 */
/**
 * @filename: unsuscribe.cntrl.php
 * Location: _app/_controllers
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20190806 RBM Created
 */
require_once 'common.cntrl.inc.php';

//CSRF token
require_once MODELS_CLASS_DIR . '/csrf_token.class.php';
$obj_csrf_token = new CSRFToken(APP_SALT);

/** Emailing system */
require_once MODELS_CLASS_DIR . '/emailing_system.class.php';

$email = AntiXSS::xssFilter($_GET['email']);
$token = AntiXSS::xssFilter($_GET['token']);
$newsletter_reference = AntiXSS::xssFilter($_GET['newsletter_reference']);

$obj_emailing = new EmailingSystem();
if ($obj_emailing->valideRecipientToken($email, $token) === false)
{
    NotificationsHandler::notify('201', false);
    exit;
}


if (count($_POST) > 0)
{
    if (isset($_POST['g-recaptcha-response']))
    {
        if ($obj_csrf_token->validateCSRFToken($_POST['csrf_token']))
        {
            require_once MODELS_CLASS_DIR . '/recaptcha.class.php';
            require_once MODELS_CLASS_DIR . '/ip.class.php';

            // Google reCaptcha Validation
            $googleReCaptcha = new ReCaptcha(GOOGLE_RECAPTCHA_SECRET_KEY);
            $response = $googleReCaptcha->verifyResponse(Ip::getIp(), $_POST['g-recaptcha-response']);
            if ($response->success === true)
            {
                $GLOBALS['obj_log_event']->saveLogEvent(LogEvent::LOG_EVENT_CAPTCHA, '', 1);
                $_SESSION['captcha_ok'] = true;


                if ($obj_emailing->unsuscribe($email, $token) === true)
                {

                    header('Location:' . $GLOBALS['HTTP_WEB_ROOT'] . '/gracias.html');
                    exit();
                }
                else
                {
                    $GLOBALS['obj_log_event']->saveLogEvent(LogEvent::LOG_EVENT_APPLICATION, 'Error al dar de baja al usuario:' . $email . ' con token:' . $token, 0);
                }
            }
            else
            {
                $GLOBALS['obj_log_event']->saveLogEvent(LogEvent::LOG_EVENT_CAPTCHA, 'Captcha incorrecto', 0);
            }
        }
    }

    NotificationsHandler::notify('200', false);
    exit;
}

include VIEWS_DIR . '/unsuscribe.view.php';
exit();
?>

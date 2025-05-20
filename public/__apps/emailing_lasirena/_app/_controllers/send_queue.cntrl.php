<?php

/**
 * Controller
 */
/**
 * @filename: send_queue.cntrl.php
 * Location: _app/_controllers
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20190806 RBM Created
 *  20201110 MSA Modified: Added log impact
 */

set_time_limit(0);
require_once 'common.cntrl.inc.php';

require_once MODELS_CLASS_DIR . '/phpmailer.class.php';
require_once MODELS_CLASS_DIR . '/smtp.class.php';
require_once MODELS_CLASS_DIR . '/emailing_system.class.php';

$obj_emailing_system = new EmailingSystem();

/** Fulfill empty tokens */
//$obj_emailing_system->generateEmptyTokens();

$email_por_envio = 20; // Emails por envío

$mail = new PHPMailer();
/** DEBUG */
$mail->SMTPDebug = 2;
$mail->isSMTP();
$mail->Host = EMAIL_HOST;
$mail->SMTPKeepAlive = true;
//$mail->Port = 25;
$mail->Port = EMAIL_PORT;
$mail->SMTPAuth = EMAIL_SMTP_AUTH;
$mail->Username = EMAIL_SMTP_AUTH_USER;
$mail->Password = EMAIL_SMTP_AUTH_PASSWORD;
$mail->setFrom(EMAIL_SENDER_ADDRESS, EMAIL_SENDER_ADDRESS);
$mail->CharSet = 'UTF-8';
$mail->FromName = EMAIL_SENDER_NAME;


for ($i = 0; $i < $email_por_envio; $i++)
{
    $sop = 'SELECT * FROM ' . $GLOBALS['DATABASE_PREFIX'] . 'sending_queue WHERE processed = 0 ORDER BY queue_id ASC LIMIT 1';
    $result = $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');

    if (count($result) > 0)
    {
        foreach ($result as $list)
        {
            //Bloquemos el registro para que no se vuelva a leer
            $sop_update = 'UPDATE ' . $GLOBALS['DATABASE_PREFIX'] . 'sending_queue SET processed = 1 WHERE queue_id = ?';
            $parameters_update = array('i', $list['queue_id']);
            $result_update = $GLOBALS['obj_mysqli']->executePreparedStatement($sop_update, $parameters_update);


            $mail->Subject = $list['subject'];

            $mail->addReplyTo($list['email_replyto'], '');
            $mail->addAddress($list['email_recipient']);
            //$mail->msgHTML($list['message']);
            $mail->Body = $list['message'];
            $mail->IsHTML(true);

            if ($mail->Send())
            {
                echo 'Sent';

                $obj_emailing_system->registerImpactLog($list['newsletter_reference'], $list['email_recipient']);

                /*$sop_historic = 'UPDATE ' . $GLOBALS['DATABASE_PREFIX'] . 'historic SET newsletter_finish_date = ? WHERE newsletter_reference = ?';
                $parameters_historic = array('ss', date('Y-m-d H:i:s', time()), $list['newsletter_reference']);
                $GLOBALS['obj_mysqli']->executePreparedStatement($sop_historic, $parameters_historic);*/

                $sop_delete = 'DELETE FROM ' . $GLOBALS['DATABASE_PREFIX'] . 'sending_queue WHERE queue_id = ?';
                $parameters_delete = array('i', $list['queue_id']);
                $GLOBALS['obj_mysqli']->executePreparedStatement($sop_delete, $parameters_delete);
            }
            else
            {
                echo 'Error sending: <br>';
                $GLOBALS['obj_log_event']->saveLogEvent(LogEvent::LOG_EVENT_APPLICATION, 'Error sending email ' . $list['newsletter_reference'] . ': ' . $mail->ErrorInfo, 0);
            }

            $mail->clearAddresses();
            $mail->clearAttachments();
            $mail->clearReplyTos();
        }
    }
}

$mail->smtpClose();

exit();

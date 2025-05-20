<?php

/**
 * Controller
 */
/**
 * @filename: open_rate_traking.cntrl.php
 * Location: _app/_controllers
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20190806 RBM Created
 */
require_once 'common.cntrl.inc.php';

$newsletter_reference = AntiXSS::xssFilter($_GET['newsletter_reference']);
$email_recipient = AntiXSS::xssFilter($_GET['email_recipient']);

/** Emailing system */
require_once MODELS_CLASS_DIR . '/emailing_system.class.php';

$obj_emailing = new EmailingSystem();
$obj_emailing->openRateTracking($newsletter_reference, $email_recipient);

$img_pixel = imagecreate(1, 1);
$color = imagecolorallocatealpha($img_pixel, 255, 255, 255, 127);
imagesetpixel($img_pixel, 1, 1, $color);
header('content-type:image/jpg');
imagejpeg($img_pixel);
imagedestroy($img_pixel);
exit();

<?php

/**
 * Controller
 */
/**
 * @filename: index.cntrl.php
 * Location: _app/_controllers
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */

require_once 'common.cntrl.inc.php';

/** Emailing system */
require_once MODELS_CLASS_DIR . '/emailing_system.class.php';

$obj_emailing_system = new EmailingSystem();

//header('Location: ' . $GLOBALS['HTTP_WEB_MAIN_URL']);
exit;
?>
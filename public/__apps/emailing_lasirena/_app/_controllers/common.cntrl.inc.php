<?php

/**
 * Controller
 */
/**
 * Common controller (INCLUDED IN ALL SCRIPTS)
 * @filename: common.cntrl.inc.php
 * Location: _app/_controllers
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
/** Configuration file */
/** case - NHS2020, NHS2100850 {recipient XXX} */
require_once __DIR__ . '/../_conf/general_conf.inc.php';
require_once __DIR__ . '/../_models/_includes/security.inc.php';


// /** Standard error handler */
// require_once  '../_models/_includes/error_handler.inc.php';

// /** These scripts are applied to avoid problems arising from server dependencies */
// require_once MODELS_INC_DIR . '/host_normalization.inc.php';

// /** Avoid accessing the browser cache */
// require_once MODELS_INC_DIR . '/no_cache.inc.php';

/** Required classes */
/** Data validation */
require_once '../_models/_class/data_validation.class.php';
$obj_data_validation = new DataValidation();
$obj_data_validation->validateData();

/** Header validation */
require_once '../_models/_class/headers_validation.class.php';
HeadersValidation::validate();

/** HTTPS navigation */
require_once '../_models/_class/https_navigation.class.php';
HttpsNavigation::setPageProtocol(USE_SSL);

/** MySqli class */
require_once '../_models/_class/mysqli.class.php';
$GLOBALS['obj_mysqli'] = new MySQL(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE, DATABASE_PORT);
$obj_mysqli = new MySQL(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE, DATABASE_PORT);

/** Log events */
require_once '../_models/_class/log_events.class.php';
$GLOBALS['obj_log_event'] = new LogEvent();

/** AntiXss */
require_once '../_models/_class/xss_filter.class.php';

/** Notification handler */
require_once '../_models/_class/notifications_handler.class.php';

session_start();
if (!isset($not_session_regenerate_id))
    session_regenerate_id(true);
?>
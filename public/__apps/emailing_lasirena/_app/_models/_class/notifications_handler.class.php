<?php

/**
 * Manages and directs the user to the page system notifications
 */

/**
 * Handle notifications messages and close active session
 * @filename: notifications_handler.class.php
 * Location: _app/_models/_class
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
class NotificationsHandler
{

    /**
     * Redirects user to the page system notifications
     * 
     * @param integer $notification_code
     * @param bool $abort_session
     * @access public	
     */
    public static function notify($notification_code, $abort_session = true)
    {
        if ($abort_session)
            NotificationsHandler::abortSession();
        header('location:' . $GLOBALS['HTTP_WEB_ROOT'] . '/notificacion.html?cod=' . $notification_code);
        exit();
    }

    /**
     * Aborting current sessions
     * @access public
     */
    public static function abortSession()
    {
        session_start();

        /** Unsets all of session variables. */
        $_SESSION = array();

        /** Also delete the cookie session. */
        /** Note: This will destroy the session, and not just the session data! */
        if (ini_get('session.use_cookies'))
        {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }

        /** Finally, destroy the session. */
        session_destroy();
    }

}

?>
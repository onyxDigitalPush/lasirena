<?php

/**
 * Secures the page like https or not depending on the is_https parameter
 */

/**
 * Secures the page like https or not depending on the is_https parameter
 * Function called from sites that try a secure connection
 * @filename: https_navigation.class.php
 * Location: _app/_models/_class
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 * 		20181212 RBM Created
 *      20190405 ABF 301 redirection on protocol change
 */
class HttpsNavigation
{

    /**
     * Switch from HTTP to HTTPS
     * @param bool $is_https HTTPS flag
     * @param bool $send_header_301
     * @access public
     */
    public static function setPageProtocol($is_https, $send_header_301 = true)
    {
        if ($is_https)
        {
            /** Version HTTPS called from the current page */
            $host = (isset($_SERVER['HTTP_X_ORIGINAL_HOST'])) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'];
            $https_url = 'https://' . $host . $_SERVER['REQUEST_URI'];

            if (!isset($_SERVER["HTTPS"]) && !isset($_SERVER['HTTP_X_ORIGINAL_PROTOCOL']) && !isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && !isset($_SERVER['HTTP_X_FORWARDED_PROTOCOL']))
            {
                /** If this function isn't called via HTTPS, redirects to the same page with the desired protocol */
                if ($send_header_301)
                {
                    header("HTTP/1.1 301 Moved Permanently");
                }
                header('location:' . $https_url);
                exit();
            }
            elseif (isset($_SERVER['HTTP_X_ORIGINAL_PROTOCOL']) && $_SERVER['HTTP_X_ORIGINAL_PROTOCOL'] != 'https')
            {
                /** Enrutamiento Incapsula */
                if ($send_header_301)
                {
                    header("HTTP/1.1 301 Moved Permanently");
                }
                header('location:' . $https_url);
                exit();
            }
        }
    }

}

?>
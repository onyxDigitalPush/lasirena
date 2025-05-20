<?php

/**
 * Get current IP
 */

/**
 * Get current IP
 * @filename: ip.class.php
 * Location: _app/_models/_class
 * @Creator: J. Raya (JRM) <info@novaigrup.com>
 *  20190516 JRM Created
 */
class Ip
{

    /**
     * Method for extracting the actual user IP
     * @return string The actual user IP, with an invalid direction returns 0.0.0.0
     * @access public
     */
    public static function getIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $valid_ip = long2ip(ip2long($_SERVER['HTTP_CLIENT_IP']));

            if ($valid_ip != '0.0.0.0')
            {
                return $valid_ip;
            }
        }
        /** First, the function checks if the user navigates through a proxy. Then, validates the IP to rule out possible XSS */
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $array_ips_proxy = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $valid_ip = long2ip(ip2long($array_ips_proxy[0]));

            if ($valid_ip != '0.0.0.0')
            {
                return $valid_ip;
            }
        }

        $ip = $_SERVER['REMOTE_ADDR'];/** If the user does not navigate through a proxy, the function validates the IP with REMOTE_ADDR */
        $valid_ip = long2ip(ip2long($ip));

        return $valid_ip;
    }

}

?>
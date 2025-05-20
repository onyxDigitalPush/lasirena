<?php
/**
 * Block access to logs
 * @filename: log.php
 * Location: _app/_controllers
 * @Creator: A. Bellavista (ABF) <info@novaigrup.com>
 *  20181212 ABF Created
 */

/**
 * Method for extracting the actual user IP
 * @return string The actual user IP, with an invalid direction returns 0.0.0.0
 * @access public
 */
function getIp()
{
    /** First, the function checks if the user navigates through a proxy. Then, validates the IP to rule out possible XSS */
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        $array_ips_proxy = explode(', ', $_SERVER["HTTP_X_FORWARDED_FOR"]);
        //$valid_ip = long2ip(ip2long(array_pop($array_ips_proxy)));
        $valid_ip = long2ip(ip2long($array_ips_proxy[0]));
    }
    else
    {
        $ip = $_SERVER["REMOTE_ADDR"];/** If the user does not navigate through a proxy, the function validates the IP with REMOTE_ADDR */
        $valid_ip = long2ip(ip2long($ip));
    }

    return $valid_ip;
}

//Allowed IP that can access to log
$allowed_ip = array('80.28.245.19', '188.119.218.21');

//Remote IP
$remote_ip = getIp();

if (in_array($remote_ip, $allowed_ip))
{
    include_once('errors_reg.html');
    exit();
}
header('HTTP/1.0 403 Forbidden');
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
        <title>403 Forbidden</title>
    </head><body>
        <h1>Forbidden</h1>
        <p>You don't have permission to access /_logs/ on this server.</p>
    </body></html>
<?php
exit();
?>
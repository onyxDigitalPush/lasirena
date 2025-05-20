<?php

/**
 * Host normalization
 */
/**
 * @filename: host_normalization.inc.php
 * Location: _app/_models/_includes
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 * 	20150513 RBM Created
 */
/** SET DEFAULT TIME ZONE */
date_default_timezone_set('Europe/Madrid');


/** LIMIT CONTROL VARIABLES RECEIVED BY POST */
/** In PHP versions > 5.3, by default this var is stablished to 1000 */
$max_input_vars = 1000;
if ($_POST)
{
    if (count($_POST) > $max_input_vars)
    {
        trigger_error("No se ha podido realizar la operación, se ha superado el número máximo de variables en el envío (max_input_vars): " . count($_POST), E_USER_ERROR);
        die();
    }
}
unset($max_input_vars);


/** "MAGIC QUOTES" PROPERTY NORMALIZATION */
$input = array(&$_GET, &$_POST, &$_COOKIE, &$_ENV, &$_SERVER);

if (get_magic_quotes_gpc())
{
    while (list( $k, $v ) = each($input))
    {
        foreach ($v as $key => $val)
        {
            if (!is_array($val))
            {
                $input[$k][$key] = stripslashes($val);
                continue;
            }
            $input[] = & $input[$k][$key];
        }
    }
    unset($input);
}

/** FORCE UTF-8 CONTENT TYPE */
header('Content-Type: text/html; charset=utf-8');

/** CANONICALIZATION: prevent SEO and Cookies problems */
// HTTP_X_ORIGINAL_HOST -> Devuelve el HOST original al trabajar con enrutamientos internos via proxy (NESTLÉ)
$served_url = (isset($_SERVER['HTTP_X_ORIGINAL_HOST'])) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'];
$real_url = parse_url($GLOBALS['HTTP_WEB_ROOT']);
$real_url_host = $real_url["host"];
$real_url_host .= ($real_url["port"] != 80 && $real_url["port"] != 443 && is_numeric($real_url["port"])) ? ':' . $real_url["port"] : '';

if ($served_url != $real_url_host)
{
    header("HTTP/1.1 301 Moved Permanently");
    $canonical_url = ( $_SERVER["HTTPS"] == "on" ) ? 'https://' : 'http://';
    if ($real_url["port"] != 80 && $real_url["port"] != 443)
    {
        $canonical_url .= $real_url["host"] . ':' . $real_url["port"] . $_SERVER['REQUEST_URI'];
    }
    else
    {
        $canonical_url .= $real_url["host"] . $_SERVER['REQUEST_URI'];
    }

    header('Location: ' . $canonical_url);
    exit();
}
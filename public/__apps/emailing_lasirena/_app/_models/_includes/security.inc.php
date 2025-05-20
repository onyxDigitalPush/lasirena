<?php

/**
 * Security script
 */
/**
 * @filename: security.inc.php
 * Location: _app/_models/_includes
 * @Creator: J. Raya (JRM) <info@novaigrup.com>
 * 	20200902 JRM Created
 */
//Disable the Apache Cache in PHP files
header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');


//Cache-Control
header('Cache-Control: max-age=2628000, private');

//Enable HSTS (Http Strict Transport Security)
header('Strict-Transport-Security: max-age=31536000');

//Enable X-Xss-Protection
header('X-Xss-Protection: 1; mode=block');

/*
  Prevents Google Chrome and Internet Explorer from trying to mime-sniff the
  content-type of a response away from the one being declared by the server.
  It reduces exposure to drive-by downloads and the risks of user uploaded content
  that, with clever naming, could be treated as a different content-type, like an executable
  IMPORTANT: Es necesario incluir el encabezado http correcto para los recursos generados dinámicamente como imágenes, js etc.
 */
header('X-Content-Type-Options: nosniff');

//Referral policy. Default unsafe-url
header('Referrer-Policy: unsafe-url');

//Disable external Iframes
header('X-Frame-Options: SAMEORIGIN');

//Disable file uploads
ini_set('upload_max_filesize', '1byte');

#####################################
# Securized use of cookies
#####################################

if (is_int(strpos($_SERVER['HTTP_HOST'], $local_development_host)) === false && is_int(strpos($_SERVER['HTTP_HOST'], $localhost_development_host)) === false)
{
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
}

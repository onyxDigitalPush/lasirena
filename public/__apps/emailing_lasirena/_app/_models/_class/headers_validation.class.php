<?php

/**
 * CLASS RESPONSIBLE TO VALIDATE SERVERS HEADERS
 */

/**
 * CLASS RESPONSIBLE TO VALIDATE SERVERS HEADERS
 * @filename: headers_validation.class.php
 * Location: _app/_models/_class
 * @Creator: J. Raya (JRM) <info@novaigrup.com>
 * 	20190708 JRM Created
 */
class HeadersValidation
{

    /**
     * Validate servers headers
     */
    public static function validate()
    {
        $headers = apache_request_headers();

        $can_continue = true;

        foreach ($headers as $header => $value)
        {
            if (strtoupper($header) == 'X-HTTP-METHOD' || strtoupper($header) == 'X-HTTP-METHOD-OVERRIDE' || strtoupper($header) == 'X-METHOD-OVERRIDE')
            {
                if (strtoupper($value) != 'GET' && strtoupper($value) != 'POST')
                {
                    $can_continue = false;
                }
            }
        }

        if (!$can_continue)
        {
            header('HTTP/1.0 403 Forbidden');
            die('403 Forbidden');
        }
    }

}

?>
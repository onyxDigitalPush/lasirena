<?php

/**
 * TOKEN CSRF (Cross Site Request Forgery). Class of securitization forms. 
 */

/**
 * Prevent Cross Site Request Forgery attacks
 * @filename: csrf_token.class.php
 * Location: _app/_models/_class
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 * 	20150420 RBM Created
 */
class CSRFToken
{

    /**
     * @var string Private key
     * @access private
     */
    private $private_key;

    /**
     * Class constructor
     * @param string $private_key
     * @access public
     */
    public function __construct($private_key)
    {
        if (!$private_key)
        {
            throw new ErrorException('CSRF Error: constructor without private key', 2);
            return false;
        }

        $this->private_key = $private_key;
        if (!isset($_SESSION["csrf_token"]))
        {
            $this->setCSRFToken();
        }
    }

    /**
     * Set current session CSRF TOKEN
     * @access public
     */
    public function setCSRFToken()
    {
        if (isset($_SESSION))
        {
            $_SESSION["csrf_token"] = base64_encode(sha1(time() . $this->private_key));
        }
    }

    /**
     * Check current session CSRF TOKEN
     * @param string $token_to_validate
     * @return bool
     * @access public
     */
    public function validateCSRFToken($token_to_validate)
    {
        $validation = ( $token_to_validate == $_SESSION["csrf_token"] ) ? true : false;
        $this->setCSRFToken();
        return $validation;
    }

}

?>
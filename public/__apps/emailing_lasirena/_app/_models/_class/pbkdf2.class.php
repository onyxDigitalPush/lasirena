<?php

/**
 * Base class for pbkdf2 unidirectional encryption 
 */

/**
 * Class responsible of encrypting passwords using the Hash and Salt technique
 * 
 * @filename: pbkdf2.class.php
 * Location: _app/_models/_class
 * @Creator: J. Raya (JRM) <info@novaigrup.com>
 * 	20181212 JRM Created
 */
class Pbkdf2
{

    /**
     * @var string cryptographic Salt
     * @access private
     */
    private $salt = '';

    /**
     * @var int Iterations used for the encryption process
     * @access private
     */
    private $iterations = 0;

    /**
     * @var int Key encryption result length
     * @access private
     */
    private $key_length = 0;

    /**
     * @var string Algorithm known for php (md5, sha1, sha256, ...)
     * @access private
     */
    private $algorithm = '';

    /**
     * Class constructor
     * @param string $salt cryptographic Salt
     * @param string $algorithm OPT Algorithm known for php (md5, sha1, sha256, ...)
     * @param int $iterations OPT Iterations used for the encryption process
     * @param int $key_length OPT Key encryption result length
     * @access public
     */
    public function __construct($salt, $algorithm = 'sha1', $iterations = 4096, $key_length = 20)
    {
        $this->salt = $salt;
        $this->algorithm = $algorithm;
        $this->iterations = $iterations;
        $this->key_length = $key_length;
    }

    /**
     * Encrypting a text string passed as parameter
     * @param string $string 
     * @return string
     * @access public
     */
    public function encrypt($string)
    {
        return $this->get_pbkdf2($this->algorithm, $string, $this->salt, $this->iterations, $this->key_length, false);
    }

    /**
     * Running hash_pbkdf2 () functionality with support for versions below PHP 5.5.
     * This will run hash_pbkdf2 () function. If it does not exist, native functionality will be emulated
     * @param string $algorithm
     * @param string $password
     * @param string $salt
     * @param int $count
     * @param int $key_length
     * @param bool $raw_output false->return hexadecimal string, true->return raw string data
     * @return string
     * @access private
     */
    private function get_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
    {
        $algorithm = strtolower($algorithm);
        if (!in_array($algorithm, hash_algos(), true))
            trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
        if ($count <= 0 || $key_length <= 0)
            trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);

        if (function_exists("hash_pbkdf2"))
        {
            // The output length is in NIBBLES (4-bits) if $raw_output is false!
            if (!$raw_output)
            {
                $key_length = $key_length * 2;
            }
            return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
        }

        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);

        $output = "";
        for ($i = 1; $i <= $block_count; $i++)
        {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++)
            {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }

        if ($raw_output)
            return substr($output, 0, $key_length);
        else
            return bin2hex(substr($output, 0, $key_length));
    }

}

?>
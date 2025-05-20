<?php

/**
 * CLASS RESPONSIBLE TO VALIDATE POST AND GET DATA
 */

/**
 * CLASS RESPONSIBLE TO VALIDATE POST AND GET DATA, THIS CLASS IS THOUGHT FOR VALIDATE REQUEST WITH THE ACCORDINGLY SECURITY
 * @filename: data_validation.class.php
 * Location: _app/_models/_class
 * @Creator: J. Raya (JRM) <info@novaigrup.com>
 * 	20181212 JRM Created
 */
class DataValidation
{

    /**
     * Regular expressión
     * @var integer
     * @access private
     */
    private $regexp = "/((\\%3C)|<)[^\\n]+((\\%3E)|>)|(?i)(.*?(\bsvg\b))+(.*?(\bonload\b))/ix"; /*     * "/((\\%3C)|<)[^\\n]+((\\%3E)|>)/ix"; */

    /**
     * Class constructor
     *
     * @access public
     */
    public function __construct()
    {
        
    }

    /**
     * Validate the information passed from POST and GET
     *
     * @param bool $stop_execution_app If set to true, metodth stop app.
     * @access public	
     */
    public function validateData($stop_execution_app = true)
    {
        $valid_data = true;

        //Securize POST input injections
        foreach ($_POST as $post => $value)
        {
            if (is_array($value))
            {
                foreach ($value as $array_value)
                {
                    if ($this->valideRegex($array_value) === false)
                    {
                        $valid_data = false;
                    }
                }
            }
            else
            {
                if ($this->valideRegex($value) === false)
                {
                    $valid_data = false;
                }
            }
        }

        //Securize GET input injections
        foreach ($_GET as $post => $value)
        {
            if (is_array($value))
            {
                foreach ($value as $array_value)
                {
                    if ($this->valideRegex($array_value) === false)
                    {
                        $valid_data = false;
                    }
                }
            }
            else
            {
                if ($this->valideRegex($value) === false)
                {
                    $valid_data = false;
                }
            }
        }

        //Send header
        if (!$valid_data)
            header('HTTP/1.0 403 Forbidden');

        //Stop app execution
        if ($stop_execution_app && !$valid_data)
            die('403 Forbidden');

        //Return result
        return $valid_data;
    }

    /**
     * Valide Regex
     * @param string $value
     * @return boolean
     */
    private function valideRegex($value)
    {
        $value = strtolower($value);
        if (preg_match($this->regexp, $value) && $post != 'g-recaptcha-response')
        {
            return false;
        }
    }

}

?>
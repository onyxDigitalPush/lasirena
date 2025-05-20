<?php

/**
 * Form validation
 */

/**
 * Form validation class
 * @filename: form_validation.class.php
 * Location: _app/_models/_class
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 * 	20150515 RBM Created
 */
class FormValidation
{

    /**
     * @var array validation elements
     * @access private
     */
    private $validation_elements = array();

    /**
     * @var array validation array
     * @access private
     */
    public $validation_array = array();

    /**
     * is triggered when invoking inaccessible methods in an object context
     * @param string $method
     * @param string $args
     * @return mixed
     * @access public
     */
    public function __call($method, $args)
    {
        if (isset($this->$method) && is_callable($this->$method))
        {
            return call_user_func_array(
                    $this->$method, $args
            );
        }
        else
        {
            throw new ErrorException('No existe la validación que desea hacer', 1);
        }
    }

    /**
     * Clear validation elements
     * @access public
     */
    public function clearValidationElements()
    {
        $this->validation_elements = array();
    }

    /**
     * Set validation element
     * @param string $input
     * @param string $validation_type
     * @param bool $mandatory
     * @return bool
     * @access public
     */
    public function setValidationElement($input, $validation_type, $mandatory = false)
    {
        $array_validation_elements = $this->getValidationElements();

        if (in_array($input, $array_validation_elements))
            throw new ErrorException('Este elemento ' . $input . ' ya se había definido anteriormente', 1);


        $this->validation_elements[$input] = array(
            "mandatory" => $mandatory,
            "validation_type" => $validation_type
        );

        return true;
    }

    /**
     * Get data validation
     * @param array $validate_data
     * @return bool
     * @access public
     */
    public function getDataValidation($validate_data)
    {
        if (!is_array($validate_data))
            throw new ErrorException('Error en los datos enviados', 1);

        /** We will validate information only if any elements of validation have been defined */
        if (is_array($this->validation_elements))
        {
            $array_validation_elements = $this->getValidationElements();

            /** We check if the data we recive requires validation */
            foreach ($validate_data as $input => $value)
            {
                if (in_array($input, $array_validation_elements))
                {
                    /** Check mandatory fields */
                    $this->validation_array[$input] = ( $this->validation_elements[$input]["mandatory"] === true && trim($value) == '' ) ? false : true;

                    if ($this->validation_array[$input] === false)
                        continue;

                    /** We will validate only fields with data and data to be validated */
                    if ($value != '' && $this->validation_elements[$input]["validation_type"] != '')
                    {
                        $this->validation_array[$input] = $this->{$this->validation_elements[$input]["validation_type"]}($value);
                    }
                }
            }

            /** Application returns an error if we configure a validation for a fiel that doesn't exist in $validate_data */
            foreach ($array_validation_elements as $input)
            {
                if (!isset($validate_data[$input]))
                {
                    $this->validation_array[$input] = false;
                }
            }
        }

        /** Answer will be ok or ko regardless the ocurring errors */
        foreach ($this->validation_array as $input => $validation)
        {
            if ($validation === false)
                return false;
        }

        return true;
    }

    /**
     * Get validation Elements -> array_column in PHP >= 5.5
     * @return array
     * @access public
     */
    private function getValidationElements()
    {
        $array_validation_elements = array();

        foreach ($this->validation_elements as $key => $value)
        {
            $array_validation_elements[] = $key;
        }

        return $array_validation_elements;
    }

    /**
     * Numeric validation
     * @param string $value
     * @return bool
     * @access public
     */
    public static function numeric($value)
    {
        return is_numeric($value);
    }

    /**
     * Url validation
     * @param string $url
     * @return bool
     * @access public
     */
    public static function url($url)
    {
        $exp = "^https?:\/\/(.+\.)+.{2,4}(\/.*)?$";
        return ( preg_match("/" . $exp . "/", $url) ) ? true : false;
    }

    /**
     * Email validation
     * @param string $email
     * @return bool
     * @access public
     */
    public static function email($email)
    {
        $exp = "/^[a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$/";
        return ( preg_match($exp, $email) ) ? true : false;
    }

    /**
     * Dni validation
     * @param string $dni
     * @return bool
     * @access public
     */
    public static function dni($dni)
    {
        $exp = "/^(([a-zA-Z][0-9]{8})|([0-9]{8}[a-zA-Z])|([a-zA-Z][0-9]{7}[a-zA-Z])|([a-zA-Z][0-9]{8}[a-zA-Z]))$/";
        return ( preg_match($exp, $dni) ) ? true : false;
    }

    /**
     * Ip validation
     * @param string $ip
     * @return bool
     * @access public
     */
    public static function ip($ip)
    {
        $valid_ip = long2ip(ip2long($ip));
        return ( $valid_ip != '0.0.0.0' ) ? true : false;
    }

    /**
     * Postal code validation
     * @param int $postal_code
     * @return bool
     * @access public
     */
    public static function postalCode($postal_code)
    {
        if (strlen($postal_code) > 5)
            return false;

        $postal_code = sprintf("%05s", $postal_code);

        $province_digit = substr($postal_code, 0, 2);

        if ((int) $province_digit > 0 && (int) $province_digit < 53)
        {
            return true;
        }

        return false;
    }

    /**
     * Date validation
     * @param int $year
     * @param int $month
     * @param int $day
     * @return bool
     * @access public
     */
    public static function checkDate($year, $month, $day)
    {
        return checkdate((int) $month, (int) $day, (int) $year);
    }

    /**
     * Date validation (only format yyyy-mm-dd)
     * @param string $date
     * @return boolean
     */
    public static function date($date)
    {
        $date_array = explode('-', $date);
        if (FormValidation::checkDate($date_array[0], $date_array[1], $date_array[2]) === true)
        {
            return true;
        }
        return false;
    }

    /**
     * Extended validation
     * @param string $function_name
     * @param string $function_params
     * @param string $function_code
     * @return mixed
     * @access public
     */
    public function setExtendedValidation($function_name, $function_params, $function_code)
    {
        $this->$function_name = create_function($function_params, $function_code);
    }

}

?>
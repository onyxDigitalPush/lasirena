<?php

/**
 * Base class for mysqli connection
 */

/**
 * Class	responsible of a friendly mysqli use
 * This request and its parameters should be used under the appropriate security
 * @filename: mysqli.class.php
 * Location: _app/_models/_class
 * @Creator: J. Raya (JRM) <info@novaigrup.com>
 * 	20181212 JRM Created
 */
class MySQL
{

    /**
     * @var object MySqli connection object
     * @access public
     */
    public $mysqli = NULL;

    /**
     * @var array Class own error controller
     * @access public
     */
    public $error = NULL;

    /**
     * @var int Last inserted ID at the last executed query
     * @access public
     */
    public $last_id_inserted = NULL;

    /**
     * Class constructor
     * @param string $host DB server host
     * @param string $user DB connection user
     * @param string $pass DB password
     * @param string $catalog DB name
     * @param int $optional_port OPTIONAL Connection port with DB. By default is 3306
     * @param string $optional_charset OPTIONAL Charset type. By default is utf8
     * @return bool
     * @access public
     */
    public function __construct($host, $user, $pass, $catalog, $optional_port = 3306, $optional_charset = 'utf8')
    {
        /** Instantiate the MySqli object */
        $this->mysqli = new MySQLi();

        /** DB connection */
        $this->mysqli->connect($host, $user, $pass, $catalog, $optional_port);
        if (mysqli_connect_errno())
        {
            $this->mysqli = NULL;
            die('Imposible conectar con la base de datos');
            return false;
        }

        $this->mysqli->set_charset($optional_charset);

        $this->error = NULL;
        return true;
    }

    /**
     * Class destruct
     * @access public
     */
    function __destruct()
    {
        if (!is_null($this->mysqli))
        {
            $this->mysqli->close();
        }
    }

    /**
     * Checking wether the connection object to the database in the constructor is correct. If so, it verifies the current state of the database. If not, it sets the value to the error handler class.
     * @return bool
     * @access private
     */
    public function checkConnectionClass()
    {
        //Checking connection object
        if (is_null($this) || !$this)
        {
            throw new ErrorException('Objeto de base de datos incorrecto', 1);
            return false;
        }
        //Checking connection to the server
        if (!$this->mysqli->stat())
        {            
            throw new ErrorException('Objeto de base de datos incorrecto', 1);
            return false;
        }

        return true;
    }

    /**
     * Function responsible of executing the last prepared statement
     * @param string $sop SQL sentence adapted for prepared queries
     * @param string $parameters Prepared query with parameters list, to be a SELECT, INSERT INTO or an UPDATE
     * @return array Bidimensional associative array with the prepared query result. Simulates a recordset.
     * @access public
     */
    public function executePreparedStatement($sop, $parameters)
    {
        $result = array();
		
		/** Debug SQL */
        if( isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL'] === true && IN_PRODUCTION === false )
        {
            // MD5($sop) as array key 
            $query_md5 = md5($sop);
            
            // Check if the same $sop has already been executed
            if( array_key_exists( $query_md5, $GLOBALS['DEBUG_SQL_QUERIES']))
            {
                // Add to the counter
                $GLOBALS['DEBUG_SQL_QUERIES'][$query_md5]['count']++;
            }
            else
            {
                // Add query to the array
                $GLOBALS['DEBUG_SQL_QUERIES'][$query_md5] = array('count' => 1, 'sql' => $sop, 'last_query' => MySQL::interpolateQuery($sop, $parameters));
            }
        }

        /** Object initialization for prepared sentence */
        $sprep = $this->mysqli->stmt_init();
        if ($sprep->prepare($sop))
        {
            /** bind_param function execution from mysqli, passing a parameter array */
            if (is_array($parameters))
            {
                if (count($parameters) > 0)
                {
                    call_user_func_array(array($sprep, 'bind_param'), $this->refValues($parameters));
                }
            }

            /** Debug SQL */
            if (isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL'] === true)
            {
                $starttime = microtime(false);
            }

            if ($sprep->execute())
            {
                /** Debug SQL */
                if (isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL'] === true)
                {
                    $endtime = microtime(false);
                    $duration = $endtime - $starttime; //calculates total time taken
                    $GLOBALS['DEBUG_SQL_QUERIES'][$query_md5]['times'] .= '[' . number_format($duration, 5) . ']';
                    if ($duration > 0.5)
                    {
                        $GLOBALS['DEBUG_SQL_QUERIES'][$query_md5]['slow'] .= '[' . number_format($duration, 5) . ']';
                    }
                }
				
				
                $sprep->store_result();
                if ($sprep->num_rows > 0)
                {
                    /** Recover fields of the SELECT query */
                    $fields = $this->fetchFields($sprep);
                    foreach ($fields as $field)
                    {
                        $associated_fields[] = &${$field};
                    }
                    call_user_func_array(array($sprep, 'bind_result'), $this->refValues($associated_fields));
                    $result = array();
                    $i = 0;
                    while ($sprep->fetch())
                    {
                        foreach ($fields as $field)
                        {
                            $result[$i][$field] = $$field;
                        }
                        $i++;
                    }
                }

                $this->last_id_inserted = $sprep->insert_id;

                /** Closing the prepared query */
                $sprep->close();
            }
            else
            {
                /** Preparing custom user exception. Tracing an application execution log */
                $error_trace = debug_backtrace();
                $error_str = '';
                if (is_array($error_trace))
                {
                    if (count($error_trace) >= 2)
                    {
                        /** Retrieving data from the method/script that instantiated the current object and the method executed "executePreparedStatement" */
                        $error_str = $sprep->error . ' [mysql.executePreparedStatement()] método llamado por [Func:' . $error_trace[1]['function'] . '()] [Arch:' . $error_trace[1]['file'] . '] [Lin:' . $error_trace[1]['line'] . ']';
                    }
                }
                $this->error = Array('cod' => '2', 'desc' => 'Error ejecutando consulta preparada');
                throw new ErrorException('Error ejecutando consulta: [' . $error_str . ']', 2);
                exit();
            }
        }
        else
        {
            /** Preparing custom user exception. Tracing an application execution log */
            $error_trace = debug_backtrace();
            $error_str = '';
            if (is_array($error_trace))
            {
                if (count($error_trace) >= 2)
                {
                    /** Retrieving data from the method/script that instantiated the current object and the method executed "executePreparedStatement" */
                    $error_str = $sprep->error . ' [mysql.executePreparedStatement()] método llamado por [Func:' . $error_trace[1]['function'] . '()] [Arch:' . $error_trace[1]['file'] . '] [Lin:' . $error_trace[1]['line'] . ']';
                }
            }
            $this->error = Array('cod' => '2', 'desc' => 'Error implementando consulta preparada');
            throw new ErrorException('Error implementando consulta: [' . $error_str . ']', 2);
            return $result;
        }

        $this->error = NULL;
        return $result;
    }

    /**
     * Executes the passed SQL statement and returns a bidimensional associative array
     * @param string $sop SQL sentence
     * @return array Bidimensional associative array
     * @access public
     */
    public function execute($sop)
    {
        $data = array();
        if ($rs = $this->mysqli->query($sop))
        {
            if ($rs->num_rows > 0)
            {
                while ($row = $rs->fetch_array(MYSQLI_BOTH))
                {
                    $data[] = $row;
                }
            }

            /** Releasing series results */
            $rs->free();
        }
        else
        {
            /** Preparing custom user exception. Tracing the historical execution of application */
            $error_trace = debug_backtrace();
            $error_str = '';
            if (is_array($error_trace))
            {
                if (count($error_trace) >= 2)
                {
                    /** Retrieving data from the method/script that instantiated the current object and the method executed "execute" */
                    $error_str = $sprep->error . ' [mysql.execute()] método llamado por [Func:' . $error_trace[1]['function'] . '()] [Arch:' . $error_trace[1]['file'] . '] [Lin:' . $error_trace[1]['line'] . ']';
                }
            }
            $this->error = Array('cod' => '2', 'desc' => 'Error ejecutando consulta');
            throw new ErrorException('Error ejecutando consulta: [' . $error_str . ']', 2);
        }

        return $data;
    }

    /**
     * Object disconnection from DB
     * @access public
     */
    public function closeConnection()
    {
        if (!is_null($this->mysqli))
        {
            $this->mysqli->close();
        }
    }

    /**
     * Function to pass the parameter values by reference ​​passed (Compatibility with php 5.3+)
     * @param array $arr
     * @return array
     * @access private
     */
    private function refValues($arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0)
        {
            $refs = array();
            foreach ($arr as $key => $value)
            {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }

    /**
     * Retrieving data from the SELECT prepared query
     * @param array $selectStmt
     * @return array
     * @access private
     */
    private function fetchFields($selectStmt)
    {
        $metadata = $selectStmt->result_metadata();
        $fields_r = array();
        while ($field = $metadata->fetch_field())
        {
            $fields_r[] = $field->name;
        }

        return $fields_r;
    }

    /**
     * Normalize params
     * @param array $params
     * @return array
     */
    public static function normalizeParams($params)
    {
        if (!is_array($params) || count($params) == 0)
        {
            return false;
        }	
        /** Get params keys, IE: isiii and transform into array */
        $param_keys = str_split($params[0], 1);
        $values = $params;
        array_shift($values);/** Delete first element param_keys from values array */
        /** ADD semicolon */
        $parameters = $values;
        $parameters = preg_filter('/^/', '\'', $parameters);
        $parameters = preg_filter('/$/', '\'', $parameters);

        $response = array(
            'keys' => $param_keys,
            'values' => $parameters
        );

        return $response;
    }

    /**
     * Interpolate query
     * @param string $query
     * @param array $params
     * @return string
     */
    public static function interpolateQuery($query, $params)
    {
        $parameters = MySQL::normalizeParams($params);

        if ($parameters !== false)
        {
            $keys = array();

            # build a regular expression for each parameter
            foreach ($parameters['keys'] as $value)
            {
                $keys[] = '/[?]/';
            }

            $query = preg_replace($keys, $parameters['values'], $query, 1, $count);
        }

        return $query;
    }
}

?>
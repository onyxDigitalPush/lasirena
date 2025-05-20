<?php

/**
 * MANAGER OF ERRORS AND EXCEPTIONS (TOP LEVEL) V2
 */

/**
 * Error handler
 * @filename: error_handler.inc.php
 * Location: _app/_models/_includes
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
class ErrorHandler
{

    /**
     * Mysql
     * @var object 
     */
    protected $obj_mysqli;

    /**
     * Log
     * @var string 
     */
    protected $log;

    /**
     * Object contructor
     */
    public function __construct()
    {
        $this->log = "file";

        error_reporting(E_ALL & ~(E_STRICT | E_DEPRECATED | E_NOTICE));

        ini_set('display_errors', false);

        if (defined("DATABASE_HOST") && defined("DATABASE_USER") && defined("DATABASE_PASSWORD") && defined("DATABASE"))
        {
            $this->obj_mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE, DATABASE_PORT);

            if ($this->obj_mysqli->ping())
            {
                $this->log = "database";
            }
        }
        if ($this->log == "database")
        {
            //Log Database
            $this->initializeTable();

            set_exception_handler(array($this, 'customDatabaseExceptionHandler'));
            register_shutdown_function(array($this, "customDatabaseFatalHandler"));
        }
        else
        {
            //Log file
            ini_set('log_errors', true);
            ini_set('error_log', APP_ROOT . '/_app/_logs/errors_reg.html');
            set_exception_handler(array($this, 'customFileExceptionHandler'));
        }
    }

    /**
     * Magic method destruct
     */
    public function __destruct()
    {
        if ($this->log == "database")
            $this->obj_mysqli->close();
    }

    /**
     * Initialize table
     */
    public function initializeTable()
    {
        if ($GLOBALS['CREATE_TABLES'] === true)
        {
            $sop_create = "CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "error_handler` (
                            `id_error_handler` INT(11) NOT NULL AUTO_INCREMENT,
                            `type` VARCHAR(20) NULL DEFAULT NULL,
                            `message` TEXT NULL,
                            `file` TEXT NULL,
                            `line` INT(11) NULL DEFAULT NULL,
                            `user_agent` TEXT NULL,
                            `ip` VARCHAR(20) NULL DEFAULT NULL,
                            `error_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id_error_handler`)
                        )COLLATE='utf8_general_ci' ENGINE=MyISAM AUTO_INCREMENT=1";

            $this->obj_mysqli->query($sop_create);
        }
    }

    /**
     * Custom file exception handler
     * @param array $exception
     */
    public function customFileExceptionHandler($exception)
    {
        if (!is_dir(APP_ROOT . '/_app/_logs/'))
        {
            /** Creating the logs directory if it does not exist */
            mkdir(APP_ROOT . '/_app/_logs/');
        }

        $file = APP_ROOT . '/_app/_logs/exceptions_reg.html';

        /** Creating registry */
        $line = '';
        $line .= '[' . date('d-M-Y H:i:s', time()) . ' Europe/Madrid] ';
        $line .= $exception->getCode() . ' ';
        $line .= $exception->getMessage() . ' ';
        $line .= $exception->getFile() . ' ';
        $line .= $exception->getLine();
        $line .= PHP_EOL;/** Correct jump line for the current platform */
        /** Open and write file logs */
        if (($f = fopen($file, 'a')) !== false)
        {
            fwrite($f, $line);
            fclose($f);
        }

        /** Redirection to the general error page */
        header('Location: ' . $GLOBALS['HTTP_WEB_ROOT'] . '/error.html');
        exit();
    }

    /**
     * Custom database exception handler
     * @param array $e
     */
    public function customDatabaseExceptionHandler($e)
    {
        $error = array();
        $error["type"] = $this->getErrorType($e->getCode());
        $error["message"] = $e->getMessage();
        $error["file"] = $e->getFile();
        $error["line"] = $e->getLine();

        $this->saveError($error);
    }

    /**
     * Custom database fatal handler
     */
    public function customDatabaseFatalHandler()
    {
        $error = error_get_last();

        if (!is_null($error))
        {
            $not_catchable = array(E_NOTICE, E_DEPRECATED);
            if (!in_array($error["type"], $not_catchable))
            {
                $error["type"] = $this->getErrorType($error["type"]);
                $this->saveError($error);
            }
        }
    }

    /**
     * Get error type
     * @param int $type
     * @return string
     */
    private function getErrorType($type)
    {
        switch ($type)
        {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }
        return $type;
    }

    /**
     * Save error
     * @param array $error
     */
    public function saveError(array $error)
    {
        if (!is_dir(APP_ROOT . '/_app/_logs/'))
        {
            /** Creating the logs directory if it does not exist */
            mkdir(APP_ROOT . '/_app/_logs/');
        }

        $file = APP_ROOT . '/_app/_logs/errors_reg.html';

        /** Creating registry */
        $line = '';
        $line .= '[' . date('d-M-Y H:i:s', time()) . ' Europe/Madrid] ';
        $line .= $error["type"] . ' ';
        $line .= $error["message"] . ' ';
        $line .= $error["file"] . ' ';
        $line .= $error["line"];
        $line .= '<br>' . PHP_EOL;/** Correct jump line for the current platform */
        /** Open and write file logs */
        if (($f = fopen($file, 'a')) !== false)
        {
            fwrite($f, $line);
            fclose($f);
        }
        $user_agent = ( isset($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $sop = "INSERT INTO " . $GLOBALS['DATABASE_PREFIX'] . "error_handler (type,message,file,line,user_agent,ip) VALUES (?,?,?,?,?,?)";
        $query = $this->obj_mysqli->prepare($sop);
        $ip = Ip::getIp();
        $query->bind_param("sssiss", $error["type"], $error["message"], $error["file"], $error["line"], $user_agent, $ip);
        $query->execute();

        header('Location: ' . $GLOBALS['HTTP_WEB_ROOT'] . '/error.html');
        exit();
    }

}

$objerror_handler = new ErrorHandler();

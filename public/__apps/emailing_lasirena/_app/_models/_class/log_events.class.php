<?php

/**
 * Log event system
 */
/** requires */
require_once 'ip.class.php';
require_once 'mysqli.class.php';

/**
 * Log event system
 * @filename: log_events.class.php
 * Location: _app/_models/_class
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 * 	20181212 RBM Created
 */
class LogEvent
{

    /**
     * DB object
     * @var object
     * @access private
     */
    private $obj_mysqli = NULL;

    /**
     * Current user id
     * @var int
     * @access private
     */
    private $user_id = 0;

    /**
     * Data base prefix
     * @var string
     * @access private
     */
    private $database_prefix = '';

    const LOG_EVENT_LOGIN = 1;
    const LOG_EVENT_LOGOUT = 2;
    const LOG_EVENT_LOCK_ACCOUNT = 3;
    const LOG_EVENT_CAPTCHA = 5;
    const LOG_EVENT_APPLICATION = 6;
    const LOG_EVENT_APPLICATION_SEC = 7;
    const LOG_EVENT_SECURITY = 8;

    /**
     * Class constructor
     * @param MySQL $obj_mysqli_opt Optional Mysql connection for multiple proyect databases
     * @param string $database_prefix_opt Data base prefix for optional mysql connection
     * @access public
     */
    public function __construct(MySQL $obj_mysqli_opt = NULL, $database_prefix_opt = '')
    {
        /** MySqli class */
        if (is_null($obj_mysqli_opt))
        {
            //Default proyect database connection
            $this->obj_mysqli = new MySQL(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE, DATABASE_PORT);
            $this->database_prefix = $GLOBALS['DATABASE_PREFIX'];
        }
        else
        {
            //Optional database connection
            $this->obj_mysqli = $obj_mysqli_opt;
            $this->database_prefix = $database_prefix_opt;
        }

        if (!method_exists($this->obj_mysqli, 'checkConnectionClass'))
            return false;

        if (!$this->obj_mysqli->checkConnectionClass())
            return false;

        if ($GLOBALS['CREATE_TABLES'] === true)
        {
            $sop = "
                    CREATE TABLE IF NOT EXISTS `" . $this->database_prefix . "log` (
                        `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
                        `log_date` timestamp COMMENT 'Date log',
                        `log_type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Log type. 1->login;2->logout;3->lock account; 4->login failed; 5->captcha failed; 6->application, 7->aplication secundary, 8->XSS filtrado',
                        `log_return_code` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'return code 0 -> error, 1 -> ok',
                        `log_text` text,
                        `http_referrer` varchar(255) NOT NULL DEFAULT '',
                        `user_agent` varchar(255) NOT NULL DEFAULT '',
                        `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'Ip',
                        `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User id if user is logged into app',
                        `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Admin id if user is logged into app',
                        `session_hash_id` varchar(255) NOT NULL DEFAULT '' COMMENT 'User session hash if user is logged into app',
                        PRIMARY KEY (`log_id`),
                        INDEX(`user_id`),
                        INDEX(`admin_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='General table log'
                    ";
            $this->obj_mysqli->executePreparedStatement($sop, '');
        }
    }

    /**
     * Save system log event
     * @param integer $log_type Log type value. Can use "LOG_EVENT_" private class constants
     * @param string $log_text
     * @param string $log_date
     * @param int $return_code
     */
    public function saveLogEvent($log_type, $log_text = '', $return_code = 1, $log_date = '')
    {
        if (!method_exists($this->obj_mysqli, 'checkConnectionClass'))
            return false;

        if (!$this->obj_mysqli->checkConnectionClass())
            return false;

        $http_referrer = ( isset($_SERVER['HTTP_REFERER']) ) ? $_SERVER['HTTP_REFERER'] : '';
        $user_agent = ( isset($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : '';

        switch ($log_type)
        {
            case self::LOG_EVENT_LOCK_ACCOUNT:
                break;
            default:
                $sop = 'INSERT INTO ' . $this->database_prefix . 'log 
                ( log_date, log_type, log_return_code, log_text, http_referrer, user_agent, ip, user_id, session_hash_id )
                VALUES
                (?,?,?,?,?,?,?,?,?)
                ';
                /** Get session date to prevent date problems on check last log login */
                $log_date = ( trim($log_date) != '' ) ? $log_date : date('Y-m-d H:i:s', time());

                $params = array('siissssis', $log_date, (int) $log_type, (int) $return_code, $log_text, $http_referrer, $user_agent, Ip::getIp(), (int) $_SESSION['user']['user_id'], session_id());
                $this->obj_mysqli->executePreparedStatement($sop, $params);
                break;
        }
    }

    /**
     * Get Last Login From log
     * @param int $user_id
     * @return string
     */
    public function getLastLogin($user_id)
    {
        $sop = "SELECT log_date FROM " . $this->database_prefix . "log WHERE user_id = ? AND log_type = ?  ORDER BY log_date DESC LIMIT 1";
        $params = array("ii", (int) $user_id, self::LOG_EVENT_LOGIN);
        $result = $this->obj_mysqli->executePreparedStatement($sop, $params);
        $last_login = $result[0]['log_date'];

        return ( $last_login ) ? date("d-m-Y H:i:s", strtotime($last_login)) : date("d-m-Y H:i:s", time());
    }

}

?>
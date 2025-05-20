<?php

/**
 * Sets handler to session control functions in order to work with database sessions
 */

/**
 * My session handler
 * @filename: sessions_handler.inc.php
 * Location: _app/_models/_includes
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 * Code from: http://php.net/manual/en/class.sessionhandlerinterface.php
 */
class MySessionHandler implements SessionHandlerInterface
{

    /**
     * @var object MySqli connection object
     * @access public
     */
    public $obj_mysqli = NULL;

    /**
     * Class constructor
     * @param object $obj_mysqli
     */
    public function __construct(MySQL $obj_mysqli)
    {
        if (!$obj_mysqli->checkConnectionClass())
            return;

        $this->obj_mysqli = $obj_mysqli;
    }

    /**
     * Open
     * @param string $savePath
     * @param string $sessionName
     * @return boolean
     */
    public function open($savePath, $sessionName)
    {
        if ($GLOBALS['CREATE_TABLES'] === true)
        {
            /** Check sessions table */
            $sop = "
                    CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "sessions` (
                      `id` char(32) NOT NULL,
                      `access` int(10) unsigned DEFAULT NULL,
                      `data` text,
                              `creation_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyIsam DEFAULT CHARSET=utf8;
                    ";

            $this->obj_mysqli->executePreparedStatement($sop, '');
        }
        return true;
    }

    /**
     * Close
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Read
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $sop = "SELECT data FROM " . $GLOBALS['DATABASE_PREFIX'] . "sessions WHERE  id = ? LIMIT 1";
        $params = array('s', $id);
        $result = $this->obj_mysqli->executePreparedStatement($sop, $params);

        if (count($result) > 0)
        {
            return $result[0]['data'];
        }

        return '';
    }

    /**
     * Write
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
        $access = time();

        $sop = "REPLACE INTO " . $GLOBALS['DATABASE_PREFIX'] . "sessions  (id,access,data) VALUES (?, ?, ?)";
        $params = array('sss', $id, $access, $data);
        $this->obj_mysqli->executePreparedStatement($sop, $params);
        return true;
    }

    /**
     * Destroy
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        $sop = "DELETE FROM " . $GLOBALS['DATABASE_PREFIX'] . "sessions WHERE id = ? LIMIT 1";
        $params = array('s', $id);
        $this->obj_mysqli->executePreparedStatement($sop, $params);

        return true;
    }

    /**
     * Garbage collector (GC)
     * @param int $maxlifetime
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        $old = time() - $maxlifetime;

        $sop = "DELETE FROM " . $GLOBALS['DATABASE_PREFIX'] . "sessions WHERE  access < ?";
        $params = array('s', $old);
        $this->obj_mysqli->executePreparedStatement($sop, $params);

        return true;
    }

}

$handler = new MySessionHandler($obj_mysqli);
session_set_save_handler($handler, true);
if (isset($GLOBALS['SESSION_NAME']))
{
    session_name($GLOBALS['SESSION_NAME']);
}
session_start();
if (!isset($not_session_regenerate_id))
    session_regenerate_id();
?>
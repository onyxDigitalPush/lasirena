<?php

require_once 'form_validation.class.php';
require_once 'pbkdf2.class.php';
require_once '../_models/_class/email_links.class.php';

class EmailingSystem
{

    private $newsletter_reference = null;
    private $subject = null;
    private $html = null;
    public $last_error = null;

    const EMPTY_EMAIL_REFERENCE = 1;
    const EMPTY_EMAIL_SUBJECT = 2;
    const EMPTY_EMAIL_HTML = 3;
    const EMPTY_EMAIL_LIST = 4;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->createTables();
    }

    /**
     * Save historic
     * @param string $newsletter_reference
     * @param string $subject
     * @param string $html
     * @return boolean
     */
    public function saveHistoric($newsletter_reference, $subject, $html)
    {
        $this->newsletter_reference = trim($newsletter_reference);
        $this->subject = trim($subject);
        $this->html = trim($html);

        if ($this->newsletter_reference == '')
        {
            $this->last_error = EmailingSystem::EMPTY_EMAIL_REFERENCE;
            return false;
        }
        if ($this->subject == '')
        {
            $this->last_error = EmailingSystem::EMPTY_EMAIL_SUBJECT;
            return false;
        }
        if ($this->html == '')
        {
            $this->last_error = EmailingSystem::EMPTY_EMAIL_HTML;
            return false;
        }

        $sop = 'SELECT
                    MAX(historic_id) as historic_id
                FROM
                    ' . $GLOBALS['DATABASE_PREFIX'] . 'historic
                ';
        $result = $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');
        $historic_id = $result[0]['historic_id'] + 1;

        $today = date('Y-m-d H:i:s', time());
        $sop = 'INSERT INTO 
                    ' . $GLOBALS['DATABASE_PREFIX'] . 'historic
                    (historic_id, newsletter_reference, subject, html, creation_date, modify_date)
                VALUES
                    (?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                    modify_date = ?
                ';
        $parameters = array(
            'issssss',
            $historic_id,
            $this->newsletter_reference,
            $this->subject,
            $this->html,
            $today,
            $today,
            $today
        );
        $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

        return true;
    }

    /**
     * Get html from file
     * @param string $file_path
     * @return string
     */
    public function getHtmlFromFile($file_path)
    {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $html_source = file_get_contents($file_path, false, stream_context_create($arrContextOptions));

        return $html_source;
    }

    /**
     * Fill sending queue
     * @param array $email_list
     * @return boolean
     */
    public function fillSendingQueue($email_list)
    {
        $result_list = $this->valideEmailList($email_list);

        if (count($result_list) == 0)
        {
            $this->last_error = EmailingSystem::EMPTY_EMAIL_LIST;
            return false;
        }

        if (count($result_list) > 0)
        {
            foreach ($result_list as $list)
            {
                //$analytics_tracking = '&utm_source=[-[newsletter_reference]-]&utm_medium=email&utm_content=[-[newsletter_reference]-]&utm_campaign=[-[newsletter_reference]-]'; //Analytics tracking code
                //$obj_email_links = new EmailLinks($this->newsletter_reference, $analytics_tracking);
                //$email_link_replace_vars = $obj_email_links->getReplacementEmailLink();

                $newsletter_reference = $this->newsletter_reference;

                $subject = $this->subject;
                $newsletter_subject = str_replace('[-[nombre_destinatario]-]', $list['name'], $subject);

                /** Email without tracking */
                //$twitter = $obj_email_links->showEmailLink('twitter', $obj_email_links::NO_TRACKING);
                //$linkedin = $obj_email_links->showEmailLink('linkedin', $obj_email_links::NO_TRACKING);
                //$emailto_contact = $obj_email_links->showEmailLink('emailto_contact', $obj_email_links::NO_TRACKING);
                //$unsubscribe_link = $obj_email_links->showEmailLink('unsubscribe_bottom', $obj_email_links::NO_TRACKING);

                /**
                 * Replace and normalize html vars
                 */
                $array_replace_vars = array(
                    '[-[newsletter_reference]-]' => $newsletter_reference,
                    '[-[email_recipient]-]' => $list['email_recipient'],
                    '[-[token]-]' => $list['token'],
                    '[-[name]-]' => $list['name'],
                    '[-[surname1]-]' => $list['surname1'] != '-' ? $list['surname1'] : '',
                    '[-[surname2]-]' => $list['surname2'] != '-' ? $list['surname2'] : '',
                    '[-[province_name]-]' => $list['province_name'],
                    '[-[speciality_name]-]' => $list['speciality_name'],
                    //'[-[twitter]-]' => $twitter,
                    //// '[-[linkedin]-]' => $linkedin,
                    // '[-[emailto_contact]-]' => $emailto_contact,
                    // '[-[unsubscribe_link]-]' => $unsubscribe_link
                );


                // if (is_array($email_link_replace_vars))
                //  {
                /** Add replace vars links in the end of email link */
                //      $array_replace_vars = array_merge($email_link_replace_vars, $array_replace_vars);
                //    }

                $html_source = $this->html;
                $message_source = $this->normalizeHtml($html_source, $array_replace_vars);

                try
                {
                    $sop = 'INSERT INTO
                            ' . $GLOBALS['DATABASE_PREFIX'] . 'sending_queue
                            (newsletter_reference, sender, email_recipient, subject, message, processed)
                        VALUES
                            (?,?,?,?,?,0)
                        ';
                    $parameters = array(
                        'sssss',
                        $newsletter_reference,
                        EMAIL_SENDER_ADDRESS,
                        $list['email_recipient'],
                        $newsletter_subject,
                        $message_source
                    );
                    $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

                    $sop = 'UPDATE
                                ' . $GLOBALS['DATABASE_PREFIX'] . 'historic
                            SET
                                emails_total = emails_total + 1
                            WHERE
                                newsletter_reference = ?
                            ';
                    $parameters = array(
                        's',
                        $newsletter_reference
                    );
                    $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);
                }
                catch (Throwable $t)
                {
                    //Whene email are duplicated
                    $GLOBALS['obj_log_event']->saveLogEvent(LogEvent::LOG_EVENT_APPLICATION, 'Email duplicado: ' . $newsletter_reference . ' -> ' . $list['email_recipient'], 0);
                }
                catch (Exception $e)
                {
                    //Whene email are duplicated
                    $GLOBALS['obj_log_event']->saveLogEvent(LogEvent::LOG_EVENT_APPLICATION, 'Email duplicado: ' . $newsletter_reference . ' -> ' . $list['email_recipient'], 0);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Valide email list
     * @param array $email_list
     * @return array
     */
    public function valideEmailList($email_list)
    {
        // Force e-mail as well formed and unique email
        $return_list = array();

        if (is_array($email_list) && count($email_list) > 0)
        {
            foreach ($email_list as $list)
            {
                if (FormValidation::email($list['email_recipient']) === true)
                /** TODO: la validación del email no reconoce dominios nuevos */
                {
                    $return_list[$list['email_recipient']] = $list;
                }
            }
        }

        return $return_list;
    }

    /**
     * Open rate tracking
     * @param string $newsletter_reference
     * @param string $email_recipient
     */
    public function openRateTracking($newsletter_reference, $email_recipient)
    {
        /*$sop = 'UPDATE
                    ' . $GLOBALS['DATABASE_PREFIX'] . 'historic
                SET
                    opens_rate = opens_rate + 1
                WHERE
                    newsletter_reference = ?
                ';
        $parameters = array('s', $newsletter_reference);
        $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);*/

        if (FormValidation::email($email_recipient) === true)
        {
            $today = date('Y-m-d H:i:s', time());
            $sop = 'INSERT INTO
                        ' . $GLOBALS['DATABASE_PREFIX'] . 'opens
                        (newsletter_reference, email_recipient, open_date)
                    VALUES
                        (?,?,?)
                    ';
            $parameters = array(
                'sss',
                $newsletter_reference,
                $email_recipient,
                $today
            );
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);
        }
    }

    /**
     * Link tracking
     * @param int $click_id
     * @param string $newsletter_reference
     * @param string $email_recipient
     * @param string $user_agent
     */
    public function linkTracking($click_id, $newsletter_reference, $email_recipient, $user_agent)
    {
        $sop = 'UPDATE
                    ' . $GLOBALS['DATABASE_PREFIX'] . 'historic
                SET
                    click_rate = click_rate + 1
                WHERE
                    newsletter_reference = ?
                ';
        $parameters = array('s', $newsletter_reference);
        $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

        if (FormValidation::email($email_recipient) === true)
        {
            $today = date('Y-m-d H:i:s', time());

            $sop = 'INSERT INTO
                        ' . $GLOBALS['DATABASE_PREFIX'] . 'clicks
                        (click_id, newsletter_reference, email_recipient, user_agent, click_date)
                    VALUES
                        (?,?,?,?,?)                     
                    ';
            $parameters = array(
                'issss',
                $click_id,
                $newsletter_reference,
                $email_recipient,
                $user_agent,
                $today
            );
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);
        }
    }

    /**
     * Token generation
     * @param int $email_recipient_id
     * @param string $email_recipient
     * @return string
     */
    private function tokenGeneration($email_recipient_id, $email_recipient = '')
    {
        $obj_pbkdf2 = new Pbkdf2(APP_SALT);
        $token = $obj_pbkdf2->encrypt($email_recipient_id . $email_recipient);
        $sop_update = 'UPDATE ' . $GLOBALS['DATABASE_PREFIX'] . 'email_recipients SET token = ? WHERE email_recipient_id = ?';
        $parameters_update = array('si', $token, $email_recipient_id);
        $GLOBALS['obj_mysqli']->executePreparedStatement($sop_update, $parameters_update);

        return $token;
    }

    /**
     * Generate empty tokens
     */
    public function generateEmptyTokens()
    {
        $sop = 'SELECT
                    email_recipients.email_recipient_id,
                    email_recipients.email_recipient
                FROM
                    ' . $GLOBALS['DATABASE_PREFIX'] . 'email_recipients as email_recipients
                WHERE
                    email_recipients.is_email_subscribed = 1 AND
                    ( email_recipients.token IS NULL OR  email_recipients.token = "" )
                ';
        $result = $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');

        if (is_array($result) && count($result) > 0)
        {
            foreach ($result as $list)
            {
                $this->tokenGeneration($list['email_recipient_id'] . $list['email_recipient']);
            }
        }
    }

    /**
     * Get email recipients
     * @param string $newsletter_reference
     * @return array
     */
    public function getEmailRecipients($newsletter_reference)
    {
        /** TODO: sería bueno revisar en la tabla log impats, pero esta solo está en prod */
        $sop = 'SELECT
                    email_recipients.email_recipient_id,
                    email_recipients.token,
                    email_recipients.name,
                    email_recipients.surname1,
                    email_recipients.surname2,
                    email_recipients.email_recipient,
                    email_recipients.province_name,
                    email_recipients.speciality_name
                FROM
                    ' . $GLOBALS['DATABASE_PREFIX'] . 'newsletter_recipients as newsletter_recipients
                LEFT JOIN ' . $GLOBALS['DATABASE_PREFIX'] . 'email_recipients as email_recipients USING(email_recipient_id)
                WHERE
                    newsletter_recipients.newsletter_reference = ? AND
                    email_recipients.is_email_subscribed = 1
                ';
        $parameters = array('s', $newsletter_reference);
        $result = $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

        $email_list_array = array();
        if (count($result) > 0)
        {
            foreach ($result as $email_recipient)
            {
                if (is_null($email_recipient['token']))
                {
                    $email_recipient['token'] = $this->tokenGeneration($email_recipient['email_recipient_id'], $email_recipient['email_recipient']);
                }

                $email_list_array[] = array(
                    'token' => $email_recipient['token'],
                    'name' => $email_recipient['name'],
                    'surname1' => $email_recipient['surname1'],
                    'surname2' => $email_recipient['surname2'],
                    'email_recipient' => $email_recipient['email_recipient'],
                    'province_name' => $email_recipient['province_name'],
                    'speciality_name' => $email_recipient['speciality_name']
                );
            }
        }
        return $email_list_array;
    }

    /**
     * valide recipient token
     * @param string $email
     * @param string $token
     * @return boolean
     */
    public function valideRecipientToken($email, $token)
    {
        $sop = 'SELECT
                    email_recipients.email_recipient_id
                FROM
                    ' . $GLOBALS['DATABASE_PREFIX'] . 'email_recipients as email_recipients
                WHERE  
                    email_recipients.is_email_subscribed = 1 AND
                    email_recipients.email_recipient = ? AND
                    email_recipients.token = ?
                ';
        $parameters = array('ss', $email, $token);

        $result = $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

        return ((int) $result[0]['email_recipient_id'] > 0) ? true : false;
    }

    /**
     * Insert Newsletter recipients
     * @param string $newsletter_reference
     * @param array $recipients
     * @return boolean
     */
    public function insertNewsletterRecipients($newsletter_reference, $recipients)
    {
        // Check newsletter referencie is not empty
        if (AntiXSS::xssFilter($newsletter_reference) == '')
            return false;

        // Check if we have recipients
        if (!is_array($recipients) && count($recipients) == 0)
            return false;

        //Check each recipient and add it to newsletter_recipients table
        foreach ($recipients as $key => $recipient)
        {
            //Validate data
            if ((int) $recipient['email_recipient_id'] == 0)
                continue;

            // Insert to newsletter_recipients table
            $sop = 'INSERT INTO `' . $GLOBALS['DATABASE_PREFIX'] . 'newsletter_recipients` (email_recipient_id, newsletter_reference) VALUES (?,?)';
            $params = array(
                'is',
                (int) $recipient['email_recipient_id'],
                AntiXSS::xssFilter($newsletter_reference)
            );
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $params);
        }
        return true;
    }

    /**
     * Unsuscribe
     * @param string $email
     * @param string $token
     * @return boolean
     */
    public function unsuscribe($email, $token)
    {
        if ($this->valideRecipientToken($email, $token))
        {
            $today = date('Y-m-d H:i:s', time());
            $sop = 'UPDATE
                        ' . $GLOBALS['DATABASE_PREFIX'] . 'email_recipients
                    SET
                        is_email_subscribed = 0,
                        unsubscribe_date = ?
                    WHERE  
                        is_email_subscribed = 1 AND
                        email_recipient = ? AND
                        token = ?
                    ';
            $parameters = array('sss', $today, $email, $token);
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

            return true;
        }
        return false;
    }

    /**
     * Normalize Html source replacing dinamic tag vars
     * @param string $html
     * @param array $array_replace_vars
     * @return string
     */
    public function normalizeHtml($html, array $array_replace_vars)
    {
        foreach ($array_replace_vars as $str_find => $str_replace)
        {
            $html = str_replace($str_find, $str_replace, $html);
        }
        return $html;
    }

    /**
     * Register impact log
     * @param string $newsletter_reference
     * @param string $email_recipient
     */
    public function registerImpactLog($newsletter_reference, $email_recipient)
    {
        $sop = 'INSERT INTO `' . $GLOBALS['DATABASE_PREFIX'] . 'log_impact_emails` (newsletter_reference, email_recipient, insert_date) VALUES (?,?,?)';
        $params = array('sss', $newsletter_reference, $email_recipient, date('Y-m-d H:i:s', time()));
        $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $params);
    }

    /**
     * Is email in recipient list
     * @param string $email_recipient
     * @return bool
     */
    public function isEmailInRecipientList($email_recipient)
    {
        $sop = 'SELECT
                    email_recipients.email_recipient_id
                FROM
                    ' . $GLOBALS['DATABASE_PREFIX'] . 'email_recipients as email_recipients
                WHERE  
                    email_recipients.email_recipient = ?
                ';

        $parameters = array('s', $email_recipient);

        $result = $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

        return ((int) $result[0]['email_recipient_id'] > 0) ? true : false;
    }

    /**
     * Create tables
     */
    private function createTables()
    {
        if ($GLOBALS['CREATE_TABLES'] === true)
        {
            $sop = "
                    CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "historic` (
                        `historic_id` int(11) NOT NULL AUTO_INCREMENT,
                        `newsletter_reference` varchar(100) NOT NULL,
                        `subject` text NOT NULL,
                        `html` longtext NOT NULL,
                        `emails_total` int(11) NOT NULL,
                        `click_rate` int(11) NOT NULL,
                        `opens_rate` int(11) NOT NULL,
                        `newsletter_init_date` datetime NOT NULL,
                        `newsletter_finish_date` datetime NOT NULL,                        
                        `creation_date` DATETIME NULL DEFAULT NULL,
                        `modify_date` DATETIME NULL DEFAULT NULL,                        
                        PRIMARY KEY (`historic_id`),
                        INDEX (`newsletter_reference`),
                        CONSTRAINT uc_historic UNIQUE (`newsletter_reference`)
                    ) ENGINE=" . $GLOBALS['DATABASE_ENGINE'] . " DEFAULT CHARSET=utf8
                    ";
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');

            $sop = "
                CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "email_recipients` (
                  `email_recipient_id` int(11) NOT NULL AUTO_INCREMENT,
                  `token` varchar(150) DEFAULT NULL,
                  `name` varchar(255) NOT NULL DEFAULT '',
                  `surname1` varchar(255) NOT NULL DEFAULT '',
                  `surname2` varchar(255) NOT NULL DEFAULT '',
                  `email_recipient` varchar(255) NOT NULL DEFAULT '',
                  `province_name` varchar(255) NOT NULL DEFAULT '',
                  `speciality_name` varchar(255) NOT NULL DEFAULT '',
                  `creation_app_id` int(11) NOT NULL,
                  `is_email_subscribed` tinyint(1) NOT NULL,
                  `register_date` datetime DEFAULT NULL,
                  `unsubscribe_date` datetime DEFAULT NULL,
                  PRIMARY KEY (`email_recipient_id`),
                  CONSTRAINT uc_email_recipient UNIQUE (`email_recipient`)
                ) ENGINE=" . $GLOBALS['DATABASE_ENGINE'] . " DEFAULT CHARSET=utf8;
                    ";
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');

            $sop = "
                CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "newsletter_recipients` (
                  `email_recipient_id` int(11) NOT NULL,
                  `newsletter_reference` varchar(100) NOT NULL,
                  PRIMARY KEY (`email_recipient_id`,`newsletter_reference`)
                ) ENGINE=" . $GLOBALS['DATABASE_ENGINE'] . " DEFAULT CHARSET=utf8;
                    ";
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');

            $sop = "
                    CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "sending_queue` (
                        `queue_id` int(11) NOT NULL AUTO_INCREMENT,
                        `newsletter_reference` varchar(100) NOT NULL,
                        `sending_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        `sender` varchar(200) NOT NULL,
                        `email_recipient` varchar(200) NOT NULL DEFAULT '',
                        `email_replyto` varchar(200) NOT NULL DEFAULT '',
                        `subject` varchar(200) NOT NULL DEFAULT '',
                        `message` longtext NOT NULL,
                        `processed` tinyint(1) NOT NULL,
                        PRIMARY KEY (`queue_id`),
                        INDEX (`newsletter_reference`),
                        CONSTRAINT uc_queue UNIQUE (`newsletter_reference`,`email_recipient`)
                    ) ENGINE=" . $GLOBALS['DATABASE_ENGINE'] . " DEFAULT CHARSET=utf8
                    ";
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');

            $sop = "
                    CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "opens` (
                        `opens_id` int(11) NOT NULL AUTO_INCREMENT,
                        `newsletter_reference` varchar(100) NOT NULL,
                        `email_recipient` varchar(100) NOT NULL,
                        `open_date` datetime NOT NULL,
                        PRIMARY KEY (`opens_id`),
                        INDEX (`email_recipient`),
                        INDEX (`newsletter_reference`)                        
                    ) ENGINE=" . $GLOBALS['DATABASE_ENGINE'] . " DEFAULT CHARSET=utf8
                    ";
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');

            $sop = "
                    CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "clicks` (
                        `auto_click_id` int(11) NOT NULL AUTO_INCREMENT,
                        `click_id` int(11) NOT NULL,
                        `newsletter_reference` varchar(100) NOT NULL,
                        `email_recipient` varchar(100) NOT NULL,
                        `click_date` datetime NOT NULL,
                        `user_agent` varchar(200) NULL,                        
                        PRIMARY KEY (`auto_click_id`),
                        INDEX (`click_id`),
                        INDEX (`email_recipient`),
                        INDEX (`newsletter_reference`)
                    ) ENGINE=" . $GLOBALS['DATABASE_ENGINE'] . " DEFAULT CHARSET=utf8
                    ";
            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');

            $sop = "
                    CREATE TABLE IF NOT EXISTS `" . $GLOBALS['DATABASE_PREFIX'] . "log_impact_emails` (
                        `log_id` BIGINT NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
                        `newsletter_reference` VARCHAR(100) NOT NULL COMMENT 'FK->penm_historic',
                        `email_recipient` VARCHAR(200) NOT NULL COMMENT 'FK->[-[newsletter_reference]-]_recipients',
                        `insert_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Insert date',
                        PRIMARY KEY(`log_id`),
                        INDEX `newsletter_reference`(`newsletter_reference`),
                        INDEX `email_recipient`(`email_recipient`)
                    ) ENGINE=" . $GLOBALS['DATABASE_ENGINE'] . " DEFAULT CHARSET=utf8
                    ";

            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');
        }
    }
}

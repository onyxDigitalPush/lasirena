<?php

/**
 * Emails links 
 */
/**
 * Requires
 */

/**
 * Email links information
 * @filename: email_links.class.php
 * Location: _app/_models/_class
 * @Creator: MA. Sanchez (MAS) <info@novaigrup.com>
 * 	20191031 MAS Created
 */
class EmailLinks
{

    /**
     * Link id
     * @var int 
     */
    private $link_id;

    /**
     * Email id
     * @var int 
     */
    private $newsletter_reference;

    /**
     * Reference of the link
     * @var string
     */
    private $link_ref;

    /**
     * Description of the link
     * @var string
     */
    private $description;

    /**
     * Call to action
     * @var string
     */
    private $cta;

    /**
     * URL
     * @var string 
     */
    private $url;

    /**
     * Analytics Tracking
     * @var string 
     */
    private $analytics_tracking;

    /**
     * Array links
     * @var array 
     */
    private $array_links;

    /**
     * Tracking constants
     */
    const HAS_TRACKING = true;
    const NO_TRACKING = false;

    /**
     * Class constructor    
     * @param int $newsletter_reference	
     */
    public function __construct($newsletter_reference = NULL, $analytics_tracking = NULL)
    {
        if (!$GLOBALS['obj_mysqli']->checkConnectionClass())
        {
            return false;
        }

        if (!is_null($newsletter_reference))
        {
            $this->newsletter_reference = $newsletter_reference;
            $this->getEmailLinks($this->newsletter_reference, $analytics_tracking);
        }
    }

    /**
     * Get links from an email
     * @param int $newsletter_reference
     * @param string $analytics_tracking
     */
    private function getEmailLinks($newsletter_reference, $analytics_tracking)
    {
        $sop = '
            SELECT
                link_id,
                link_ref,
                url
            FROM 
                `' . $GLOBALS['DATABASE_PREFIX'] . 'email_links`
            WHERE
                newsletter_reference = ?
                ';
        $parameters = array('s', $newsletter_reference);
        $result = $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

        if (count($result) > 0)
        {
            $array_links = array();
            foreach ($result as $link)
            {
                $complete_url = $GLOBALS['TRACKING_URL_NHS_PLATFORM'] . '&click_id=' . $link['link_id'] . '&url=' . $link['url'];

                $array_links[$link['link_ref']] = $complete_url;
            }
            $this->array_links = $array_links;
            $this->analytics_tracking = $analytics_tracking;
        }
    }

    /**
     * Show links from an email
     * @param string $link_ref
     * @param bool $has_tracking
     */
    public function showEmailLink($link_ref, $has_tracking = true)
    {
        if (is_array($this->array_links) && array_key_exists($link_ref, $this->array_links))
        {
            if ($has_tracking)
            {
                //Checking if the URL has query to put '?' or '&' on the analytics tracking
                $parsed_url = parse_url($this->array_links[$link_ref]);

                $parsed_query = array();
                parse_str($parsed_url['query'], $parsed_query);

                if (strpos($parsed_query['url'], '?'))
                {
                    return $this->array_links[$link_ref] . $this->analytics_tracking;
                }
                return $this->array_links[$link_ref] . substr_replace($this->analytics_tracking, '?', 0, 1);
            }
            else
            {
                return $this->array_links[$link_ref];
            }
        }

        /** SEND ERROR REPORT */
        $GLOBALS['obj_log_event']->saveLogEvent(LogEvent::LOG_EVENT_APPLICATION, 'Error getting the link from database with ref:' . $link_ref, $link_ref);
    }

    /**
     * Get replacement email links
     * @return array
     */
    public function getReplacementEmailLink()
    {
        $email_link_replace_vars = array();
        if (is_array($this->array_links) && count($this->array_links) > 0)
        {
            foreach ($this->array_links as $link_ref => $url)
            {
                $email_link_replace_vars['[-[' . $link_ref . ']-]'] = $this->showEmailLink($link_ref, self::HAS_TRACKING);
            }
        }

        return $email_link_replace_vars;
    }

    /**
     * Create object system tables
     */
    private function createTables()
    {
        if ($GLOBALS['CREATE_TABLES'] === true)
        {
            $sop = "
            CREATE TABLE IF NOT EXISTS 
                `" . $GLOBALS['DATABASE_PREFIX'] . "email_links` (
                `link_id` int(11) NOT NULL,
                `newsletter_reference` varchar(100) NOT NULL,
                `link_ref` varchar(255) NOT NULL,
                `description` varchar(255) NOT NULL,
                `cta` varchar(255) NOT NULL,
                `url` text NOT NULL,
            PRIMARY KEY (`link_id`,`newsletter_reference`),
            INDEX(`link_id`),
            INDEX(`newsletter_reference`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8;
            ";

            $GLOBALS['obj_mysqli']->executePreparedStatement($sop, '');
        }
    }

}

?>
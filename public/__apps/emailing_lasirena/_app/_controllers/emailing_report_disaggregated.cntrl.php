<?php
/**
 * Controller
 */
/**
 * @filename: email_report.cntrl.php
 * Location: _app/_controllers
 * @Creator: MA. Sanchez(MSA) <info@novaigrup.com>
 *  20210504 MSA Created
 * https://clickcumple.nestlehealthscience.es/emailing-nhs/_app/_controllers/emailing_report_disaggregated.cntrl.php
 * https://clickcumple.nestlehealthscience.es/emailing-nhs/_app/_controllers/emailing_report_disaggregated.cntrl.php?newsletter_reference=NHS_20210323_39
 */
require_once 'common.cntrl.inc.php';

/**
 * Method for extracting the actual user IP
 * @return string The actual user IP, with an invalid direction returns 0.0.0.0
 * @access public
 */
//IP restriction
function getIp()
{
    /** First, the function checks if the user navigates through a proxy. Then, validates the IP to rule out possible XSS */
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        $array_ips_proxy = explode(', ', $_SERVER["HTTP_X_FORWARDED_FOR"]);
//$valid_ip = long2ip(ip2long(array_pop($array_ips_proxy)));
        $valid_ip = long2ip(ip2long($array_ips_proxy[0]));
    }
    else
    {
        $ip = $_SERVER["REMOTE_ADDR"];/** If the user does not navigate through a proxy, the function validates the IP with REMOTE_ADDR */
        $valid_ip = long2ip(ip2long($ip));
    }

    return $valid_ip;
}

//Allowed IP that can access to log
$allowed_ip = array('80.28.245.19', '188.119.218.21');
//Remote IP
$remote_ip = getIp();

if (!in_array($remote_ip, $allowed_ip) && IN_PRODUCTION === true)
{
    header('HTTP/1.0 404 Not Found');
    ?>
    <html><head><title>404-not found</title></head><body><h1> Error occurred: 404 - not found</h1><hr><address>Apache Server</address></body></html>
    <?php
    exit;
}

// Controller loginc
$common_recipient_limitation = ' (email_recipients.email_recipient NOT LIKE ("%@novaigrup%") AND email_recipients.email_recipient NOT LIKE ("%@riverflex%") AND email_recipients.email_recipient NOT LIKE ("%nestle.com")) AND ';

// Getting all the newsletters
$sop = 'SELECT
            historic.newsletter_reference
        FROM
            ' . $GLOBALS['DATABASE_PREFIX'] . 'historic as historic
        ';

$parameters = array();
$array_newsletter_references = $GLOBALS['obj_mysqli']->executePreparedStatement($sop, $parameters);

$data_found = false;

if (isset($_GET['newsletter_reference']) && $_GET['newsletter_reference'] != '')
{
    $newsletter_reference = $_GET['newsletter_reference'];
    $array_links = array();

    $sop = 'SELECT
            link_id,
            description,
            cta
        FROM
            ' . $GLOBALS['DATABASE_PREFIX'] . 'email_links
        WHERE
            newsletter_reference = ?
        ';
    $parameters = array('s', $newsletter_reference);
    $array_links = $obj_mysqli->executePreparedStatement($sop, $parameters);

    if (is_array($array_links) && count($array_links) > 0)
    {
        $select_link = '';
        $comma_count = 1;
        foreach ($array_links as $link)
        {
            if ($comma_count == count($array_links))
            {
                $select_link .= '( SELECT IF(count(*) > 0, "Sí", "No") FROM penm_clicks as clicks WHERE clicks.email_recipient = email_recipients.email_recipient AND newsletter_reference = "' . $newsletter_reference . '" AND click_id = ' . $link['link_id'] . ' ) AS "' . $link['cta'] . '" ';
            }
            else
            {
                $select_link .= '( SELECT IF(count(*) > 0, "Sí", "No") FROM penm_clicks as clicks WHERE clicks.email_recipient = email_recipients.email_recipient AND newsletter_reference = "' . $newsletter_reference . '" AND click_id = ' . $link['link_id'] . ' ) AS "' . $link['cta'] . '", ';
            }
            $comma_count ++;
        }
        $array_emails = array();
        $array_clicks_list = array();

        $sop = 'SELECT
            email_recipients.email_recipient_id,
            email_recipients.email_recipient as email,
            "Sí" AS envio_nl,
            ( SELECT MAX(DATE_FORMAT(log_impact_emails.insert_date, "%d/%m/%Y")) FROM penm_log_impact_emails as log_impact_emails WHERE log_impact_emails.email_recipient = email_recipients.email_recipient AND log_impact_emails.newsletter_reference = ? ) as date_sent,
            ( SELECT IF(count(*) > 0, "Sí", "No") FROM penm_opens as opens WHERE opens.email_recipient = email_recipients.email_recipient AND opens.newsletter_reference = ? ) as has_open,
            ' . $select_link . '
        FROM
            ' . $GLOBALS['DATABASE_PREFIX'] . 'email_recipients as email_recipients
        WHERE
            ' . $common_recipient_limitation . '
            email_recipients.email_recipient IN (
                SELECT
                        DISTINCT email_recipient
                FROM
                        penm_log_impact_emails
                WHERE
                        newsletter_reference = ?
                )
        ';

        $parameters = array('sss', $newsletter_reference, $newsletter_reference, $newsletter_reference);

        $result = $obj_mysqli->executePreparedStatement($sop, $parameters);

        if (is_array($result) && count($result) > 0)
        {
            $data_found = true;
            $email_data = $result;
        }
    }
}

include VIEWS_DIR . '/emailing_report_disaggregated.view.php';
exit;

exit();


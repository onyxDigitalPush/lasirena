<?php
/**
 * Controller
 */
/**
 * @filename: email_report.cntrl.php
 * Location: _app/_controllers
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20210126 RBM Created
 * https://clickcumple.nestlehealthscience.es/emailing-nhs/_app/_controllers/emailing_report.cntrl.php
 * https://clickcumple.nestlehealthscience.es/emailing-nhs/_app/_controllers/emailing_report.cntrl.php?date_ini=2021-01-01&date_fin=2021-02-01
 */
require_once 'common.cntrl.inc.php';

$common_ref_limitation = ' newsletter_reference LIKE "NHS%" AND ';
$common_recipient_limitation = ' (email_recipient NOT LIKE ("%@novaigrup%") AND email_recipient NOT LIKE ("%@riverflex%") AND email_recipient NOT LIKE ("%nestle.com")) AND ';

/**
 * Method for extracting the actual user IP
 * @return string The actual user IP, with an invalid direction returns 0.0.0.0
 * @access public
 */
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

$date_first = '2020-01-01 00:00:00';
$date_last = date('Y-m-d H:i:s', time());


if (isset($_GET['date_ini']))
{
    $date_first = date('Y-m-d H:i:s', strtotime($_GET['date_ini'] . ' 00:00:00'));
}
if (isset($_GET['date_fin']))
{
    $date_last = date('Y-m-d H:i:s', strtotime($_GET['date_fin'] . ' 23:59:59'));
}



$array_emails = array();
$array_clicks_list = array();

$sop = 'SELECT
            clicks.newsletter_reference,
            clicks.click_id as link_id,
            COUNT(clicks.email_recipient) as clicks,
            COUNT(DISTINCT clicks.email_recipient) as unique_clicks
        FROM
            ' . $GLOBALS['DATABASE_PREFIX'] . 'clicks as clicks
        WHERE
            ' . $common_ref_limitation . '
            ' . $common_recipient_limitation . '
            clicks.click_date BETWEEN ? AND ?
        GROUP BY 
            clicks.newsletter_reference, clicks.click_id
        ';
$parameters = array('ss', $date_first, $date_last);
$result = $obj_mysqli->executePreparedStatement($sop, $parameters);

if (is_array($result) && count($result) > 0)
{
    $click_rate = array();
    foreach ($result as $list)
    {
        $array_emails[$list['newsletter_reference']] = $list['newsletter_reference'];
        $click_rate[$list['newsletter_reference']]['clicks'] += $list['clicks'];
        $click_rate[$list['newsletter_reference']]['unique_clicks'] += $list['unique_clicks'];
        $array_clicks_list[$list['link_id']] = $list['link_id'];
        $click_rate[$list['newsletter_reference']]['clicks_list'][$list['link_id']]['clicks'] += $list['clicks'];
        $click_rate[$list['newsletter_reference']]['clicks_list'][$list['link_id']]['unique_clicks'] += $list['unique_clicks'];
    }
}

if (is_array($array_clicks_list) && count($array_clicks_list) > 0)
{
    /** Get email info */
    $sop = 'SELECT
                email_links.newsletter_reference,
                email_links.link_id,
                email_links.link_ref,
                email_links.description,
                email_links.cta,
                email_links.url
            FROM
                ' . $GLOBALS['DATABASE_PREFIX'] . 'email_links as email_links
            WHERE
                ' . $common_ref_limitation . '
                email_links.link_id IN (' . implode(',', $array_clicks_list) . ')
            ';
    $result = $obj_mysqli->executePreparedStatement($sop, '');

    if (is_array($result) && count($result) > 0)
    {
        $links_info = array();
        foreach ($result as $list)
        {
            $links_info[$list['newsletter_reference']][$list['link_id']] = $list;
        }
    }
}


$sop = 'SELECT
            opens.newsletter_reference,
            COUNT(opens.email_recipient) as opens,
            COUNT(DISTINCT opens.email_recipient) as unique_opens
        FROM
            ' . $GLOBALS['DATABASE_PREFIX'] . 'opens as opens
        WHERE
            ' . $common_ref_limitation . '
            ' . $common_recipient_limitation . '
            opens.open_date BETWEEN ? AND ?
        GROUP BY 
            opens.newsletter_reference
        ';
$parameters = array('ss', $date_first, $date_last);
$result = $obj_mysqli->executePreparedStatement($sop, $parameters);

if (is_array($result) && count($result) > 0)
{
    $open_rate = array();
    foreach ($result as $list)
    {
        $array_emails[$list['newsletter_reference']] = $list['newsletter_reference'];
        $open_rate[$list['newsletter_reference']]['opens'] += $list['opens'];
        $open_rate[$list['newsletter_reference']]['unique_opens'] += $list['unique_opens'];
    }
}

$sop = 'SELECT
            log_impact_emails.newsletter_reference,
            COUNT(log_impact_emails.email_recipient) as impacts,
            COUNT(DISTINCT log_impact_emails.email_recipient) as unique_impacts
        FROM
            ' . $GLOBALS['DATABASE_PREFIX'] . 'log_impact_emails as log_impact_emails
        WHERE
            ' . $common_ref_limitation . '
            ' . $common_recipient_limitation . '
            log_impact_emails.insert_date BETWEEN ? AND ?
        GROUP BY 
            log_impact_emails.newsletter_reference
        ';
$parameters = array('ss', $date_first, $date_last);
$result = $obj_mysqli->executePreparedStatement($sop, $parameters);

if (is_array($result) && count($result) > 0)
{
    $impact_rate = array();
    foreach ($result as $list)
    {
        $array_emails[$list['newsletter_reference']] = $list['newsletter_reference'];
        $impact_rate[$list['newsletter_reference']]['impacts'] += $list['impacts'];
        $impact_rate[$list['newsletter_reference']]['unique_impacts'] += $list['unique_impacts'];
    }
}


if (is_array($array_emails) && count($array_emails) > 0)
{
    /** Get email info */
    $sop = 'SELECT
                historic.newsletter_reference,
                historic.subject
            FROM
                ' . $GLOBALS['DATABASE_PREFIX'] . 'historic as historic
            WHERE
                ' . $common_ref_limitation . '            
                newsletter_reference IN ("' . implode('","', $array_emails) . '")
            ';
    $result = $obj_mysqli->executePreparedStatement($sop, '');
    if (is_array($result) && count($result) > 0)
    {
        $email_info = array();
        foreach ($result as $list)
        {
            $email_info[$list['newsletter_reference']] = $list;
        }
    }

    /** Create counter data */
    foreach ($array_emails as $newsletter_reference)
    {
        $counter_open[$newsletter_reference] = array(
            'open' => (int) $open_rate[$newsletter_reference]['opens'],
            'unique_opens' => (int) $open_rate[$newsletter_reference]['unique_opens'],
            'clicks' => (int) $click_rate[$newsletter_reference]['clicks'],
            'unique_clicks' => (int) $click_rate[$newsletter_reference]['unique_clicks'],
            'clicks_list' => $click_rate[$newsletter_reference]['clicks_list'],
            'impacts' => (int) $impact_rate[$newsletter_reference]['impacts'],
            'unique_impacts' => (int) $impact_rate[$newsletter_reference]['unique_impacts'],
            'description' => $email_info[$newsletter_reference]['subject'],
            'newsletter_reference' => $newsletter_reference
        );
    }
}

if (is_array($counter_open) && count($counter_open))
{
    ksort($counter_open);
    foreach ($counter_open as &$open)
    {
        if (is_array($open))
        {
            ksort($open);
        }
    }
}

include VIEWS_DIR . '/emailing_report.view.php';
exit;

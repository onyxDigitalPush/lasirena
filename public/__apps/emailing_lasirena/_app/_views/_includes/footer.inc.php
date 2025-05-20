<?php
/**
 * View include
 */
/**
 * View include
 * @filename: footer.inc.php
 * Location: _app/_views/_inludes
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
PHP_EOL;/** PhpDoc Fix */
?>
<script type="text/javascript" src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?><?php AntiXSS::show(DIR_JS); ?>bfbs.js?v=<?php AntiXSS::show(APP_VERSION); ?>"></script>

<!--[if lt IE 9]><div id="nvg_ie_compat"></div><![endif]-->
<script type="text/javascript" src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?><?php AntiXSS::show(DIR_JS); ?>jquery-old-browser-detection.js?v=<?php AntiXSS::show(APP_VERSION); ?>"></script>

<?php
if ($no_cookies == true)
{
    
}
else
{
    $url_cookies = str_replace('https:', '', str_replace('http:', '', $GLOBALS['HTTP_WEB_ROOT']));
    ?>
    <script type="text/javascript">
        var __afpc_conf = {
            URL_website: "<?php AntiXSS::show($url_cookies); ?>",
            URL_politica_cookies: "<?php AntiXSS::show($url_cookies); ?>/politica-sobre-cookies.html"
        };
    </script>
    <script type="text/javascript" src="<?php AntiXSS::show($GLOBALS['HTTP_WEB_ROOT']); ?>/_widgets/_aviso_cookies/afpc.js"></script>

    <?php
//Cookies entorno Purina
    /*
      <script type="text/javascript" src="//www.purinaspain.es/_widgets/_aviso_cookies/afpc.js"></script>
     */
}
?>

<?php
if (is_object($obj_user_session))
{
    if ($obj_user_session->isUserLoged())
    {
        ?>
        <script type="text/javascript" src="<?php Antixss::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?><?php Antixss::show(DIR_JS); ?>common_restricted_area.js?v=<?php Antixss::show(APP_VERSION); ?>"></script>
        <?php
    }
}
?>
<?php
if (isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL'] === true && IN_PRODUCTION === false)
{
    ?>
    <div style="display: block; width: 100%; height: 200px; overflow-y: scroll; margin: 0px; padding: 0px; bottom: 0px; right: 0px; position: fixed; background-color: rgba(127, 188, 204, 0.9); z-index: 100;">
        <?php
        $debug_total_queries = 0;
        foreach ($GLOBALS['DEBUG_SQL_QUERIES'] as $debug_query_md5 => $debug_query_array)
        {
            ?>
            <ul style="font-size: 12px; font-family: monospace; margin: 5px; padding: 0px;">
                <?php
                foreach ($debug_query_array as $key => $value)
                {
                    echo '<li>' . $key . ': ' . $value . '</li>';
                    if ($key === 'count')
                    {
                        $debug_total_queries += $value;
                    }
                }
                ?>
            </ul>
            <?php
        }
        ?>        
        <br>Total queries: <?php echo $debug_total_queries; ?>
    </div>
    <?php
}
?>
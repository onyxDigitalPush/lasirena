<?php
/**
 * View include
 */
/**
 * View include
 * @filename: analytics.inc.php
 * Location: _app/_views/_inludes
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
PHP_EOL;/** PhpDoc Fix */
?>
<?php
if (!defined('USE_GTM') || USE_GTM === true)
{
    //Client custom GTM
    ?>
    <!-- falta incluir el tag manager -->
    <?php
}
else
{
    /**
     * ******************************
     * ******************************
     * Novaigrup analytics test
     * ****************************** 
     * ****************************** 
     */
    ?>
    <!-- Novaigrup Google Tag Manager -->
    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5KB3TB"
                      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({'gtm.start':
                        new Date().getTime(), event: 'gtm.js'});
            var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                    '//www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5KB3TB');</script>
    <!-- End Novaigrup Google Tag Manager -->
    <?php
}
?>
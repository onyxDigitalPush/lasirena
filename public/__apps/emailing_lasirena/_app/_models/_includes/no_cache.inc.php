<?php

/**
 * No cache headers common file
 */
/**
 * @filename: no_cache.inc.php
 * Location: _app/_models/_includes
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 *  20181212 RBM Created
 */
header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<?php
/**
 * View
 */
/**
 * View
 * @filename: emailing_report.view.php
 * Location: _app/_views
 * @Creator: P. Antúnez (PAC) <info@novaigrup.com>
 *   20201022 PAC Created
 */
PHP_EOL;/** PhpDoc Fix */
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Dashboard</title>
        <link rel="stylesheet" type="text/css" href="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_css/jquery.dataTables.min.css"/>
        <style type="text/css">
            .pull-left{float:left!important;}
        </style>
        <script type="text/javascript" src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_js/jquery-3.4.1.min.js"></script>
        <script type="text/javascript" src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_js/jquery.dataTables.min.js"></script>
        <script type="text/javascript">
            $( document ).ready( function () {

                var domLayout = "<'pull-left'f><'row pb-2'<'col-sm-12 col-md-6'p>>" +
                        "<'row pb-2'<'col-sm-12'tr>>" +
                        "<'row pb-2'<'col-sm-12 col-md-6'p>>";


                var oTable_EmailingReport = $( '#dtEmailingReport' ).DataTable( {
                    "responsive": true,
                    "dom": domLayout,
                    "stateSave": true,
                    "pageLength": 500
                } );
            } );
        </script>


    </head>
    <body>
        <table id="dtEmailingReport" style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">
                        Mail description
                    </th>
                    <th class="text-center">
                        REF
                    </th>
                    <th class="text-center">
                        Impacts
                    </th>
                    <th class="text-center">
                        Opens
                    </th>
                    <th class="text-center">
                        %
                    </th>
                    <th class="text-center">
                        Clicks
                    </th>                                                    
                    <th class="text-center">
                        %
                    </th>
                    <th class="text-center">
                        link_id
                    </th>                    
                </tr>
            </thead>
            <tbody>
                <?php
                if (is_array($counter_open) && count($counter_open) > 0)
                {
                    foreach ($counter_open as $item)
                    {
                        if ($item['description'] == '')
                        {
                            continue;
                        }
                        if ((int) $item['impacts'] > 0)
                        {
                            $open_rate = round((int) $item['unique_opens'] / (int) $item['impacts'] * 100, 1);
                        }
                        else
                        {
                            $open_rate = 0;
                        }
                        if ((int) $item['unique_opens'] > 0)
                        {
                            $click_rate = round((int) $item['unique_clicks'] / (int) $item['unique_opens'] * 100, 1);
                        }
                        else
                        {
                            $click_rate = 0;
                        }
                        $txt_impacts = ($item['impacts'] == 0) ? 'Sin datos' : $item['impacts'];
                        echo '<tr><td>' . $item['description'] . '</td><td>' . $item['newsletter_reference'] . '</td><td>' . $txt_impacts . '</td><td>' . $item['unique_opens'] . '(' . $item['open'] . ')' . '</td><td>' . $open_rate . '%</td><td>' . $item['unique_clicks'] . '(' . $item['clicks'] . ')' . '</td><td>' . $click_rate . '%</td><td><table><tr>';
                        if (is_array($item['clicks_list']) && count($item['clicks_list']) > 0)
                        {
                            foreach ($item['clicks_list'] as $link_id => $values)
                            {
                                $title_info = $link_id;
                                $link_info = '';

                                /** Cuando tenemos información del link del e-mail (solo nuevos) mostramos un link con tooltip con los datos */
                                $link_data = ( isset($links_info[$item['newsletter_reference']][$link_id]) ) ? $links_info[$item['newsletter_reference']][$link_id] : false;

                                if ($link_data !== false)
                                {
                                    $title_info = $link_data['link_ref'];
                                    $link_info = '(<a href="javascript:void(0);" title="ID: ' . $link_data['link_id'] . PHP_EOL . 'Desc: ' . $link_data['description'] . PHP_EOL . 'Ref: ' . $link_data['link_ref'] . PHP_EOL . 'CTA: ' . $link_data['cta'] . PHP_EOL . 'URL: ' . $link_data['url'] . '">?</a>)';
                                }
                                echo '<td>' . $title_info . $link_info . '<br>' . $values['clicks'] . '(' . $values['unique_clicks'] . ')' . '</td>';
                            }
                        }
                        echo '</tr></table></td></tr>';
                    }
                }
                ?>                
            </tbody>
        </table>
    </body>
</html>
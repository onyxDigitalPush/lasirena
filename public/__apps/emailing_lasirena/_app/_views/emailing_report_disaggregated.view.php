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
            $(document).ready(function () {

                var domLayout = "<'pull-left'f><'row pb-2'<'col-sm-12 col-md-6'p>>" +
                        "<'row pb-2'<'col-sm-12'tr>>" +
                        "<'row pb-2'<'col-sm-12 col-md-6'p>>";


                var oTable_EmailingReport = $('#dtEmailingReport').DataTable({
                    "responsive": true,
                    "dom": domLayout,
                    "stateSave": true,
                    "pageLength": 10000
                });
            });
        </script>


    </head>
    <body>
        <div style="display:block; float:left; width:100%; margin-bottom:10px;">

            <?php
            $selected_reference_style = '';
            if (is_array($array_newsletter_references) && count($array_newsletter_references) > 0)
            {
                $year_month = '';
                foreach ($array_newsletter_references as $list_newsletter_reference)
                {
                    $date_reference = explode('_', $list_newsletter_reference['newsletter_reference']);

                    if ($year_month != substr($date_reference[1], 0, 6))
                    {
                        $year_month = substr($date_reference[1], 0, 6);
                        echo '<hr>';
                    }

                    if (isset($_GET['newsletter_reference']) && ($_GET['newsletter_reference'] != '') && ($list_newsletter_reference['newsletter_reference'] == $newsletter_reference))
                    {
                        $selected_reference_style = 'color:black;font-size:20px;font-weight:bold;';
                    }
                    else
                    {
                        $selected_reference_style = '';
                    }
                    ?>
                    <a style="padding:5px;<?php echo $selected_reference_style; ?>" href="<?php AntiXSS::show($GLOBALS['HTTP_WEB_ROOT']); ?>/_app/_controllers/emailing_report_disaggregated.cntrl.php?newsletter_reference=<?php echo $list_newsletter_reference['newsletter_reference']; ?>" title="<?php echo $list_newsletter_reference['newsletter_reference']; ?>">
                        <?php echo $list_newsletter_reference['newsletter_reference']; ?>
                    </a>
                    <?php
                }
            }
            ?>
        </div>
        <hr>
        <?php
        if ($data_found === true)
        {
            ?>
            <table id="dtEmailingReport" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">
                            Email Recipient Id
                        </th>
                        <th class="text-center">
                            Email
                        </th>
                        <th class="text-center">
                            Envío NL
                        </th>
                        <th class="text-center">
                            Date Sent
                        </th>
                        <th class="text-center">
                            Has open
                        </th>
                        <?php
                        foreach ($array_links as $link)
                        {
                            ?>
                            <th class="text-center">
                                <?php echo $link['description']; ?>
                            </th>                                       
                            <?php
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($email_data as $data)
                    {
                        echo '<tr><td>' . $data['email_recipient_id'] . '</td>';
                        echo '<td>' . $data['email'] . '</td>';
                        echo '<td>' . $data['envio_nl'] . '</td>';
                        echo '<td>' . $data['date_sent'] . '</td>';
                        echo '<td>' . $data['has_open'] . '</td>';

                        foreach ($array_links as $link)
                        {
                            echo '<td>' . $data[$link['cta']] . '</td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }
        else
        {
            ?>
            <h1>No data</h1>
            <?php
        }
        ?>
    </body>
</html>
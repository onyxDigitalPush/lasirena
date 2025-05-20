<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>La sirena</title>
</head>

<body style="font-size: 15px;font-family: Arial, Helvetica, sans-serif;">
    @php
        $relative_url = IN_PRODUCTION ? '/emailing-lasirena/open_rate_tracking.html' : '/lasirena/public/emailing-lasirena/open_rate_tracking.html';
    @endphp
    <img src="' . HTTPS_WEB_ROOT . $relative_url . '?newsletter_reference=' . $email_id . '&' . 'email_recipient=' . $obj_email->recipient . '"
        alt="La Sirena" width="1" height="1">


    <table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td style="line-height:0px;">
                <table border="0" align="center" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td width="277"> </td>
                            <td height="80">
                                <img src="' . HTTPS_WEB_ROOT . '/' . DIR_IMG . '/logo-header-la-sirena.jpg?v=' . config('app.version') . '"
                                    alt="Logo de La Sirena" width="146" height="60" style="display:block; border:0px;">
                            </td>
                            <td width="277"> </td>
                        </tr>
                    </tbody>
                </table>
        </tr>
        <tr>
            <td width="700" height="25" style="background-color:#f2f2f2;">&nbsp;</td>
        </tr>
        <tr>
            <td>
                <table border="0" align="center" cellpadding="0" cellspacing="0" style="background-color:#f2f2f2;">
                    <tbody>
                        <tr>
                            <td width="40"> </td>
                            <td>
                                <span style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">';
                                    {{ $texts_translation['text_1'] }}
                                    $html .= '
                                </span>
                                <br>
                                <br>
                                <span style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">';
                                    {{ $texts_translation['text_2'] }}

                                </span>
                                <br><br>
                            </td>
                            <td width="40"> </td>
                        </tr>

                        @foreach ($array_gropued_product_by_dates as $array_product)
                            {


                            <tr>
                                <td width="40"> </td>
                                <td>
                                    <span
                                        style="font-size: 17px; font-family: Arial, Helvetica, sans-serif; font-weight: bold;">';
                                        {{ $texts_translation['text_3'] }}
                                        {{ Carbon::parse($array_product[0]->start_date)->format('d/m') }} -
                                        {{ Carbon::parse($array_product[0]->end_date)->format('d/m') }}

                                    </span>
                                    <br><br>
                                    <table style="background-color:#f2f2f2;" cellspacing="0" cellpadding="0" border="0"
                                        align="center">
                                        <tbody>
                                            <tr>
                                                <td style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;"
                                                    width="620">
                                                    <table style=" border-collapse: collapse; width: 75%;"
                                                        cellspacing="0" cellpadding="10">
                                                        <thead>
                                                            <tr
                                                                style=" background-color: #7ea2cf; border: 1px solid black; text-align: center; padding: 8px; line-height: 18px;">
                                                                <th
                                                                    style="border: 1px solid black; text-align: center; padding: 8px;">

                                                                    {{ $texts_translation['text_4'] }}

                                                                </th>
                                                                <th
                                                                    style="border: 1px solid black; text-align: center; padding: 8px;">

                                                                    {{ $texts_translation['text_5'] }}

                                                                </th>
                                                                <th
                                                                    style="border: 1px solid black; text-align: center; padding: 8px;">
                                                                    {{ $texts_translation['text_6'] }}

                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';
                                                            @php
                                                                $have_sap_dates = false;
                                                            @endphp
                                                            @foreach ($array_product as $product)
                                                                @php
                                                                    if ($product->start_sap != null && $product->end_sap != null) {
                                                                        $have_sap_dates = true;
                                                                    }
                                                                @endphp

                                                                <tr
                                                                    style="border: 1px solid black; text-align: center; padding: 8px; line-height: 18px;">
                                                                    <td
                                                                        style="border: 1px solid black; text-align: center; padding: 8px;">

                                                                        {{ $product->product_cod }}

                                                                    </td>
                                                                    <td
                                                                        style="border: 1px solid black; text-align: center; padding: 8px;">

                                                                        {{ $product->product_description }}

                                                                    </td>
                                                                    <td
                                                                        style="border: 1px solid black; text-align: center; padding: 8px;">

                                                                        {{ intval($product->dto) . '%' }}

                                                                    </td>
                                                                </tr>
                                                                }

                                                        </tbody>
                                                    </table>
                                                    <br>
                                                    {{ $texts_translation['text_7'] }}

                                                    @if ($have_sap_dates)

                                                        $html .= '<br /> <br />';
                                                        $html .= $texts_translation['text_8'];
                                                        $html .= '<ul>';
                                                            foreach ($array_product as $product)
                                                            {
                                                            if ($product->start_sap != null && $product->end_sap !=
                                                            null)
                                                            {
                                                            $html .= '<li>' . $texts_translation['text_9'] . ' ';
                                                                $html .= '<strong>' . $product->product_cod .
                                                                    '</strong>';
                                                                $html .= ' ' . $texts_translation['text_12'] . ' ';
                                                                $html .= '<strong>' .
                                                                    Carbon::parse($product->start_sap)->format('d/m') .
                                                                    ' -
                                                                    ' .
                                                                    Carbon::parse($product->end_sap)->format('d/m') .
                                                                    '</strong>';
                                                                $html .= '</li>';
                                                            }
                                                            }
                                                            $html .= '</ul>';
                                                        }
                                                        $html .= '
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td width="40"> </td>
                            </tr>
                            ';
                            }
                            $html .= '
                            <tr>
                                <td width="40"> </td>
                                <td>

                                    <span
                                        style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">';
                                        $html .= $texts_translation['text_10'];
                                        $html .= '
                                    </span>
                                    <br><br>
                                    <span
                                        style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">';
                                        $html .= $texts_translation['text_11'];
                                        $html .= '
                                    </span>
                                </td>
                                <td width="40"> </td>
                            </tr>
                    </tbody>
                </table>
                <table style="background-color:#f2f2f2;" cellspacing="0" cellpadding="0" border="0" align="center">
                    <tbody>
                        <tr>
                            <td style="line-height:25px;" width="700" height="25"> </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>

        <tr>
            <td style="background-color:#EBEBEA;">
                <table border="0" align="left" cellpadding="0" cellspacing="0" style="background-color:#EBEBEA;">
                    <tr>
                        <td width="40"> </td>
                        <td>
                            <br />

                            <img width="146" height="60" style="display:block; border:0px;" alt="Logo de La Sirena"
                                src="' . HTTPS_WEB_ROOT . '/' . DIR_IMG . '/logo-footer-la-sirena.jpg?v=' . config('app.version') . '">
                            <br />
                            <br />
                            <span style="font-size: 13px; color: #002655;">Ramon Llull, s/n Can Trias</span>
                            <br />
                            <span style="font-size: 13px; color: #002655;">E-08232 Viladecavalls (Barcelona)</span>
                            <br />
                            <span style="font-size: 13px;font-weight: bold; color: #002655;">+34 937 45 43 00</span>
                            <br />
                            <br />
                        </td>
                        <td width="40"> </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>

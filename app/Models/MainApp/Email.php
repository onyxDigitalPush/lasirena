<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\MainApp\Product;
use App\Models\EmailingApp\SendingQueue;
use App\Models\EmailingApp\OpenTracking;
use App\Models\EmailingApp\EmailImpact;



class Email extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'emails';

    protected $primaryKey = 'email_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'recipient',
        'language',
        'email_html',
        'type'
    ];


    public function storeEmails($array_data, $project_id, $type)
    {
        
        foreach ($array_data as $data)
        {
            //check if data is valid 
            if ($this->verify_email_data($data, $project_id))
            {



                
                //get the email 
                $search_email = Email::where([['project_id', $project_id], ['recipient', $data->email]])->select('project_id', 'email_id')->first();
                $obj_product = new Product();
              
                //check if the email created and if is created stroe the product and if not create an email and store the product
                if (isset($search_email))
                {
                    $obj_product->store_product($data, $search_email->email_id);
                }
                else
                {

                    if ($data->email != '')
                    {

                        $language = '';

                        if ($data->language != '' && $data->language != 'es' && $data->language != 'ca')
                        {
                            $language = 'es';
                        }
                        else
                        {
                            $language = $data->language;
                        }

                        $email = Email::create([
                            'project_id' => $project_id,
                            'recipient' => $data->email,
                            'language' => $language,
                            'email_html' => '',
                            'type' => $type
                        ]);
                        $obj_product->store_product($data, $email->email_id);
                    }
                }
            }
        }
    }

    public function putEmailQueue($project_id, $type, $email_id = 0)
    {
        //if don't have the email_id is beacause is a normal email and we search by product
        if (isset($email_id) && $email_id != 0 && $type == 2)
        {
            $array_email = Email::where([['email_id', $email_id], ['type', $type]])->select('email_id', 'project_id', 'recipient', 'language', 'email_html')->get();
        }
        //if have email_id is because is a redemption email and we search by email_id for don-t get the other emails redemption
        else
        {
            $array_email = Email::where([['project_id', $project_id], ['type', $type]])->select('email_id', 'project_id', 'recipient', 'language', 'email_html')->get();
        }

        $obj_sending_queue = new SendingQueue();

        $obj_project = Project::where('project_id', $project_id)->select('project_id', 'subject', 'reply_to', 'ccs')->first();

        foreach ($array_email as $email)
        {

            $html = $this->createEmailHtml($email->email_id, $email->language, $type);
            $email->email_html = $html;
            $email->save();

            //$newsletter_reference, $sending_date, $sender, $email_recipient, $email_replyto, $subject, $message
            $sending_date = Carbon::now()->format('Y-m-d H:i:s');
            
            $obj_sending_queue->storeQueueEmails($email->email_id, $sending_date, 'sebastiand.onyx@gmail.com', $email->recipient, $obj_project->reply_to, $obj_project->subject, $html);
        }
    }
    public function putEmailCcsQueue($project_id, $type, $email_id = 0)
    {

        if (isset($email_id) && $email_id != 0 && $type == 2)
        {
            //create the array with 1 id because is redemption email
            $array_emails_id = array($email_id);
        }
        else
        {
            //take all the ids of the emails array
            $array_emails_id = Email::where([['project_id', $project_id], ['type', $type]])->pluck('email_id');
        }

        $obj_sending_queue = new SendingQueue();

        $obj_project = Project::where('project_id', $project_id)->select('project_id', 'subject', 'reply_to', 'ccs')->first();

        //search all the products with this email_id
        $array_product = Product::whereIn('email_id', $array_emails_id)->select('product_id', 'email_id', 'provider_cod', 'provider_name', 'product_cod', 'product_description', 'dto', 'email', 'sales')->get();

        //create the email for de ccs
        $html_ccs = $this->createEmailHtmlCcs($array_product, $type);
        $array_ccs = explode(',', $obj_project->ccs);

        foreach ($array_ccs as $ccs)
        {
            $email = Email::create([
                'project_id' => $project_id,
                'recipient' => $ccs,
                'language' => 'es',
                'email_html' => $html_ccs,
                'type' => 1
            ]);
            $sending_date = Carbon::now()->format('Y-m-d H:i:s');
            $obj_sending_queue->storeQueueEmails($email->email_id, $sending_date, 'sebastiand.onyx@gmail.com', $ccs, $obj_project->reply_to, $obj_project->subject, $html_ccs);
        }
    }
    public function createEmailHtml($email_id, $language, $type)
    {
        //create product object
        $obj_product = new Product();

        //call the method to get the products of the emails grouped by start date and end date
        $array_gropued_product_by_dates = $obj_product->get_email_products_grouped_by_dates($email_id);

        //call the method to get the text translations 
        $texts_translation = $this->get_email_translations($language);

        $obj_email = Email::where('email_id', $email_id)->select('email_id', 'recipient')->first();

        //construct the email html
        $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
            <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>La sirena</title>
            </head>
            <body style="font-size: 15px;font-family: Arial, Helvetica, sans-serif;">';
        $relative_url = IN_PRODUCTION ? '/emailing-lasirena/open_rate_tracking.html' : '/lasirena/public/emailing-lasirena/open_rate_tracking.html';
        $html .= '<img src="' . HTTPS_WEB_ROOT . $relative_url . '?newsletter_reference=' . $email_id . '&' . 'email_recipient=' . $obj_email->recipient . '" alt="La Sirena" width="1" height="1" >';

        $html .= ' 
            <table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
            <td style="line-height:0px;">
            <table border="0" align="center" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td width="277"> </td>
                    <td height="80">
            <img src="' . HTTPS_WEB_ROOT . '/' . DIR_IMG . '/logo-header-la-sirena.jpg?v=' . config('app.version') . '" alt="Logo de La Sirena"
            width="146" height="60" style="display:block; border:0px;"></td>
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
        $html .= $texts_translation['text_1'];
        $html .= '
            </span>
            <br>
            <br>
            <span style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">';
        if ($type == 2)
        {
            $html .= $texts_translation['text_15'];
        }
        else
        {
            $html .= $texts_translation['text_2'];
        }

        $html .= '
            </span>
            <br><br>
            </td>
            <td width="40"> </td>
            </tr> ';

        foreach ($array_gropued_product_by_dates as $array_product)
        {

            $html .= '  
                <tr>
                <td width="40"> </td>
                <td>
                <span style="font-size: 17px; font-family: Arial, Helvetica, sans-serif; font-weight: bold;">';
            if ($type == 2)
            {
                $html .= $texts_translation['text_14'] . ' ' . Carbon::parse($array_product[0]->start_date)->format('d/m') . '-' . Carbon::parse($array_product[0]->end_date)->format('d/m');
            }
            else
            {
                $html .= $texts_translation['text_3'] . ' ' . Carbon::parse($array_product[0]->start_date)->format('d/m') . '-' . Carbon::parse($array_product[0]->end_date)->format('d/m');
            }

            $html .= ' 
                </span>
                <br><br>
                <table style="background-color:#f2f2f2;" cellspacing="0" cellpadding="0" border="0" align="center">
                <tbody>



                <tr>
                <td style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;" width="620">
                <table style=" border-collapse: collapse; width: 75%;" cellspacing="0" cellpadding="10">
                <thead>
                <tr style=" background-color: #7ea2cf; border: 1px solid black; text-align: center; padding: 8px; line-height: 18px;">
                <th style="border: 1px solid black; text-align: center; padding: 8px;">';
            $html .= $texts_translation['text_4'];
            $html .= '
                </th>
                <th style="border: 1px solid black; text-align: center; padding: 8px;">';
            $html .= $texts_translation['text_5'];
            $html .= '
                </th>
                <th style="border: 1px solid black; text-align: center; padding: 8px;">';
            $html .= $texts_translation['text_6'];
            $html .= '
                </th>
                
                
                ';
               

            if ($type == 2)
            {
                $html .= ' <th style="border: 1px solid black; text-align: center; padding: 8px;">';
                $html .= $texts_translation['text_13'];
                $html .= '
                    </th>';
            }
            else if ($type == 0)
            {
                $html .= ' <th style="border: 1px solid black; text-align: center; padding: 8px;">';
                $html .= $texts_translation['text_16'];
                $html .=  ' <th style="border: 1px solid black; text-align: center; padding: 8px;">
                Previsión compras X uds (desde el envío de la comunicación hasta finalizar la oferta)
            </th> ';

                $html .= '
                    </th>';

            }

          
               
           

           




            $html .= '
                </tr>
                </thead>
                <tbody>';
            $have_sap_dates = false;
            foreach ($array_product as $product)
            {
                if ($product->start_sap != null && $product->end_sap != null) $have_sap_dates = true;
                $html .= '
                    <tr style="border: 1px solid black; text-align: center; padding: 8px; line-height: 18px;">
                    <td style="border: 1px solid black; text-align: center; padding: 8px;">';
                $html .= $product->product_cod;
                $html .= '
                    </td>
                    <td style="border: 1px solid black; text-align: center; padding: 8px;">';
                $html .= $product->product_description;
                $html .= '
                    </td>
                    <td style="border: 1px solid black; text-align: center; padding: 8px;">';
                $html .= number_format($product->dto, 2) . '%';
                $html .= '
                    </td>';
                if ($type == 2)
                {
                    $html .=  '<td style="border: 1px solid black; text-align: center; padding: 8px;">';
                    $html .= $product->sales;
                    $html .= '</td>';
                }




                else if ($type == 0)
                {
                    $html .=  '<td style="border: 1px solid black; text-align: center; padding: 8px;">';
                    $html .= $product->redemption ? 'Sí' : 'No';
                    $html .= '</td>';

                    $html .=  '<td style="border: 1px solid black; text-align: center; padding: 8px;">';
                    $html .= $product->prevision ;
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '
                </tbody>
                </table>
                <br>';


            if ($have_sap_dates)
            {


                $show_text = false;

                for ($i = 0; $i < count($array_product); $i++)
                {
                    if ($array_product[$i]->dto > 0)
                    {
                        $show_text = true;
                    }
                }

                if ($show_text)
                {
                    if ($type != 2)
                    {
                        $html .= $texts_translation['text_7'];
                    }
                    $html .= '<br /> <br />';


                    $html .= $texts_translation['text_8'];
                    $html .= '<ul>';
                    foreach ($array_product as $product)
                    {
                        if ($product->start_sap != null && $product->end_sap != null && $product->end_sap && $product->dto > 0)
                        {
                            $html .= '<li>' . $texts_translation['text_9'] . ' ';
                            $html .= '<strong>' . $product->product_cod . '</strong>';
                            $html .= ' ' . $texts_translation['text_12'] . '2 ';
                            $html .=  '<strong>' . Carbon::parse($product->start_sap)->format('d/m') . ' - ' . Carbon::parse($product->end_sap)->format('d/m') . '</strong>';
                            $html .= '</li>';
                        }
                    }
                    $html .= '</ul>';
                }
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
            <td >
           
            <span style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">';
        if ($type != 2)
        {
            $html .= $texts_translation['text_10'];
        }
        $html .= '
            </span>
            <br><br>
            <span style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">';
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
          
            <img  width="146" height="60" style="display:block; border:0px;" alt="Logo de La Sirena"
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
        ';
        return $html;
    }

    public function createEmailHtmlCcs($array_product, $type)
    {
        $html = '
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
            <html>

            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>La sirena</title>
            </head>

            <body style="font-size: 15px;font-family: Arial, Helvetica, sans-serif;">
            <table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
            <td style="line-height:0px;">
            <table border="0" align="center" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
            <td width="277"> </td>
            <td height="80">
            <img src="' . HTTPS_WEB_ROOT . '/' . DIR_IMG . '/logo-header-la-sirena.jpg?v=' . config('app.version') . '" alt="Logo de La Sirena"
            width="146" height="60" style="display:block; border:0px;"></td>
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
            <span
            style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">Hola,
            </span>
            <br>
            <br>
            <span style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">
            ';
        if ($type == 2)  $html .= 'Os informamos que hemos enviado un mail a los siguientes proveedores con las ventas:';
        else $html .= 'Os informamos que hemos enviado un mail a los siguientes proveedores con los descuentos:';
        $html .= '
            </span>
            <br><br>
            </td>
            <td width="40"> </td>
            </tr>
            <tr>
            <td width="40"> </td>
            <td>
            <table style="background-color:#f2f2f2;" cellspacing="0" cellpadding="0" border="0"
            align="center">
            <tbody>
            <tr>
            <td style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;"
            width="620">
            <table style=" border-collapse: collapse; width: 100%;" cellspacing="0"
            cellpadding="10">
            <thead>
            <tr
            style=" background-color: #7ea2cf; border: 1px solid black; text-align: center; padding: 8px; line-height: 18px;">
            <th
            style="border: 1px solid black; text-align: center; padding: 8px;">
            Ref
            </th>
            <th
            style="border: 1px solid black; text-align: center; padding: 8px;">
            Descripción Producto
            </th>
            <th
            style="border: 1px solid black; text-align: center; padding: 8px;">
            Ref Prov
            </th>
            <th
            style="border: 1px solid black; text-align: center; padding: 8px;">
            Email Prov
            </th>
            <th
            style="border: 1px solid black; text-align: center; padding: 8px;">
            Descuento
            </th>';
        if ($type == 2)
        {
            $html .= '
            <th
            style="border: 1px solid black; text-align: center; padding: 8px;">
            Ventas
            </th>
            ';
        }
        $html .= '
            </tr>
            </thead>
            <tbody>
        ';
        foreach ($array_product as $product)
        {
            $html .= '
                    <tr style="border: 1px solid black; text-align: center; padding: 8px; line-height: 18px;">
                    <td style="border: 1px solid black; text-align: center; padding: 8px;">';
            $html .= $product->product_cod;
            $html .= '
                    </td>
                    <td style="border: 1px solid black; text-align: center; padding: 8px;">';
            $html .= $product->product_description;
            $html .= '
                    </td>
                    <td style="border: 1px solid black; text-align: center; padding: 8px;">';
            $html .= $product->provider_cod;
            $html .= '
            </td>
            <td style="border: 1px solid black; text-align: center; padding: 8px;">';
            $html .= $product->email;
            $html .= '  </td><td style="border: 1px solid black; text-align: center; padding: 8px;">';
            $html .= number_format($product->dto, 2) . '%';
            $html .= '
                    </td>';
            if ($type == 2)
            {
                $html .= '<td style="border: 1px solid black; text-align: center; padding: 8px;">';
                $html .= $product->sales;
                $html .= '</td>';
            }

            $html .= '</tr>';
        }
        $html .= '
                </tbody>
                </table>
                </td>
                </tr>
                </tbody>
                </table>
                </td>
                <td width="40"> </td>
                </tr>

                <tr>
                <td width="40"> </td>
                <td>
                <br><br>
                <span
                style="color:black; font-size: 15px;font-family: Arial, Helvetica, sans-serif;">Muchas
                gracias,
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
                <img  width="146" height="60" style="display:block; border:0px;" alt="Logo de La Sirena"
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
        ';

        return $html;
    }
    public function get_email_translations($language)
    {
        $array_text_translations = [
            'es' => [
                'text_1' => 'Hola,',
                'text_2' => 'Os informamos que en nuestras ofertas aparecerán las siguientes referencias, en las siguientes fechas:',
                'text_3' => 'Oferta del',
                'text_4' => 'Ref',
                'text_5' => 'Descripción Producto',
                'text_6' => 'Descuento',
                'text_7' => 'Contamos con los siguientes descuentos en estas referencias.',
                'text_8' => 'Las fechas de aplicación de estos descuentos para todos los pedidos que entren en el almacén en este período serán los siguientes:',
                'text_9' => 'Para el producto con referencia',
                'text_10' => 'Por favor, tenedlo en cuenta para stocks y producciones.',
                'text_11' => 'Muchas gracias,',
                'text_12' => 'del',
                'text_13' => 'Ventas',
                'text_14' => 'Ventas del',
                'text_15' => 'Os informamos que se han hecho las siguientes ventas, en las siguientes fechas:',
                'text_16' => 'Redención'
            ],
            'ca' => [
                'text_1' => 'Hola,',
                'text_2' => 'Us informem que en les nostres ofertes apareixeran les següents referències, en les següents dates:',
                'text_3' => 'Oferta del',
                'text_4' => 'Ref',
                'text_5' => 'Descripció Producte',
                'text_6' => 'Descompte',
                'text_7' => 'Comptem amb els següents descomptes en aquestes referències.',
                'text_8' => "Les dates d'aplicació d'aquests descomptes per a totes les comandes que entrin en magatzem en aquest període seran els següents:",
                'text_9' => 'Per al producte amb referència',
                'text_10' => 'Si us plau, tingueu-ho en compte per a estocs i produccions.',
                'text_11' => 'Moltes gràcies,',
                'text_12' => 'del',
                'text_13' => 'Vendes',
                'text_14' => 'Vendes del',
                'text_15' => "Us informem que s'han fet les següents vendes, en les següents dates:",
                'text_16' => 'Redempció'
            ]

        ];
        return $array_text_translations[$language];
    }

    public function verify_email_data($data, $project_id)
    {
        $continue = true;

        if ($data->family == '')
        {
            $continue = false;

            Log::channel('main_app')->error('Product family is null', [
                'family' => $data->family,
                'project_id' => $project_id,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->email == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product email is null', [
                'project_id' => $project_id,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->cod_prov == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product codprov is null or is not a int', [
                'project_id' => $project_id,
                'cod_prov' => $data->cod_prov,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->provider_name == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product provider_name is null', [
                'project_id' => $project_id,
                'provider_name' => $data->provider_name,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->prod == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product prod is null or is not a int', [
                'project_id' => $project_id,
                'prod' => $data->prod,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->product_description == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product product_description is null', [
                'project_id' => $project_id,
                'product_description' => $data->product_description,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->dto == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product dto is null or is not a int', [
                'project_id' => $project_id,
                'dto' => number_format($data->dto, 2),
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->start_date == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product start_date is null', [
                'project_id' => $project_id,
                'start_date' => $data->start_date,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->end_date == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product end_date is null', [
                'project_id' => $project_id,
                'end_date' => $data->end_date,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->send_email == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product send_email is null', [
                'project_id' => $project_id,
                'send_email' => $data->send_email,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->redemption == '')
        {
            $continue = false;
            Log::channel('main_app')->error('Product redemption is null', [
                'project_id' => $project_id,
                'redemption' => $data->redemption,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
        }
        if ($data->send_email == 'false')
        {
            Log::channel('main_app')->error('Send email is false', [
                'project_id' => $project_id,
                'send_email' => $data->send_email,
                'email' => $data->email,
                'error_date' => Carbon::now()
            ]);
            $continue = false;
        }
        //$mail = str_replace(' ', '', $data->email);
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL))
        {
            $continue = false;
            Log::channel('main_app')->error('Email no valid', [
                'project_id' => $project_id,
                'email' => str_replace(' ', '', $data->email),
                'error_date' => Carbon::now()
            ]);
        }

        return $continue;
    }

    public function opens()
    {
        return $this->hasMany(OpenTracking::class, 'newsletter_reference');
    }

    public function email_impact()
    {
        return $this->hasMany(EmailImpact::class, 'newsletter_reference');
    }

    public function check_redemption()
    {

        $array_product = Product::where('email_id', $this->email_id)->select('product_id', 'email_id', 'redemption', 'redempted')->get();

        foreach ($array_product as $product)
        {
            if ($product->redemption == 1 && $product->redempted == 0) return true;
        }

        return false;
    }

    public function getProviderName()
    {
        return Product::where('email_id', $this->email_id)->pluck('provider_name')->first();
    }
}

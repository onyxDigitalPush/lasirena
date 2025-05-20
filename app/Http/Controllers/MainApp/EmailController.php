<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainApp\Email;
use App\Models\MainApp\Product;
use App\Models\MainApp\Project;

class EmailController extends Controller
{
    public function index($project_id)
    {

        $array_email = Email::where([['project_id', $project_id], ['type', 0]])
            ->select('email_id', 'project_id', 'recipient', 'created_at')
            ->orderBy('email_id', 'desc')->with('opens', 'email_impact')->get();

        return view('MainApp.email_list', compact('array_email'));
    }

    public function show($email_id)
    {

        $array_product = Product::where('email_id', $email_id)->select('product_id', 'email_id', 'sales', 'family', 'provider_name', 'product_cod', 'provider_cod', 'product_description', 'dto', 'start_date', 'end_date', 'start_sap', 'end_sap', 'email', 'redemption', 'redempted')->orderBy('product_id', 'desc')->get();

        $obj_email = Email::where('email_id', $email_id)->select('project_id', 'email_id')->first();

        $obj_project = Project::where('project_id', $obj_email->project_id)->select('project_id', 'ccs')->first();

        return view('MainApp.email', compact('array_product', 'obj_email', 'obj_project'));
    }

    public function sendRedemptionEmail(Request $request)
    {
        $project_id = $request->project_id;
        $email_id = $request->email_id;
        $array_sales = json_decode($request->str_sales);
        $ccs = $request->ccs;

        //update the css in the project table
        Project::where('project_id', $project_id)->update(['ccs' => $ccs]);

        //create the new email redemption, in this case we replicate the normal email and change the email_html and the type
        $email_redemption = Email::where('email_id', $email_id)->first()->replicate();
        $email_redemption->type = 2;
        $email_redemption->email_html = '';
        $email_redemption->save();

        foreach ($array_sales as $sales)
        {
            //update the product sales
            Product::where('product_id', $sales->product_id)->update(['sales' => $sales->sales, 'redempted' => 1]);

            //create the new product redemption, in this case we replicate the normal product and change the email_id        
            $product_redemption = Product::where('product_id', $sales->product_id)->first()->replicate();
            $product_redemption->email_id = $email_redemption->email_id;
            $product_redemption->save();
        }

		
		
        //Instancia del objeto email para poder hacer las operaciones
        $obj_email = new Email();

        //create the email html and store in queue
        $obj_email->putEmailQueue($project_id, 2, $email_redemption->email_id);

        //create the email html and store in queue for the css
        $obj_email->putEmailCcsQueue($project_id, 2, $email_redemption->email_id);
		
        return redirect()->route('email.show', $email_id);
    }
}

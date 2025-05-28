<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainApp\Project;
use App\Models\MainApp\Email;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;



class UploadExcelController extends Controller
{
    public function index()
    {

        $user_email = auth()->user()->email;
        return view('MainApp.upload_excel', compact('user_email'));
    }

    public function store(Request $request)
    {

        $obj_email = new Email();
        //Validate the inputs of POST
        $validator = Validator::make($request->all(), [
            'project_name' => 'required',
            'subject' => 'required',
            'ccs' => 'required',
            'reply_to' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect('/proyecto/subir-excel')
                ->withErrors($validator)
                ->withInput();
        }


        $ccs = str_replace(' ',  '', $request->ccs);
        $ccs = str_replace(';', ',', $ccs);

        //Create the project with the data of the post
        $project = Project::create([
            'project_name' => $request->project_name,
            'subject' => $request->subject,
            'ccs' => $ccs,
            'reply_to' => $request->reply_to,
            'document_url' => ''
        ]);

        //Check if exist request file
        if ($request->hasFile('excelFile')) {

            $file = $request->file('excelFile');
            //Saving the file on stroage
            $project->save_excel_file($file);
        }

        //Save the project
        $project->save();

        //Instancia del objeto email para poder hacer las operaciones
        $obj_email = new Email();
        Log::info("Iniciando storeEmails...");
        $obj_email->storeEmails(json_decode($request->str_projects), $project->project_id, 0);
        Log::info("Finalizado storeEmails.");

        Log::info("Iniciando putEmailQueue...");
        $obj_email->putEmailQueue($project->project_id, 0);
        Log::info("Finalizado putEmailQueue.");

        Log::info("Iniciando putEmailCcsQueue...");
        $obj_email->putEmailCcsQueue($project->project_id, 0);
        Log::info("Finalizado putEmailCcsQueue.");

        return redirect()->route('project.index');
    }
}

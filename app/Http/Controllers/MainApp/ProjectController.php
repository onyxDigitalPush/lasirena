<?php

namespace App\Http\Controllers\MainApp;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;

use App\Models\MainApp\Project;

class ProjectController extends Controller
{
    public function index()
    {
        
        $array_project = Project::select('project_id', 'project_name', 'subject', 'document_url', 'created_at', 'reply_to')
            ->with(['opens' => function ($query)
            {
                $query->where('emails.type', 0);
            }])
            ->with(['emails' => function ($query)
            {
                $query->where('emails.type', 0);
            }])
            ->with(['email_impact' => function ($query)
            {
                $query->where('emails.type', 0);
            }])
            ->orderBy('project_id', 'desc')
            ->get();
            if(Auth::user()->type_user == 1 || Auth::user()->type_user == 3)
            {
                      return view('MainApp.project_list', compact('array_project'));
            }
            else
            {
                dd("otro proyecto");
            }
    }
}

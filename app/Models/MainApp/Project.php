<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\EmailingApp\OpenTracking;
use App\Models\MainApp\Email;
use App\Models\EmailingApp\EmailImpact;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $primaryKey = 'project_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_name',
        'subject',
        'ccs',
        'reply_to',
        'document_url'

    ];

    public function save_excel_file($file)
    {
        //creamos el nombre del excel que vamos a guardar con la extension que viene
        $excel_name = $this->project_name . '_' . Carbon::now()->format('Y_m_d_H_i_m') . '.' . $file->extension();
        //Guardamos en el storage el excel
        Storage::disk('local')->put('/excels/' . $excel_name,  \File::get($file));
        //Guardamos el document url del proyecto
        $this->attributes['document_url'] = $excel_name;
    }

    public function opens()
    {

        return $this->hasManyThrough(
            OpenTracking::class,
            Email::class,
            'project_id', // Foreign key on the email table...
            'newsletter_reference', // Foreign key on the opentraking table...
            'project_id', // Local key on the projects table...
            'email_id' // Local key on the email table...
        );
    }

    public function email_impact()
    {
        return $this->hasManyThrough(
            EmailImpact::class,
            Email::class,
            'project_id', // Foreign key on the email table...
            'newsletter_reference', // Foreign key on the opentraking table...
            'project_id', // Local key on the projects table...
            'email_id' // Local key on the email table...
        );
    }
    public function emails()
    {
        return $this->hasMany(Email::class, 'project_id', 'project_id');
    }

    public function emails_count()
    {
        $emails_count = Email::where([['project_id', $this->project_id], ['type', 0]])->count();

        return $emails_count;
    }
}

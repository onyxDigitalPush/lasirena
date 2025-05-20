<?php

namespace App\Models\EmailingApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailImpact extends Model
{
    use HasFactory;

    protected $table = 'log_impact_emails';

    protected $primaryKey = 'log_id';

    public $timestamps = false;
}

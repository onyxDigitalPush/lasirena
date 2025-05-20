<?php

namespace App\Models\EmailingApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenTracking extends Model
{
    use HasFactory;




    protected $table = 'opens';

    protected $primaryKey = 'opens_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
}

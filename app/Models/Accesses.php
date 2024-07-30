<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesses extends Model
{
    public $timestamps = false;
    protected $table = "accesses";
    protected $fillable = [
        'user_id',
        'file_id',
        'access_type',
    ];
}

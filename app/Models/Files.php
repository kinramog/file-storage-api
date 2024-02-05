<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    public $timestamps = false;
    protected $table = "files";
    protected $fillable = [
        "file_id",
        "user_id",
        "name",
        "path",
    ];
}

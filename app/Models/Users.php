<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    public $timestamps = false;
    protected $table = "users";
    protected $fillable = [
        "email",
        "password",
        "first_name",
        "last_name",
        "token",
    ];
}

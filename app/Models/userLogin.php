<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class userLogin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $connection = "mongodb";
    protected $collection = "user_logins";

    protected $fillable = [
        'login_date',
    ];
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $connection = "mongodb";
    protected $collection = "users";

    protected $fillable = [
        '_id',
        'attribute',
        'hub',
        'status',
        'id_number',
        'name',
        'loa_number',
        'license_number',
        'type',
        'rank',
        'license_expiry',
        'email',
        'photo_url',
        'last_login',
    ];

    protected $cast = [
        'license_expiry' => 'date',
    ];
 

    protected $primaryKey = '_id';

}

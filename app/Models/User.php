<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $connection = "mongodb";
    protected $collection = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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
    ];

    protected $cast = [
        'license_expiry' => 'datetime',
    ];
 

    protected $primaryKey = '_id';

}

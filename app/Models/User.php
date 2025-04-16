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
        'ATTRIBUTE',
        'HUB',
        'STATUS',
        "ID NO",
        'NAME',
        "LOA NO",
        "LICENSE NO.",
        'TYPE',
        'LICENSE EXPIRY',
        'RANK',
        'EMAIL',
    ];

    protected $primaryKey = '_id';

}

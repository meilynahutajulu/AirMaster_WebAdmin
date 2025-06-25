<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingType extends Model
{
    protected $connection = "mongodb";
    protected $collection = "users";

    protected $fillable = [
        'id',
        'training',
        'recurrent',
        'training_description',
        'is_delete',
    ];

    protected $primaryKey = 'id';

}

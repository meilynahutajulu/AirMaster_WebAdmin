<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| Device Model
|--------------------------------------------------------------------------
| This model represents the 'devices' collection in MongoDB.
| It defines the structure and fields that can be mass assigned.
| MongoDB will auto-generate the '_id' field.
|
| Fields:
| - device_id      : Custom identifier for the device (can be unique)
| - doc_version    : Document version information
| - ios_version    : iOS version running on the device
| - fly_smart      : FlySmart application version
| - lido_version   : Lido application version
| - hub            : Associated hub (e.g., base airport)
|
*/

class Device extends Model
{
    /**
     * Specifies the database connection to use (MongoDB).
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Specifies the collection name.
     *
     * @var string
     */
    protected $table = 'devices';

    /**
     * Disables default timestamp columns.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Defines mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'doc_version',
        'ios_version',
        'fly_smart',
        'lido_version',
        'hub',
    ];
}

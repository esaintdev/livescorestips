<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    protected $fillable = [
        'route_name',
        'key',
        'value',
    ];

    //
}

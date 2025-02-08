<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'stores';

    protected $fillable = [
        'name',
        'lat',
        'long',
        'is_open',
        'store_type',
        'max_delivery_distance',
    ];
}

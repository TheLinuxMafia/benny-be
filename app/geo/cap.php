<?php

namespace App\geo;

use Illuminate\Database\Eloquent\Model;

class cap extends Model
{
    protected $table = 'geo.cap';
    protected $primaryKey = 'id_cap';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

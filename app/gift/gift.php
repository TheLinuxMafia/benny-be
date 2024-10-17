<?php

namespace App\gift;

use Illuminate\Database\Eloquent\Model;

class gift extends Model
{
    protected $table = 'card.gift';
    protected $primaryKey = 'id_gift';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

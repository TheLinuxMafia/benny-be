<?php

namespace App\gift;

use Illuminate\Database\Eloquent\Model;

class lottigift extends Model
{
    protected $table = 'card.lottigift';
    protected $primaryKey = 'id_lottogift';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

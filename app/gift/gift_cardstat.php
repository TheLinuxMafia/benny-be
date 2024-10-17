<?php

namespace App\gift;

use Illuminate\Database\Eloquent\Model;

class gift_cardstat extends Model
{
    protected $table = 'card.gift_cardstat';
    protected $primaryKey = 'id_gift_cardstat';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

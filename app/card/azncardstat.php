<?php

namespace App\card;

use Illuminate\Database\Eloquent\Model;

class azncardstat extends Model
{
    protected $table = 'card.azn_cardstat';
    protected $primaryKey = 'id_azn_cardstat';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

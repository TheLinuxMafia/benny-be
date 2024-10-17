<?php

namespace App\gift;

use Illuminate\Database\Eloquent\Model;

class giftlog extends Model
{
    protected $table = 'card.giftlog';
    protected $primaryKey = 'id_giftlog';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

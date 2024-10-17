<?php

namespace App\card;

use Illuminate\Database\Eloquent\Model;

class cambio extends Model
{
    protected $table = 'card.cambio';
    protected $primaryKey = 'id_cambio';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

<?php

namespace App\card;

use Illuminate\Database\Eloquent\Model;

class promofidelity extends Model
{
    protected $table = 'card.promofidelity';
    protected $primaryKey = 'id_promofidelity';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

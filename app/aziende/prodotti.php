<?php

namespace App\aziende;

use Illuminate\Database\Eloquent\Model;

class prodotti extends Model
{
    protected $table = 'aziende.prodotti';
    protected $primaryKey = 'id_prodotto';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

}

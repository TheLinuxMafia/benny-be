<?php

namespace App\aziende;

use Illuminate\Database\Eloquent\Model;

class azn_puntivendita extends Model
{
    protected $table = 'aziende.azn_puntivendita';
    protected $primaryKey = 'id_azn_puntovendita';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

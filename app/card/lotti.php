<?php

namespace App\card;

use Illuminate\Database\Eloquent\Model;

class lotti extends Model
{
    protected $table = 'card.lotti';
    protected $primaryKey = 'id_lotto';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

/*
|-------------------------------------------------------------------------------
| Relazione Campagne
|-------------------------------------------------------------------------------
| Description:    Restituisce le campagne a cui un lotto è assegnato
| Parameters:     id_lotto
| Schema: aziende
| Db Table: lotti
| Type:     hasMany
*/

public function lotticampagna() {
    return $this->hasOne('App\card\lotticampagna', 'id_lotto', 'id_lotto');
}



}

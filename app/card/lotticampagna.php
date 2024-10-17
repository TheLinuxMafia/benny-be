<?php

namespace App\card;

use Illuminate\Database\Eloquent\Model;

class lotticampagna extends Model
{
    protected $table = 'card.lotticampagne';
    protected $primaryKey = 'id_lottocampagna';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

/*
|-------------------------------------------------------------------------------
| Relazione lotto
|-------------------------------------------------------------------------------
| Description:    Relazione lotto
| Parameters:     id_lotto
| Schema: aziende
| Db Table: lotti
| Type:     hasOne
*/

public function lotto() {
    return $this->hasOne('App\card\lotti', 'id_lotto', 'id_lotto');
}


/*
|-------------------------------------------------------------------------------
| Relazione campagna
|-------------------------------------------------------------------------------
| Description:    Restituisce il nome della campagna associata al lotto
| Parameters:     id_campagna
| Schema: aziende
| Db Table: lotti
| Type:     hasOne
*/

public function campagna() {
    return $this->hasOne('App\aziende\campagne', 'id_campagna', 'id_campagna');
}


}

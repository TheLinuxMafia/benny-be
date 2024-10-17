<?php

namespace App\card;

use Illuminate\Database\Eloquent\Model;
use DB;

class movimenti extends Model
{
    protected $table = 'card.movimenti';
    protected $primaryKey = 'id_movimento';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

/*
|-------------------------------------------------------------------------------
| Relazione user
|-------------------------------------------------------------------------------
| Description:    Restituisce l'utente cha ha inserito un movimento
| Parameters:     card
| Schema: aziende
| Db Table: movimenti
| Type:     hasOne
*/

public function user() {
    return $this->hasOne('App\User', 'id', 'user_ins');
    }

/*
|-------------------------------------------------------------------------------
| Relazione somma
|-------------------------------------------------------------------------------
| Description:    Restituisce il totale dei punti della card
| Parameters:     card
| Schema: card
| Db Table: movimenti
| Type:     hasOne
*/

public function somma() {
    return $this->hasMany('App\card\movimenti')
        ->selectRaw('id_movimento, SUM(valore) as punti')
        ->where('card', 'card')
        ->groupBy('id_movimento');
    }
}


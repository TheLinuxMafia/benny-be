<?php

namespace App\gift;

use Illuminate\Database\Eloquent\Model;

class movgift extends Model
{
    protected $table = 'card.movgift';
    protected $primaryKey = 'id_movgift';

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

/** Restituisce il proprietario della carta */
public function owner() {
    return $this->hasOne('App\gift\giftutenti', 'gift', 'gift');
}
}

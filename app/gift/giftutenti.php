<?php

namespace App\gift;

use Illuminate\Database\Eloquent\Model;

class giftutenti extends Model
{
    protected $table = 'card.giftutenti';
    protected $primaryKey = 'id_giftutenti';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

/*
|-------------------------------------------------------------------------------
| Relazione Lotto
|-------------------------------------------------------------------------------
| Description:    Restituisce il lotto di una gift
| Parameters:     id_lotto
| Schema: aziende
| Db Table: lotti
| Type:     hasOne
*/

public function lotto() {
    return $this->hasOne('App\gift\lottigift', 'id_lottogift', 'lotto_gift');
}

/*
|-------------------------------------------------------------------------------
| Relazione azienda
|-------------------------------------------------------------------------------
| Description:    Restituisce l'azienda di una card
| Parameters:     id_lotto
| Schema: aziende
| Db Table: azn_anagrafica
| Type:     hasOne
*/

public function azienda() {
    return $this->hasOne('App\aziende\azn_anagrafiche', 'id_azn_anagrafica', 'id_azn_anagrafica');
}

/*
|-------------------------------------------------------------------------------
| Relazione movimenti
|-------------------------------------------------------------------------------
| Description:    Restituisce i movimenti di una card
| Parameters:     card
| Schema: aziende
| Db Table: movimenti
| Type:     hasMany
*/

public function movimenti() {
    return $this->hasMany('App\gift\movgift', 'gift', 'gift')->orderBy('id_movgift', 'desc')->take(5);
}

/*
|-------------------------------------------------------------------------------
| Relazione movimenti
|-------------------------------------------------------------------------------
| Description:    Restituisce l'ultimo movimento
| Parameters:     card
| Schema: aziende
| Db Table: movimenti
| Type:     hasOne
*/

public function lastmov() {
    return $this->hasOne('App\gift\movgift', 'gift', 'gift')->orderBy('id_movgift', 'desc');
}

/*
|-------------------------------------------------------------------------------
| Relazione utente
|-------------------------------------------------------------------------------
| Description:    Restituisce i movimenti di una card
| Parameters:     card
| Schema: aziende
| Db Table: movimenti
| Type:     hasMany
*/

public function utente() {
    return $this->hasOne('App\gift\giftutenti', 'gift', 'gift');
}
}

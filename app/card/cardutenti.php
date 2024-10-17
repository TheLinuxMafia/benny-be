<?php

namespace App\card;

use Illuminate\Database\Eloquent\Model;

class cardutenti extends Model
{
    protected $table = 'card.cardutenti';
    protected $primaryKey = 'id_cardutenti';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

/*
|-------------------------------------------------------------------------------
| Relazione Lotto
|-------------------------------------------------------------------------------
| Description:    Restituisce il lotto di una card
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
    return $this->hasMany('App\card\movimenti', 'card', 'card')->orderBy('id_movimento', 'DESC')->take(5);
}

/*
|-------------------------------------------------------------------------------
| Relazione somma punti
|-------------------------------------------------------------------------------
| Description:    Restituisce i movimenti di una card
| Parameters:     card
| Schema: aziende
| Db Table: movimenti
| Type:     hasMany
*/

public function punti() {
$data = $this->hasMany('App\card\movimenti', 'card', 'card');
    return $data->sum('movimenti.valore');
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
    return $this->hasOne('App\card\cardutenti', 'card', 'card');
}

}

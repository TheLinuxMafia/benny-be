<?php

namespace App\aziende;

use Illuminate\Database\Eloquent\Model;

class campagne extends Model
{
    protected $table = 'aziende.campagne';
    protected $primaryKey = 'id_campagna';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

/*
|-------------------------------------------------------------------------------
| Relazione azienda
|-------------------------------------------------------------------------------
| Description:    Relazione azienda
| Parameters:     id_azn_anagrafica
| Schema: aziende
| Db Table: azn_anagrafiche
| Type:     hasOne
*/

public function azienda() {
    return $this->hasOne('App\aziende\azn_anagrafiche', 'id_azn_anagrafica', 'id_azn_anagrafica');
}

/*
|-------------------------------------------------------------------------------
| Relazione azienda
|-------------------------------------------------------------------------------
| Description:    Relazione prodotti
| Parameters:     id_campagna
| Schema: aziende
| Db Table: prodotti
| Type:     hasMany
*/

public function prodotti() {
    return $this->hasMany('App\aziende\prodotti', 'id_campagna', 'id_campagna');
}

/*
|-------------------------------------------------------------------------------
| Relazione promozioni
|-------------------------------------------------------------------------------
| Description:    Relazione promozioni
| Parameters:     id_campagna
| Schema: aziende
| Db Table: fidelitypromo
| Type:     hasOne
*/

public function promozioni() {
    return $this->hasOne('App\card\promofidelity', 'id_campagna', 'id_campagna');
}


}

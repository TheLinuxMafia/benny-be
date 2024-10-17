<?php

namespace App\aziende;

use Illuminate\Database\Eloquent\Model;

class azn_anagrafiche extends Model
{
    protected $table = 'aziende.azn_anagrafiche';
    protected $primaryKey = 'id_azn_anagrafica';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

/*
|-------------------------------------------------------------------------------
| Relazione punti vendita
|-------------------------------------------------------------------------------
| Description:    Relazione punti vendita
| Parameters:     id_azn_anagrafica
| Schema: aziende
| Db Table: azn_anagrafiche
| Type:     hasMany
*/

public function pv() {
    return $this->hasMany('App\aziende\azn_puntivendita', 'id_azn_anagrafica', 'id_azn_anagrafica');
}

}

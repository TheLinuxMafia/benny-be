<?php

namespace App\geo;

use Illuminate\Database\Eloquent\Model;

class comuni extends Model
{
    protected $table = 'geo.comuni';
    protected $primaryKey = 'id_comune';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

/*
|-------------------------------------------------------------------------------
| Relazione cap
|-------------------------------------------------------------------------------
| Description:    Relazione cap
| Parameters:     id_comune
| Schema: geo
| Db Table: cap
| Type:     hasMany
*/

public function cap() {
    return $this->hasMany('App\geo\cap', 'id_comune', 'id_comune');
}
}

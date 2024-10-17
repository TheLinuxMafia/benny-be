<?php

namespace App\carburanti;

use Illuminate\Database\Eloquent\Model;

class ec extends Model
{
    protected $table = 'carburanti.ec';
    protected $primaryKey = 'id';
    protected $fillable = [
        'numero',
        'data',
        'importo',
        'azienda',
        'id_azienda',
        'tipologia',
        'periodo',
        'pagato',
        'data_pagamento',
        'mod_pagamento',
        'file',
        'stato',
        'acconto'
    ];
}

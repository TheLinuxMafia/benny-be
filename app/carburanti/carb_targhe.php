<?php

namespace App\carburanti;

use Illuminate\Database\Eloquent\Model;

class carb_targhe extends Model
{
    protected $table = 'carburanti.carb_targhe';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_azienda',
        'ragsoc',
        'piva',
        'tipo',
        'targa',
        'prodotto',
        'prodotto1',
        'prodotto2',
        'km',
        'marca',
        'modello', 
        'userins',
        'centro',
        'shared'
    ];

    public function azienda() {
        return $this->hasOne('App\carburanti\carb_aziende', 'id', 'id_azienda');
    }
}

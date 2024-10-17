<?php

namespace App\carburanti;

use Illuminate\Database\Eloquent\Model;

class carb_trans extends Model
{
    protected $table = 'carburanti.carb_trans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'scode',
        'prodotto',
        'prodotto1',
        'prodotto2',
        'pr_importo',
        'pr1_importo',
        'pr2_importo',
        'adblue',
        'olio',
        'accessori',
        'id_azienda',
        'targa',
        'ragsoc',
        'piva',
        'km',
        'tipo',
        'marca',
        'modello',
        'totale',
        'id_puntov',
        'attiva',
        'pr_litri',
        'pr1_litri',
        'pr2_litri',
        'contabilizzata',
        'ec',
        'adblue_litri'

    ];
}

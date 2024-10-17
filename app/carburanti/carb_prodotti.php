<?php

namespace App\carburanti;

use Illuminate\Database\Eloquent\Model;

class carb_prodotti extends Model
{
    protected $table = 'carburanti.carb_prodotti';
    protected $primaryKey = 'id';
    protected $fillable = [
        'prodotto',
        'prezzo'
    ];
}

<?php

namespace App\carburanti;

use Illuminate\Database\Eloquent\Model;

class carb_centricosto extends Model
{
    protected $table = 'carburanti.carb_centricosto';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_azienda',
        'centro'
    ];
}

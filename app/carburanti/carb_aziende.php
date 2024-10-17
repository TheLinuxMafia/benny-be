<?php

namespace App\carburanti;

use Illuminate\Database\Eloquent\Model;

class carb_aziende extends Model
{
    protected $table = 'carburanti.carb_aziende';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'ragsoc',
        'indirizzo',
        'regione',
        'provincia',
        'comune',
        'piva',
        'codfis',
        'nome',
        'cognome',
        'sdi',
        'email',
        'telefono',
        'cellulare',
        'pec',
        'userins',
        'cap',
        'send_email'
    ];

    public function centricosto() {
        return $this->hasMany('App\carburanti\carb_centricosto', 'id_azienda', 'id');
    }
}

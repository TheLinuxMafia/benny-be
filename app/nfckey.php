<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class nfckey extends Model
{
    protected $table = 'public.nfckey';
    protected $primaryKey = 'id_nfckey';

    protected $hidden = [
        'created_at', 'updated_at',
    ];
}

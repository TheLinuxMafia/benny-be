<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GrpmemberResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id_gruppo_assoc'       =>      $this->id_gruppo_assoc,
            'id_gruppo_cdr'         =>      $this->id_gruppo_cdr,
            'id_impianto'           =>      $this->id_impianto,
            'id_gestore_cdr'        =>      $this['impianti']['id_gestore_cdr'],
            'nome'                  =>      $this['impianti']['nome'],
            'descrizione'           =>      $this['impianti']['descrizione'],
            'id_indirizzo'          =>      $this['impianti']['id_indirizzo'],


        ];
    }
}

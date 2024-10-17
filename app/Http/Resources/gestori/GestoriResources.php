<?php

namespace App\Http\Resources\gestori;

use Illuminate\Http\Resources\Json\JsonResource;

class GestoriResources extends JsonResource
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
            'id_gestore'       => $this->id_gestore_cdr,
            'id_codice'         =>     $this->id_codice,
            'codice'        =>      $this['cod_gestore']['codice'],
            'denominazione' =>  $this['cod_gestore']['anagrafica']['denominazione']
        ];
    }
}




/*
[
    {
        "id_gestore_cdr": 29,
        "id_codice": 208,
        "user_create": null,
        "last_user_update": null,
        "id_indirizzo": 28,
        "cod_gestore": {
            "id_codice": 208,
            "codice": "04450430659",
            "user_create": null,
            "last_user_update": null,
            "id_typeuser": 3,
            "anagrafica": {
                "id_codice": 208,
                "denominazione": "linuxit.it di",
                "user_create": null,
                "last_user_update": null,
                "nome": null,
                "cognome": null,
                "id_anagrafica": 16
            }
        }
    }
]

*/
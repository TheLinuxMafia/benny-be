<?php

namespace App\Http\Resources\anagrafiche;

use Illuminate\Http\Resources\Json\JsonResource;

class ContattiIndirizziResources extends JsonResource
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
            'id_contatto'       =>      $this->id_contatto,
            'contatto'          =>      $this->contatto,
            'tipocontatto'      =>      $this['typecontact']['type_contact'],
            'typeid'            =>      $this['typecontact']['id_typecontact'],
            'priorita'          =>      $this->priority,
            'active'            =>      $this->active,
            "id_indirizzo"      =>      $this->id_indirizzo,
        ];
    }
}

/*
"contattiind": [
    {
        "id_contatto": 24,
        "contatto": "info@linuxit.it",
        "id_codice": 208,
        "user_create": null,
        "last_user_update": null,
        "id_typecontact": 3,
        "priority": 1,
        "active": true,
        "id_indirizzo": 11,
        "typecontact": [
            {
                "id_typecontact": 3,
                "type_contact": "Email"
            }
        ]
    }
],
*/
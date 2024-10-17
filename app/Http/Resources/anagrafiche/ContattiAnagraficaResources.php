<?php

namespace App\Http\Resources\anagrafiche;

use Illuminate\Http\Resources\Json\JsonResource;

class ContattiAnagraficaResources extends JsonResource
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
            'type'              =>      $this['typecontact']['type_contact'],
            'idtype'            =>      $this['typecontact']['id_typecontact'],
            'priorita'          =>      $this->priority,
            'active'            =>      $this->active,
        ];
    }
}


/*
"contattiana": [
        {
            "id_contatto": 22,
            "contatto": "giacomo@linusit.it",
            "id_codice": 208,
            "user_create": null,
            "last_user_update": null,
            "id_typecontact": 3,
            "priority": 1,
            "active": true,
            "id_indirizzo": null,
            "typecontact": [
                {
                    "id_typecontact": 3,
                    "type_contact": "Email"
                }
            ]
        },
        */
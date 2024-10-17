<?php

namespace App\Http\Resources\cdr;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\anagrafiche\ContattiIndirizziResources;

class CdrResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $contattiind = collect($this->resource->cdrindirizzo->contattiind);
        return [
        'id_impianto'           =>  $this->id_impianto,
        'id_gestore_cdr'        =>  $this->id_gestore_cdr,
        'nome'                  =>  $this->nome,
        'id_gruppo_cdr'         =>  $this->id_gruppo_cdr,
        'descrizione'           =>  $this->descrizione,
        'id_gruppo_assoc'       =>  $this['idgruppo']['id_gruppo_assoc'],
        'indirizzo'             =>  $this['cdrindirizzo']['indirizzo'],
        'civico'                =>  $this['cdrindirizzo']['civico'],
        'id_indirizzo'          =>  $this['cdrindirizzo']['id_indirizzo'],
        'cap'                   =>  $this['cdrindirizzo']['cap'],
        'tipologia'             =>  $this['cdrindirizzo']['typeaddress']['type_address'],
        'riferimento'           =>  $this['cdrindirizzo']['riferimento'],
        'denregion'             =>  $this['cdrindirizzo']['municipalitys']['denregion'],
        'denprovince'           =>  $this['cdrindirizzo']['municipalitys']['denprovince'],
        'siglaprovince'         =>  $this['cdrindirizzo']['municipalitys']['siglaprovince'],
        'namemunicipality'      =>  $this['cdrindirizzo']['municipalitys']['namemunicipality'],
        'contattiind'           =>  ContattiIndirizziResources::collection($contattiind),
        'gestore'               =>  $this['cdrgestore']['denominazione'],
        'id_codice'             =>  $this['cdrgestore']['id_codice'],
      ];
    }
}

/*
[

            },

            ],
            "typeaddress": {
                "id_typeaddress": 2,
                "type_address": "Unità Locale"
            }
        },
        "cdrgestore": {
            "id_codice": 208,
            "denominazione": "linuxit.it di Santamaria Giacomo",
            "user_create": null,
            "last_user_update": null,
            "nome": null,
            "cognome": null,
            "id_anagrafica": 16
        }
    }
]
*/

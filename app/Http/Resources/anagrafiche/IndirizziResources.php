<?php

namespace App\Http\Resources\anagrafiche;

use Illuminate\Http\Resources\Json\JsonResource;

class IndirizziResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $contattiind = collect($this->resource->contattiind);
        return [
            'id_indirizzo'      =>      $this->id_indirizzo,
            'indirizzo'         =>      $this->indirizzo,
            'id_typeaddress'    =>      $this->id_typeaddress,
            'civico'            =>      $this->civico,
            'cap'               =>      $this->cap,
            'scala'             =>      $this->scala,
            'piano'             =>      $this->piano,
            'interno'           =>      $this->interno,
            'riferimento'       =>      $this->riferimento,
            'regione'           =>      $this['municipalitys']['denregion'],
            'provincia'         =>      $this['municipalitys']['denprovince'],
            'siglaprovincia'           =>      $this['municipalitys']['siglaprovince'],
            'comune'                =>      $this['municipalitys']['namemunicipality'],
            'id_regione'        =>      $this->id_region,
            'id_provincia'      =>      $this->id_province,
            'id_comune'         =>      $this->id_municipality,
            'contattiind'       =>      ContattiIndirizziResources::collection($contattiind),
            'indgestore'        =>      $this['indGestore']['id_indirizzo'],
            'id_impianto'           =>      $this['haveCdr']['id_impianto'],
            'impianto'              =>      $this['haveCdr']['nome'],
        ];
    }
}


/*
"indirizzi": [
    {
        "id_indirizzo": 11,
        "id_codice": 208,
        "indirizzo": "via R. Mauri",
        "id_region": 15,
        "id_province": 65,
        "id_municipality": 6352,
        "cap": "84129",
        "user_create": null,
        "last_user_update": null,
        "id_typeaddress": 1,
        "scala": "a",
        "piano": "1",
        "interno": "5",
        "civico": "135",
        "riferimento": null,
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
        "municipalitys": {
            "id_municipality": 6352,
            "id_region": 15,
            "codregionalfa": "15",
            "codcittametropolitana": null,
            "id_province": 65,
            "codprovincelfa": "065",
            "progmunicipalityalfa": "116",
            "codcomunealfa": "065116",
            "namemunicipality": "Salerno",
            "codripartizionegeografica": 4,
            "ripartizionegeografica": "Sud",
            "denregion": "Campania",
            "dencittametropolitana": "",
            "denprovince": "Salerno",
            "flagcapoluogoprovince": true,
            "siglaprovince": "SA",
            "codcomune": 65116,
            "person": 132608,
            "validdate": "2018-04-30",
            "id_typemunicipality": null,
            "id_user_create": null,
            "id_user_update": null
        }
    },
    {
        "id_indirizzo": 12,
        "id_codice": 208,
        "indirizzo": "via tanagro",
        "id_region": 15,
        "id_province": 65,
        "id_municipality": 6352,
        "cap": "84129",
        "user_create": null,
        "last_user_update": null,
        "id_typeaddress": 2,
        "scala": "a",
        "piano": "terra",
        "interno": "1",
        "civico": "12",
        "riferimento": null,
        "contattiind": [
            {
                "id_contatto": 25,
                "contatto": "3939253035",
                "id_codice": 208,
                "user_create": null,
                "last_user_update": null,
                "id_typecontact": 1,
                "priority": 1,
                "active": true,
                "id_indirizzo": 12,
                "typecontact": [
                    {
                        "id_typecontact": 1,
                        "type_contact": "Cellulare"
                    }
                ]
            },
            {
                "id_contatto": 26,
                "contatto": "089759098",
                "id_codice": 208,
                "user_create": null,
                "last_user_update": null,
                "id_typecontact": 2,
                "priority": 2,
                "active": true,
                "id_indirizzo": 12,
                "typecontact": [
                    {
                        "id_typecontact": 2,
                        "type_contact": "Fisso"
                    }
                ]
            }
        ],
        "municipalitys": {
            "id_municipality": 6352,
            "id_region": 15,
            "codregionalfa": "15",
            "codcittametropolitana": null,
            "id_province": 65,
            "codprovincelfa": "065",
            "progmunicipalityalfa": "116",
            "codcomunealfa": "065116",
            "namemunicipality": "Salerno",
            "codripartizionegeografica": 4,
            "ripartizionegeografica": "Sud",
            "denregion": "Campania",
            "dencittametropolitana": "",
            "denprovince": "Salerno",
            "flagcapoluogoprovince": true,
            "siglaprovince": "SA",
            "codcomune": 65116,
            "person": 132608,
            "validdate": "2018-04-30",
            "id_typemunicipality": null,
            "id_user_create": null,
            "id_user_update": null
        }
    }
],

*/

<?php

namespace App\Http\Resources\gestori;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\anagrafiche\IndirizziResources;

class IndirizziGestoriResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $indirizzi = collect($this->resource->indirizzi_gestore);
        return [
            'indirizzo'             =>      $this['indirizzi_gestore']['indirizzo'],
            'civico'                =>      $this['indirizzi_gestore']['civico'],
            'cap'                   =>      $this['indirizzi_gestore']['cap'],
            'id_indirizzo'          =>      $this['indirizzi_gestore']['id_indirizzo'],
            'riferimento'           =>      $this['indirizzi_gestore']['riferimento'],
            'namemunicipality'      =>      $this['indirizzi_gestore']['municipalitys']['namemunicipality'],
            'denregion'             =>      $this['indirizzi_gestore']['municipalitys']['denregion'],
            'denprovince'           =>      $this['indirizzi_gestore']['municipalitys']['denprovince'],
            'siglaprovince'         =>      $this['indirizzi_gestore']['municipalitys']['siglaprovince'],
            'id_impianto'           =>      $this['haveCdr']['id_impianto'],
            'impianto'              =>      $this['haveCdr']['nome'],

        ];
    }
}


/*

        "have_cdr": {
            "id_impianto": 4,
            "id_gestore_cdr": 29,
            "nome": "CDR Arechi",
            "id_gruppo_cdr": null,
            "id_indirizzo": 28,
            "user_create": 1,
            "last_user_update": null,
            "descrizione": "Centro di Raccolta Arechi",
            "id_codice": 208

[
    {
        "id_gestore_cdr": 29,
        "id_codice": 208,
        "user_create": null,
        "last_user_update": null,
        "id_indirizzo": 28,
        "indirizzi_gestore": [
            {
                "id_indirizzo": 28,
                "id_codice": 208,
                "indirizzo": "Via Roma",
                "id_region": 15,
                "id_province": 65,
                "id_municipality": 6352,
                "cap": "84131",
                "user_create": null,
                "last_user_update": null,
                "id_typeaddress": 2,
                "scala": "A",
                "piano": "2",
                "interno": "11",
                "civico": "80",
                "riferimento": "Negozio",
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
        ]
    },

            "have_cdr": {
            "id_impianto": 4,
            "id_gestore_cdr": 29,
            "nome": "CDR Arechi",
            "id_gruppo_cdr": null,
            "id_indirizzo": 28,
            "user_create": 1,
            "last_user_update": null,
            "descrizione": "Centro di Raccolta Arechi",
            "id_codice": 208

    */

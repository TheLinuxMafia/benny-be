<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssocImpiantiGestoriResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $grpmember = collect($this->resource->grpmember);
        return [
            'id_gruppo_cdr' =>  $this->id_gruppo_cdr,
            'nome'          =>  $this->nome,
            'descrizione'   =>  $this->descrizione,
            'grpmember'     =>  GrpmemberResources::collection($grpmember),

        ];
    }
}

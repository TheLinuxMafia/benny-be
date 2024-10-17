<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\aziende\campagne;
use App\card\lotticampagna;

class AdmCampagneController extends Controller
{
    /**
     * Lista delle campagne di un'azienda (aziende.campagne)
     * method. GET
     * url api/admin/campagne
     * @param mixed $request
     * @return object
     */

    public function campagne() {
        $campagna = campagne::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->with('azienda', 'azienda.pv')->orderBy('id_campagna')->get();
            return response()->json($campagna, 200);
    }

    /**
     * Lista delle campagne di un'azienda (aziende.campagne)
     * method. GET
     * url api/admin/campagne
     * @param mixed $request
     * @return object
     */

    public function campagneattive() {
        $campagna = campagne::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->where('active', true)->with('azienda', 'azienda.pv')->orderBy('id_campagna')->get();
            return response()->json($campagna, 200);
    }


    /**
     * Attiva o disattiva una campagna (aziende.campagne)
     * method. GET
     * url api/admin/campagne
     * @param mixed $request
     * @return boolean
     */

    public function modcampagne($id) {
        $campagna = campagne::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->where('id_campagna', $id)->with('azienda', 'azienda.pv')->first();
        if($campagna->active == 'true') {
            $campagna->active = false;
            $campagna->update();
            return response()->json(true, 200);
        } else {
            $campagna->active = true;
            $campagna->update();
            return response()->json(true, 200);
        }
            return response()->json(false, 442);
    }

    /**
     * Modifica campagna (aziende.campagne)
     * method. POST
     * url api/auth/campagna
     * @param mixed $request
     * @return array
     */

    public function modcampagna(Request $request) {

            $campagna = campagne::where('id_campagna', $request->id_campagna)->first();
            $campagna->id_azn_anagrafica = auth()->user()->id_azn_anagrafica;
            $campagna->nome_campagna = $request->nome_campagna;
            $campagna->slogan_campagna = $request->slogan_campagna;
            $campagna->logo     = $request->logo;
            $campagna->catalogo     = $request->catalogo;
            $campagna->importo_premi     = $request->importo_premi;
            $campagna->data_start     = $request->data_start;
            $campagna->data_end     = $request->data_end;
            if ($campagna->update()) {
                return response()->json($campagna, 200);
            }
            return response()->json(false, 422);
    }
}

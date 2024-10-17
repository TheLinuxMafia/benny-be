<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\aziende\campagne;
use App\aziende\prodotti;
use App\card\lotticampagna;
use App\card\promofidelity;

class CampagneController extends Controller
{
    /**
     * Inserimento o modifica nuova campagna (aziende.campagne)
     * method. POST
     * url api/auth/campagna
     * @param mixed $request
     * @return array
     */

    public function campagna(Request $request) {

        if($request->id_campagna > 0) {
            $campagna = campagne::where('id_campagna', $request->id_campagna)->first();
            $campagna->id_azn_anagrafica = $request->azienda;
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
        }

        $campagna = new campagne;
        $campagna->id_azn_anagrafica = $request->azienda;
        $campagna->nome_campagna = $request->nome_campagna;
        $campagna->slogan_campagna = $request->slogan_campagna;
        $campagna->logo     = $request->logo;
        $campagna->catalogo     = $request->catalogo;
        $campagna->importo_premi     = $request->importo_premi;
        $campagna->data_start     = $request->data_start;
        $campagna->data_end     = $request->data_end;

        if ($campagna->save()) {
            return response()->json($campagna, 200);
        }
    }

    /**
     * Lista delle campagne del sistema (aziende.campagne)
     * method. GET
     * url api/auth/campagna
     * @param mixed $request
     * @return array
     */

    public function campagnaLista() {
        $campagna = campagne::with('azienda', 'azienda.pv', 'prodotti', 'promozioni')->orderBy('id_campagna')->get();
            return response()->json($campagna, 200);
    }

    /**
     * Dettaglio di una campagna (aziende.campagne)
     * method. GET
     * url api/auth/campagna/{id}
     * @param mixed $request
     * @return object
     */

    public function campagnaDettaglio($id) {
        $campagna = campagne::where('id_campagna', $id)->with('azienda', 'azienda.pv')->first();
            return response()->json($campagna, 200);
    }

    /**
     * Sospende o attiva una campagna (aziende.campagne)
     * method. GET
     * url api/auth/campagna/{id}
     * @param mixed $request
     * @return bool
     */

    public function campagnaStatus(Request $request) {
        if($request->active === 'true') {
            $data = campagne::where('id_campagna', $request->id_campagna)->first();
            $data->active = true;
            if ($data->update()) {
                $lotto = lotticampagna::where('id_campagna', $request->id_campagna)->first();
                if ($lotto) {
                    $lotto->attiva = true;
                    if ($lotto->update()) {
                        return response()->json(true, 200);
                    }
                }
                return response()->json(true, 200);
            }
        } else {
            $data = campagne::where('id_campagna', $request->id_campagna)->first();
            $data->active = false;
            if ($data->update()) {
                $lotto = lotticampagna::where('id_campagna', $request->id_campagna)->first();
                if ($lotto) {
                    $lotto->attiva = false;
                    if ($lotto->update()) {
                        return response()->json(true, 200);
                    }
                }
                return response()->json(true, 200);
            }
        }
        return response()->json(false, 200);
    }



    /**
     * Setup di una campagna (aziende.campagne)
     * method. GET
     * url api/auth/setupcampagna
     * @param mixed $request
     * @return bool
     */

     public function setupcampagna(Request $request) {


        /** Modifica del campo tipo_punti nella tabella aziende.campagne */
        /** In caso che il campo tipo_punti è prodotti bisogna inserire i dati nella tabella prodotti */

        $updcampagna = campagne::where('id_campagna', $request->id_campagna)->first();
        $updcampagna->type = $request->type;
        $updcampagna->update();

        if($request->type === 'prodotti') {
            $del = prodotti::where('id_campagna', $request->id_campagna)->delete();

        foreach ($request->prodotti as $prodotto) {
            $prodotti = new prodotti();
            $prodotti->prodotto = $prodotto['prodotto'];
            $prodotti->punti = $prodotto['punti'];
            $prodotti->id_campagna = $request->id_campagna;
            $prodotti->save();
        }
    }

        return response()->json(true, 200);
     }

    /**
     * Configurazione di una promozione a tempo
     * method. POST
     * url api/auth/promocampagna
     * @param mixed $request
     * @return bool
     */

    public function addpromocampagna(Request $request) {
        if($request->id_promofidelity === null) {
            $data = new promofidelity();
            $data->promo    = $request->promo;
            $data->fattore    = $request->fattore;
            $data->valore    = $request->valore;
            $data->condizione     = $request->condizione;
            $data->data_start    = $request->data_start;
            $data->data_end    = $request->data_end;
            $data->id_campagna    = $request->id_campagna;
            $data->valcondizione    = $request->valcondizione;
            if($data->save()) {
                return response()->json($data, 200);
            }
            return response()->json(false, 429);
        } else {
            $data = promofidelity::where('id_promofidelity', $request->id_promofidelity)->first();
            $data->promo    = $request->promo;
            $data->fattore    = $request->fattore;
            $data->valore    = $request->valore;
            $data->condizione     = $request->condizione;
            $data->data_start    = $request->data_start;
            $data->data_end    = $request->data_end;
            $data->id_campagna    = $request->id_campagna;
            $data->valcondizione    = $request->valcondizione;
            if($data->update()) {
                return response()->json($data, 200);
            }
            return response()->json(false, 429);
        }
    }

}




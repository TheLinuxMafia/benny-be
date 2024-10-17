<?php

/** Prendo il timestamp
 *  - Rimuovo le ultime 2 cifre
 * - Aggiungo l'id azienda
 * - controllo che siano
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function Newride\EAN13\create;
use App\card\lotti;
use App\card\carte;
use App\card\azncardstat;
use App\card\lotticampagna;
use DB;

class LottiController extends Controller
{
    /**
     * Creazione nuovo lotto ed associazione carte. (card.lotti)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return array
     */


public function addLotto(Request $request) {

    DB::beginTransaction();
    try {


/** Prima inserisco i dati nella tabella dei lotti */
        $lotto = new lotti;
        $lotto->lotto = time();
        $lotto->tot_schede = $request->qtacard;
        $lotto->id_azn_anagrafica = $request->id_azn_anagrafica;

/** Eseguo le funzioni solo se riesco ad inserire il lotto nella tabella */
        if($lotto->save()) {

/** Controllo nella tabella azn_cardstat se esiste un movimento dell'azienda */
            $checknumber = azncardstat::where('id_azn_anagrafica', $request->id_azn_anagrafica)->first();

/** Se esiste il movimento dell'azienda allora creo delle card sequenziali */
            if($checknumber) {
                $bcode = (int)$checknumber->numero_carta;
                $numerocarte = $request->qtacard; /** Numero delle carte da generare */
                $cardfinale = ($bcode + $numerocarte -1); /** Es. 146223 (number) */

/** Funzione che viene richiamata solo se il tipo doi carta da creare è una EAN13 */
                if($request->typecard === 'ean13') {
                    $statocard = $conv = sprintf('%012d', $cardfinale);
                }

/** Inizio il ciclo per la creazione dei numeri delle card */
                for ($i=$bcode; $cardfinale >= $i; $i++) {

/** Converto il numero della card in stringa ed aggiungo gli zeri fino ad arrivare all'occorrenza di 12 */
                    $conv = sprintf('%012d', $i);

/** Funzione che viene richiamata solo se il tipo doi carta da creare è una EAN13 */
                    if($request->typecard === 'ean13') {
                        $carta = create($i);
                    }

/** Inserisco le carte nella tabella card */
                        $card = new carte;
                        $card->numero_carta = $carta;
                        $card->id_lotto = $lotto->id_lotto;
                        $card->usata = false;
                        $card->attiva = true;
                        $card->id_azn_anagrafica = $request->id_azn_anagrafica;
                        $card->save();
                 }

/** Aggiorno lo stato delle card dell'azienda nella tabella azn_cardstat */
                 $updatecheck = azncardstat::where('id_azn_anagrafica', $request->id_azn_anagrafica)->first();
                 $updatecheck->numero_carta = $statocard;
                 $updatecheck->data_ins = date("Y-m-d H:i:s");
                 $updatecheck->update();

/** Se non esiste alcun movimento dell'azienda allora inizio creando le prime card */

            } else { ################ SE E' LA PRIMA CREAZIONE DELLE CARD PER L'AZIENDA ####################

            $id = $request->id_azn_anagrafica; //Le prime cifre del barcode

            $numerocarte = $request->qtacard; /** Numero delle carte da generare */

             $rand =  (mt_rand(1000,9999)); /** Numero random di 4 cifre */

             $acode = ($id . $rand); // Es. 145133 (string)

             $bcode = (int)$acode; // Es. 145133 (number)

             $cardfinale = ($bcode + $numerocarte -1); /** Es. 146223 (number) */

             $ccode = sprintf('%012d', $bcode); /** Il numero diventa 12 cifre Es. 000993041000 (string) */

/** Questa è il numero dell'ultima card che serve da salvare nello stato, tabella azn_statocard */
             if($request->typecard === 'ean13') {
                $statocard = $conv = sprintf('%012d', $cardfinale);
            }

/** Inizio il ciclo per la creazione dei numeri delle card */
             for ($i=$bcode; $cardfinale >= $i; $i++) {
                $conv = sprintf('%012d', $i);

/** Se il tipo di card da creare è un EAN13 allora utilizzo questa funzione */
                if($request->typecard === 'ean13') {
                    $carta = create($i);
                }

/** Inserisco le carte nella tabella card */
                $card = new carte;
                $card->numero_carta = $carta;
                $card->id_lotto = $lotto->id_lotto;
                $card->usata = false;
                $card->attiva = true;
                $card->id_azn_anagrafica = $request->id_azn_anagrafica;
                $card->save();
             }

/** Aggiorno lo stato delle card dell'azienda nella tabella azn_cardstat */
             $updatecheck = new azncardstat;
             $updatecheck->id_azn_anagrafica = $request->id_azn_anagrafica;
             $updatecheck->numero_carta = $statocard;
             $updatecheck->data_ins = date("Y-m-d H:i:s");
             $updatecheck->save();
            }
        }

                DB::commit();
                    $success = true;
                    }
                    catch (Exception $e) {
                        $success = false;
                        DB::rollback();
                        }
                        if ($success) {
                            return response()->json(['status' => 'success'], 200);
                        } else {
                            return response()->json(['message' => 'false'], 404);
                        }
        }


    /**
     * Restituisce tutti i lotti non utilizzati registrati nel sistema. (card.lotti)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return array
     */

    public function showLotti($id_azn_anagrafica) {
        $lotti = lotti::where('utilizzato', false)->where('id_azn_anagrafica', $id_azn_anagrafica)->get();
            return response()->json($lotti, 200);
    }


    /**
     * Assegna un lotto ad una campagna. (card.lotticampagna
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function addLottoCampagna(Request $request) {
        $data = new lotticampagna;
        $data->id_lotto = $request->id_lotto;
        $data->id_azn_anagrafica = $request->id_azn_anagrafica;
        $data->id_campagna = $request->id_campagna;
        $data->lotto = $request->lotto;
        $data->tot_schede = $request->tot_schede;
        $data->data_ins = date("Y-m-d H:i:s");
        $data->userins = auth()->user()->id;
        if($data->save()) {

            /** In questa fase indico che il lotto è utilizzato modificando il valore utilizzato a true nella tabella card.lotti */
            $changelottostatus = lotti::where('id_lotto', $request->id_lotto)->first();
            $changelottostatus->utilizzato  = true;
            $changelottostatus->update();
            return response()->json(['status => success'], 200);

        }
        return response()->json(['status' => 'failed'], 422);
    }

    /**
     * Rimuove un lotto ad una campagna. (card.lotticampagna
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function delLottoCampagna(Request $request) {
        $data = lotticampagna::where('id_azn_anagrafica', $request->id_azn_anagrafica)
        ->where('id_campagna', $request->id_campagna)
        ->where('id_lotto', $request->id_lotto)
        ->first();
        if($data->delete()) {
                /** In questa fase indico che il lotto è libero modificando il valore utilizzato a false nella tabella card.lotti */
                $changelottostatus = lotti::where('id_lotto', $request->id_lotto)->first();
                $changelottostatus->utilizzato  = false;
                $changelottostatus->update();
                return response()->json(['status => success'], 200);
            return response()->json(['status => success'], 200);
        }
        return response()->json(['status' => 'failed'], 422);
    }

    /**
     * Restituisce i lotti assegnati ad una campagna. (card.lotticampagna)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return array
     */

     public function lottiCampagna($id){
        $data = lotticampagna::where('id_campagna', $id)->get();
            return response()->json($data, 200);
     }
}

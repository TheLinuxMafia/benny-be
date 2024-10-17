<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\card\carte;
use App\card\cardutenti;
use App\card\movimenti;
use App\card\lotticampagna;
use App\card\cambio;
use App\card\promofidelity;
use Carbon\Carbon;
use Illuminate\Support\Str;
use DB;
use App\Mail\EmailForQueuing;
use App\Mail\EmailForPrivacy;
use App\Mail\EmailForAccount;
use App\Mail\NotifyFidelityPoint;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class AdmCarteController extends Controller
{
    /**
     * conta le carte totali disponibili per un'azienda
     * method. GET
     * url api/admin/totcarte
     * @param mixed $request
     * @return number
     */

     public function totalecarte() {
         $data = carte::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->count();
            return response()->json($data, 200);
     }


    /**
     * conta le carte già associate ad un utente per un'azienda
     * method. GET
     * url api/admin/totcarteutilizzate
     * @param mixed $request
     * @return number
     */

     public function carteutilizzate() {
        $data = cardutenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->count();
        return response()->json($data, 200);
     }

    /**
     * Controlla se una carta fedeltà è inserita nel sistema ed è associata all'azienda
     *
     * @return bool
     */
    public function controllacarta(Request $request)
    {
        $data = carte::where('numero_carta', $request->carta)->where('usata', 'false')->where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->first();
        if ($data) {
            return response()->json(['result' => true], 200);
        } else {
            return response()->json(['result' => false], 200);
        }
    }

    /**
     * Restituisce le carte associate per un'azienda
     * method. GET
     * url api/admin/getcarte
     * @param mixed $request
     * @return array
     */

     public function getcarte() {
        $data = cardutenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->with('lotto')->get();
            if($data) {
                return response()->json($data, 200);
            }
            return response()->json(false, 422);
     }

    /**
     * Controlla se una carta è già associata e può essere caricata con i punti
     * method. POST
     * url api/admin/checkassociata
     * @param mixed $request
     * @return array
     */

    public function checkassociata(Request $request)
    {
      //  return response()->json($request, 200);
        $data = cardutenti::where('card', $request->carta)
        ->where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)
        ->with('lotto', 'movimenti', 'lotto.lotticampagna', 'lotto.lotticampagna.campagna', 'lotto.lotticampagna.campagna.prodotti')->first();
        if ($data) {
            return response()->json($data, 200);
        } else if (!$data) {
            $data = carte::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->where('numero_carta', $request->carta)->first();
            if ($data) {
                return response()->json($data, 200);
            }
        }
          return response()->json(['result' => false], 200);
    }

    /**
     * Inserisce i punti ad una card creando un movimento nella tabella (card.movimenti)
     * Viene rilevato il tipo di prodotto utilizzato e va fatto quindi un calcolo dei punti
     * method. POST
     * url api/admin/addpoint
     * @param mixed $request
     * @return array
     */

     public function addpoint(Request $request) {

        /** Recupero l'id del lotto della card */
        $idlotto = cardutenti::where('card', $request->card)->first();

        /** recupero i dati della campagna */
        $campagna = lotticampagna::where('id_lotto', $idlotto->id_lotto)->first();

        $today = Carbon::now();
        //return response()->json($today, 200);
        /** Controllo se c'è una promozione valida in corso  */

        if($promo = promofidelity::where('data_start', '<=', $today)->where('data_end', '>=', $today)->first()) {
            /** Eseguo le varie funzioni pe capire in che modo valorizzare la promozione */
         //   return response()->json($today, 200);
            /** Nel caso che la condizione sia valida sempre */
            if($promo->condizione === 'sempre') {
                if($promo->fattore === '+') {
                    $newval = ($request->valore + $promo->valore);
                }
                if($promo->fattore === '-') {
                    $newval = ($request->valore - $promo->valore);
                }
                if($promo->fattore === '*') {
                    $newval = ($request->valore * $promo->valore);
                }
                if($promo->fattore === '/') {
                    $newval = ($request->valore / $promo->valore);
                }
            }

            if($promo->condizione === 'maggiore') {
                if ($request->valore > $promo->valcondizione) {

                    if($promo->fattore === '+') {
                        $newval = ($request->valore + $promo->valore);
                    }
                    if($promo->fattore === '-') {
                        $newval = ($request->valore - $promo->valore);
                    }
                    if($promo->fattore === '*') {
                        $newval = ($request->valore * $promo->valore);
                    }
                    if($promo->fattore === '/') {
                        $newval = ($request->valore / $promo->valore);
                    }
                } else {
                    $newval = $request->valore;
                }
            }
        } else {
            $newval = $request->valore;
        }

        /** Calcolo il numero di punti da aggiungere in base al valore ed ai parametri che mi arrivano dal frontend */
        /** Es: se vengono caricati 10 unità per un prodotto che vale 0.5 unità devo effettuare il calcolo: 10 * 0.5 = 5 */
        /** Quindi il valore delle unità da caricare sarà 5 */
        if($request->haveProdotto === true) {
            $val = ($newval * $request->punti);
            $value = number_format($val, 2, '.', '');

        } else {
            $value = number_format($newval, 2, '.', '');
        }



        /** Selezioni la fidelity card per verificare lo stato attuale del credito */
        $stato = movimenti::where('card', $request->card)->orderBy('id_movimento', 'desc')->first();
        if($stato) {
            $nuovostato = $value + $stato->stato;
        } else {
            $nuovostato = $value;
        }

         $data = new movimenti;
         $data->card = $request->card;
         $data->data_movimento = Carbon::now(new \DateTimeZone('Europe/Rome'));
         $data->user_ins = auth()->user()->id;
         $data->valore = $value;
         $data->id_campagna = $campagna->id_campagna;
         $data->id_lotto = $idlotto->id_lotto;
         $data->id_azn_anagrafica = auth()->user()->id_azn_anagrafica;
         if($request->termid) {
            $data->term_id = $request->termid;
         }
         $data->stato = $nuovostato;
         if($data->save()) {
            $datimail = [
                'dataora' => $data->data_movimento,
                'email' => $idlotto->email,
                'punti' => $value,
                'totali' => $nuovostato,
                'subject' =>   "Benny Card - Comunicazione accredito punti",
                'bcc'   =>  'info@linuxit.it',
            ];

             Mail::to($idlotto->email)->send(new NotifyFidelityPoint($datimail));
             return response()->json($data, 200);
         }
         return response()->json(['message' => 'failed'], 200);
     }

    /**
     * Restituisce gli ultimi 10 movimenti (card.movimenti)
     * method. GET
     * url api/admin/lastpoint
     * @param null
     * @return array
     */

     public function lastpoint() {
         $data = movimenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->take(5)->orderBy('id_movimento', 'DESC')->get();
            return response()->json($data, 200);
     }

    /**
     * Restituisce il dettaglio di una card
     * method. GET
     * url api/admin/dettagliocard
     * @param null
     * @return array
     */

    public function dettagliocard($cards) {
        $data = cardutenti::where('card', $cards)->with('lotto', 'lotto.lotticampagna', 'lotto.lotticampagna.campagna', 'lotto.lotticampagna.campagna.prodotti', 'azienda', 'movimenti', 'movimenti.user', 'utente')->first();
           if($data) {
               return response()->json($data, 200);
           }
           return response()->json(false, 500);
    }

    /**
     * Disabilita una card
     * method. GET
     * url api/admin/dettagliocard
     * @param null
     * @return array
     */

    public function cardchangestatus($card) {
        $data = cardutenti::where('card', $card)->first();
        if($data->attiva == true) {
            $data->attiva = false;
            $data->update();


            return response()->json(['status' => true], 200);
        }
        $data->attiva = true;
        $data->update();

        return response()->json(['status' => true], 200);
    }

    /**
     * Associa una carta registrata nel sistema ad un utente
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function nuovacard(request $request) {

        $daticard = carte::where('numero_carta', $request->card)->first();
        if($daticard) {
            $data = new cardutenti;
            $data->card                 =   $request->card;
            $data->id_lotto             =   $daticard->id_lotto;
            $data->id_azn_anagrafica    =   $daticard->id_azn_anagrafica;
            $data->nome                 =   $request->nome;
            $data->cognome              =   $request->cognome;
            $data->datanascita          =   $request->datanascita;
            $data->sesso                =   $request->sesso;
            $data->cap                  =   $request->cap;
            $data->comune               =   $request->comune;
            $data->indirizzo            =   $request->indirizzo;
            $data->email                =   $request->email;
            $data->cellulare            =   $request->cellulare;
            $data->telefono             =   $request->telefono;
            $data->data_ins             =   Carbon::now(new \DateTimeZone('Europe/Rome'));
            $data->token                =   $token = Str::random(60);
            if($data->save()) {
                $carta = carte::where('numero_carta', $request->card)->first();
                if($carta->delete()) {

                    $data = [
                        'nome' => $request->nome . ' ' . $request->cognome,
                        'email' => $request->email,
                        'subject' =>   "Benny Card - Comunicazione regolamento privacy e trattamento dati",
                        'msg' => '',
                        'bcc'   =>  'info@linuxit.it',
                        'token' => $token,
                    ];

                Mail::to($request->email)->send(new EmailForPrivacy($data));


                    return response()->json(true, 200);
                }
            }
            return response()->json(false, 422);
        }
    }

    /**
     * Restituisce tutti i movimenti di una card
     * method. POST
     * url api/auth/movimenticard
     * @param string $card
     * @return array
     */

     public function movimenticard($card) {
        $data = movimenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->where('card', $card)->with('user')->orderBy('id_movimento', 'DESC')->get();
            return response()->json($data, 200);
     }

    /**
     * Modifica il numero di una card in caso di sostituzione per furto o smarrimento
     * method. POST
     * url api/auth/changecard
     * @param string $card
     * @return array
     */

     public function changecard(Request $request) {
         /** TODO
          * Controllo se la nuova card è compresa in un lotto abilitato
          * Modifico i riferimenti della vecchia card con la nuova nella tabella cardutenti
          */

        $checkcard = carte::where('numero_carta', $request->newcard)->where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->first();
        if(isset($checkcard)) {
            DB::beginTransaction();
            try {

            /** Modifico il riferimento della card per l'utente */
            $cardutente = cardutenti::where('card', $request->oldcard)->where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->first();
                    $cardutente->card   =  $request->newcard;
                    $cardutente->update();

                        /** Creo il log del cambio */
                        $cambio = new cambio;
                        $cambio->oldcard = $request->oldcard;
                        $cambio->newcard = $request->newcard;
                        $cambio->user_ins = auth()->user()->id;
                        $cambio->id_azn_anagrafica = auth()->user()->id_azn_anagrafica;
                        $cambio->datains = Carbon::now(new \DateTimeZone('Europe/Rome'));
                        $cambio->save();

                                    /** Rimuovo la nuova card da quelle disponibili */
                                    $card = carte::where('numero_carta', $request->newcard)->where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->first();
                                    $card->delete();

                                            /** Modifico i movimenti con la nuova card */
                                            $movimenti = movimenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->where('card', $request->oldcard)->update(['card' => $request->newcard]);

            DB::commit();
            $success = true;
            }
            catch (Exception $e) {
                $success = false;
                DB::rollback();
                }
                if ($success) {
                    return response()->json($success, 200);
                } else {
                    return response()->json(['message' => 'false'], 404);
                }
        return response()->json(['message' => 'false'], 200);
     }
    }

    public function getMovimentiTerminale($termid) {
        $data = movimenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)
        ->where('term_id', $termid)
        ->with('user')
        ->take(10)
        ->orderBy('id_movimento', 'DESC')
        ->get();
        return response()->json($data, 200);
    }


    /**
     * Modifica i dati utente di una fidelity card registrata
     * method. POST
     * url api/auth/modcard
     * @param mixed $request
     * @return bool
     */

    public function modcard(request $request) {

        $data = cardutenti::where('card', $request->card)->first();
            $data->nome                 =   $request->nome;
            $data->cognome              =   $request->cognome;
            $data->datanascita          =   $request->datanascita;
            $data->sesso                =   $request->sesso;
            $data->cap                  =   $request->cap;
            $data->comune               =   $request->comune;
            $data->indirizzo            =   $request->indirizzo;
            $data->email                =   $request->email;
            $data->cellulare            =   $request->cellulare;
            $data->telefono             =   $request->telefono;
            if($data->update()) {
                    return response()->json(true, 200);
                }
            return response()->json(false, 422);
    }

}

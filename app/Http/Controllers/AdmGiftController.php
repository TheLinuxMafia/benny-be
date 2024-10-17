<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\gift\gift;
use App\gift\gift_cardstat;
use App\gift\lottigift;
use App\gift\giftutenti;
use App\gift\movgift;
use Carbon\Carbon;
use App\gift\giftlog;
use Illuminate\Support\Str;
use DB;
use App\Mail\EmailForQueuing;
use App\Mail\EmailForPrivacy;
use App\Mail\EmailForAccount;
use App\Mail\GiftPayNotify;
use App\Mail\GiftRicarica;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class AdmGiftController extends Controller
{

    /**
     * conta le gift totali disponibili per un'azienda
     * method. GET
     * url api/admin/totalegift
     * @param mixed $request
     * @return number
     */

    public function totalegift() {
        $data = gift::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->count();
           return response()->json($data, 200);
    }

    /**
     * conta le gift già associate ad un utente per un'azienda
     * method. GET
     * url api/admin/giftutilizzate
     * @param mixed $request
     * @return number
     */

    public function giftutilizzate() {
        $data = giftutenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->count();
        return response()->json($data, 200);
     }

    /**
     * Controlla se una gift card è inserita nel sistema ed è associata all'azienda
     *
     * @return bool
     */
    public function controllagift(Request $request)
    {
        $data = gift::where('numero_gift', $request->carta)->where('usata', 'false')->where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->first();
        if ($data) {
            return response()->json(['result' => true], 200);
        } else {
            return response()->json(['result' => false], 200);
        }
    }

    /**
     * Restituisce le gift associate per un'azienda
     * method. GET
     * url api/admin/getgift
     * @param mixed $request
     * @return array
     */

    public function getgift() {
        $data = giftutenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->with('lotto')->get();
            if($data) {
                return response()->json($data, 200);
            }
            return response()->json(false, 422);
     }

    /**
     * Controlla se una gift è già associata e può essere caricata con i punti
     * method. GET
     * url api/admin/giftassociata
     * @param mixed $request
     * @return array
     */

    public function giftassociata(Request $request)
    {
        $data = giftutenti::where('gift', $request->carta)
        ->where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)
        ->with('lotto', 'lastmov')->first();
        if ($data) {
            return response()->json($data, 200);
        } else if (!$data) {
            $data = gift::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->where('numero_gift', $request->carta)->first();
            if ($data) {
                return response()->json($data, 200);
            }
        }
          return response()->json(['result' => false], 200);
    }

    /**
     * Restituisce gli ultimi 5 movimenti (card.movimenti)
     * method. GET
     * url api/admin/lastgiftmov
     * @param null
     * @return array
     */

    public function lastgiftmov() {
        $data = movgift::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->take(5)->orderBy('id_movgift', 'DESC')->get();
           return response()->json($data, 200);
        }

     /**
     * Associa una gift card registrata nel sistema ad un utente
     * method. POST
     * url api/auth/nuovagift
     * @param mixed $request
     * @return bool
     */

    public function nuovagift(request $request) {

        DB::beginTransaction();
        try {

        $daticard = gift::where('numero_gift', $request->card)->first();
            $data = new giftutenti;
            $data->gift                 =   $request->card;
            $data->lotto_gift           =   $daticard->lotto_gift;
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
            $data->id_user              =   auth()->user()->id;
            $data->data_ins             =   Carbon::now(new \DateTimeZone('Europe/Rome'));
            $data->attiva               =   true;
            $data->token                =   $token = Str::random(60);
            $data->save();

                /** Elimina la gift card dalla lista */
                $carta = gift::where('numero_gift', $request->card)->first();
                $carta->delete();

                /** 
                 * Questa funzione serve per sommare il valore della ricarica fatta in fase di attivazione
                 * al valore che è stato dato alle card in fase di creazione.
                 */
                if ($request->valore < 1) {
                    $rqvalore = 0;
                } else {
                    $rqvalore = $request->valore;
                }

                $valore = $rqvalore + $daticard->valore;

                    /** Creo un movimento per ricaricare la carta */
                    $data = new movgift;
                    $data->gift = $request->card;
                    $data->data_movimento   = Carbon::now(new \DateTimeZone('Europe/Rome'));
                    $data->user_ins     = auth()->user()->id;
                    $data->valore = $rqvalore;
                    $data->id_azn_anagrafica                =   auth()->user()->id_azn_anagrafica;
                    $data->tipo_mov = 'carico';
                    $data->stato = $valore;
                    if($request->termid) {
                        $data->termid =   $request->termid;
                    }
                    $data->save();

                    /** Crea il log del movimento */
                    $log = new giftlog;
                    $log->user = auth()->user()->id;
                    $log->data = Carbon::now(new \DateTimeZone('Europe/Rome'));
                    $log->log  = 'Registrazione card numero ' . $request->card . ' da ' . auth()->user()->name;
                    $log->save();

                    $data = [
                        'nome' => $request->nome . ' ' . $request->cognome,
                        'email' => $request->email,
                        'subject' =>   "Benny Card - Comunicazione regolamento privacy e trattamento dati",
                        'msg' => '',
                        'bcc'   =>  'info@linuxit.it',
                        'token' => $token,
                    ];

                Mail::to($request->email)->send(new EmailForPrivacy($data));

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
     * Ricarica una gift card
     * method. POST
     * url api/auth/ricaricagift
     * @param mixed $request
     * @return bool
     */

    public function ricaricagift(request $request) {

        DB::beginTransaction();
        try {

            /** Controllo se la card è associata ad un utente */
            $data = giftutenti::where('gift', $request->gift)->first();

            /** Selezioni la gift card per verificare lo stato attuale del credito */
            $stato = movgift::where('gift', $request->gift)->orderBy('id_movgift', 'desc')->first();

            $mov = new movgift;
            $mov->gift = $data->gift;
            $mov->tipo_mov = 'carico';
            $mov->valore = $request->valore;
            $mov->user_ins = auth()->user()->id;
            $mov->id_azn_anagrafica =   auth()->user()->id_azn_anagrafica;
            $mov->data_movimento   = Carbon::now(new \DateTimeZone('Europe/Rome'));
            if($request->termid) {
                $data->termid =   $request->termid;
            }
            $mov->stato = $request->valore + $stato['stato'];
            $mov->save();
            //--

                    /** Crea il log del movimento */
                    $log = new giftlog;
                    $log->user = auth()->user()->id;
                    $log->data = Carbon::now(new \DateTimeZone('Europe/Rome'));
                    $log->log  = 'Ricarica card numero ' . $data->gift . ' di € ' . $request->valore . ' da ' . auth()->user()->name;
                    $log->save();

            DB::commit();
            $success = true;
            }

            catch (Exception $e) {
                $success = false;
                DB::rollback();
                }
                if ($success) {

                    $datimail = [
                        'dataora' => $log->data,
                        'email' => $data->email,
                        'card' => $data->gift,
                        'valore' => $request->valore,
                        'residuo' => $mov->stato,
                        'subject' =>   "Benny Gift - Comunicazione ricarica effettuata",
                        'bcc'   =>  'info@linuxit.it',
                    ];
        
                     Mail::to($data->email)->send(new GiftRicarica($datimail));


                    return response()->json(['status' => 'success'], 200);
                } else {
                    return response()->json(['message' => 'false'], 404);
                }
            }

     /**
     * Pagamento con gift card
     * method. POST
     * url api/auth/pagacongift
     * @param mixed $request
     * @return bool
     */

    public function pagacongift(request $request) {

        DB::beginTransaction();
        try {

            /** Controllo se la card è associata ad un utente */
            $data = giftutenti::where('gift', $request->card)->first();

                        /** Selezioni la gift card per verificare lo stato attuale del credito */
                        $stato = movgift::where('gift', $request->card)->orderBy('id_movgift', 'desc')->first();
                    //    return response()->json($stato, 200);
                        /** Se il valore del pagamento è maggiore della disponibilità sollevo eccezione */
                        if($request->valore > $stato['stato']) {
                            return response()->json(['residuo' => $stato['stato']], 401);
                        }
          //  return response()->json($data, 200);
            $mov = new movgift;
            $mov->gift = $data->gift;
            $mov->tipo_mov = 'scarico';
            $mov->valore = $request->valore;
            $mov->user_ins = auth()->user()->id;
            $mov->id_azn_anagrafica =   auth()->user()->id_azn_anagrafica;
            $mov->data_movimento   = Carbon::now(new \DateTimeZone('Europe/Rome'));
            if($request->termid) {
                $data->termid =   $request->termid;
            }
            $mov->stato = $stato['stato'] - $request->valore;
            $mov->save();
            //--
            $residuo = $stato['stato'] - $request->valore;
         //   return response()->json($residuo, 200);

                /** Crea il log del movimento */
                $log = new giftlog;
                $log->user = auth()->user()->id;
                $log->data = Carbon::now(new \DateTimeZone('Europe/Rome'));
                $log->log  = 'Pagamento card numero ' . $data->gift . ' di € ' . $request->valore . ' da ' . auth()->user()->name;
                $log->save();

            DB::commit();
            $success = true;
            }

            catch (Exception $e) {
                $success = false;
                DB::rollback();
                }
                if ($success) {

                    $datimail = [
                        'dataora' => $log->data,
                        'email' => $data->email,
                        'card' => $data->gift,
                        'valore' => $request->valore,
                        'residuo' => $mov->stato,
                        'subject' =>   "Benny Gift - Comunicazione pagamento effettuato",
                        'bcc'   =>  'info@linuxit.it',
                    ];
        
                     Mail::to($data->email)->send(new GiftPayNotify($datimail));


                    return response()->json(['status' => 'success', 'residuo' => $residuo ], 200);
                } else {
                    return response()->json(['message' => 'false'], 404);
                }
            }

    /**
     * Restituisce il dettaglio di una gift card
     * method. GET
     * url api/admin/dettagliogift
     * @param null
     * @return array
     */

    public function dettagliogift($card) {
        $data = giftutenti::where('gift', $card)->with('lotto', 'azienda', 'movimenti', 'movimenti.user', 'utente')->first();
           return response()->json($data, 200);
    }


    /**
     * Disabilita o abilita una gift card
     * method. GET
     * url api/admin/giftchangestatus
     * @param null
     * @return array
     */

    public function giftchangestatus($card) {
        $data = giftutenti::where('gift', $card)->first();
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
     * Restituisce tutti i movimenti di una gift card
     * method. POST
     * url api/auth/movimentigift
     * @param string $card
     * @return array
     */

    public function movimentigift($card) {
        $data = movgift::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->where('gift', $card)->with('user')->orderBy('id_movgift', 'DESC')->get();
            return response()->json($data, 200);
     }

     /**
     * Restituisce i pagamenti effettuati tramite POS nella giornata.
     * url api/auth/termgiftmov
     * @param string $termid
     * @return array
    */
     
    public function getMovimentiGiftTerminale($termid) {
        $data = movgift::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)
        ->whereDate('data_movimento', date("Y-m-d"))
        ->with('user')
        ->orderBy('id_movgift', 'DESC')
        ->get();
     //   return response()->json(date("Y-m-d"), 200);
        return response()->json($data, 200);
    }


    /**
     * Associa una targa ad una gift card
     * url api/auth/targatogift
     * @param array
     * @return boolean
     */
    public function targatogift(Request $request) {
        $status = false;
        
        /** Controllo prima che l'utente sia abilitato alla modifica */
        $log = auth()->user()->role;
        if(($log === 'PUNTOV') or ($log === 'GOD') or ($log ===  'ADMIN') ) {

        /** Rimuovo tutti gli spazi e converto in maiuscolo*/
        $searchString = " ";
        $replaceString = "";
        $targaTmp = str_replace($searchString, $replaceString, $request->targa);
        $stringa = preg_replace('/[^A-Za-z0-9_]/', '', $targaTmp );
        $targa = strtoupper($stringa);

        /** Controllo se la targa ha già un associazione */
        $check = giftutenti::where('targa', $targa)->first();

        if($check) {
            $obj = new \stdClass;
            $obj->response = 422;
            $obj->targa = $targa;
            $obj->msg = 'La targa è già presente in archivio';
            return response()->json($obj, 200);
        }
            /** Seleziono il record da modificare */
            $data = giftutenti::where('gift', $request->card)->first();

            /** Verifico se è nuovo inserimento o aggiornamento targa */
            if($data->targa) {
                $type = 'UPDATE';
            } else {
                $type = 'NEW';
            }


            $data->targa = $targa;
            if($data->update()) {
                $status = true;
            }

            $obj = new \stdClass;
            $obj->response = 200;
            $obj->msg = 'La targa è stata associata con successo';
            $obj->auth = true;
            $obj->role = $log;
            $obj->card = $request->card;
            $obj->targa = $targa;
            $obj->status = $status;
            $obj->type = $type;

            return response()->json($obj, 200);
        } else {
            $obj = new \stdClass;
            $obj->auth = false;
            $obj->role = $log;
            $obj->status = $status;
            $obj->msg = 'Non sei autorizzato ad effettuare questa modifica';
            return response()->json($obj, 200);
        }
    }

}

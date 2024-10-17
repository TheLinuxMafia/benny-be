<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\gift\gift;
use App\gift\gift_cardstat;
use App\gift\lottigift;
use App\gift\giftutenti;
use App\gift\movgift;
use DB;
use stdClass;

class GiftController extends Controller
{

    /**
     * Restituisce la lista di tutte le gift card assegnate
     *
     * @return array
     */

    public function giftcardassegnate() {
        $data = giftutenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->with('lastmov')->orderBy('email')->get();
            return response()->json($data, 200);
    }



    /**
     * Controlla se una gift card è inserita nel sistema
     *
     * @return bool
     */
    public function checkGift(Request $request)
    {
        $data = gift::where('numero_gift', $request->carta)->where('usata', 'false')->first();
        if ($data) {
            return response()->json(['result' => true], 200);
        } else {
            return response()->json(['result' => false], 200);
        }
    }


    /**
     * Controlla se una mail è già in uso
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function checkGiftMail($mail)
    {
        $data = giftutenti::where('email', $mail)->first();
        if ($data) {
            return response()->json(['result' => true], 200);
        } else {
            return response()->json(['result' => false], 200);
        }
    }



    /**
     * Associa una gift registrata nel sistema ad un utente
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function nuovagift(request $request) {
        DB::beginTransaction();
        try {

        $daticard = gift::where('numero_gift', $request->card)->first();
        if($daticard) {
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
            $data->save();
                $carta = gift::where('numero_gift', $request->card)->first();
                $carta->usata = true;
                $carta->update();

            DB::commit();
            $success = true;
            }
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

        /** Generazione di un report in PDF dei movimenti di una gift card, compresi in un range di date */
        public function giftreportdate(Request $request) {

            /** Raccolta informazioni utente  */
            $user = giftutenti::where('gift', $request->gift)->first();

            /** Selezione dei movimenti fra le date indicate */
            $report = movgift::where('gift', $request->gift)->whereBetween('data_movimento', [$request->start, $request->end])->get();

            return response()->json($report, 200);
        }

        /** Generazione report di tutti i movimenti di una gift card */
        public function reportallgift(Request $request) {

            /** Raccolta informazioni utente  */
            $user = giftutenti::where('gift', $request->gift)->first();

            /** Selezione dei movimenti fra le date indicate */
            $report = movgift::where('gift', $request->gift)->get();

            $obj = new stdClass();
            $obj->user = $user;
            $obj->report = $report; 

            return response()->json($obj, 200);

        }

        /** Generazione report dei movimenti del giorno di tutte le gift card */
        public function allgifttoday($oggi) {
         //   $oggi = '2022-09-08';
            $reports = movgift::whereDate('data_movimento', $oggi)->with('owner')->get();

            $user = new stdClass();
            $user->nome = 'Benny';
            $user->cognome = 'Oil';
            $user->gift = 'Tutte';
            $user->data = $oggi;

            $obj = new stdClass();
            $obj->user = $user;
            $obj->report = $reports; 

            return response()->json($obj, 200);
        }

        /** Restituisce la somma del credito residuo sulle gift card */
        public function sommacreditogift() {
            $dati = giftutenti::where('gift', '!=', '0000062446525')->where('gift', '!=', '0000062446532')->where('gift', '!=', '0000062444026')->with('lastmov')->get();

            $sum = 0;
            foreach ($dati as $data) {
                $sum += $data->lastmov['stato'];
            }
            return response()->json($sum, 200);
        }

    }


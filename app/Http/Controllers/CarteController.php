<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\card\carte;
use App\card\cardutenti;

class CarteController extends Controller
{

    /**
     * Restituisce la lista di tutte le fidelity card assegnate
     *
     * @return array
     */

    public function fidelitycardassegnate() {
        $data = cardutenti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->get();
            return response()->json($data, 200);
    }


    /**
     * Controlla se una carta fedeltà è inserita nel sistema
     *
     * @return bool
     */
    public function checkCarta(Request $request)
    {
        $data = carte::where('numero_carta', $request->carta)->where('usata', 'false')->first();
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
    public function checkMail($mail)
    {
        $data = cardutenti::where('email', $mail)->first();
        if ($data) {
            return response()->json(['result' => true], 200);
        } else {
            return response()->json(['result' => false], 200);
        }
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
            if($data->save()) {
                $carta = carte::where('numero_carta', $request->card)->first();
                $carta->usata = true;
                if($carta->update()) {
                    return response()->json(true, 200);
                }
            }
            return response()->json(false, 422);
        }
    }
}

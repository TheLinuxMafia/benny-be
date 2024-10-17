<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\aziende\azn_puntivendita;
use Carbon\Carbon;
use App\User;
use App\Mail\EmailForQueuing;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use DB;

class PuntiVenditaController extends Controller
{
    /**
     * Inserimento nuovo punto vendita relativo ad un'azienda. (aziende.azn_puntivendita)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function addpv(Request $request) {
        $request->validate([
            'azienda' => 'required',
            'nomepv' => 'required|max:50',
            'indirizzopv' => 'required|max:150',
            'comune' => 'required|max:50',
            'cap'   =>  'required|max:5',
            'ragsoc'    =>  'max:100',
            'email' =>  'required|max:100|unique:pgsql.aziende.azn_puntivendita',
            'telefono'  =>  'max:12',
            'referente'  =>  'max:100',
        ], [
            'azienda'   =>      'Azienda è un campo richiesto',
            'nomepv'    =>  'Nome punto vendita è un campo richiesto',
            'indirizzopv.required' => 'Indirizzo è un campo richiesto',
            'comune.required' => 'Comune è un campo richiesto',
            'cap.required' => 'CAP è un campo richiesto',
            'email.required'  => 'Email è un campo richiesto',
            'email.unique'  => 'Email già registrata',
          ]);

          DB::beginTransaction();
          try {

          $data = new azn_puntivendita;
          $data->id_azn_anagrafica  =      $request->azienda;
          $data->nomepv             =      $request->nomepv;
          $data->indirizzopv        =      $request->indirizzopv;
          $data->comune             =      $request->comune;
          $data->cap                =      $request->cap;
          $data->pivapv             =      $request->pivapv;
          $data->email              =      $request->email;
          $data->ragsoc             =      $request->ragsoc;
          $data->telefono           =      $request->telefono;
          $data->referente          =      $request->referente;
          $data->id_userins         =      auth()->user()->id;
          $data->data_ins           =      Carbon::now();
          $data->save();

            /** Creo un utente con il ruolo di PUNTOV dell'azienda */
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
            $password = substr($random, 0, 10);

            $user = new User;
                $user->name = $request->referente;
                $user->email = $request->email;
                $user->password = Hash::make($password);
                $user->role = 'PUNTOV';
                $user->active = true;
                $user->id_azn_anagrafica = $request->azienda;
                $user->id_azn_puntovendita =    $data->id_azn_puntovendita;
                $user->save();

                      /** Se l'azienda viene inserita correttamente invio la mail con i parametri */


            $data = [
            'nome' => $request->ragsoc,
            'email' => $request->email,
            'subject' =>   "Abilitazione azienda $request->ragsoc accesso riservato sistema fidelity card come Punto Vendita",
            'msg' => 'Abbiamo abilitato la Vostra azienda per l\'accesso alla nostra area riservata. Di seguito i parametri per accedere',
            'password' => $password,
            'bcc'   =>  'info@linuxit.it'
        ];

                Mail::to($request->email)->send(new EmailForQueuing($data));

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
                  return response()->json($e, 404);
              }
    }

    /**
     * Modifica punto vendita relativo ad un'azienda. (aziende.azn_puntivendita)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function modpv(Request $request) {
        $request->validate([
            'azienda' => 'required',
            'nomepv' => 'required|max:50',
            'indirizzopv' => 'required|max:150',
            'comune' => 'required|max:50',
            'cap'   =>  'required|max:5',
            'pivapv'   =>  'required|max:11',
            'ragsoc'    =>  'max:100',
            'email' =>  'required|max:100|unique:pgsql.aziende.azn_puntivendita',
            'telefono'  =>  'max:12',
            'referente'  =>  'max:100',
        ], [
            'azienda'   =>      'Azienda è un campo richiesto',
            'nomepv'    =>  'Nome punto vendita è un campo richiesto',
            'indirizzopv.required' => 'Indirizzo è un campo richiesto',
            'pivapv.required'  => 'Partita IVA è un campo richiesto',
            'comune.required' => 'Comune è un campo richiesto',
            'cap.required' => 'CAP è un campo richiesto',
            'email.required'  => 'Email è un campo richiesto',
            'email.unique'  => 'Email già registrata',
          ]);

          $data = azn_puntivendita::where('id_azn_puntovendita', $request->id_azn_puntovendita)->first();
          $data->id_azn_anagrafica  =      $request->azienda;
          $data->nomepv             =      $request->nomepv;
          $data->indirizzopv        =      $request->indirizzopv;
          $data->comune             =      $request->comune;
          $data->cap                =      $request->cap;
          $data->pivapv             =      $request->pivapv;
          $data->email              =      $request->email;
          $data->ragsoc             =      $request->ragsoc;
          $data->telefono           =      $request->telefono;
          $data->referente          =      $request->referente;
          $data->id_userins         =      auth()->user()->id;
          $data->data_ins           =      Carbon::now();
          if($data->save()) {
              return response()->json(true, 200);
          }
    }

    /**
     * Lista di tutti i punti vendita. (aziende.azn_puntivendita)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return array
     */

    public function listpv() {
        $data = azn_puntivendita::All();
            return response()->json($data, 200);
    }

    /**
     * Dettaglio di un punto vendita relativo ad un'azienda. (aziende.azn_puntivendita)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return object
     */

    public function getpv($id) {
        $data = azn_puntivendita::where('id_azn_puntovendita', $id)->first();
            return response()->json($data, 200);
    }


    /**
     * Attiva o disattiva  punto vendita. (aziende.azn_puntivendita)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function actPuntiv(Request $request) {
        // return response()->json($request, 200);
            $request->validate([
                'id_azn_puntovendita' => 'required|numeric',
                'attiva' => 'required|string',
            ]);

            if ($request->attiva === 'false') {
                $status = false;
            } else {
                $status = true;
            }

            $pv = azn_puntivendita::where('id_azn_puntovendita', $request->id_azn_puntovendita)->first();
            $pv->attiva = $status;
            $pv->id_usermodify = auth()->user()->id;
            if ($pv->update()) {
                return response()->json(true, 200);
            } else {
                return response()->json(false, 200);
            }

    }

    /**
     * Restituisce i dati per lo store del punto vendita dell'utente
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

     public function storePv() {
        $data = azn_puntivendita::where('id_azn_puntovendita', auth()->user()->id_azn_puntovendita)->first();
            return response()->json($data, 200);
     }
}

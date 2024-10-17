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

class AdmPuntiVenditaController extends Controller
{

    /**
     * Lista di tutti i punti vendita di un'azienda. (aziende.azn_puntivendita)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return array
     */

    public function puntivendita() {
        $data = azn_puntivendita::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->get();
            return response()->json($data, 200);
    }

    /**
     * Lista di tutti i punti vendita di un'azienda per inserimento nuovo user. (aziende.azn_puntivendita)
     * method. POST
     * url api/auth/puntivenditagod
     * @param mixed $request
     * @return array
     */

    public function puntivenditagod($id) {
        $data = azn_puntivendita::where('id_azn_anagrafica', $id)->get();
            return response()->json($data, 200);
    }


    /**
     * Inserimento nuovo punto vendita relativo ad un'azienda. (aziende.azn_puntivendita)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function addpuntovendita(Request $request) {
        $request->validate([
            'nomepv' => 'required|max:50',
            'indirizzopv' => 'required|max:150',
            'comune' => 'required|max:50',
            'cap'   =>  'required|max:5',
            'ragsoc'    =>  'max:100',
            'email' =>  'required|max:100|unique:pgsql.aziende.azn_puntivendita',
            'telefono'  =>  'max:12',
            'referente'  =>  'max:100',
        ], [
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
          $data->id_azn_anagrafica  =      auth()->user()->id_azn_anagrafica;
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

                    /** Creo un utente con il ruolo di ADMIN dell'azienda */
                    $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
                    $password = substr($random, 0, 10);

                    $user = new User;
                      $user->name = $request->referente;
                      $user->email = $request->email;
                      $user->password = Hash::make($password);
                      $user->role = 'PUNTOV';
                      $user->active = true;
                      $user->id_azn_anagrafica = auth()->user()->id_azn_anagrafica;
                      $user->id_azn_puntovendita = $data->id_azn_puntovendita;
                      $user->save();

                      /** Se l'azienda viene inserita correttamente invio la mail con i parametri */


                      $data = [
                          'nome' => $request->referente,
                          'email' => $request->email,
                          'subject' =>   "Abilitazione $request->referente accesso riservato sistema fidelity card come Punto Vendita",
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

    public function modpuntovendita(Request $request) {
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

}

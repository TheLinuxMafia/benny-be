<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\aziende\azn_anagrafiche;
use App\aziende\azn_puntivendita;
use App\User;
use Carbon\Carbon;
use App\Mail\EmailForQueuing;
use App\Mail\EmailForPrivacy;
use App\Mail\EmailForAccount;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class AziendeController extends Controller
{

    public function testmail() {

        $token = Str::random(32);

        $data = [
            'nome' => 'Giacomo',
            'email' => 'info@linuxit.it',
            'subject' =>   "Benny Card - Comunicazione regolamento privacy e trattamento dati",
            'msg' => '',
            'password' => '123456',
            'bcc'   =>  'info@linuxit.it',
            'token' => $token,
        ];

    Mail::to('info@linuxit.it')->send(new EmailForQueuing($data));
    }

    /**
     * Inserimento nuova azienda nelle Anagrafiche. (aziende.azn_anagrafiche)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function addAzienda(Request $request) {
        $request->validate([
            'indirizzo' => 'required|max:150',
            'piva' => 'required|max:11',
            'comune' => 'required|max:50',
            'cap'   =>  'required|max:5',
            'email' =>  'required|max:100|unique:pgsql.aziende.azn_anagrafiche',
            'telefono'  =>  'max:15',
            'nome'  =>  'required|max:50',
            'cognome'   =>  'required|max:50',
            'ragsoc'    =>  'required|max:100'
        ], [
            'indirizzo.required' => 'Indirizzo è un campo richiesto',
            'piva.required'  => 'Partita IVA è un campo richiesto',
            'comune.required' => 'Comune è un campo richiesto',
            'cap.required' => 'CAP è un campo richiesto',
            'email.required'  => 'Email è un campo richiesto',
            'email.unique'  => 'Email già registrata',
            'nome.required' => 'Nome è un campo richiesto',
            'cognome.required' => 'Cognome è un campo richiesto',
            'ragsoc.required' => 'Ragione Sociale è un campo richiesto',
          ]);

          DB::beginTransaction();
          try {

          $data = new azn_anagrafiche;
          $data->azn_indirizzo      =      $request->indirizzo;
          $data->azn_piva           =      $request->piva;
          $data->sdi                =      $request->sdi;
          $data->comune             =      $request->comune;
          $data->cap                =      $request->cap;
          $data->telefono           =      $request->telefono;
          $data->email              =      $request->email;
          $data->azn_nome           =      $request->nome;
          $data->azn_cognome        =      $request->cognome;
          $data->azn_ragsoc         =      $request->ragsoc;
          $data->id_userins         =      auth()->user()->id;
          $data->data_ins           =      Carbon::now();
          $data->logo               =      $request->logo;
          $data->save();


          /** Creo un utente con il ruolo di ADMIN dell'azienda */
          $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
          $password = substr($random, 0, 10);

          $user = new User;
            $user->name = $request->nome . ' ' .  $request->cognome;
            $user->email = $request->email;
            $user->password = Hash::make($password);
            $user->role = 'ADMIN';
            $user->active = true;
            $user->id_azn_anagrafica = $data->id_azn_anagrafica;
            $user->save();

            /** Se l'azienda viene inserita correttamente invio la mail con i parametri */


            $data = [
                'nome' => $request->ragsoc,
                'email' => $request->email,
                'subject' =>   "Abilitazione azienda $request->ragsoc accesso riservato sistema fidelity card come Amministratore",
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
     * Modifica azienda già inserita in anagrafica. (aziende.azn_anagrafiche)
     * method. PUT
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function modAzienda(Request $request) {
        $request->validate([
            'indirizzo' => 'required|max:150',
            'comune' => 'required|max:50',
            'cap'   =>  'required|max:5',
            'telefono'  =>  'max:15',
            'nome'  =>  'required|max:50',
            'cognome'   =>  'required|max:50',
            'ragsoc'    =>  'required|max:100'
        ], [
            'indirizzo.required' => 'Indirizzo è un campo richiesto',
            'comune.required' => 'Comune è un campo richiesto',
            'cap.required' => 'CAP è un campo richiesto',
            'nome.required' => 'Nome è un campo richiesto',
            'cognome.required' => 'Cognome è un campo richiesto',
            'ragsoc.required' => 'Ragione Sociale è un campo richiesto',
          ]);

          $data = azn_anagrafiche::where('id_azn_anagrafica', $request->id_azn_anagrafica)->first();
          $data->azn_indirizzo      =      $request->indirizzo;
          $data->sdi                =      $request->sdi;
          $data->comune             =      $request->comune;
          $data->cap                =      $request->cap;
          $data->telefono           =      $request->telefono;
          $data->azn_nome           =      $request->nome;
          $data->azn_cognome        =      $request->cognome;
          $data->azn_ragsoc         =      $request->ragsoc;
          $data->id_modify          =      auth()->user()->id;
          $data->data_ins           =      Carbon::now();
          $data->logo               =      $request->logo;
          if($data->save()) {
              return response()->json(true, 200);
          }
    }

    /**
     * Restituisce la lista di tutte le aziende. (aziende.azn_anagrafiche)
     * method. GET
     * url api/auth/azienda
     * @return array
     */

    public function getAziende() {
        $data = azn_anagrafiche::with('pv')->get();
            return response()->json($data, 200);
    }


     /**
     * Restituisce una sola azienda. (aziende.azn_anagrafiche)
     * method. GET
     * url api/auth/azienda
     * @param mixed $id
     * @return array
     */

    public function getAzienda($id) {
        $data = azn_anagrafiche::where('id_azn_anagrafica', $id)->with('pv')->first();
            return response()->json($data, 200);
    }



    /**
     * Controlla se un indirizzo email è già stato registrato come azienda. (aziende.azn_anagrafiche)
     * method. GET
     * url api/auth/azienda
     * @param mixed $mail
     * @return bool
     */

    public function mailAzienda($mail) {
        $data = azn_anagrafiche::where('email', $mail)->first();
        if ($data) {
            return response()->json(['result' => true], 200);
        } else {
            return response()->json(['result' => false], 200);
        }
    }


    /**
     * Controlla se una partita iva è già registrata come azienda. (aziende.azn_anagrafiche)
     * method. GET
     * url api/auth/azienda
     * @param mixed $piva
     * @return bool
     */

    public function pivaAzienda($piva) {
        $data = azn_anagrafiche::where('azn_piva', $piva)->first();
        if ($data) {
            return response()->json(['result' => true], 200);
        } else {
            return response()->json(['result' => false], 200);
        }
    }

    /**
     * Attiva o disattiva un'azienda e tutti i suoi punti vendita. (aziende.azn_anagrafiche | aziende.azn_puntivendita)
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

    public function actAzienda(Request $request) {
    // return response()->json($request, 200);
        $request->validate([
            'id_azn_anagrafica' => 'required|numeric',
            'attiva' => 'required|string',
        ]);

        if ($request->attiva === 'false') {
            $status = false;
        } else {
            $status = true;
        }


         $data = azn_anagrafiche::where('id_azn_anagrafica', $request->id_azn_anagrafica)->first();
         DB::beginTransaction();
         try {

                $data->attiva = $status;
                $data->id_modify = auth()->user()->id;
                $data->update();

        $pv = azn_puntivendita::where('id_azn_anagrafica', $request->id_azn_anagrafica)->get();
            foreach ($pv as $punti) {

                    $punti->attiva = $status;
                    $punti->id_usermodify = auth()->user()->id;
                    $punti->update();

            }

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
     * Restituisce i dati per lo store dell'azienda dell'utente
     * method. POST
     * url api/auth/azienda
     * @param mixed $request
     * @return bool
     */

     public function aznStore() {
        $data = azn_anagrafiche::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->first();
            return response()->json($data, 200);
     }


    /**
     * Registrazione di un utente
     * method. POST
     * url api/auth/reguser
     * @param mixed $request
     * @return bool
     */

     public function reguser(Request $request) {
         $data = new User;
         $data->name = $request->nome . ' ' . $request->cognome;
         $data->email = $request->email;
         $data->password = Hash::make($request->password);
         $data->id_azn_anagrafica = $request->azienda;
         $data->id_azn_puntovendita = $request->puntov;
         $data->role = $request->ruolo;
         $data->active           =      true;
         if($data->save()) {


            /** Se l'azienda viene inserita correttamente invio la mail con i parametri */

            $data = [
                'nome' => $request->ragsoc,
                'email' => $request->email,
                'subject' =>   "Abilitazione azienda per l'accesso riservato sistema fidelity card",
                'msg' => 'Abbiamo abilitato la Vostra azienda per l\'accesso alla nostra area riservata. Di seguito i parametri per accedere',
                'password' => $request->password,
                'bcc'   =>  'info@linuxit.it'
            ];

        Mail::to($request->email)->send(new EmailForQueuing($data));

            return response()->json($data, 200);
         }
         return response()->json($false, 500);
     }

}


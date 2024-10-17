<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\card\carte;
use App\card\cardutenti;
use App\card\movimenti;
use App\card\lotticampagna;
use App\card\cambio;
use Carbon\Carbon;
use App\gift\gift;
use App\gift\gift_cardstat;
use App\gift\lottigift;
use App\gift\giftutenti;
use App\gift\movgift;
use App\gift\giftlog;
use Illuminate\Support\Str;
use App\User;
use DB;

class GodController extends Controller
{
    /**
     * conta le carte totali disponibili nel sistema
     * method. GET
     * url api/god/godtotalecarte
     * @param mixed $request
     * @return number
     */

    public function godtotalecarte() {
        $data = carte::count();
           return response()->json($data, 200);
    }

    /**
     * conta le carte già associate ad un utente
     * method. GET
     * url api/god/godcarteutilizzate
     * @param mixed $request
     * @return number
     */

    public function godcarteutilizzate() {
        $data = cardutenti::count();
        return response()->json($data, 200);
     }

    /**
     * conta le carte sospese
     * method. GET
     * url api/god/godcartesospese
     * @param mixed $request
     * @return number
     */

    public function godcartesospese() {
        $data = cardutenti::where('attiva', false)->count();
        return response()->json($data, 200);
     }

    /**
     * conta le gift totali disponibili
     * method. GET
     * url api/god/godtotalegift
     * @param mixed $request
     * @return number
     */

    public function godtotalegift() {
        $data = gift::count();
           return response()->json($data, 200);
    }


    /**
     * conta le gift già associate per un'azienda
     * method. GET
     * url api/god/godgiftutilizzate
     * @param mixed $request
     * @return number
     */

    public function godgiftutilizzate() {
        $data = giftutenti::count();
        return response()->json($data, 200);
     }

    /**
     * conta le gift sospese
     * method. GET
     * url api/god/godgiftsospese
     * @param mixed $request
     * @return number
     */

    public function godgiftsospese() {
        $data = giftutenti::where('attiva', false)->count();
        return response()->json($data, 200);
     }

    /**
     * Restituisce la lista degli utenti che hanno accesso al sistema
     * method. GET
     * url api/god/godlistusers
     * @param mixed $request
     * @return array
     */

     public function godlistusers() {
         $user = User::with('azienda', 'puntov')->where('show', true)->orderBy('id')->get();
            return response()->json($user, 200);
     }

    /**
     * Nasconde un utente dal sistema
     * method. GET
     * url api/god/goddeleteuser
     * @param number $id
     * @return boolen
     */

    public function goddeleteuser($id) {
        if($id != 1) {
            $user = User::where('id', $id)->first();
            $user->show = false;
            if($user->save()) {
                return response()->json(true, 200);
            }
               return response()->json(false, 500);
            }
        }

    /**
     * Blocca l'accsso al sistema ad un utente
     * method. GET
     * url api/god/godbanuser
     * @param number $id
     * @return boolen
     */

    public function godbanuser($id) {
        if($id != 1) {
            $user = User::where('id', $id)->first();
            if ($user->active == true) {
                $user->active = false;
                if($user->save()) {
                    return response()->json(true, 200);
                }
            } else {
                $user->active = true;
                if($user->save()) {
                    return response()->json(true, 200);
                }
            }
        }
    }

    /**
     * Generazione di una API Key
     * method. GET
     * url api/god/genapi
     * @param number $id
     * @return string
     */

     public function genapi($id) {
         $token = Str::random(60);
         $api_token = hash('sha256', $token);
         $user = User::where('id', $id)->first();
         $user->api_token = $token;
         if($user->save()) {
            return response()->json($api_token, 200);
         }
     }
}

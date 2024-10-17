<?php
/** In questo controller vengono inserite tutte le funzioni che
 * gestiscono la parte pubblica dell'applicazione e la parte degli user
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\card\cardutenti;
use App\card\movimenti;
use App\gift\giftutenti;
use App\User;
use Carbon\Carbon;
use App\Mail\EmailForQueuing;
use App\Mail\EmailForPrivacy;
use App\Mail\EmailForAccount;
use App\Mail\EmailForSignupUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicController extends Controller
{

    /**
     * Restituisce il numero dei punti di una card (utilizzato dal centralino)
     * method. GET
     * url api/auth/saldopunti/{numerocard}
     * @param string $card
     * @return array
     */

    public function saldopunti($card) {
        $data = movimenti::select('stato')->where('card', $card)->latest('stato')->first();
        if($data) {
            return $data['stato'];
        } else {
            return 0;
        }
    }

    /**
     * Restituisce il numero della card dal numero di cellulare (utilizzato dal centralino)
     * method. GET
     * url api/auth/phonetocard/{phone}
     * @param string $phone
     * @return array
     */

    public function phonetocard($phone) {

        /** Rimuovo i primi 3 caratteri dalla string di input (Es. +39) */
        $mobile = substr($phone, 3);

        /** Controllo nella tabella card.cardutenti se trovo corrispondenza */
        $card = cardutenti::select('card')->where('cellulare', $mobile)->first();
        return response()->json($card['card'], 200);
    }



    public function checkuserpublic(Request $request) {

        $checktokencard = cardutenti::where('token', $request->token)->first();
            if($checktokencard) {
                return response()->json($checktokencard, 200);
            }
            $checktokengift = giftutenti::where('token', $request->token)->first();
            if($checktokengift) {
                return response()->json($checktokengift, 200);
            }
            $checkusercard = cardutenti::where('card', $request->card)->where('email', $request->email)->first();
            if($checkusercard) {
                return response()->json($checkusercard, 200);
            }
            $checkusergift = giftutenti::where('gift', $request->card)->where('email', $request->email)->first();
            if($checkusergift) {
                return response()->json($checkusergift, 200);
            }

            return response()->json(['status' => false], 500);
    }

    public function signupuserpublic(Request $request) {

        /** Controllo se i dati sono già stati registrati */
        /** Questo controllo è comunque supeerfuluo perchè una volta registrato l'utente  */
        /** Scompare il token e quindi non può effettuare la registrazione */

        $data = new User;
        $data->name = $request->nome . ' ' . $request->cognome;
        $data->email = $request->email;
        $data->password = Hash::make($request->password);
        $data->role = 'PUBLIC';
        $data->show = true;
        $data->active           =      true;
        if($data->save()) {

            $updatecard = cardutenti::where('card', $request->card)->first();
            $updatecard->id_user = $data->id;
            $updatecard->datanascita = $request->datanascita;
            $updatecard->sesso = $request->sesso;
            $updatecard->comune = $request->comune;
            $updatecard->cap = $request->cap;
            $updatecard->indirizzo = $request->indirizzo;
            $updatecard->cellulare = $request->cellulare;
            $updatecard->update();

           /** Se l'azienda viene inserita correttamente invio la mail con i parametri */

           $data = [
               'nome' => $request->nome . ' ' . $request->cognome,
               'email' => $request->email,
               'subject' =>   "Abilitazione per l'accesso riservato sistema fidelity card",
               'msg' => 'Grazie per esserti registrato sul nostro portale.',
               'password' => $request->password,
               'bcc'   =>  'info@linuxit.it'
           ];

       Mail::to($request->email)->send(new EmailForSignupUser($data));

           return response()->json($data, 200);
        }
        return response()->json($false, 500);
    }
}

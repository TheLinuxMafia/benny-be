<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\gift\gift;
use App\gift\gift_cardstat;
use App\gift\lottigift;
Use DB;
use function Newride\EAN13\create;

class LottiGiftController extends Controller
{
    /**
     * Creazione nuovo lotto ed associazione gift card. (card.lottigift)
     * method. POST
     * url api/auth/addlottogift
     * @param mixed $request
     * @return array
     */


    public function addlottogift(Request $request) {

        DB::beginTransaction();
        try {

    /** Prima inserisco i dati nella tabella dei lotti */
            $lotto = new lottigift;
            $lotto->lottogift = time();
            $lotto->tot_gift = $request->qtacard;
            $lotto->id_azn_anagrafica = $request->id_azn_anagrafica;
            $lotto->utilizzato = false;
            $lotto->save();


    /** Controllo nella tabella azn_cardstat se esiste un movimento dell'azienda */
                $checknumber = gift_cardstat::where('id_azn_anagrafica', $request->id_azn_anagrafica)->first();

    /** Se esiste il movimento dell'azienda allora creo delle card sequenziali */
                if($checknumber) {
                    $bcode = (int)$checknumber->numero_gift;
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
                            $card = new gift;
                            $card->numero_gift = $carta;
                            $card->lotto_gift = $lotto->id_lottogift;
                            $card->usata = false;
                            $card->attiva = true;
                            $card->id_azn_anagrafica = $request->id_azn_anagrafica;
                            $card->valore = $request->valore;
                            $card->save();
                     }

    /** Aggiorno lo stato delle card dell'azienda nella tabella azn_cardstat */
                     $updatecheck = gift_cardstat::where('id_azn_anagrafica', $request->id_azn_anagrafica)->first();
                     $updatecheck->numero_gift = $statocard;
                     $updatecheck->data_ins = date("Y-m-d H:i:s");
                     $updatecheck->update();

    /** Se non esiste alcun movimento dell'azienda allora inizio creando le prime card */

                } else { ################ SE E' LA PRIMA CREAZIONE DELLE CARD PER L'AZIENDA ####################

                $id = $request->id_azn_anagrafica; //Le prime cifre del barcode

                $numerocarte = $request->qtacard; /** Numero delle carte da generare */

                 $rand =  (mt_rand(100000,999999)); /** Numero random di 4 cifre */

                 $acode = ($id . $rand); // Es. 145133 (string)

                 $bcode = (int)$acode; // Es. 145133 (number)

                 $cardfinale = ($bcode + $numerocarte) -1; /** Es. 146223 (number) */

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
                    $card = new gift;
                    $card->numero_gift = $carta;
                    $card->lotto_gift = $lotto->id_lottogift;
                    $card->usata = false;
                    $card->attiva = true;
                    $card->id_azn_anagrafica = $request->id_azn_anagrafica;
                    $card->valore = $request->valore;
                    $card->save();
                 }

    /** Aggiorno lo stato delle card dell'azienda nella tabella azn_cardstat */
                 $updatecheck = new gift_cardstat;
                 $updatecheck->id_azn_anagrafica = $request->id_azn_anagrafica;
                 $updatecheck->numero_gift = $statocard;
                 $updatecheck->data_ins = date("Y-m-d H:i:s");
                 $updatecheck->save();
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


    }


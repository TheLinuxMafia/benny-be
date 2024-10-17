<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\carburanti\carb_trans;
use App\carburanti\carb_prodotti;
use App\carburanti\carb_aziende;
use App\Jobs\EmailTransazioneJob;
use Carbon\Carbon;
use App\gift\movgift;
use Log;

class TransazioniController extends Controller
{
    public function store(Request $request) {
        /** Ottengo il prezzo dei prodotti  */
        $pr_litri = null;
        $pr1_litri = null;
        $pr2_litri = null;
        $adblue_litri = null;

        $prezzi = carb_prodotti::all();


        /** Questa funzione cerca il prodotto nell'array dei prodotti
         * Ottiene l'index dell'array che corrisponde al prodotto
         * Divide il costo del prodotto della transazione per il prezzo del prodotto
         * ed ottiene i litri.
         * Il calcolo viene effettuato solo per i prodotti presenti nella transazione
         */

        if($request->prodotto) {
            $index = array_search($request->prodotto, array_column($prezzi->toarray(), 'prodotto'));
            $pr_litri = round(($request->pr_importo / $prezzi[$index]['prezzo']), 2 ,PHP_ROUND_HALF_ODD);
        };

        if($request->prodotto1) {
            $index = array_search($request->prodotto1, array_column($prezzi->toarray(), 'prodotto'));
            $pr1_litri = round(($request->pr1_importo / $prezzi[$index]['prezzo']), 2 ,PHP_ROUND_HALF_ODD);
        };

        if($request->prodotto2) {
            $index = array_search($request->prodotto2, array_column($prezzi->toarray(), 'prodotto'));
            $pr2_litri = round(($request->pr2_importo / $prezzi[$index]['prezzo']), 2 ,PHP_ROUND_HALF_ODD);
        };

        if($request->adblue > 0) {
            $index = array_search('AD-BLUE', array_column($prezzi->toarray(), 'prodotto'));
            $adblue_litri = round(($request->adblue / $prezzi[$index]['prezzo']), 2 ,PHP_ROUND_HALF_ODD);
        }


        $totale = $request->pr_importo + $request->pr1_importo + $request->pr2_importo + $request->adblue + $request->olio + $request->accessori ;

        $scode = time();

        $data = new carb_trans();
        $data->scode = $scode;
        $data->prodotto = $request->prodotto;
        $data->prodotto1 = $request->prodotto1;
        $data->prodotto2 = $request->prodotto2;
        $data->pr_importo = $request->pr_importo;
        $data->pr1_importo = $request->pr1_importo;
        $data->pr2_importo = $request->pr2_importo;
        $data->adblue = $request->adblue;
        $data->olio = $request->olio;
        $data->accessori = $request->accessori;
        $data->id_azienda = $request->id_azienda;
        $data->targa = $request->targa;
        $data->ragsoc = $request->ragsoc;
        $data->piva = $request->piva;
        $data->km = $request->kmnew;
        $data->tipo = $request->tipo;
        $data->marca = $request->marca;
        $data->modello = $request->modello;
        $data->totale = $totale;
        $data->centro = $request->centro;
        $data->pr_litri = $pr_litri;
        $data->pr1_litri = $pr1_litri;
        $data->pr2_litri = $pr2_litri;
        $data->adblue_litri = $adblue_litri;
        $data->id_puntov = auth()->user()->id_azn_puntovendita;
        $data->userins = auth()->user()->name;

        Log::info($data);
        if($data->save()) {


            /** Se la transazione viene salvata allora preparo i dati per la mail */
            $azienda = carb_aziende::where('id', $request->id_azienda)->first();
            Log::info('cerco azienda');
            if($azienda->email != null && $azienda->send_email == true) {
                Log::info('Azienda Trovata');

                $datitrans = [
                    'targa' => $request->targa,
                    'totale' => $totale,
                    'codice' => $scode,
                    'km' => $request->kmnew,
                    'azienda' => $azienda->ragsoc,
                    'email' => $azienda->email,
                    'subject' => 'Nuova transazione targa '. $request->targa,
                    'data'  =>  date_format($data->created_at, 'd/m/Y'),
                    'ora'   =>  date_format($data->created_at, 'H:i:s'),
                ];

/*             $job = (new EmailTransazioneJob($datitrans))
                    ->delay(Carbon::now()->addSeconds(3)); */
                    EmailTransazioneJob::dispatch($datitrans);
            }

            return response()->json($data, 200);
        } else {
            return response()->json(false, 500);
        }
    }

/**
 * Ultime dieci transazioni
 */
public function last() {
    return carb_trans::latest()->where('id_puntov', auth()->user()->id_azn_puntovendita)->take(10)->get();
}


/**
 * Tutte le transazioni di un anno di un'azienda
 * @param: id_azienda - number
 * @typ: GET
 * @return: array
 */
public function transazioni_anno($id_azienda) {
    $year = date('Y');
    return(carb_trans::where('id_azienda', $id_azienda)->whereYear('created_at', $year)->where('eliminata', false)->get());
}

/**
 * La somma di tutte le transazioni del giorno
 * @type: GET
 * @return: array
 */
public function sum_day() {
    $data = date('Y-m-d');
    return(carb_trans::whereDate('created_at', $data)->where('eliminata', false)->get());
}

/**
 * Tutte le transazioni del mese e anno di un'azienda
 * @param: Request
 * @param: id_azienda - number
 * @param: anno
 * @param numero = numero del mese
 */
public function transazioni_mese(Request $request) {
    return(carb_trans::where('id_azienda', $request->id_azienda)->whereYear('created_at', $request->anno)->whereMonth('created_at', $request->numero)->where('eliminata', false)->get());
}

/**
 * Restituisce le ultime 200 transazioni
 */
public function ultime200() {
    return carb_trans::latest()->take(200)->get();
}

/**
 * Ricerca in tutte le transazioni dell'anno in corso
 */
public function cerca_transazioni($string) {
    $anno = date('Y');
    return(carb_trans::where('targa', 'ILIKE','%'.$string.'%'))
        ->orWhere('ragsoc', 'ILIKE','%'.$string.'%')
        ->whereYear('created_at', $anno)->orderBy('created_at', 'DESC')
        ->get();
}

public function chartNumeroTransazioni() {
    $numero = [];
    $valore = [];
    $mese = date('m');
    $anno = date('Y');
    $giorni = cal_days_in_month(CAL_GREGORIAN,$mese,$anno);
    $oggi = date('d');
    $totale_mese = carb_trans::whereMonth('created_at', $mese)->whereYear('created_at', $anno)->where('eliminata', false)->where('id_puntov', auth()->user()->id_azn_puntovendita)->sum('totale');

    for($i = 1; $i <= $oggi; $i++) {
        $somma = carb_trans::whereDay('created_at', $i)->whereMonth('created_at', $mese)->whereYear('created_at', $anno)->where('eliminata', false)->where('id_puntov', auth()->user()->id_azn_puntovendita)->sum('totale');
        $count = carb_trans::whereDay('created_at', $i)->whereMonth('created_at', $mese)->whereYear('created_at', $anno)->where('eliminata', false)->where('id_puntov', auth()->user()->id_azn_puntovendita)->count();
        $gift = movgift::whereDay('data_movimento', $i)->whereMonth('data_movimento', $mese)->whereYear('data_movimento', $anno)->where('tipo_mov', 'scarico')->sum('valore');
        $totale = $somma + $gift;
        $dataRif = $i.'/'.$mese.'/'.$anno;

        array_push($numero, $count);
        array_push($valore, ['giorno' => $dataRif, 'valore' => $somma, 'numero' => $count, 'gift' => $gift, 'totale' => $totale]);
    }
    //return $numero;
    return ['totale_mese' => $totale_mese, 'numero' => $numero, 'valore' => $valore];
}

public function mod_transazione(Request $request) {
    $totale = $request->pr_importo + $request->pr1_importo + $request->pr2_importo + $request->adblue + $request->olio + $request->accessori ;
    return $data = carb_trans::updateOrCreate([
        'id'    =>  $request->id,
    ],
    [
        "scode" => time(),
        "prodotto"  =>  $request->prodotto,
        "pr_importo"    =>  $request->pr_importo,
        "pr1_importo"   =>  $request->pr1_importo,
        "pr2_importo"   =>  $request->pr2_importo,
        "adblue"    =>  $request->adblue,
        "olio"  =>  $request->olio,
        "accessori" =>  $request->accessori,
        "created_at"    =>  $request->created_at,
        "totale"    =>  $totale,
        "userins"   =>  auth()->user()->name,
        "prodotto1" =>  $request->prodotto1,
        "prodotto2" =>  $request->prodotto2,
        "centro"    =>  $request->centro,
    ]);
}

public function elimina_transazione($id) {
    return(carb_trans::where('id', $id)->update(['eliminata' => true]));
}

public function riattiva_transazione($id) {
    return(carb_trans::where('id', $id)->update(['eliminata' => false]));
}


/**
 * Funzione temporanea che assegna i litri a tutte le transazioni
 */

 public function litri_transazione() {
    $transazioni = carb_trans::all();
    $prezzi = carb_prodotti::all();

    foreach($transazioni as $transazione) {
        if($transazione['pr_importo'] > 0) {
            $index = array_search($transazione['prodotto'], array_column($prezzi->toarray(), 'prodotto'));
            $pr_litri = round(($transazione['pr_importo'] / $prezzi[$index]['prezzo']), 2 ,PHP_ROUND_HALF_ODD);

            carb_trans::where('id', $transazione['id'])->update(['pr_litri' => $pr_litri ]);
        }


        if($transazione['pr1_importo'] > 0) {
            $index = array_search($transazione['prodotto1'], array_column($prezzi->toarray(), 'prodotto'));
            $pr_litri = round(($transazione['pr1_importo'] / $prezzi[$index]['prezzo']), 2 ,PHP_ROUND_HALF_ODD);

            carb_trans::where('id', $transazione['id'])->update(['pr1_litri' => $pr_litri ]);
        }


        if($transazione['pr2_importo'] > 0) {
            $index = array_search($transazione['prodotto2'], array_column($prezzi->toarray(), 'prodotto'));
            $pr_litri = round(($transazione['pr2_importo'] / $prezzi[$index]['prezzo']), 2 ,PHP_ROUND_HALF_ODD);

            carb_trans::where('id', $transazione['id'])->update(['pr2_litri' => $pr_litri ]);
        }
    }

    return true;
 }

     /** Questa funzione va lanciata una sola volta per aggiungere la tabella dei litri di ad blue venduti */
     public function update_litri_adblue() {
        $prezzo = carb_prodotti::where('prodotto', 'AD-BLUE')->first();
        $transazioni = carb_trans::where('adblue', '>', 0)->get();

        foreach($transazioni as $transazione) {
            $litri = $transazione['adblue'] / $prezzo['prezzo'];
            carb_trans::where('id', $transazione['id'])->update(['adblue_litri' => number_format($litri, 2) ]);
        }

        return response()->json($transazioni, 200);
    }


    /** Questa funzione visualizza i totali per periodo */
    public function totali_periodo(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $response = new \stdClass();


        $transazioni = carb_trans::where('eliminata', false)->where('id_puntov', auth()->user()->id_azn_puntovendita)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->orderBy('created_at')->get();

        $totale = array_sum(array_column($transazioni->toarray(), 'totale'));

        $pr = array_sum(array_column($transazioni->toarray(), 'pr_importo'));
        $pr1 = array_sum(array_column($transazioni->toarray(), 'pr1_importo'));
        $pr2 = array_sum(array_column($transazioni->toarray(), 'pr2_importo'));

        $carburanti = $pr + $pr1 + $pr2;

        $olio = array_sum(array_column($transazioni->toarray(), 'olio'));
        $accessori = array_sum(array_column($transazioni->toarray(), 'accessori'));
        $adblue = array_sum(array_column($transazioni->toarray(), 'adblue'));

        $response->carburanti = $carburanti;
        $response->olio = $olio;
        $response->accessori = $accessori;
        $response->adblue = $adblue;
        $response->totale = $totale;

        return response()->json($response, 200);
    }

}

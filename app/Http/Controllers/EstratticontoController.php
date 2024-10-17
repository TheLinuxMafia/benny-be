<?php

namespace App\Http\Controllers;
use App\carburanti\carb_trans;
use App\carburanti\carb_prodotti;
use App\carburanti\carb_aziende;
use App\carburanti\carb_targhe;
use App\carburanti\ec;
use App\aziende\azn_puntivendita;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Log;
use Mail;
use Carbon\Carbon;
use App\Http\Helper\EstrattiContoHelper;

use Illuminate\Http\Request;
$localString = "it_IT";

class EstratticontoController extends Controller
{

    /**
     * Estratto conto mensile analitico per targhe di un'azienda
     * @param: data: data completa
     * @param: id id azienda
     * @param: data: data completa
     */
    public function azienda(Request $request) {
        $timestamp_iniziale = microtime(true);

        $anno = $this->setAnno($request);

      //  $anno = date('Y', strtotime($request->data)); //Anno
        $mese = date('m', strtotime($request->data)); //Mese
        $giorno = date('d', strtotime($request->data)); //Giorno attuale mi serve per capire se prendo dati nel futuro
        $giorni = cal_days_in_month(CAL_GREGORIAN,$mese,$anno); //Numero di giorni del mese attuale
        $data = new \stdClass;
        $azienda = carb_aziende::where('id', $request->id)->first(); // Dati azienda

        $filename = date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.$azienda['ragsoc'].'-'.time();

        /** Test con somma lato server e non lato sql */
        $transazioni = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->get();

        $pr = array_sum(array_column($transazioni->toarray(), 'pr_importo'));
        $pr1 = array_sum(array_column($transazioni->toarray(), 'pr1_importo'));
        $pr2 = array_sum(array_column($transazioni->toarray(), 'pr2_importo'));

        $totaleMeseAzienda = array_sum(array_column($transazioni->toarray(), 'totale'));
        $totaleOlioMeseAzienda = array_sum(array_column($transazioni->toarray(), 'olio'));
        $totaleAccessoriMeseAzienda = array_sum(array_column($transazioni->toarray(), 'accessori'));
        $totaleAdBlueMesAziendae = array_sum(array_column($transazioni->toarray(), 'adblue'));

        $totaleCarburantiMeseAzienda = $pr + $pr1 + $pr2;

        $data->azienda = $azienda;
        $data->azienda->mese = date("F", mktime(0, 0, 0, $mese, 10));
        $data->azienda->anno = $anno;
        $data->azienda->totale = $totaleMeseAzienda;
        $data->azienda->olio = $totaleOlioMeseAzienda;
        $data->azienda->accessori = $totaleAccessoriMeseAzienda;
        $data->azienda->adblue = $totaleAdBlueMesAziendae;
        $data->azienda->carburanti = $totaleCarburantiMeseAzienda;

        $targhe = carb_targhe::where('id_azienda', $request->id)->get();
        $datiTarga = [];

        foreach($targhe as $targa) {

            $transazioni = carb_trans::where('targa', $targa->targa)->where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->get();
            $totaleMese = array_sum(array_column($transazioni->toarray(), 'totale'));
            $totaleOlioMese = array_sum(array_column($transazioni->toarray(), 'olio'));
            $totaleAccessoriMese = array_sum(array_column($transazioni->toarray(), 'accessori'));
            $totaleAdBlueMese = array_sum(array_column($transazioni->toarray(), 'adblue'));

            $pr = array_sum(array_column($transazioni->toarray(), 'pr_importo'));
            $pr1 = array_sum(array_column($transazioni->toarray(), 'pr1_importo'));
            $pr2 = array_sum(array_column($transazioni->toarray(), 'pr2_importo'));

            $totaleCarburantiMese = $pr + $pr1 + $pr2;

            array_push($datiTarga, ['targa' => $targa->targa, 'totale_mese' => $totaleMese, 'olio_mese' => $totaleOlioMese, 'accessori_mese' => $totaleAccessoriMese, 'adblue' => $totaleAdBlueMese, 'totale_carburanti' => $totaleCarburantiMese, 'transazioni' => $transazioni]);
        }

        $data->targhe = $datiTarga;

        $pdf = PDF::loadView('estrattoazienda', ['estratto' => $data])->setPaper('a4', 'landscape');
        \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
        $obj = new \stdClass;
        $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
        $timestamp_finale = microtime(true);
        $secondi_totali = $timestamp_finale - $timestamp_iniziale;
        $obj->time = $secondi_totali;
        return response()->json($obj, 200);
        return $data;
    }




    /**
     * Estratto conto analitico per targa per mese di una targa
     * restituisce i totali del mese e il dettaglio dei giorni
     * @param: targa: targa veicolo
     * @param: id: id azienda
     * @param: data: data completa
     */
    public function targa_mese(Request $request) {
        $timestamp_iniziale = microtime(true);

        $anno = date('Y', strtotime($request->data)); //Anno
        $mese = date('m', strtotime($request->data)); //Mese
        $giorno = date('d', strtotime($request->data)); //Giorno attuale mi serve per capire se prendo dati nel futuro
        $giorni = cal_days_in_month(CAL_GREGORIAN,$mese,$anno); //Numero di giorni del mese attuale
        $data = new \stdClass;
        $azienda = carb_aziende::where('id', $request->id)->first(); // Dati azienda
        $data->azienda = $azienda;

        $totaleMese = carb_trans::where('targa', $request->targa)->where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('totale');
        $totaleOlioMese = carb_trans::where('targa', $request->targa)->where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('olio');
        $totaleAccessoriMese = carb_trans::where('targa', $request->targa)->where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('accessori');
        $totaleAdBlueMese = carb_trans::where('targa', $request->targa)->where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('adblue');
        $transazioni = carb_trans::select('prodotto', 'pr_importo', 'prodotto1', 'pr1_importo', 'prodotto2', 'pr2_importo', 'olio', 'adblue', 'accessori', 'totale', 'created_at')->where('targa', $request->targa)->where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->get();

        $data->totaleMese = $totaleMese;
        $data->totaleOlioMese = $totaleOlioMese;
        $data->totaleAccessoriMese = $totaleAccessoriMese;
        $data->totaleAdBlueMese = $totaleAdBlueMese;
        $data->transazioni = $transazioni;

        return $data;
    }

    /**
     * Estratto conto mensile di tutte le aziende
     * Visualizza solo i totali senza i dettagli giornalieri
     * @param: data: data completa
     */
    public function aziende(Request $request) {
        $timestamp_iniziale = microtime(true);

        $anno = date('Y', strtotime($request->data)); //Anno
        $mese = date('m', strtotime($request->data)); //Mese
        $aziende = carb_aziende::all();
        $prodotti = carb_prodotti::all();
        $array = [];
        foreach($aziende as $azienda) {

            /** Ottengo le transazioni effettuate da un'azienda in un mese */
            $transazioni = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->get();

            /** Sommo il totale delle transazioni fatte */
            $totaleAziendaMese = array_sum(array_column($transazioni->toarray(), 'totale'));

            /** Sommo gli importi relativi ai soli carburanti */
            $pr = array_sum(array_column($transazioni->toarray(), 'pr_importo'));
            $pr1 = array_sum(array_column($transazioni->toarray(), 'pr1_importo'));
            $pr2 = array_sum(array_column($transazioni->toarray(), 'pr2_importo'));
            $totaleAziendaCarburantiMese = $pr + $pr1 + $pr2;

            $pr_litri = array_sum(array_column($transazioni->toarray(), 'pr_litri'));
            $pr1_litri = array_sum(array_column($transazioni->toarray(), 'pr1_litri'));
            $pr2_litri = array_sum(array_column($transazioni->toarray(), 'pr2_litri'));

            $totaleAziendaLitriMese = $pr_litri + $pr1_litri + $pr2_litri;

            /** Creazione di una classe dove inserire i dati aziendali */
            $azn = new \stdClass;
            $azn->anno = $anno;
            $azn->mese = date("F", mktime(0, 0, 0, $mese, 10));
            $azn->totalemese = $totaleAziendaMese;
            $azn->totale_carburanti_mese = $totaleAziendaCarburantiMese;
            $azn->totale_litri_mese = $totaleAziendaLitriMese;

            /** Prendo dalle transazioni tutte le targhe che hanno effettuato movimenti per una determinata azienda */
            $targhe = carb_trans::where('id_azienda', $azienda['id'])->whereYear('created_at',$anno)->whereMonth('created_at', $mese)->where('eliminata', false)->distinct()->get('targa');
                $arrayTarghe = [];
                foreach($targhe as $targa) {

                    /** Ottengo tutte le transazioni fatte da una targa in un mese */
                    $transazioni = carb_trans::where('targa', $targa->targa)->where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->get();

                    /** Somma mensile totale delle transazioni */
                    $totaleMese = array_sum(array_column($transazioni->toarray(), 'totale'));

                    /** Somma mensile delle transazioni di olio */
                    $totaleOlioMese = array_sum(array_column($transazioni->toarray(), 'olio'));

                     /** Somma mensile delle transazioni di accessori */
                    $totaleAccessoriMese = array_sum(array_column($transazioni->toarray(), 'accessori'));

                     /** Somma mensile delle transazioni di adblue */
                    $totaleAdBlueMese = array_sum(array_column($transazioni->toarray(), 'adblue'));

                    /** Sommo solo gli importi relativi ai carburanti */
                    $pr = array_sum(array_column($transazioni->toarray(), 'pr_importo'));
                    $pr1 = array_sum(array_column($transazioni->toarray(), 'pr1_importo'));
                    $pr2 = array_sum(array_column($transazioni->toarray(), 'pr2_importo'));
                    $totaleCarburantiMese = $pr + $pr1 + $pr2;

                    /** Creo un array vuoro che conterrà il dettaglio dei carburanti utilizzati da una targa */
                    $dett_carburanti = [];

                    /** Ora devo fare la somma dei carburanti suddivisa per prodotti. Devo cercare nelle transazioni i carburanti acquistati */
                    $tmp = [];
                    foreach($prodotti as $prodotto) {

                    }

                    return $tmp;


                    /** Dettaglio carburanti */
                    $tot_pr = carb_trans::where('targa', $targa->targa)->where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $targa->prodotto)->sum('pr_importo');
                    $prezzo = carb_prodotti::select('prezzo', 'prodotto')->where('prodotto', $targa->prodotto)->first();
                    array_push($dett_carburanti, ['prodotto' => $targa->prodotto, 'valore' => $tot_pr]);

                    if($targa->prodotto1 != null) {
                    $tot_pr1 = carb_trans::where('targa', $targa->targa)
                        ->where('id_azienda', $azienda->id)
                        ->whereYear('created_at', $anno)
                        ->whereMonth('created_at', $mese)
                        ->where('prodotto1', $targa->prodotto1)->sum('pr1_importo');
                        $prezzo = carb_prodotti::select('prezzo', 'prodotto')->where('prodotto', $targa->prodotto1)->first();
                    array_push($dett_carburanti, ['prodotto' => $targa->prodotto1, 'valore' => $tot_pr1]);
                    }

                    if($targa->prodotto2 != null) {
                    $tot_pr2 = carb_trans::where('targa', $targa->targa)
                        ->where('id_azienda', $azienda->id)
                        ->whereYear('created_at', $anno)
                        ->whereMonth('created_at', $mese)
                        ->where('prodotto2', $targa->prodotto2)->sum('pr2_importo');
                        $prezzo = carb_prodotti::select('prezzo', 'prodotto')->where('prodotto', $targa->prodotto2)->first();
                    array_push($dett_carburanti, ['prodotto' => $targa->prodotto2, 'valore' => $tot_pr2]);
                    }

                    $tar = new \stdClass;
                    $tar->targa = $targa->targa;
                    $tar->totale =  $totaleMese;
                    $tar->olio = $totaleOlioMese;
                    $tar->adblue = $totaleAdBlueMese;
                    $tar->accessori = $totaleAccessoriMese;
                    $tar->carburanti = $totaleCarburantiMese;
                    $tar->dettaglio = $dett_carburanti;

                    array_push($arrayTarghe, $tar);
                }
            $azn->azienda = $azienda['ragsoc'];
            $azn->targhe = $arrayTarghe;
            array_push($array, $azn);
        }

      $filename = 'mensile-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
      $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('mesetutteaziende', ['array' => $array])->setPaper('a4', 'landscape');
      \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
      $obj = new \stdClass;
      $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
      $timestamp_finale = microtime(true);
      $secondi_totali = $timestamp_finale - $timestamp_iniziale;
      $obj->time = $secondi_totali;
   //   return response()->json($obj, 200);

        return $array;
    }


    /**
     * Estratto conto mensile di tutte le aziende
     * Visualizza solo i totali senza i dettagli delle targhe
     * @param: data: data completa
     */
    public function aziende_mese(Request $request) {
        $anno = date('Y', strtotime($request->data)); //Anno
        $mese = date('m', strtotime($request->data)); //Mese
        $giorno = date('d', strtotime($request->data)); //Giorno attuale mi serve per capire se prendo dati nel futuro
        $totale_giorno = carb_trans::whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('totale');
        $totale_olio = carb_trans::whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('olio');
        $totale_accessori = carb_trans::whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('accessori');
        $totale_adblue = carb_trans::whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('adblue');
        $carburanti = $totale_giorno - $totale_olio - $totale_accessori - $totale_adblue;

        $info = new \stdClass;
        $info->data = $mese.'/'.$anno;
        $info->totale = $totale_giorno;
        $info->olio = $totale_olio;
        $info->accessori = $totale_accessori;
        $info->adblue = $totale_adblue;
        $info->carburanti = $carburanti;

        $aziende = carb_aziende::all();

        $array = [];
        foreach($aziende as $azienda) {
            $totale = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('totale');
            if($totale > 0) {
            $pr = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('pr_importo');
            $pr1 = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('pr1_importo');
            $pr2 = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('pr2_importo');
            $pr_litri = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('pr_litri');
            $pr1_litri  = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('pr1_litri');
            $pr2_litri  = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('pr2_litri');
            $olio = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('olio');
            $accessori = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('accessori');
            $adblue = carb_trans::where('id_azienda', $azienda->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('eliminata', false)->sum('adblue');

            $carburanti = $pr + $pr1 + $pr2;
            $litri = $pr_litri + $pr1_litri + $pr2_litri;

            $azn = new \stdClass;
            $azn->nome = $azienda['ragsoc'];
            $azn->totale = $totale;
            $azn->olio = $olio;
            $azn->accessori = $accessori;
            $azn->adblue = $adblue;
            $azn->carburanti = $carburanti;
            $azn->litri = $litri;

            array_push($array, $azn);
        }
    }

        $riepilogo = new \stdClass;
        $riepilogo->info = $info;
        $riepilogo->aziende = $array;

        $filename = 'aziende-'.$giorno.'-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('estrattoaziendemese', ['array' => $riepilogo])->setPaper('a4', 'landscape');
        \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
        $obj = new \stdClass;
        $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
        return response()->json($obj, 200);
        //return $riepilogo;
    }

    /**
     * Estratto conto completo mensile di tutte le aziende
     * con il dettaglio dei carburanti e dei litri.
     */

     public function mensile_analitico_aziende(Request $request) {
        $anno = date('Y', strtotime($request->data)); //Anno
        $mese = date('m', strtotime($request->data)); //Mese
        $result = [];

        /** Tutti i prodotti */
        $prodotti = carb_prodotti::All();

        /** Tutte le aziende */
        $aziende = carb_aziende::All();

        /** Ora per ogni azienda ciclo il prodotto per le transazioni nel mese */
        foreach($aziende as $azienda) {
            $olio = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->sum('olio');
            $adblue = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->sum('adblue');
            $accessori = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->sum('accessori');

            $azn = new \stdClass();
            $azn->azienda = $azienda->ragsoc;
            $azn->olio = $olio;
            $azn->adblue = $adblue;
            $azn->accessori = $accessori;

            $prodazienda = [];
            /** Per ogni prodotto sommo l'importo ed i litri */
            foreach ($prodotti as $prodotto) {
                $pr_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $prodotto->prodotto)->where('id_azienda', $azienda->id)->sum('pr_importo');
                $pr1_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto1', $prodotto->prodotto)->where('id_azienda', $azienda->id)->sum('pr1_importo');
                $pr2_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto2', $prodotto->prodotto)->where('id_azienda', $azienda->id)->sum('pr2_importo');

                $pr_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $prodotto->prodotto)->where('id_azienda', $azienda->id)->sum('pr_litri');
                $pr1_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto1', $prodotto->prodotto)->where('id_azienda', $azienda->id)->sum('pr1_litri');
                $pr2_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto2', $prodotto->prodotto)->where('id_azienda', $azienda->id)->sum('pr2_litri');


           //     $olio = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->sum('adblue');
           //     $olio = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->sum('accessori');

                $importo = $pr_importo + $pr1_importo + $pr2_importo;
                $litri = $pr_litri + $pr1_litri + $pr2_litri;

                if($importo > 0) {
                    $res = new \stdClass();
                    $res->prodotto = $prodotto->prodotto;
                    $res->litri = $litri;
                    $res->importo = $importo;

                    array_push($prodazienda, $res);
                }
            }
            $azn->prodotti = $prodazienda;
            array_push($result, $azn);
        }

        return response()->json($result, 200);

     }


     /**
      * Estratto conto mensile con il dettaglio delle targhe e tutti i prodotti
      * per tutte le aziende
      */

      public function mensile_analitico_targhe(Request $request) {
        $anno = date('Y', strtotime($request->data)); //Anno
        $mese = date('m', strtotime($request->data)); //Mese
        $response = [];
        $aziende = carb_aziende::orderBy('ragsoc', 'ASC')->get();

        $prodotti_all = carb_prodotti::All();

        foreach($aziende as $azienda) {

            $veicoli = [];
            $azn_olio = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda['id'])->sum('olio');
            $azn_adblue = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda['id'])->sum('adblue');
            $azn_accessori = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda['id'])->sum('accessori');

            $tot_generale = [];

            foreach($prodotti_all as $prodotto_all) {
                Log::info('Sommo le transazioni per ' . $azienda['ragsoc'] . ' per il prodotto ' . $prodotto_all['prodotto']);
                $tot_azienda_pr = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda['id'])
                    ->where('prodotto', $prodotto_all['prodotto'])->sum('pr_importo');

                $tot_azienda_pr1 = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda['id'])
                ->where('prodotto1', $prodotto_all['prodotto'])->sum('pr1_importo');

                $tot_azienda_pr2 = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda['id'])
                ->where('prodotto2', $prodotto_all['prodotto'])->sum('pr2_importo');

                if($tot_azienda_pr > 0 || $tot_azienda_pr1 > 0 || $tot_azienda_pr2 > 0) {
                    array_push($tot_generale, [$prodotto_all['prodotto'] => ($tot_azienda_pr + $tot_azienda_pr1 + $tot_azienda_pr2)]);
                }
            }


            $azn = new \stdClass();
            $azn->azienda = $azienda->ragsoc;
            $azn->olio = $azn_olio;
            $azn->adblue = $azn_adblue;
            $azn->accessori = $azn_accessori;
            $azn->tot_generate = $tot_generale;


           // $targhe = carb_targhe::where('id_azienda', $azienda->id)->get();

            $targhe = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->distinct('targa')->get();

                foreach($targhe as $targa) {

                    $res_targa = new \stdClass();
                    $res_targa->targa = $targa->targa;


                    $olio = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('olio');
                    $adblue = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('adblue');
                    $accessori = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('accessori');

                    $res_targa->olio = $olio;
                    $res_targa->adblue = $adblue;
                    $res_targa->accessori = $accessori;

                    $prodotti = [];

                    if(isset($targa->prodotto)) {
                        $pr_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr_importo');
                        $pr_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr_litri');
                        if($pr_importo > 0) {
                            array_push($prodotti, ['prodotto' => $targa->prodotto, 'importo' => $pr_importo, 'litri' => $pr_litri ]);
                        }
                    }

                    if(isset($targa->prodotto1)) {
                        $pr1_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr1_importo');
                        $pr1_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr1_litri');
                        if($pr1_importo > 0) {
                            array_push($prodotti, ['prodotto' => $targa->prodotto1, 'importo' => $pr1_importo, 'litri' => $pr1_litri ]);
                        }
                    }

                    if(isset($targa->prodotto2)) {
                        $pr2_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr2_importo');
                        $pr2_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr2_litri');
                        if($pr2_importo > 0) {
                            array_push($prodotti, ['prodotto' => $targa->prodotto2, 'importo' => $pr2_importo, 'litri' => $pr2_litri ]);
                        }
                    }

                    if(count($prodotti) > 0) {
                        $res_targa->prodotti = $prodotti;
                    }

                    if(count($prodotti) > 0 || $olio > 0 || $adblue > 0 || $accessori > 0) {
                        array_push($veicoli, $res_targa);
                    }
                }

                if(count($veicoli) > 0) {
                    $azn->veicoli = $veicoli;
                    array_push($response, $azn);
                }
        }
        $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendemese', ['array' => $response])->setPaper('a4', 'landscape');
        \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
        $obj = new \stdClass;
        $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
        $obj->response = $response;
        return response()->json($obj, 200);
      }




    /**
     * Estratto conto giornaliero di tutte le aziende
     * Visualizza i totali e i dettagli delle targhe
     * @param: data: data completa
     */

      public function aziende_giorno(Request $request) {
        $anno = date('Y', strtotime($request->data)); //Anno
        $mese = date('m', strtotime($request->data)); //Mese
        $giorno = date('d', strtotime($request->data)); //Giorno

        $response = [];
        $aziende = carb_aziende::orderBy('ragsoc', 'ASC')->get();

        $prodotti_all = carb_prodotti::All();

        foreach($aziende as $azienda) {

            $veicoli = [];
            $azn_olio = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda['id'])->sum('olio');
            $azn_adblue = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda['id'])->sum('adblue');
            $azn_accessori = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda['id'])->sum('accessori');

            $tot_generale = [];

            foreach($prodotti_all as $prodotto_all) {
                Log::info('Sommo le transazioni per ' . $azienda['ragsoc'] . ' per il prodotto ' . $prodotto_all['prodotto']);
                $tot_azienda_pr = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda['id'])
                    ->where('prodotto', $prodotto_all['prodotto'])->sum('pr_importo');

                $tot_azienda_pr1 = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda['id'])
                ->where('prodotto1', $prodotto_all['prodotto'])->sum('pr1_importo');

                $tot_azienda_pr2 = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda['id'])
                ->where('prodotto2', $prodotto_all['prodotto'])->sum('pr2_importo');

                if($tot_azienda_pr > 0 || $tot_azienda_pr1 > 0 || $tot_azienda_pr2 > 0) {
                    array_push($tot_generale, [$prodotto_all['prodotto'] => ($tot_azienda_pr + $tot_azienda_pr1 + $tot_azienda_pr2)]);
                }
            }


            $azn = new \stdClass();
            $azn->azienda = $azienda->ragsoc;
            $azn->olio = $azn_olio;
            $azn->adblue = $azn_adblue;
            $azn->accessori = $azn_accessori;
            $azn->tot_generate = $tot_generale;


           // $targhe = carb_targhe::where('id_azienda', $azienda->id)->get();

            $targhe = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda->id)->distinct('targa')->get();

                foreach($targhe as $targa) {

                    $res_targa = new \stdClass();
                    $res_targa->targa = $targa->targa;


                    $olio = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('olio');
                    $adblue = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('adblue');
                    $accessori = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('accessori');

                    $res_targa->olio = $olio;
                    $res_targa->adblue = $adblue;
                    $res_targa->accessori = $accessori;

                    $prodotti = [];

                    if(isset($targa->prodotto)) {
                        $pr_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr_importo');
                        $pr_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr_litri');
                        if($pr_importo > 0) {
                            array_push($prodotti, ['prodotto' => $targa->prodotto, 'importo' => $pr_importo, 'litri' => $pr_litri ]);
                        }
                    }

                    if(isset($targa->prodotto1)) {
                        $pr1_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr1_importo');
                        $pr1_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr1_litri');
                        if($pr1_importo > 0) {
                            array_push($prodotti, ['prodotto' => $targa->prodotto1, 'importo' => $pr1_importo, 'litri' => $pr1_litri ]);
                        }
                    }

                    if(isset($targa->prodotto2)) {
                        $pr2_importo = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr2_importo');
                        $pr2_litri = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->where('prodotto', $targa->prodotto)->where('id_azienda', $azienda->id)->where('targa', $targa->targa)->sum('pr2_litri');
                        if($pr2_importo > 0) {
                            array_push($prodotti, ['prodotto' => $targa->prodotto2, 'importo' => $pr2_importo, 'litri' => $pr2_litri ]);
                        }
                    }

                    if(count($prodotti) > 0) {
                        $res_targa->prodotti = $prodotti;
                    }

                    if(count($prodotti) > 0 || $olio > 0 || $adblue > 0 || $accessori > 0) {
                        array_push($veicoli, $res_targa);
                    }
                }

                if(count($veicoli) > 0) {
                    $azn->veicoli = $veicoli;
                    array_push($response, $azn);
                }
        }
        $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendemese', ['array' => $response])->setPaper('a4', 'landscape');
        \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
        $obj = new \stdClass;
        $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
        $obj->response = $response;
        return response()->json($obj, 200);
      }

      /**
       * Estratto conto mensile di un'azienda con i dettagli delle targhe e di tutte transazioni
       */

       public function estratto_analitico_azienda_mese(Request $request) {

        $anno = date('Y', strtotime($request->data)); //Anno
        $mese = date('m', strtotime($request->data)); //Mese

        setlocale(LC_TIME, 'it_IT');
        $month = Carbon::parse(strtotime($request->data))->formatLocalized('%B');

        $azienda = carb_aziende::where('id', $request->id)->first();
        $prodotti = carb_prodotti::All();

        $azn = new \stdClass();
        $azn->azienda = $azienda->ragsoc;
        $azn->piva = $azienda->piva;
        $azn->mese = $month;
        $azn->anno = $anno;

        $targhe = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->distinct('targa')->get();

        $riep_targa = [];

        foreach($targhe as $targa) {

            $transazioni = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->orderBy('created_at')->get();

            $olio = array_sum(array_column($transazioni->toarray(), 'olio'));
            $adblue = array_sum(array_column($transazioni->toarray(), 'adblue'));
            $accessori = array_sum(array_column($transazioni->toarray(), 'accessori'));
            $prod = [];
            foreach($prodotti as $prodotto) {

                $pr_trans = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto', $prodotto['prodotto'])->orderBy('created_at')->get();
                $pr1_trans = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto1', $prodotto['prodotto'])->orderBy('created_at')->get();
                $pr2_trans = carb_trans::where('eliminata', false)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto2', $prodotto['prodotto'])->orderBy('created_at')->get();

                $pr_sum = array_sum(array_column($pr_trans->toarray(), 'pr_importo'));
                $pr_litri = array_sum(array_column($pr_trans->toarray(), 'pr_litri'));

                $pr1_sum = array_sum(array_column($pr_trans->toarray(), 'pr1_importo'));
                $pr1_litri = array_sum(array_column($pr_trans->toarray(), 'pr1_litri'));

                $pr2_sum = array_sum(array_column($pr_trans->toarray(), 'pr2_importo'));
                $pr2_litri = array_sum(array_column($pr_trans->toarray(), 'pr2_litri'));

                $totale = $pr_sum + $pr1_sum + $pr2_sum;
                $litri = $pr_litri + $pr1_litri + $pr2_litri;

                if($totale > 0) {
                    array_push($prod, ['prodotto' => $prodotto['prodotto'], 'totale' => $totale, 'litri' => $litri]);
                }
            }
            array_push($riep_targa, ['targa' => $targa['targa'], 'olio' => $olio, 'adblue' => $adblue, 'accessori' => $accessori, 'prodotti' => $prod, 'transazioni' => $transazioni]);
        }
        $azn->targhe = $riep_targa;

        if($request->print == 'true') {
            Log::info('Stampo estratto');
            Log::info($request->print);
            $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendamese', ['array' => $azn])->setPaper('a4', 'landscape');
            \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
            $obj = new \stdClass;
            $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
            $obj->response = $azn;
            return response()->json($obj, 200);
        } else if($request->email) {
            Log::info('infoo Email');
            $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendamese', ['array' => $azn])->setPaper('a4', 'landscape');
            \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
            $obj = new \stdClass;
            $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
            $obj->response = $azn;

            $data["email"] =  $request->email;
            $data["title"] = "Estratto conto " . $month .  " " . $anno . " - Benny srl";
            $data["body"] = "In allegato l'estratto conto richiesto.";

            $files = [
                public_path('storage/public/pdf/'.$filename.'.pdf'),
            ];

            Mail::send('mails.invioallegato', $data, function($message)use($data, $files) {
                $message->to($data["email"], $data["email"])
                        ->from('info@bennysrl.it', 'Benny srl - Estratti conto')
                        ->subject($data["title"]);

                foreach ($files as $file){
                    $message->attach($file);
                }
            });

            return response()->json($obj, 200);
        }
        return response()->json($azn, 200);
       }



       /**
        * Estratto conto parziale delle targhe selezionate
        */
        public function estratto_targhe_selezionate(Request $request) {

            $from = $request->from;
            $to = $request->to;
            $mese = date('m', strtotime($request->from));
            $anno = date('Y', strtotime($request->from));

            setlocale(LC_TIME, 'it_IT');

            $azienda = carb_aziende::where('id', $request->azn)->first();
            $prodotti = carb_prodotti::All();

            /** Calcolo dei totali azienda del periodo */
/*             $tutte_transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->orderBy('created_at')->get();
            if(!$tutte_transazioni || empty($tutte_transazioni) || count($tutte_transazioni) < 1) {
                return response()->json(['success' => 'Nessun movimento nel periodo indicato'], 200);
            } */


/*             $olio = array_sum(array_column($tutte_transazioni->toarray(), 'olio'));
            $adblue = array_sum(array_column($tutte_transazioni->toarray(), 'adblue'));
            $accessori = array_sum(array_column($tutte_transazioni->toarray(), 'accessori')); */

/*             $totale = array_sum(array_column($tutte_transazioni->toarray(), 'totale'));
            $totale_altro = $olio + $adblue + $accessori;
            $totale_carburanti = $totale - $totale_altro; */


            $totale_olio = 0;
            $totale_adblue = 0;
            $totale_accessori = 0;
            $totale_carburanti = 0;

            $targhe = $request['targhe'];

            $riep_targa = [];

            foreach($targhe as $targa) {

                if($targa['selected'] === true) {
                $transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->orderBy('created_at')->get();

                $olio = array_sum(array_column($transazioni->toarray(), 'olio'));
                $adblue = array_sum(array_column($transazioni->toarray(), 'adblue'));
                $accessori = array_sum(array_column($transazioni->toarray(), 'accessori'));

                $totale_olio = $olio + $totale_olio;
                $totale_adblue = $adblue + $totale_adblue;
                $totale_accessori = $accessori + $totale_accessori;

                $prod = [];
                foreach($prodotti as $prodotto) {

                    $pr_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto', $prodotto['prodotto'])->orderBy('created_at')->get();
                    $pr1_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto1', $prodotto['prodotto'])->orderBy('created_at')->get();
                    $pr2_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto2', $prodotto['prodotto'])->orderBy('created_at')->get();

                    $pr_sum = array_sum(array_column($pr_trans->toarray(), 'pr_importo'));
                    $pr_litri = array_sum(array_column($pr_trans->toarray(), 'pr_litri'));

                    $pr1_sum = array_sum(array_column($pr_trans->toarray(), 'pr1_importo'));
                    $pr1_litri = array_sum(array_column($pr_trans->toarray(), 'pr1_litri'));

                    $pr2_sum = array_sum(array_column($pr_trans->toarray(), 'pr2_importo'));
                    $pr2_litri = array_sum(array_column($pr_trans->toarray(), 'pr2_litri'));

                    $totale = $pr_sum + $pr1_sum + $pr2_sum;
                    $litri = $pr_litri + $pr1_litri + $pr2_litri;

                    $totale_carburanti = $totale + $totale_carburanti;

                    if($totale > 0) {
                        array_push($prod, ['prodotto' => $prodotto['prodotto'], 'totale' => $totale, 'litri' => $litri]);
                    }
                }
                array_push($riep_targa, ['targa' => $targa['targa'], 'olio' => $olio, 'adblue' => $adblue, 'accessori' => $accessori, 'prodotti' => $prod, 'transazioni' => $transazioni]);
            }
        }


            $azn = new \stdClass();
            $azn->targhe = $riep_targa;
            $azn->azienda = $azienda->ragsoc;
            $azn->piva = $azienda->piva;
            $azn->from = $from;
            $azn->to = $to;
            $azn->totale = $totale_olio + $totale_adblue + $totale_accessori + $totale_carburanti;
            $azn->totale_carburanti = $totale_carburanti;
            $azn->totale_altro = $totale_olio + $totale_adblue + $totale_accessori;
            $azn->totale_adblue = $totale_adblue;
            $azn->totale_olio = $totale_olio;
            $azn->totale_accessori = $totale_accessori;
            $azn->contabilizzato = '';

            if($request->print == 'true') {
                Log::info('Stampo estratto');
                Log::info($request->print);
                $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
                $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodo', ['array' => $azn])->setPaper('a4', 'landscape');
                \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
                $obj = new \stdClass;
                $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
                $obj->response = $azn;
                return response()->json($obj, 200);
            } else if($request->email) {
                Log::info('infoo Email');
                $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
                $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodo', ['array' => $azn])->setPaper('a4', 'landscape');
                \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
                $obj = new \stdClass;
                $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
                $obj->response = $azn;

                $data["email"] =  $request->email;
                $data["title"] = "Estratto conto " . $mese .  " " . $anno . " - Benny srl";
                $data["body"] = "In allegato l'estratto conto richiesto.";

                $files = [
                    public_path('storage/public/pdf/'.$filename.'.pdf'),
                ];

                Mail::send('mails.invioallegato', $data, function($message)use($data, $files) {
                    $message->to($data["email"], $data["email"])
                            ->from('info@bennysrl.it', 'Benny srl - Estratti conto')
                            ->subject($data["title"]);

                    foreach ($files as $file){
                        $message->attach($file);
                    }
                });

                return response()->json($obj, 200);
            }
            return response()->json($azn, 200);
    }


    public function estratto_analitico_azienda_periodo_pv(Request $request) {

        if($request->contabilizza) {
            return $this->estratto_analitico_azienda_periodo_contabilizzato_pv($request);
        } else {
        setlocale(LC_TIME, 'it_IT');


        $from = $request->from;
        $to = $request->to;

        $mese = EstrattiContoHelper::setMese($request);

        $anno = EstrattiContoHelper::setAnno($request);

        $puntov = EstrattiContoHelper::getPuntoVendita(id_azn_puntovendita: $request->id_azn_puntovendita);

        $azienda = EstrattiContoHelper::getAzienda(id: $request->azn);

        $prodotti = carb_prodotti::All();

        /** Calcolo dei totali azienda del periodo */
        //$tutte_transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->orderBy('created_at')->get();
        $tutte_transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('id_puntov', $request->id_azn_puntovendita)->orderBy('created_at')->get();
        if(!$tutte_transazioni || empty($tutte_transazioni) || count($tutte_transazioni) < 1) {
            return response()->json(['success' => 'Nessun movimento nel periodo indicato'], 200);
        }


        $olio = array_sum(array_column($tutte_transazioni->toarray(), 'olio'));
        $adblue = array_sum(array_column($tutte_transazioni->toarray(), 'adblue'));
        $accessori = array_sum(array_column($tutte_transazioni->toarray(), 'accessori'));

        $totale = array_sum(array_column($tutte_transazioni->toarray(), 'totale'));
        $totale_altro = $olio + $adblue + $accessori;
        $totale_carburanti = $totale - $totale_altro;

        $azn = new \stdClass();
        $azn->azienda = $azienda->ragsoc;
        $azn->puntovendita = $puntov->nomepv;
        $azn->piva = $azienda->piva;
        $azn->from = $from;
        $azn->to = $to;
        $azn->totale = $totale;
        $azn->totale_carburanti = $totale_carburanti;
        $azn->totale_altro = $totale_altro;
        $azn->totale_adblue = $adblue;
        $azn->totale_olio = $olio;
        $azn->totale_accessori = $accessori;
        $azn->contabilizzato = '';

        $targhe = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('id_puntov', $request->id_azn_puntovendita)->distinct('targa')->get();

        $riep_targa = [];

        foreach($targhe as $targa) {

            $transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('id_puntov', $request->id_azn_puntovendita)->orderBy('created_at')->get();

            $olio = array_sum(array_column($transazioni->toarray(), 'olio'));
            $adblue = array_sum(array_column($transazioni->toarray(), 'adblue'));
            $accessori = array_sum(array_column($transazioni->toarray(), 'accessori'));

            $prod = [];
            foreach($prodotti as $prodotto) {

                $pr_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto', $prodotto['prodotto'])->orderBy('created_at')->get();
                $pr1_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto1', $prodotto['prodotto'])->orderBy('created_at')->get();
                $pr2_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto2', $prodotto['prodotto'])->orderBy('created_at')->get();

                $pr_sum = array_sum(array_column($pr_trans->toarray(), 'pr_importo'));
                $pr_litri = array_sum(array_column($pr_trans->toarray(), 'pr_litri'));

                $pr1_sum = array_sum(array_column($pr_trans->toarray(), 'pr1_importo'));
                $pr1_litri = array_sum(array_column($pr_trans->toarray(), 'pr1_litri'));

                $pr2_sum = array_sum(array_column($pr_trans->toarray(), 'pr2_importo'));
                $pr2_litri = array_sum(array_column($pr_trans->toarray(), 'pr2_litri'));

                $totale = $pr_sum + $pr1_sum + $pr2_sum;
                $litri = $pr_litri + $pr1_litri + $pr2_litri;

                if($totale > 0) {
                    array_push($prod, ['prodotto' => $prodotto['prodotto'], 'totale' => $totale, 'litri' => $litri]);
                }
            }
            array_push($riep_targa, ['targa' => $targa['targa'], 'olio' => $olio, 'adblue' => $adblue, 'accessori' => $accessori, 'prodotti' => $prod, 'transazioni' => $transazioni]);
        }
        $azn->targhe = $riep_targa;

        if($request->print == 'true') {
            Log::info('Stampo estratto');
            Log::info($request->print);
            $filename = $azienda->ragsoc.'-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time().'-'.$puntov->nomepv;
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodoperpv', ['array' => $azn])->setPaper('a4', 'landscape');
            \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
            $obj = new \stdClass;
            $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
            $obj->response = $azn;
            return response()->json($obj, 200);
        } else if($request->email) {
            Log::info('infoo Email');
            $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodoperpv', ['array' => $azn])->setPaper('a4', 'landscape');
            \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
            $obj = new \stdClass;
            $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
            $obj->response = $azn;

            $data["email"] =  $request->email;
            $data["title"] = "Estratto conto " . $mese .  " " . $anno . " - Benny srl";
            $data["body"] = "In allegato l'estratto conto richiesto.";

            $files = [
                public_path('storage/public/pdf/'.$filename.'.pdf'),
            ];

            Mail::send('mails.invioallegato', $data, function($message)use($data, $files) {
                $message->to($data["email"], $data["email"])
                        ->from('info@bennysrl.it', 'Benny srl - Estratti conto')
                        ->subject($data["title"]);

                foreach ($files as $file){
                    $message->attach($file);
                }
            });

            return response()->json($obj, 200);
        }
        return response()->json($azn, 200);
    }
}


       /**
       * Estratto conto per periodo di un'azienda con i dettagli delle targhe e di tutte transazioni
       */

       public function estratto_analitico_azienda_periodo(Request $request) {

        if($request->contabilizza) {
            return $this->estratto_analitico_azienda_periodo_contabilizzato($request);
        } else {

        $from = $request->from;
        $to = $request->to;
        $mese = date('m', strtotime($request->from));
        $anno = date('Y', strtotime($request->from));

        setlocale(LC_TIME, 'it_IT');

        $azienda = carb_aziende::where('id', $request->azn)->first();
        $prodotti = carb_prodotti::All();

        /** Calcolo dei totali azienda del periodo */
        //$tutte_transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->orderBy('created_at')->get();
        $tutte_transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->orderBy('created_at')->get();
        if(!$tutte_transazioni || empty($tutte_transazioni) || count($tutte_transazioni) < 1) {
            return response()->json(['success' => 'Nessun movimento nel periodo indicato'], 200);
        }


        $olio = array_sum(array_column($tutte_transazioni->toarray(), 'olio'));
        $adblue = array_sum(array_column($tutte_transazioni->toarray(), 'adblue'));
        $accessori = array_sum(array_column($tutte_transazioni->toarray(), 'accessori'));

        $totale = array_sum(array_column($tutte_transazioni->toarray(), 'totale'));
        $totale_altro = $olio + $adblue + $accessori;
        $totale_carburanti = $totale - $totale_altro;

        $azn = new \stdClass();
        $azn->azienda = $azienda->ragsoc;
        $azn->piva = $azienda->piva;
        $azn->from = $from;
        $azn->to = $to;
        $azn->totale = $totale;
        $azn->totale_carburanti = $totale_carburanti;
        $azn->totale_altro = $totale_altro;
        $azn->totale_adblue = $adblue;
        $azn->totale_olio = $olio;
        $azn->totale_accessori = $accessori;
        $azn->contabilizzato = '';

        $targhe = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->distinct('targa')->get();

        $riep_targa = [];

        foreach($targhe as $targa) {

            $transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->orderBy('created_at')->get();

            $olio = array_sum(array_column($transazioni->toarray(), 'olio'));
            $adblue = array_sum(array_column($transazioni->toarray(), 'adblue'));
            $accessori = array_sum(array_column($transazioni->toarray(), 'accessori'));

            $prod = [];
            foreach($prodotti as $prodotto) {

                $pr_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto', $prodotto['prodotto'])->orderBy('created_at')->get();
                $pr1_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto1', $prodotto['prodotto'])->orderBy('created_at')->get();
                $pr2_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto2', $prodotto['prodotto'])->orderBy('created_at')->get();

                $pr_sum = array_sum(array_column($pr_trans->toarray(), 'pr_importo'));
                $pr_litri = array_sum(array_column($pr_trans->toarray(), 'pr_litri'));

                $pr1_sum = array_sum(array_column($pr_trans->toarray(), 'pr1_importo'));
                $pr1_litri = array_sum(array_column($pr_trans->toarray(), 'pr1_litri'));

                $pr2_sum = array_sum(array_column($pr_trans->toarray(), 'pr2_importo'));
                $pr2_litri = array_sum(array_column($pr_trans->toarray(), 'pr2_litri'));

                $totale = $pr_sum + $pr1_sum + $pr2_sum;
                $litri = $pr_litri + $pr1_litri + $pr2_litri;

                if($totale > 0) {
                    array_push($prod, ['prodotto' => $prodotto['prodotto'], 'totale' => $totale, 'litri' => $litri]);
                }
            }
            array_push($riep_targa, ['targa' => $targa['targa'], 'olio' => $olio, 'adblue' => $adblue, 'accessori' => $accessori, 'prodotti' => $prod, 'transazioni' => $transazioni]);
        }
        $azn->targhe = $riep_targa;

        if($request->print == 'true') {
            Log::info('Stampo estratto');
            Log::info($request->print);
            $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodo', ['array' => $azn])->setPaper('a4', 'landscape');
            \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
            $obj = new \stdClass;
            $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
            $obj->response = $azn;
            return response()->json($obj, 200);
        } else if($request->email) {
            Log::info('infoo Email');
            $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time();
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodo', ['array' => $azn])->setPaper('a4', 'landscape');
            \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
            $obj = new \stdClass;
            $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
            $obj->response = $azn;

            $data["email"] =  $request->email;
            $data["title"] = "Estratto conto " . $mese .  " " . $anno . " - Benny srl";
            $data["body"] = "In allegato l'estratto conto richiesto.";

            $files = [
                public_path('storage/public/pdf/'.$filename.'.pdf'),
            ];

            Mail::send('mails.invioallegato', $data, function($message)use($data, $files) {
                $message->to($data["email"], $data["email"])
                        ->from('info@bennysrl.it', 'Benny srl - Estratti conto')
                        ->subject($data["title"]);

                foreach ($files as $file){
                    $message->attach($file);
                }
            });

            return response()->json($obj, 200);
        }
        return response()->json($azn, 200);
    }
}

public function estratto_analitico_azienda_periodo_contabilizzato($request) {
    Log::info('estratto_analitico_azienda_periodo_contabilizzato');

    $numero_contabile = time();


    $from = $request->from;
    $to = $request->to;
    $mese = date('m', strtotime($request->from));
    $anno = date('Y', strtotime($request->from));

    setlocale(LC_TIME, 'it_IT');

    $azienda = carb_aziende::where('id', $request->azn)->first();
    $prodotti = carb_prodotti::All();

    /** Calcolo dei totali azienda del periodo non ancora contabilizzate*/
    $tutte_transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('contabilizzata', false)->orderBy('created_at')->get();
    if(!$tutte_transazioni || empty($tutte_transazioni) || count($tutte_transazioni) < 1) {
        return response()->json(['success' => 'Nessun movimento nel periodo indicato'], 200);
    }
    $olio = array_sum(array_column($tutte_transazioni->toarray(), 'olio'));
    $adblue = array_sum(array_column($tutte_transazioni->toarray(), 'adblue'));
    $accessori = array_sum(array_column($tutte_transazioni->toarray(), 'accessori'));

    $tot_gen = array_sum(array_column($tutte_transazioni->toarray(), 'totale'));
    $totale_altro = $olio + $adblue + $accessori;
    $totale_carburanti = $tot_gen - $totale_altro;

    $azn = new \stdClass();
    $azn->azienda = $azienda->ragsoc;
    $azn->piva = $azienda->piva;
    $azn->from = $from;
    $azn->to = $to;
    $azn->totale = $tot_gen;
    $azn->totale_carburanti = $totale_carburanti;
    $azn->totale_altro = $totale_altro;
    $azn->totale_adblue = $adblue;
    $azn->totale_olio = $olio;
    $azn->totale_accessori = $accessori;
    $azn->contabilizzato = '*** DOCUMENTO CONTABILIZZATO NUMERO '.$numero_contabile.' ***';


    /** Prendo tutte le targhe associate all'azienda che hanno fatto movimenti nel periodo non ancora contabilizzati */
    $targhe = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('contabilizzata', false)->distinct('targa')->get();

    $riep_targa = [];

    foreach($targhe as $targa) {

        $transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('contabilizzata', false)->orderBy('created_at')->get();

        $olio = array_sum(array_column($transazioni->toarray(), 'olio'));
        $adblue = array_sum(array_column($transazioni->toarray(), 'adblue'));
        $accessori = array_sum(array_column($transazioni->toarray(), 'accessori'));

        $prod = [];
        foreach($prodotti as $prodotto) {

            $pr_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto', $prodotto['prodotto'])->where('contabilizzata', false)->orderBy('created_at')->get();
            $pr1_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto1', $prodotto['prodotto'])->where('contabilizzata', false)->orderBy('created_at')->get();
            $pr2_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto2', $prodotto['prodotto'])->where('contabilizzata', false)->orderBy('created_at')->get();

            $pr_sum = array_sum(array_column($pr_trans->toarray(), 'pr_importo'));
            $pr_litri = array_sum(array_column($pr_trans->toarray(), 'pr_litri'));

            $pr1_sum = array_sum(array_column($pr_trans->toarray(), 'pr1_importo'));
            $pr1_litri = array_sum(array_column($pr_trans->toarray(), 'pr1_litri'));

            $pr2_sum = array_sum(array_column($pr_trans->toarray(), 'pr2_importo'));
            $pr2_litri = array_sum(array_column($pr_trans->toarray(), 'pr2_litri'));

            $totale = $pr_sum + $pr1_sum + $pr2_sum;
            $litri = $pr_litri + $pr1_litri + $pr2_litri;

            if($totale > 0) {
                array_push($prod, ['prodotto' => $prodotto['prodotto'], 'totale' => $totale, 'litri' => $litri]);
            }
        }

        array_push($riep_targa, ['targa' => $targa['targa'], 'olio' => $olio, 'adblue' => $adblue, 'accessori' => $accessori, 'prodotti' => $prod, 'transazioni' => $transazioni]);
   }

   $azn->targhe = $riep_targa;


   /** In questa fase devo dichiarare contabilizzate tutte le transazioni utilizzate per comporre questo estratto conto */
   /** Se la funzione va a buon fine devo inserire un record dell'estratto conto */
    $ec = new ec();
    $ec->numero = $numero_contabile;
    $ec->data = Date("Y-m-d");
    $ec->importo = $tot_gen;
    $ec->azienda = $azienda->ragsoc;
    $ec->id_azienda = $azienda->id;
    $ec->tipologia = 'Periodo';
    $ec->periodo = 'dal '. Date('d/m/Y', strtotime($from)) . ' al ' . Date('d/m/Y', strtotime($to));
    if($ec->save()) {
        carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('contabilizzata', false)->update(['contabilizzata' => true, 'ec' => $numero_contabile]);
    }

if($request->print == 'true') {
    Log::info('Stampo estratto');
    Log::info($request->print);
    $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.$numero_contabile;
    $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodo', ['array' => $azn])->setPaper('a4', 'landscape');
    \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
    $obj = new \stdClass;
    $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
    $obj->response = $azn;
    ec::where('numero', $numero_contabile)->update(['file' => env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf']);
    return response()->json($obj, 200);
} else if($request->email) {
    Log::info('infoo Email');
    $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.$numero_contabile;
    $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodo', ['array' => $azn])->setPaper('a4', 'landscape');
    \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
    $obj = new \stdClass;
    $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
    $obj->response = $azn;
    ec::where('numero', $numero_contabile)->update(['file' => env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf']);
    $data["email"] =  $request->email;
    $data["title"] = "Estratto conto " . $mese .  " " . $anno . " - Benny srl";
    $data["body"] = "In allegato l'estratto conto numero " . $numero_contabile;

    $files = [
        public_path('storage/public/pdf/'.$filename.'.pdf'),
    ];

    Mail::send('mails.invioallegato', $data, function($message)use($data, $files) {
        $message->to($data["email"], $data["email"])
                ->from('info@bennysrl.it', 'Benny srl - Estratti conto')
                ->subject($data["title"]);

        foreach ($files as $file){
            $message->attach($file);
        }
    });

    return response()->json($obj, 200);
}
return response()->json($azn, 200);
}






/** Lista di tutti gli estratti conto */

public function all() {
    $response = new \stdClass();
    $estratti = ec::orderBy('azienda', 'ASC')->where('pagato', false)->take(200)->get();
    $response->somma = array_sum(array_column($estratti->toarray(), 'importo'));
    $response->acconti = array_sum(array_column($estratti->toarray(), 'acconto'));
    $response->resto = $response->somma - $response->acconti;
    $response->estratti = $estratti;
    return response()->json($response, 200);
}

public function delete($id) {
    $ec = ec::where('id', $id)->first();
    if(ec::destroy($id)) {
        if(carb_trans::where('ec', $ec->numero)->update(['contabilizzata' => false, 'ec' => null])) {
            return response()->json(true, 200);
        }
    } else {
        return response()->json(false, 500);
    }
}

public function paga(Request $request) {
    $acconto = 0;
    $ec = ec::where('id', $request->id)->first();
    if($ec) {

        if($ec['acconto'] > 0) {
            $acconto = $ec['acconto'] + $request->importo;
        } else {
            $acconto = $request->importo;
        }


    if($request->tipo == 'acconto') {
        $ec->acconto = $acconto;
        $ec->stato = 'Acconto';
        $ec->data_pagamento = Date('Y-m-d', strtotime($request->data));
        $ec->mod_pagamento = $request->metodo;
        if($ec->update()) {
            return response()->json(true, 200);
        }
    } else {
        $ec->pagato = true;
        $ec->data_pagamento = Date('Y-m-d', strtotime($request->data));
        $ec->mod_pagamento = $request->metodo;
        $ec->stato = 'Saldato';
        $ec->acconto = 0;
        if($ec->update()) {
            return response()->json(true, 200);
        }
    }
    }
    return response()->json(false, 500);
    }


public function filtra(Request $request) {
    switch ($request->tipo) {
        case 'azienda':
            $response = new \stdClass();
            $response->estratti = ec::where('id_azienda', $request->id)->orderBy('data', 'DESC')->take(200)->get();
            $response->somma = array_sum(array_column($response->estratti->toarray(), 'importo'));
            $response->acconti = array_sum(array_column($response->estratti->toarray(), 'acconto'));
            $response->resto = $response->somma - $response->acconti;
            return response()->json($response, 200);
            break;
        case 'stato':
            return response()->json(ec::where('pagato', $request->stato)->orderBy('data', 'DESC')->take(200)->get(), 200);
            break;
        case 'periodo':
            return response()->json(ec::whereBetween('data', [$request->from, $request->to])->orderBy('data', 'DESC')->get(), 200);
            break;
    }
}

public function non_contabilizzate(Request $request) {
    $from = $request->from;
    $to = $request->to;
    $mese = date('m', strtotime($request->from));
    $anno = date('Y', strtotime($request->from));

    setlocale(LC_TIME, 'it_IT');

    $transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('contabilizzata', false)->orderBy('created_at', 'ASC')->get();

    return $transazioni;
}

public function estratto_analitico_azienda_periodo_contabilizzato_pv(Request $request) {
    Log::info('estratto_analitico_azienda_periodo_contabilizzato_pv');

    $numero_contabile = time();


    $from = $request->from;
    $to = $request->to;
    $mese = date('m', strtotime($request->from));
    $anno = date('Y', strtotime($request->from));
    $puntov = azn_puntivendita::where('id_azn_puntovendita', $request->id_azn_puntovendita)->first();

    setlocale(LC_TIME, 'it_IT');

    $azienda = carb_aziende::where('id', $request->azn)->first();

    $prodotti = carb_prodotti::All();

    /** Calcolo dei totali azienda del periodo non ancora contabilizzate*/
    $tutte_transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('contabilizzata', false)->where('id_puntov', $request->id_azn_puntovendita)->orderBy('created_at')->get();
    if(!$tutte_transazioni || empty($tutte_transazioni) || count($tutte_transazioni) < 1) {
        return response()->json(['success' => 'Nessun movimento nel periodo indicato'], 200);
    }
    $olio = array_sum(array_column($tutte_transazioni->toarray(), 'olio'));
    $adblue = array_sum(array_column($tutte_transazioni->toarray(), 'adblue'));
    $accessori = array_sum(array_column($tutte_transazioni->toarray(), 'accessori'));

    $tot_gen = array_sum(array_column($tutte_transazioni->toarray(), 'totale'));
    $totale_altro = $olio + $adblue + $accessori;
    $totale_carburanti = $tot_gen - $totale_altro;

    $azn = new \stdClass();
    $azn->azienda = $azienda->ragsoc;
    $azn->puntovendita = $puntov->nomepv;
    $azn->piva = $azienda->piva;
    $azn->from = $from;
    $azn->to = $to;
    $azn->totale = $tot_gen;
    $azn->totale_carburanti = $totale_carburanti;
    $azn->totale_altro = $totale_altro;
    $azn->totale_adblue = $adblue;
    $azn->totale_olio = $olio;
    $azn->totale_accessori = $accessori;
    $azn->contabilizzato = '*** DOCUMENTO CONTABILIZZATO NUMERO '.$numero_contabile.' ***';


    /** Prendo tutte le targhe associate all'azienda che hanno fatto movimenti nel periodo non ancora contabilizzati */
    $targhe = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('contabilizzata', false)->where('id_puntov', $request->id_azn_puntovendita)->distinct('targa')->get();

    $riep_targa = [];

    foreach($targhe as $targa) {

        $transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('contabilizzata', false)->where('id_puntov', $request->id_azn_puntovendita)->orderBy('created_at')->get();

        $olio = array_sum(array_column($transazioni->toarray(), 'olio'));
        $adblue = array_sum(array_column($transazioni->toarray(), 'adblue'));
        $accessori = array_sum(array_column($transazioni->toarray(), 'accessori'));

        $prod = [];
        foreach($prodotti as $prodotto) {

            $pr_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto', $prodotto['prodotto'])->where('contabilizzata', false)->where('id_puntov', $request->id_azn_puntovendita)->orderBy('created_at')->get();
            $pr1_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto1', $prodotto['prodotto'])->where('contabilizzata', false)->where('id_puntov', $request->id_azn_puntovendita)->orderBy('created_at')->get();
            $pr2_trans = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('targa', $targa['targa'])->where('prodotto2', $prodotto['prodotto'])->where('contabilizzata', false)->where('id_puntov', $request->id_azn_puntovendita)->orderBy('created_at')->get();

            $pr_sum = array_sum(array_column($pr_trans->toarray(), 'pr_importo'));
            $pr_litri = array_sum(array_column($pr_trans->toarray(), 'pr_litri'));

            $pr1_sum = array_sum(array_column($pr_trans->toarray(), 'pr1_importo'));
            $pr1_litri = array_sum(array_column($pr_trans->toarray(), 'pr1_litri'));

            $pr2_sum = array_sum(array_column($pr_trans->toarray(), 'pr2_importo'));
            $pr2_litri = array_sum(array_column($pr_trans->toarray(), 'pr2_litri'));

            $totale = $pr_sum + $pr1_sum + $pr2_sum;
            $litri = $pr_litri + $pr1_litri + $pr2_litri;

            if($totale > 0) {
                array_push($prod, ['prodotto' => $prodotto['prodotto'], 'totale' => $totale, 'litri' => $litri]);
            }
        }

        array_push($riep_targa, ['targa' => $targa['targa'], 'olio' => $olio, 'adblue' => $adblue, 'accessori' => $accessori, 'prodotti' => $prod, 'transazioni' => $transazioni]);
   }

   $azn->targhe = $riep_targa;


   /** In questa fase devo dichiarare contabilizzate tutte le transazioni utilizzate per comporre questo estratto conto */
   /** Se la funzione va a buon fine devo inserire un record dell'estratto conto */
    $ec = new ec();
    $ec->numero = $numero_contabile;
    $ec->data = Date("Y-m-d");
    $ec->importo = $tot_gen;
    $ec->azienda = $azienda->ragsoc;
    $ec->id_azienda = $azienda->id;
    $ec->tipologia = 'Periodo';
    $ec->periodo = 'dal '. Date('d/m/Y', strtotime($from)) . ' al ' . Date('d/m/Y', strtotime($to));
    if($ec->save()) {
        carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('id_azienda', $azienda->id)->where('contabilizzata', false)->update(['contabilizzata' => true, 'ec' => $numero_contabile]);
    }

if($request->print == 'true') {
    Log::info('Stampo estratto');
    Log::info($request->print);
    $filename = $azienda->ragsoc.'-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.time().'-'.$puntov->nomepv;
    $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodoperpv', ['array' => $azn])->setPaper('a4', 'landscape');
    \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
    $obj = new \stdClass;
    $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
    $obj->response = $azn;
    ec::where('numero', $numero_contabile)->update(['file' => env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf']);
    return response()->json($obj, 200);
} else if($request->email) {
    Log::info('infoo Email');
    $filename = 'analitico-aziende-'.date("F", mktime(0, 0, 0, $mese, 10)).'-'.$anno.'-'.$numero_contabile;
    $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('analiticoaziendaperiodoperpv', ['array' => $azn])->setPaper('a4', 'landscape');
    \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());
    $obj = new \stdClass;
    $obj->link = env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf';
    $obj->response = $azn;
    ec::where('numero', $numero_contabile)->update(['file' => env('APP_URL').'/storage/public/pdf/'.$filename.'.pdf']);
    $data["email"] =  $request->email;
    $data["title"] = "Estratto conto " . $mese .  " " . $anno . " - Benny srl";
    $data["body"] = "In allegato l'estratto conto numero " . $numero_contabile;

    $files = [
        public_path('storage/public/pdf/'.$filename.'.pdf'),
    ];

    Mail::send('mails.invioallegato', $data, function($message)use($data, $files) {
        $message->to($data["email"], $data["email"])
                ->from('info@bennysrl.it', 'Benny srl - Estratti conto')
                ->subject($data["title"]);

        foreach ($files as $file){
            $message->attach($file);
        }
    });

    return response()->json($obj, 200);
}
return response()->json($azn, 200);
}

}


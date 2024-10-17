<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\carburanti\carb_trans;
use App\carburanti\carb_prodotti;
use App\carburanti\carb_aziende;
use App\carburanti\carb_targhe;
use Log;

setlocale(LC_ALL, 'it_IT@euro', 'it_IT', 'ita_ita');
class StatisticheController extends Controller
{

    /** 
     * Restituisce le statistiche di un'azienda' per un anno, tutti i mesi e tutti i giorni
     * @param: data: data completa
     * @param: id: id azienda
     */
    public function storica(Request $request) {
        $mese = date('m'); //Mese attuale
        $anno = date('Y'); //Anno attuale
        $giorni = cal_days_in_month(CAL_GREGORIAN,$mese,$anno); //Numero di giorni del mese attuale
        $oggi = date('d'); //Giorno attuale
        $prodotti = carb_prodotti::All(); // Tutti i prodotti commercializzati

        $azienda = carb_aziende::where('id', $request->id)->first();

        $data = new \stdClass;
        $data->azienda = $azienda;

       // return ['mese' => $mese, 'anno' => $anno, 'giorno' => $oggi];

        /** Eseguo prima le quesry per i dati dell'anno */
        $totaleAnno = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->sum('totale');
        /** Totale per Olio, Accessori e AdBlue */
        $totaleAnnoAdBlue = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->sum('adblue');
        $totaleAnnoOlio = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->sum('olio');
        $totaleAnnoAccessori = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->sum('accessori');
        /** Totale prodotti  anno */
        $year = new \stdClass;
        $year->anno = $anno;
        $year->totale = $totaleAnno;
        $year->olio = $totaleAnnoOlio;
        $year->adblue = $totaleAnnoAdBlue;
        $year->accessori = $totaleAnnoAccessori;
        $riepilogoCarburantiAnno = [];
        foreach($prodotti as $prodotto) {
            $pr_anno = carb_trans::select('prodotto', 'pr_importo')->where('id_azienda', $request->id)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto', $prodotto['prodotto'])
                                    ->sum('pr_importo');


            $pr1_anno = carb_trans::select('prodotto1', 'pr1_importo')->where('id_azienda', $request->id)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto1', $prodotto['prodotto'])
                                    ->sum('pr1_importo');
            
                        
            $pr2_anno = carb_trans::select('prodotto2', 'pr2_importo')->where('id_azienda', $request->id)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto2', $prodotto['prodotto'])
                                    ->sum('pr2_importo');

            $sommaAnno = $pr_anno + $pr1_anno + $pr2_anno;

            array_push($riepilogoCarburantiAnno, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaAnno]);
        }

        $year->carburanti = $riepilogoCarburantiAnno;
        $data->anno = $year;

        /** Eseguo le operazioni per tutti i mesi */

        $datimese = [];
        for($i = 1; $i <= $mese; $i++) {
            Log::info($i);
            $totaleMese = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->sum('totale');

            /** Totale per Olio, Accessori e AdBlue */
            $totaleMeseAdBlue = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $i)->sum('adblue');
            $totaleMeseOlio = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $i)->sum('olio');
            $totaleMeseAccessori = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $i)->sum('accessori');

            $month = new \stdClass;
            $month->mese = $i;
            $month->anno = $anno;
            $month->name =  date("F", mktime(0, 0, 0, $i, 10));
            $month->olio = $totaleMeseOlio;
            $month->adblue = $totaleMeseAdBlue;
            $month->accessori = $totaleMeseAccessori;
            $month->totaleMese = $totaleMese;

            /** Totale per carburanti */
            $riepilogoCarburantiMese = [];
            foreach($prodotti as $prodotto) {
                $pr_mese = carb_trans::select('prodotto', 'pr_importo')->where('id_azienda', $request->id)
                                        ->whereMonth('created_at', $i)
                                        ->whereYear('created_at', $anno)
                                        ->where('prodotto', $prodotto['prodotto'])
                                        ->sum('pr_importo');
    
    
                $pr1_mese = carb_trans::select('prodotto1', 'pr1_importo')->where('id_azienda', $request->id)
                                        ->whereMonth('created_at', $i)
                                        ->whereYear('created_at', $anno)
                                        ->where('prodotto1', $prodotto['prodotto'])
                                        ->sum('pr1_importo');
                
                            
                $pr2_mese = carb_trans::select('prodotto2', 'pr2_importo')->where('id_azienda', $request->id)
                                        ->whereMonth('created_at', $i)
                                        ->whereYear('created_at', $anno)
                                        ->where('prodotto2', $prodotto['prodotto'])
                                        ->sum('pr2_importo');
    
                $sommaMese = $pr_mese + $pr1_mese + $pr2_mese;
    
                array_push($riepilogoCarburantiMese, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaMese]);
            }
            $month->carburanti = $riepilogoCarburantiMese;
            array_push($datimese, $month);
        }
        $data->mesi = $datimese;
        

        /** Provo a creare le condizioni per giorno  */
        $datiGiorni = [];
        for($m = 1; $m <= $mese; $m++) {
            $giorniMese = cal_days_in_month(CAL_GREGORIAN,$m,$anno); //Numero di giorni del mese del ciclo

            /** Ciclo i giorni del mese */
            for($g = 1; $g <= $giorniMese; $g++) {


                /** Controllo se il giorno non è nel futuro */
                if($g <= $oggi && $m <= $mese) {

                $totaleGiornoAdBlue = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $m)->whereDay('created_at', $g)->sum('adblue');
                $totaleGiornoOlio = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $m)->whereDay('created_at', $g)->sum('olio');
                $totaleGiornoAccessori = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $m)->whereDay('created_at', $g)->sum('accessori');

                $riepilogoCarburantiGiorno = [];
                foreach($prodotti as $prodotto) {
                    $pr_giorno = carb_trans::select('prodotto', 'pr_importo')->where('id_azienda', $request->id)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $m)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto', $prodotto['prodotto'])
                                            ->sum('pr_importo');
        
        
                    $pr1_giorno = carb_trans::select('prodotto1', 'pr1_importo')->where('id_azienda', $request->id)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $m)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto1', $prodotto['prodotto'])
                                            ->sum('pr1_importo');
                    
                                
                    $pr2_giorno = carb_trans::select('prodotto2', 'pr2_importo')->where('id_azienda', $request->id)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $m)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto2', $prodotto['prodotto'])
                                            ->sum('pr2_importo');
        
                    $sommaGiorno = $pr_giorno + $pr1_giorno + $pr2_giorno;
        
                    array_push($riepilogoCarburantiGiorno, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaGiorno]);
                }

                $day = new \stdClass;
                $day->giorno = $g.'/'.$m.'/'.$anno;
                $day->adblue = $totaleGiornoAdBlue;
                $day->olio = $totaleGiornoOlio;
                $day->accessori = $totaleGiornoAccessori;
                $day->carburanti = $riepilogoCarburantiGiorno;
    
                array_push($datiGiorni, $day);
            }
        }
    }

        $data->giorni = $datiGiorni;
        return $data;
    }



    /** 
     * Restituisce le statistiche di un'azienda' per un determinato mese e tutti i giorni
     * @param: data: data completa
     * @param: id: id azienda
     */
    public function mese(Request $request) {
        $mese = date('m', strtotime($request->data)); //Mese 
        $anno = date('Y', strtotime($request->data)); //Anno 
        $giorni = cal_days_in_month(CAL_GREGORIAN,$mese,$anno); //Numero di giorni del mese attuale
        $oggi = date('d', strtotime($request->data)); //Giorno attuale

       // return ['mese' => $mese, 'anno' => $anno, 'giorno' => $oggi];

        $prodotti = carb_prodotti::All(); // Tutti i prodotti commercializzati

        $azienda = carb_aziende::where('id', $request->id)->first();

        $data = new \stdClass;
        $data->azienda = $azienda;

        $totaleMese = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('totale');

        /** Totale per Olio, Accessori e AdBlue */
        $totaleMeseAdBlue = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('adblue');
        $totaleMeseOlio = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('olio');
        $totaleMeseAccessori = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('accessori');

        $month = new \stdClass;
        $riepilogoCarburantiMese = [];
        foreach($prodotti as $prodotto) {
            $pr_mese = carb_trans::select('prodotto', 'pr_importo')->where('id_azienda', $request->id)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto', $prodotto['prodotto'])
                                    ->sum('pr_importo');


            $pr1_mese = carb_trans::select('prodotto1', 'pr1_importo')->where('id_azienda', $request->id)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto1', $prodotto['prodotto'])
                                    ->sum('pr1_importo');
            
                        
            $pr2_mese = carb_trans::select('prodotto2', 'pr2_importo')->where('id_azienda', $request->id)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto2', $prodotto['prodotto'])
                                    ->sum('pr2_importo');

            $sommaMese = $pr_mese + $pr1_mese + $pr2_mese;

            array_push($riepilogoCarburantiMese, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaMese]);
        }
       
            $month->mese = $mese;
            $month->anno = $anno;
            $month->name =  date("F", mktime(0, 0, 0, $mese, 10));
            $month->olio = $totaleMeseOlio;
            $month->adblue = $totaleMeseAdBlue;
            $month->accessori = $totaleMeseAccessori;
            $month->totaleMese = $totaleMese;
            $month->carburanti = $riepilogoCarburantiMese;
            $data->mese = $month;


            $datiGiorni = [];
            for($g = 1; $g <= $giorni; $g++) {
                /** Controllo se il giorno non è nel futuro */
                if($g <= $oggi) {

                $totaleGiornoAdBlue = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $g)->sum('adblue');
                $totaleGiornoOlio = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $g)->sum('olio');
                $totaleGiornoAccessori = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $g)->sum('accessori');

                $riepilogoCarburantiGiorno = [];
                foreach($prodotti as $prodotto) {
                    $pr_giorno = carb_trans::select('prodotto', 'pr_importo')->where('id_azienda', $request->id)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $mese)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto', $prodotto['prodotto'])
                                            ->sum('pr_importo');
        
        
                    $pr1_giorno = carb_trans::select('prodotto1', 'pr1_importo')->where('id_azienda', $request->id)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $mese)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto1', $prodotto['prodotto'])
                                            ->sum('pr1_importo');
                    
                                
                    $pr2_giorno = carb_trans::select('prodotto2', 'pr2_importo')->where('id_azienda', $request->id)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $mese)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto2', $prodotto['prodotto'])
                                            ->sum('pr2_importo');
        
                    $sommaGiorno = $pr_giorno + $pr1_giorno + $pr2_giorno;


                    array_push($riepilogoCarburantiGiorno, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaGiorno]);

                }
                $day = new \stdClass;
                $day->giorno = $g.'/'.$mese.'/'.$anno;
                $day->adblue = $totaleGiornoAdBlue;
                $day->olio = $totaleGiornoOlio;
                $day->accessori = $totaleGiornoAccessori;
                $day->carburanti = $riepilogoCarburantiGiorno;
                array_push($datiGiorni, $day);
            }

            $data->giorni = $datiGiorni;
        }
        return $data;
    }


    /** 
     * Restituisce le statistiche di un'azienda' per un determinato giorno
     * @param: data: data completa
     * @param: id: id azienda
     */
    public function giorno(Request $request) {
        $mese = date('m', strtotime($request->data)); //Mese 
        $anno = date('Y', strtotime($request->data)); //Anno 
        $giorno = date('d', strtotime($request->data)); //Giorno attuale
        $prodotti = carb_prodotti::All(); // Tutti i prodotti commercializzati
        $azienda = carb_aziende::where('id', $request->id)->first();

        $data = new \stdClass;
        $data->azienda = $azienda;

        $totaleGiornoAdBlue = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->sum('adblue');
        $totaleGiornoOlio = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->sum('olio');
        $totaleGiornoAccessori = carb_trans::where('id_azienda', $request->id)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->sum('accessori');

        $riepilogoCarburantiGiorno = [];
        foreach($prodotti as $prodotto) {
            $pr_giorno = carb_trans::select('prodotto', 'pr_importo')->where('id_azienda', $request->id)
                                    ->whereDay('created_at', $giorno)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto', $prodotto['prodotto'])
                                    ->sum('pr_importo');


            $pr1_giorno = carb_trans::select('prodotto1', 'pr1_importo')->where('id_azienda', $request->id)
                                    ->whereDay('created_at', $giorno)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto1', $prodotto['prodotto'])
                                    ->sum('pr1_importo');
            
                        
            $pr2_giorno = carb_trans::select('prodotto2', 'pr2_importo')->where('id_azienda', $request->id)
                                    ->whereDay('created_at', $giorno)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto2', $prodotto['prodotto'])
                                    ->sum('pr2_importo');

            $sommaGiorno = $pr_giorno + $pr1_giorno + $pr2_giorno;


            array_push($riepilogoCarburantiGiorno, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaGiorno]);
        }

        $day = new \stdClass;
        $day->giorno =  $giorno.'/'.$mese.'/'.$anno;
        $day->adblue = $totaleGiornoAdBlue;
        $day->olio = $totaleGiornoOlio;
        $day->accessori = $totaleGiornoAccessori;
        $day->carburanti = $riepilogoCarburantiGiorno;

        $data->giorno = $day;

        return $data;
    }


    /**
     * Statistiche storiche per targa
     * @param: targa: targa veicolo
     * @param: id: id dell'azienda
     */
    public function targa_storica(Request $request) {
        $mese = date('m'); //Mese attuale
        $anno = date('Y'); //Anno attuale
        $giorni = cal_days_in_month(CAL_GREGORIAN,$mese,$anno); //Numero di giorni del mese attuale
        $oggi = date('d'); //Giorno attuale
        $prodotti = carb_prodotti::All(); // Tutti i prodotti commercializzati

        $targa = carb_targhe::where('targa', $request->targa)->first();

        $data = new \stdClass;
        $data->targa = $targa;

       // return ['mese' => $mese, 'anno' => $anno, 'giorno' => $oggi];

        /** Eseguo prima le quesry per i dati dell'anno */
        $totaleAnno = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->sum('totale');
        /** Totale per Olio, Accessori e AdBlue */
        $totaleAnnoAdBlue = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->sum('adblue');
        $totaleAnnoOlio = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->sum('olio');
        $totaleAnnoAccessori = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->sum('accessori');
        /** Totale prodotti  anno */
        $year = new \stdClass;
        $year->anno = $anno;
        $year->totale = $totaleAnno;
        $year->olio = $totaleAnnoOlio;
        $year->adblue = $totaleAnnoAdBlue;
        $year->accessori = $totaleAnnoAccessori;
        $riepilogoCarburantiAnno = [];
        foreach($prodotti as $prodotto) {
            $pr_anno = carb_trans::select('prodotto', 'pr_importo')->where('targa', $request->targa)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto', $prodotto['prodotto'])
                                    ->sum('pr_importo');


            $pr1_anno = carb_trans::select('prodotto1', 'pr1_importo')->where('targa', $request->targa)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto1', $prodotto['prodotto'])
                                    ->sum('pr1_importo');
            
                        
            $pr2_anno = carb_trans::select('prodotto2', 'pr2_importo')->where('targa', $request->targa)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto2', $prodotto['prodotto'])
                                    ->sum('pr2_importo');

            $sommaAnno = $pr_anno + $pr1_anno + $pr2_anno;

            array_push($riepilogoCarburantiAnno, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaAnno]);
        }

        $year->carburanti = $riepilogoCarburantiAnno;
        $data->anno = $year;

        /** Eseguo le operazioni per tutti i mesi */

        $datimese = [];
        for($i = 1; $i <= $mese; $i++) {
            Log::info($i);
            $totaleMese = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->sum('totale');

            /** Totale per Olio, Accessori e AdBlue */
            $totaleMeseAdBlue = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $i)->sum('adblue');
            $totaleMeseOlio = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $i)->sum('olio');
            $totaleMeseAccessori = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $i)->sum('accessori');

            $month = new \stdClass;
            $month->mese = $i;
            $month->anno = $anno;
            $month->name =  date("F", mktime(0, 0, 0, $i, 10));
            $month->olio = $totaleMeseOlio;
            $month->adblue = $totaleMeseAdBlue;
            $month->accessori = $totaleMeseAccessori;
            $month->totaleMese = $totaleMese;

            /** Totale per carburanti */
            $riepilogoCarburantiMese = [];
            foreach($prodotti as $prodotto) {
                $pr_mese = carb_trans::select('prodotto', 'pr_importo')->where('targa', $request->targa)
                                        ->whereMonth('created_at', $i)
                                        ->whereYear('created_at', $anno)
                                        ->where('prodotto', $prodotto['prodotto'])
                                        ->sum('pr_importo');
    
    
                $pr1_mese = carb_trans::select('prodotto1', 'pr1_importo')->where('targa', $request->targa)
                                        ->whereMonth('created_at', $i)
                                        ->whereYear('created_at', $anno)
                                        ->where('prodotto1', $prodotto['prodotto'])
                                        ->sum('pr1_importo');
                
                            
                $pr2_mese = carb_trans::select('prodotto2', 'pr2_importo')->where('targa', $request->targa)
                                        ->whereMonth('created_at', $i)
                                        ->whereYear('created_at', $anno)
                                        ->where('prodotto2', $prodotto['prodotto'])
                                        ->sum('pr2_importo');
    
                $sommaMese = $pr_mese + $pr1_mese + $pr2_mese;
    
                array_push($riepilogoCarburantiMese, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaMese]);
            }
            $month->carburanti = $riepilogoCarburantiMese;
            array_push($datimese, $month);
        }
        $data->mesi = $datimese;
        

        /** Provo a creare le condizioni per giorno  */
        $datiGiorni = [];
        for($m = 1; $m <= $mese; $m++) {
            $giorniMese = cal_days_in_month(CAL_GREGORIAN,$m,$anno); //Numero di giorni del mese del ciclo

            /** Ciclo i giorni del mese */
            for($g = 1; $g <= $giorniMese; $g++) {


                /** Controllo se il giorno non è nel futuro */
                if($g <= $oggi && $m <= $mese) {

                $totaleGiornoAdBlue = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $m)->whereDay('created_at', $g)->sum('adblue');
                $totaleGiornoOlio = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $m)->whereDay('created_at', $g)->sum('olio');
                $totaleGiornoAccessori = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $m)->whereDay('created_at', $g)->sum('accessori');

                $riepilogoCarburantiGiorno = [];
                foreach($prodotti as $prodotto) {
                    $pr_giorno = carb_trans::select('prodotto', 'pr_importo')->where('targa', $request->targa)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $m)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto', $prodotto['prodotto'])
                                            ->sum('pr_importo');
        
        
                    $pr1_giorno = carb_trans::select('prodotto1', 'pr1_importo')->where('targa', $request->targa)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $m)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto1', $prodotto['prodotto'])
                                            ->sum('pr1_importo');
                    
                                
                    $pr2_giorno = carb_trans::select('prodotto2', 'pr2_importo')->where('targa', $request->targa)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $m)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto2', $prodotto['prodotto'])
                                            ->sum('pr2_importo');
        
                    $sommaGiorno = $pr_giorno + $pr1_giorno + $pr2_giorno;
        
                    array_push($riepilogoCarburantiGiorno, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaGiorno]);
                }

                $day = new \stdClass;
                $day->giorno = $g.'/'.$m.'/'.$anno;
                $day->adblue = $totaleGiornoAdBlue;
                $day->olio = $totaleGiornoOlio;
                $day->accessori = $totaleGiornoAccessori;
                $day->carburanti = $riepilogoCarburantiGiorno;
    
                array_push($datiGiorni, $day);
            }
        }
    }

        $data->giorni = $datiGiorni;
        return $data;
    }


    /**
     * Ritorna le statistiche del mese una targa e il dettaglio dei giorni
     * @param: data: data completa
     * @param: targa: targa veicolo
     */
    public function targa_mese(Request $request) {
        $mese = date('m', strtotime($request->data)); //Mese 
        $anno = date('Y', strtotime($request->data)); //Anno 
        $giorni = cal_days_in_month(CAL_GREGORIAN,$mese,$anno); //Numero di giorni del mese attuale
        $oggi = date('d', strtotime($request->data)); //Giorno attuale

       // return ['mese' => $mese, 'anno' => $anno, 'giorno' => $oggi];

        $prodotti = carb_prodotti::All(); // Tutti i prodotti commercializzati

        $targa = carb_targhe::where('targa', $request->targa)->first();

        $data = new \stdClass;
        $data->targa = $targa;

        $totaleMese = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('totale');

        /** Totale per Olio, Accessori e AdBlue */
        $totaleMeseAdBlue = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('adblue');
        $totaleMeseOlio = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('olio');
        $totaleMeseAccessori = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->sum('accessori');

        $month = new \stdClass;
        $riepilogoCarburantiMese = [];
        foreach($prodotti as $prodotto) {
            $pr_mese = carb_trans::select('prodotto', 'pr_importo')->where('targa', $request->targa)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto', $prodotto['prodotto'])
                                    ->sum('pr_importo');


            $pr1_mese = carb_trans::select('prodotto1', 'pr1_importo')->where('targa', $request->targa)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto1', $prodotto['prodotto'])
                                    ->sum('pr1_importo');
            
                        
            $pr2_mese = carb_trans::select('prodotto2', 'pr2_importo')->where('targa', $request->targa)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto2', $prodotto['prodotto'])
                                    ->sum('pr2_importo');

            $sommaMese = $pr_mese + $pr1_mese + $pr2_mese;

            array_push($riepilogoCarburantiMese, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaMese]);
        }
       
            $month->mese = $mese;
            $month->anno = $anno;
            $month->name =  date("F", mktime(0, 0, 0, $mese, 10));
            $month->olio = $totaleMeseOlio;
            $month->adblue = $totaleMeseAdBlue;
            $month->accessori = $totaleMeseAccessori;
            $month->totaleMese = $totaleMese;
            $month->carburanti = $riepilogoCarburantiMese;
            $data->mese = $month;


            $datiGiorni = [];
            for($g = 1; $g <= $giorni; $g++) {
                /** Controllo se il giorno non è nel futuro */
                if($g <= $oggi) {

                $totaleGiornoAdBlue = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $g)->sum('adblue');
                $totaleGiornoOlio = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $g)->sum('olio');
                $totaleGiornoAccessori = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $g)->sum('accessori');

                $riepilogoCarburantiGiorno = [];
                foreach($prodotti as $prodotto) {
                    $pr_giorno = carb_trans::select('prodotto', 'pr_importo')->where('targa', $request->targa)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $mese)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto', $prodotto['prodotto'])
                                            ->sum('pr_importo');
        
        
                    $pr1_giorno = carb_trans::select('prodotto1', 'pr1_importo')->where('targa', $request->targa)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $mese)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto1', $prodotto['prodotto'])
                                            ->sum('pr1_importo');
                    
                                
                    $pr2_giorno = carb_trans::select('prodotto2', 'pr2_importo')->where('targa', $request->targa)
                                            ->whereDay('created_at', $g)
                                            ->whereMonth('created_at', $mese)
                                            ->whereYear('created_at', $anno)
                                            ->where('prodotto2', $prodotto['prodotto'])
                                            ->sum('pr2_importo');
        
                    $sommaGiorno = $pr_giorno + $pr1_giorno + $pr2_giorno;


                    array_push($riepilogoCarburantiGiorno, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaGiorno]);

                }
                $day = new \stdClass;
                $day->giorno = $g.'/'.$mese.'/'.$anno;
                $day->adblue = $totaleGiornoAdBlue;
                $day->olio = $totaleGiornoOlio;
                $day->accessori = $totaleGiornoAccessori;
                $day->carburanti = $riepilogoCarburantiGiorno;
                array_push($datiGiorni, $day);
            }

            $data->giorni = $datiGiorni;
        }
        return $data;
    }



    /** 
     * Restituisce le statistiche di una targa per un determinato giorno
     */
    public function targa_giorno(Request $request) {
        $mese = date('m', strtotime($request->data)); //Mese 
        $anno = date('Y', strtotime($request->data)); //Anno 
        $giorno = date('d', strtotime($request->data)); //Giorno attuale
        $prodotti = carb_prodotti::All(); // Tutti i prodotti commercializzati
        $targa = carb_targhe::where('targa', $request->targa)->first();

        $data = new \stdClass;
        $data->targa = $targa;

        $totaleGiornoAdBlue = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->sum('adblue');
        $totaleGiornoOlio = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->sum('olio');
        $totaleGiornoAccessori = carb_trans::where('targa', $request->targa)->whereYear('created_at', $anno)->whereMonth('created_at', $mese)->whereDay('created_at', $giorno)->sum('accessori');

        $riepilogoCarburantiGiorno = [];
        foreach($prodotti as $prodotto) {
            $pr_giorno = carb_trans::select('prodotto', 'pr_importo')->where('targa', $request->targa)
                                    ->whereDay('created_at', $giorno)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto', $prodotto['prodotto'])
                                    ->sum('pr_importo');


            $pr1_giorno = carb_trans::select('prodotto1', 'pr1_importo')->where('targa', $request->targa)
                                    ->whereDay('created_at', $giorno)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto1', $prodotto['prodotto'])
                                    ->sum('pr1_importo');
            
                        
            $pr2_giorno = carb_trans::select('prodotto2', 'pr2_importo')->where('targa', $request->targa)
                                    ->whereDay('created_at', $giorno)
                                    ->whereMonth('created_at', $mese)
                                    ->whereYear('created_at', $anno)
                                    ->where('prodotto2', $prodotto['prodotto'])
                                    ->sum('pr2_importo');

            $sommaGiorno = $pr_giorno + $pr1_giorno + $pr2_giorno;


            array_push($riepilogoCarburantiGiorno, ['prodotto' => $prodotto['prodotto'], 'totale' => $sommaGiorno]);
        }

        $day = new \stdClass;
        $day->giorno =  $giorno.'/'.$mese.'/'.$anno;
        $day->adblue = $totaleGiornoAdBlue;
        $day->olio = $totaleGiornoOlio;
        $day->accessori = $totaleGiornoAccessori;
        $day->carburanti = $riepilogoCarburantiGiorno;

        $data->giorno = $day;

        return $data;
    }
    
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\carburanti\carb_trans;
use App\carburanti\carb_prodotti;

class VenditeController extends Controller
{
    public function riepilogo_prodotto(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $mese = date('m', strtotime($request->from));
        $anno = date('Y', strtotime($request->from));

        if($request->prodotto != 'AD-BLUE') {
        $prodotto = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
        ->where('prodotto', $request->prodotto)->get();

        $pr_importo = array_sum(array_column($prodotto->toarray(), 'pr_importo'));
        $pr_litri = array_sum(array_column($prodotto->toarray(), 'pr_litri'));

        $prodotto1 = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
        ->where('prodotto1', $request->prodotto)->get();

        $pr1_importo = array_sum(array_column($prodotto->toarray(), 'pr1_importo'));
        $pr1_litri = array_sum(array_column($prodotto->toarray(), 'pr1_litri'));

        $prodotto2 = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
        ->where('prodotto2', $request->prodotto)->get();

        $pr2_importo = array_sum(array_column($prodotto->toarray(), 'pr2_importo'));
        $pr2_litri = array_sum(array_column($prodotto->toarray(), 'pr2_litri'));

        $litri_totali = $pr_litri + $pr1_litri + $pr2_litri;
        $importo_totale = $pr_importo + $pr1_importo + $pr2_importo;

        return response()->json(['litri' => number_format( $litri_totali, 2), 'importo' => number_format($importo_totale, 2)], 200);
        } else {
        $prodotto = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
        ->where('adblue', '>', 0)->get();

        $importo = array_sum(array_column($prodotto->toarray(), 'adblue'));
        $litri = array_sum(array_column($prodotto->toarray(), 'adblue_litri'));

        return response()->json(['litri' => number_format( $litri, 2), 'importo' => number_format($importo, 2)], 200);
        }
    }

    public function riepilogo_analitico_periodo(Request $request) {
        $from = $request->from;
        $to = $request->to;

        /** Tutti i prodotti */
        $prodotti = carb_prodotti::All();

        /** tutte le transazioni fra le date indicate */
        $transazioni = carb_trans::where('eliminata', false)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->get();

        /** Ciclo tutti i prodotti 
         * Per ogni prodotto cerco nelle transaioni e sommo il totale
        */
        foreach($prodotti as $prodotto) {
            $pr_litri = array_sum(array_column($transazioni->toarray(), 'pr_litri'));
            $pr1_litri = array_sum(array_column($transazioni->toarray(), 'pr1_litri'));
            $pr2_litri = array_sum(array_column($transazioni->toarray(), 'pr2_litri'));
        }
        
    }
}

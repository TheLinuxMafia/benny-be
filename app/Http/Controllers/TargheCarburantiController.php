<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\carburanti\carb_targhe;
use App\carburanti\carb_aziende;
use Log;

class TargheCarburantiController extends Controller
{
    /** Controlla se la targa è già presente */
    public function checktarga(Request $request) {
        $targa = carb_targhe::where('targa',strtoupper($request->targa))->first();
        if($targa) {
            return response()->json($targa, 200);
        } else {
            return response()->json(false, 204);
        }
    }



    public function store(Request $request) {

        $data = carb_targhe::updateOrCreate([
            'id' =>  strtoupper($request->id)
        ],
        [
            'id_azienda'    =>  $request->id_azienda,
            'ragsoc'    =>  $request->ragsoc,
            'piva'  =>  $request->piva,
            'codfis'    =>  strtoupper($request->codfis),
            'tipo'   =>  strtoupper($request->tipo),
            'targa' =>  strtoupper($request->targa),
            'prodotto'  =>  $request->prodotto,
            'prodotto1' =>  $request->prodotto1,
            'prodotto2' =>  $request->prodotto2,
            'km'    =>  $request->km,
            'marca' =>  strtoupper($request->marca),
            'modello'   =>  strtoupper($request->modello),
            'userins'   =>  auth()->user()->name,
            'centro'    =>  $request->centro,
            'shared'    =>  $request->shared
        ]);

        if($data->wasRecentlyCreated) {
            return response()->json($data, 201);
        } else {
            return response()->json($data, 204);
        }
    }

    public function targa(Request $request) {
        $string = $request->search;
        $targhe = carb_targhe::where('attiva', true)->where('targa', 'ILIKE', "%$string%")
        ->orWhere('ragsoc', 'ILIKE', "%$string%")->where('attiva', true)
        ->with('azienda')
        ->get();


        $aziende = carb_aziende::where('ragsoc', 'ILIKE', "%$string%")->where('attiva', true)->get();

        return response()->json(['targhe' => $targhe, 'aziende' => $aziende], 200);
    }

    public function targhe_azienda($id) {
        Log::info('targhe_azienda');
        return(carb_targhe::where('id_azienda', $id))->orderBy('targa', 'ASC')->get();
    }

    public function search(Request $request) {
        Log::info('search');
        return(carb_targhe::where('id_azienda', $request->id_azienda)->where('targa', 'ILIKE','%'.$request->string.'%'))->where('attiva', true)->get();
    }

    public function all() {
        Log::info('all');
        return(carb_targhe::All());
    }

    public function cercatutte($string) {
        Log::info('cercatutte');
        return(carb_targhe::where('targa', 'ILIKE','%'.$string.'%')->get());
    }

    public function elimina_targa($id) {
        return(carb_targhe::where('id', $id)->delete());
    }
}

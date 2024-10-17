<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\carburanti\carb_aziende;
use App\carburanti\carb_targhe;
use App\carburanti\carb_centricosto;

class AziendeCarburantiController extends Controller
{
    public function store(Request $request) {
        if(!$request->ragsoc) {
            $ragsoc = $request->cognome . ' ' . $request->nome;
        } else {
            $ragsoc = $request->ragsoc;
        };

        $data = carb_aziende::updateOrCreate([
            'piva'  =>  $request->piva,
            'codfis'  =>  $request->codfis
        ],
        [
            'ragsoc'    =>  $ragsoc,
            'indirizzo' =>  $request->indirizzo,
            'regione'   =>  $request->regione,
            'provincia' =>  $request->provincia,
            'comune'    =>  $request->comune,
            'piva'  =>  $request->piva,
            'codfis'  =>  $request->codfis,
            'nome'  =>  $request->nome,
            'cognome'   =>  $request->cognome,
            'sdi'   =>  $request->sdi,
            'email' =>  $request->email,
            'telefono'  =>  $request->telefono,
            'cellulare' =>  $request->cellulare,
            'pec'   =>  $request->pec,
            'cap'   =>  $request->cap,
            'userins'   =>  auth()->user()->name,
            'send_email'    =>  $request->send_email
        ]);

        foreach($request->centricosto as $centricosto) {
            $centro = carb_centricosto::updateOrCreate([
                'id_azienda'    =>  $data['id'],
                'centro'    =>  $centricosto['centro']
            ]);
        }

        return response()->json(carb_aziende::where('id', $data['id'])->with('centricosto')->first(), 200);
    }

    public function all() {
        return carb_aziende::with('centricosto')->orderBy('ragsoc', 'ASC')->get();
    }

    public function delete($id) {
        if(carb_aziende::where('id', $id)->update(['attiva' => false])) {
            carb_targhe::where('id_azienda', $id)->update(['attiva' => false]);
            return response()->json(true, 200);
        }
        return response()->json(false, 500);
    }

    public function attiva($id) {
        if(carb_aziende::where('id', $id)->update(['attiva' => true])) {
            carb_targhe::where('id_azienda', $id)->update(['attiva' => true]);
            return response()->json(true, 200);
        }
        return response()->json(false, 500);
    }

    public function search($string) {
        return(carb_aziende::where('ragsoc', 'ILIKE','%'.$string.'%'))->with('centricosto')->get();
    }

    public function delete_centro_costo($id) {
        return carb_centricosto::where('id', $id)->delete();
    }

    public function aziende_centro_costo($id) {
        return carb_centricosto::where('id_azienda', $id)->get();
    }
}

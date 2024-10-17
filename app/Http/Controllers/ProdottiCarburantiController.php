<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\carburanti\carb_prodotti;

class ProdottiCarburantiController extends Controller
{
    public function store(Request $request) {
        $data = carb_prodotti::updateOrCreate([
            'prodotto'  =>  $request->prodotto
        ],
        [
            'prezzo'    =>  $request->prezzo
        ]);

        return response()->json($data, 200);
    }

    public function all() {
        return carb_prodotti::All();
    }

    public function delete($id) {
        if(carb_prodotti::where('id', $id)->delete()) {
            return response()->json(true, 200);
        }
        return response()->json(false, 500);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\card\lotti;
use App\card\carte;
use App\card\azncardstat;
use App\card\lotticampagna;

class AdmLottiController extends Controller
{
    public function lotti() {
        $data = lotti::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)
        ->where('utilizzato', false)
        ->get();
        return response()->json($data);
    }
}

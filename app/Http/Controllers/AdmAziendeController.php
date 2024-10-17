<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\aziende\azn_anagrafiche;
use App\aziende\azn_puntivendita;
use Carbon\Carbon;
use DB;

class AdmAziendeController extends Controller
{
     /**
     * Restituisce una sola azienda. (aziende.azn_anagrafiche)
     * method. GET
     * url api/auth/azienda
     * @param mixed $id
     * @return array
     */

    public function azienda() {
        $data = azn_anagrafiche::where('id_azn_anagrafica', auth()->user()->id_azn_anagrafica)->with('pv')->first();
            return response()->json($data, 200);
    }


}

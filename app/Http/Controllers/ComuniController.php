<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\geo\comuni;

class ComuniController extends Controller
{
    public function searchComune($string) {
        $result = comuni::where('comune', 'ILIKE', "%$string%")->with('cap')->orderBy('comune')->get();
            return response()->json($result, 200);
        }

    }

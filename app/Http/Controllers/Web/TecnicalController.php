<?php

namespace App\Http\Controllers\Web;

use App\Models\Distrito;
use App\Models\Freguesia;
use App\Models\Municipio;
use App\Models\Place;
use App\Models\CodigoPostal;
use Illuminate\Http\Request;

class TecnicalController extends Controller
{
    public function placesview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int)$request->input('resultsPerPage'));
        }

        $perPage = max((int)$request->session()->get('resultsPerPage', 10), 1);
        $places = Place::with(['distrito', 'municipio', 'freguesia'])->orderBy('id')->paginate($perPage);
        return view('places.index', compact('places'));
    }
}
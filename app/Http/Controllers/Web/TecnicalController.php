<?php

namespace App\Http\Controllers\Web;

use App\Models\Distrito;
use App\Models\Freguesia;
use App\Models\Municipio;
use App\Models\Place;
use Illuminate\Http\Request;

class TecnicalController extends Controller
{
    public function area()
    {
        return view('tecnical.index');
    }

    public function distritosview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $distritos = Distrito::orderBy('id')->paginate($resultsPerPage);

        return view('tecnical.distritos', compact('distritos'));
    }

    public function municipiosview(Request $request)
    {
       if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $municipios = Municipio::orderBy('id')->paginate($resultsPerPage);

        return view('tecnical.municipios', compact('municipios'));
    }

    public function freguesiasview(Request $request)
    {
       if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $freguesias = Freguesia::orderBy('id')->paginate($resultsPerPage);

        return view('tecnical.freguesias', compact('freguesias'));
    }

    public function placesview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $places = Place::orderBy('id')->paginate($resultsPerPage);

        return view('tecnical.places', compact('places'));
    }
}
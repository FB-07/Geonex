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
    public function distritosview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $distritos = Distrito::orderBy('id')->paginate($resultsPerPage);

        return view('distritos.index', compact('distritos'));
    }

    public function municipiosview(Request $request)
    {
       if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $municipios = Municipio::orderBy('id')->paginate($resultsPerPage);

        return view('municipios.index', compact('municipios'));
    }

    public function freguesiasview(Request $request)
    {
       if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $freguesias = Freguesia::orderBy('id')->paginate($resultsPerPage);

        return view('freguesias.index', compact('freguesias'));
    }

    public function placesview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $places = Place::orderBy('id')->paginate($resultsPerPage);

        return view('places.index', compact('places'));
    }

    public function cpview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }
 
        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
 
        $cps = CodigoPostal::orderBy('codigo')->paginate($resultsPerPage);

        return view('cp.index', compact('cps'));
    }
}
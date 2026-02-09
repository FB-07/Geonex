<?php

namespace App\Http\Controllers\Web;

use App\Models\Distrito;
use App\Models\Freguesia;
use App\Models\Municipio;
use App\Models\Place;
class TecnicalController extends Controller
{
    public function area()
    {
        return view('tecnical.index');
    }

    public function distritosview()
    {
        $distritos = Distrito::orderBy('nome')->get();
        return view('tecnical.distritos', compact('distritos'));
    }

    public function municipiosview()
    {
        $municipios = Municipio::orderBy('nome')->get();
        return view('tecnical.municipios', compact('municipios'));
    }

    public function freguesiasview()
    {
        $freguesias = Freguesia::orderBy('nome')->get();

        return view('tecnical.freguesias', compact('freguesias'));
    }

    public function placesview()
    {
        $places = Place::orderBy('id')->get();

        return view('tecnical.places', compact('places'));
    }
}
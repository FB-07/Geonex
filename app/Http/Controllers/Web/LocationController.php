<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Distrito;
use App\Models\Municipio;
use App\Models\Freguesia;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function distritos()
    {
        return response()->json(
            Distrito::orderBy('nome')
                ->pluck('nome')
                ->values()
        );
    }

    public function municipios(Request $request)
    {
        $distrito = $request->query('distrito');
        if (!$distrito) return response()->json([]);

        return response()->json(
            Municipio::join('distritos', 'municipios.distrito_id', '=', 'distritos.id')
                ->where('distritos.nome', $distrito)
                ->orderBy('municipios.nome')
                ->pluck('municipios.nome')
        );
    }


    public function freguesias(Request $request)
    {
        $municipio = $request->query('municipio');
        if (!$municipio) return response()->json([]);

        return response()->json(
            Freguesia::join('municipios', 'freguesias.municipio_id', '=', 'municipios.id')
                ->where('municipios.nome', $municipio)
                ->orderBy('freguesias.nome')
                ->pluck('freguesias.nome')
        );
    }
}

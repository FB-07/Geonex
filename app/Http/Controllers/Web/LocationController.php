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

        if (!$distrito) {
            return response()->json([]);
        }

        return response()->json(
            Municipio::whereHas('distrito', function ($q) use ($distrito) {
                $q->where('nome', $distrito);
            })
            ->orderBy('nome')
            ->pluck('nome')
            ->values()
        );
    }

    public function freguesias(Request $request)
    {
        $municipio = $request->query('municipio');

        if (!$municipio) {
            return response()->json([]);
        }

        return response()->json(
            Freguesia::whereHas('municipio', function ($q) use ($municipio) {
                $q->where('nome', $municipio);
            })
            ->orderBy('nome')
            ->pluck('nome')
            ->values()
        );
    }
}

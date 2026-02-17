<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Pontos_Interesse;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointsInterestController extends Controller
{
    public function interestview(Request $request)
{
    if ($request->filled('resultsPerPage')) {
        $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
    }

    $perPage = max((int) $request->session()->get('resultsPerPage', 10), 1);
    $userId = (int) auth()->user()->gfuser_id;

    $groupsPage = DB::connection('mysql2')
        ->table('pontos_interesse as pi')
        ->join('distritos as d', 'd.id', '=', 'pi.distrito_id')
        ->join('municipios as m', 'm.id', '=', 'pi.municipio_id')
        ->join('freguesias as f', 'f.id', '=', 'pi.freguesia_id')
        ->where('pi.gfuser_id', $userId)
        ->select(
            'pi.nome',
            'pi.distrito_id',
            'pi.municipio_id',
            'pi.freguesia_id',
            'd.nome as distrito_nome',
            'm.nome as municipio_nome',
            'f.nome as freguesia_nome'
        )
        ->groupBy(
            'pi.nome',
            'pi.distrito_id',
            'pi.municipio_id',
            'pi.freguesia_id',
            'd.nome',
            'm.nome',
            'f.nome'
        )
        ->orderBy('pi.nome')
        ->orderBy('d.nome')
        ->orderBy('m.nome')
        ->orderBy('f.nome')
        ->paginate($perPage);

    $groups = collect($groupsPage->items());

    $keys = $groups->map(fn ($g) =>
        $g->nome.'|'.$g->distrito_id.'|'.$g->municipio_id.'|'.$g->freguesia_id
    )->values()->all();

    $rows = DB::connection('mysql2')
        ->table('pontos_interesse as pi')
        ->join('places as p', 'p.place_id', '=', 'pi.place_id')
        ->where('pi.gfuser_id', $userId)
        ->whereIn(DB::raw("CONCAT(pi.nome,'|',pi.distrito_id,'|',pi.municipio_id,'|',pi.freguesia_id)"), $keys)
        ->select(
            'pi.nome',
            'pi.distrito_id',
            'pi.municipio_id',
            'pi.freguesia_id',
            'p.place_id',
            'p.name',
            'p.formatted_address',
            'p.lat',
            'p.lng',
            'p.types'
        )
        ->orderBy('pi.nome')
        ->orderBy('p.name')
        ->get()
        ->groupBy(fn ($r) =>
            $r->nome.'|'.$r->distrito_id.'|'.$r->municipio_id.'|'.$r->freguesia_id
        );

    return view('interest.index', [
        'groupsPage' => $groupsPage, 
        'placesByGroup' => $rows,    
    ]);
}

    public function placesview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }

        $perPage = max((int) $request->session()->get('resultsPerPage', 10), 1);

        $places = Place::orderBy('id')->paginate($perPage);

        return view('places.index', compact('places'));
    }

    public function interestaddview()
    {
        return view('interest.form-pesquisa');
    }
}

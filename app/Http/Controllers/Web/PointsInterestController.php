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

        $namesPage = DB::connection('mysql2')
            ->table('pontos_interesse')
            ->where('gfuser_id', $userId)
            ->select('nome')
            ->groupBy('nome')
            ->orderBy('nome')
            ->paginate($perPage);

        $names = collect($namesPage->items())->pluck('nome');

        $rows = DB::connection('mysql2')
            ->table('pontos_interesse as pi')
            ->join('places as p', 'p.place_id', '=', 'pi.place_id')
            ->where('pi.gfuser_id', $userId)
            ->whereIn('pi.nome', $names)
            ->select(
                'pi.nome as interesse_nome',
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
            ->groupBy('interesse_nome');

        return view('interest.index', [
            'groups' => $rows,
            'namesPage' => $namesPage,
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

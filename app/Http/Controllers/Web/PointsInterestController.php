<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Pontos_Interesse;
use App\Models\SearchHistory;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PointsInterestController extends Controller
{
    private function extractCpDigits(?string $formattedAddress): ?string
    {
        if (!$formattedAddress) return null;
        if (!preg_match('/\b(\d{4})\s*[-\s]\s*(\d{3})\b/u', $formattedAddress, $m)) {
            return null;
        }
        return $m[1] . $m[2];
    }

    private function cpToHierarchy($conn, string $cpDigits): ?object
    {
        return $conn->table('codigos_postais as cp')
            ->join('freguesias as f', 'f.id', '=', 'cp.freguesia_id')
            ->join('municipios as m', 'm.id', '=', 'f.municipio_id')
            ->join('distritos as d', 'd.id', '=', 'm.distrito_id')
            ->where('cp.codigo', $cpDigits)
            ->select([
                'f.id as freguesia_id',
                'm.id as municipio_id',
                'd.id as distrito_id',
            ])
            ->first();
    }

    public function check(Request $request)
    {
        $data = $request->validate([
            'pesquisa' => 'required|string|max:255',
            'distrito' => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:120',
            'freguesia' => 'nullable|string|max:160',
        ]);

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return response()->json(['exists' => false]);
        }

        $userId = (int) $user->gfuser_id;
        $conn = DB::connection('mysql2');

        $distritoId = !empty($data['distrito'])
            ? $conn->table('distritos')->where('nome', $data['distrito'])->value('id')
            : null;
        $municipioId = !empty($data['municipio'])
            ? $conn->table('municipios')->where('nome', $data['municipio'])->value('id')
            : null;
        $freguesiaId = !empty($data['freguesia'])
            ? $conn->table('freguesias')->where('nome', $data['freguesia'])->value('id')
            : null;

        $q = (new Pontos_Interesse())
            ->setConnection('mysql2')
            ->newQuery()
            ->where('gfuser_id', $userId)
            ->where('nome', $data['pesquisa']);

        if ($distritoId) $q->where('distrito_id', $distritoId);
        if ($municipioId) $q->where('municipio_id', $municipioId);
        if ($freguesiaId) $q->where('freguesia_id', $freguesiaId);

        return response()->json(['exists' => $q->exists()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pesquisa' => 'required|string|max:255',
            'distrito' => 'required|string|max:120',
            'municipio' => 'required|string|max:120',
            'freguesia' => 'required|string|max:160',
        ]);

        $conn = DB::connection('mysql2');
        $distritoId = $conn->table('distritos')->where('nome', $data['distrito'])->value('id');
        $municipioId = $conn->table('municipios')->where('nome', $data['municipio'])->value('id');
        $freguesiaId = $conn->table('freguesias')->where('nome', $data['freguesia'])->value('id');

        if (!$distritoId || !$municipioId || !$freguesiaId) {
            return back()->withErrors(['pesquisa' => 'Localização inválida.'])->withInput();
        }

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return back()->withErrors(['pesquisa' => 'Utilizador não autenticado.']);
        }

        $userId = (int) $user->gfuser_id;
        $search = trim(mb_strtolower($data['pesquisa']));

        $existingPlaces = $conn->table('places')
            ->where('freguesia_id', $freguesiaId)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
            ->get();

        if ($existingPlaces->isNotEmpty()) {
            foreach ($existingPlaces as $place) {
                (new Pontos_Interesse())
                    ->setConnection('mysql2')
                    ->newQuery()
                    ->firstOrCreate(
                        ['gfuser_id' => $userId, 'place_id' => $place->place_id],
                        [
                            'nome' => $data['pesquisa'],
                            'distrito_id' => $distritoId,
                            'municipio_id' => $municipioId,
                            'freguesia_id' => $freguesiaId,
                        ]
                    );

                (new SearchHistory())
                    ->setConnection('mysql2')
                    ->newQuery()
                    ->create([
                        'gfuser_id' => $userId,
                        'place_id' => $place->place_id,
                        'query' => $data['pesquisa'],
                    ]);
            }

            return redirect()->route('interest')
                ->with('success', 'Resultados encontrados na base de dados.');
        }

        $apiKey = config('services.google_maps.key');
        if (!$apiKey) {
            return back()->withErrors(['pesquisa' => 'Google Maps key não configurada.']);
        }

        $response = Http::timeout(20)->withHeaders([
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => 'places.id,places.displayName.text,places.formattedAddress,places.location,places.types'
        ])->post('https://places.googleapis.com/v1/places:searchText', [
            'textQuery' => $data['pesquisa'],
            'regionCode' => 'PT'
        ]);

        if (!$response->successful()) {
            return back()->withErrors(['pesquisa' => 'Erro na API Google.']);
        }

        $places = $response->json('places') ?? [];

        if (empty($places)) {
            return back()->withInput()->withErrors(['pesquisa' => 'Nenhum resultado encontrado para essa freguesia.']);
        }

        DB::connection('mysql2')->beginTransaction();
        $found = false;

        try {
            foreach ($places as $p) {
                if (!isset($p['formattedAddress'])) continue;
                $formatted = $p['formattedAddress'];
                $cpDigits = $this->extractCpDigits($formatted);
                if (!$cpDigits) continue;

                $hier = $this->cpToHierarchy($conn, $cpDigits);
                if (!$hier || (int)$hier->freguesia_id !== (int)$freguesiaId) continue;

                $found = true;

                $place = (new Place())
                    ->setConnection('mysql2')
                    ->newQuery()
                    ->updateOrCreate(
                        ['place_id' => $p['id']],
                        [
                            'name' => $p['displayName']['text'] ?? '',
                            'formatted_address' => $formatted,
                            'lat' => $p['location']['latitude'] ?? null,
                            'lng' => $p['location']['longitude'] ?? null,
                            'types' => $p['types'] ?? null,
                            'distrito_id' => $distritoId,
                            'municipio_id' => $municipioId,
                            'freguesia_id' => $freguesiaId,
                        ]
                    );

                (new Pontos_Interesse())
                    ->setConnection('mysql2')
                    ->newQuery()
                    ->firstOrCreate(
                        ['gfuser_id' => $userId, 'place_id' => $place->place_id],
                        [
                            'nome' => $data['pesquisa'],
                            'distrito_id' => $distritoId,
                            'municipio_id' => $municipioId,
                            'freguesia_id' => $freguesiaId,
                        ]
                    );

                (new SearchHistory())
                    ->setConnection('mysql2')
                    ->newQuery()
                    ->create([
                        'gfuser_id' => $userId,
                        'place_id' => $place->place_id,
                        'query' => $data['pesquisa'],
                    ]);
            }

            if (!$found) {
                DB::connection('mysql2')->rollBack();
                return back()->withInput()->withErrors([
                    'pesquisa' => 'Nenhum resultado encontrado dentro da freguesia selecionada.'
                ]);
            }

            DB::connection('mysql2')->commit();
        } catch (\Throwable $e) {
            DB::connection('mysql2')->rollBack();
            return back()->withInput()->withErrors(['pesquisa' => 'Erro interno ao guardar.']);
        }

        return redirect()->route('interest')->with('success', 'Pesquisa guardada com sucesso.');
    }

    public function interestview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int)$request->input('resultsPerPage'));
        }

        $perPage = max((int)$request->session()->get('resultsPerPage', 10), 1);
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
        $keys = $groups->map(fn($g) => $g->nome.'|'.$g->distrito_id.'|'.$g->municipio_id.'|'.$g->freguesia_id)
            ->values()
            ->all();

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
                'p.types',
                'p.created_at'
            )
            ->orderBy('pi.nome')
            ->orderBy('p.name')
            ->get()
            ->groupBy(fn($r) => $r->nome.'|'.$r->distrito_id.'|'.$r->municipio_id.'|'.$r->freguesia_id);

        return view('interest.index', [
            'groupsPage' => $groupsPage,
            'placesByGroup' => $rows,
        ]);
    }

    public function placesview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int)$request->input('resultsPerPage'));
        }

        $perPage = max((int)$request->session()->get('resultsPerPage', 10), 1);
        $places = Place::orderBy('id')->paginate($perPage);

        return view('places.index', compact('places'));
    }

    public function interestaddview()
    {
        return view('interest.form-pesquisa');
    }
}
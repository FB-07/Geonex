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
    public function index(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int)$request->input('resultsPerPage'));
        }

        $perPage = max((int)$request->session()->get('resultsPerPage', 10), 1);
        $userId  = (int) auth()->user()->gfuser_id;

        $groupsPage = DB::connection('mysql2')
            ->table('pontos_interesse as pi')
            ->leftJoin('distritos as d', 'd.id', '=', 'pi.distrito_id')
            ->leftJoin('municipios as m', 'm.id', '=', 'pi.municipio_id')
            ->leftJoin('freguesias as f', 'f.id', '=', 'pi.freguesia_id')
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
            ->groupBy('pi.nome')
            ->orderBy('pi.nome')
            ->paginate($perPage);

        $allPlaces = DB::connection('mysql2')
            ->table('pontos_interesse as pi')
            ->join('places as p', 'p.place_id', '=', 'pi.place_id')
            ->where('pi.gfuser_id', $userId)
            ->select('pi.nome', 'p.*')
            ->get()
            ->groupBy('nome');

        return view('interest.index', [
            'groupsPage'    => $groupsPage,
            'placesByGroup' => $allPlaces,
        ]);
    }

    public function create()
    {
        return view('interest.form-pesquisa');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pesquisa'  => 'required|string|max:255',
            'distrito'  => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:120',
            'freguesia' => 'nullable|string|max:160',
        ]);

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return redirect()->route('interest.create')
                ->withErrors(['pesquisa' => 'Utilizador não autenticado.']);
        }

        $userId = (int) $user->gfuser_id;
        $conn   = DB::connection('mysql2');

        $distritoId  = !empty($data['distrito'])  ? $conn->table('distritos')->where('nome', $data['distrito'])->value('id') : null;
        $municipioId = !empty($data['municipio']) ? $conn->table('municipios')->where('nome', $data['municipio'])->value('id') : null;
        $freguesiaId = !empty($data['freguesia']) ? $conn->table('freguesias')->where('nome', $data['freguesia'])->value('id') : null;

        $search = trim(mb_strtolower($data['pesquisa']));
        $apiKey = config('services.google_maps.key');

        if (!$apiKey) {
            return redirect()->route('interest.create')
                ->withErrors(['pesquisa' => 'Google Maps key não configurada.']);
        }

        $response = Http::timeout(20)->withHeaders([
            'Content-Type'     => 'application/json',
            'X-Goog-Api-Key'   => $apiKey,
            'X-Goog-FieldMask' => 'places.id,places.displayName.text,places.formattedAddress,places.location,places.types'
        ])->post('https://places.googleapis.com/v1/places:searchText', [
            'textQuery'  => $data['pesquisa'],
            'regionCode' => 'PT'
        ]);

        if (!$response->successful()) {
            return redirect()->route('interest.create')
                ->withInput()
                ->withErrors(['pesquisa' => 'Erro na API Google.']);
        }

        $places = $response->json('places') ?? [];

        if (empty($places)) {
            return redirect()->route('interest.create')
                ->withInput()
                ->withErrors(['pesquisa' => 'Nenhum resultado encontrado.']);
        }

        DB::connection('mysql2')->beginTransaction();

        try {
            $savedCount = 0;

            foreach ($places as $p) {
                if (!isset($p['formattedAddress'])) continue;

                $formatted = $p['formattedAddress'];
                $cpDigits  = $this->extractCpDigits($formatted);
                if (!$cpDigits) continue;

                $hier = $this->cpToHierarchy($conn, $cpDigits);
                if (!$hier) continue;

                if ($distritoId !== null && $hier->distrito_id != $distritoId) continue;
                if ($municipioId !== null && $hier->municipio_id != $municipioId) continue;
                if ($freguesiaId !== null && $hier->freguesia_id != $freguesiaId) continue;

                $place = (new Place())
                    ->setConnection('mysql2')
                    ->newQuery()
                    ->updateOrCreate(
                        ['place_id' => $p['id']],
                        [
                            'name'              => $p['displayName']['text'] ?? '',
                            'formatted_address' => $formatted,
                            'lat'               => $p['location']['latitude'] ?? null,
                            'lng'               => $p['location']['longitude'] ?? null,
                            'types'             => $p['types'] ?? null,
                            'distrito_id'       => $hier->distrito_id,
                            'municipio_id'      => $hier->municipio_id,
                            'freguesia_id'      => $hier->freguesia_id,
                        ]
                    );

                (new Pontos_Interesse())
                    ->setConnection('mysql2')
                    ->newQuery()
                    ->updateOrCreate(
                        [
                            'gfuser_id' => $userId,
                            'place_id'  => $place->place_id,
                        ],
                        [
                            'nome'         => $data['pesquisa'],
                            'distrito_id'  => $distritoId,
                            'municipio_id' => $municipioId,
                            'freguesia_id' => $freguesiaId,
                        ]
                    );

                (new SearchHistory())
                    ->setConnection('mysql2')
                    ->newQuery()
                    ->create([
                        'gfuser_id' => $userId,
                        'place_id'  => $place->place_id,
                        'query'     => $data['pesquisa'],
                    ]);

                $savedCount++;
            }

            if ($savedCount === 0) {
                DB::connection('mysql2')->rollBack();
                return redirect()->route('interest.create')
                    ->withInput()
                    ->withErrors(['pesquisa' => 'Nenhum resultado corresponde aos filtros selecionados.']);
            }

            DB::connection('mysql2')->commit();
        } catch (\Throwable $e) {
            DB::connection('mysql2')->rollBack();
            return redirect()->route('interest.create')
                ->withInput()
                ->withErrors(['pesquisa' => 'Erro interno ao guardar.']);
        }

        return redirect()->route('interest.index')
            ->with('success', 'Pesquisa guardada com sucesso.');
    }

    public function edit($nome)
    {
        $userId = (int) auth()->user()->gfuser_id;

        $poi = DB::connection('mysql2')
            ->table('pontos_interesse')
            ->where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->first();

        if (!$poi) {
            return redirect()->route('interest.index')
                ->withErrors(['nome' => 'POI não encontrado.']);
        }

        return view('interest.edit', [
            'poi' => $poi,
        ]);
    }

    public function update(Request $request, $nome)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $userId = (int) auth()->user()->gfuser_id;

        $updated = DB::connection('mysql2')
            ->table('pontos_interesse')
            ->where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->update([
                'nome' => $request->input('nome'),
            ]);

        if (!$updated) {
            return redirect()->route('interest.index')
                ->withErrors(['nome' => 'Não foi possível atualizar o POI.']);
        }

        return redirect()->route('interest.index')
            ->with('success', 'Nome do POI atualizado com sucesso.');
    }

    public function destroy($nome)
    {
        $userId = (int) auth()->user()->gfuser_id;

        DB::connection('mysql2')
            ->table('pontos_interesse')
            ->where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->delete();

        return redirect()->route('interest.index')
            ->with('success', 'POI apagado com sucesso.');
    }

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

    public function names(Request $request)
    {
        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return response()->json(['names' => []], 200);
        }

        $userId = (int) $user->gfuser_id;

        $names = DB::connection('mysql2')
            ->table('pontos_interesse')
            ->where('gfuser_id', $userId)
            ->selectRaw('MIN(nome) as nome')
            ->groupBy(DB::raw('LOWER(nome)'))
            ->orderByRaw('MIN(nome) ASC')
            ->pluck('nome')
            ->values();

        return response()->json(['names' => $names], 200);
    }

    public function placesByName(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'distrito'  => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:120',
            'freguesia' => 'nullable|string|max:160',
        ]);

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return response()->json(['places' => []], 200);
        }
        $userId = (int) $user->gfuser_id;
        $conn = DB::connection('mysql2');

        $distritoId  = !empty($data['distrito'])  ? $conn->table('distritos')->where('nome', $data['distrito'])->value('id') : null;
        $municipioId = !empty($data['municipio']) ? $conn->table('municipios')->where('nome', $data['municipio'])->value('id') : null;
        $freguesiaId = !empty($data['freguesia']) ? $conn->table('freguesias')->where('nome', $data['freguesia'])->value('id') : null;

        $q = $conn->table('pontos_interesse as pi')
            ->join('places as p', 'p.place_id', '=', 'pi.place_id')
            ->where('pi.gfuser_id', $userId)
            ->whereRaw('LOWER(pi.nome) = ?', [mb_strtolower($data['name'])])
            ->select(
                'p.place_id',
                'p.name',
                'p.formatted_address',
                'p.lat',
                'p.lng',
                'p.types'
            );

        if ($distritoId)  $q->where('pi.distrito_id', $distritoId);
        if ($municipioId) $q->where('pi.municipio_id', $municipioId);
        if ($freguesiaId) $q->where('pi.freguesia_id', $freguesiaId);

        $places = $q->get();

        return response()->json(['places' => $places], 200);
    }

    public function check(Request $request)
    {
        $data = $request->validate([
            'pesquisa'  => 'required|string|max:255',
            'distrito'  => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:120',
            'freguesia' => 'nullable|string|max:160',
        ]);

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return response()->json(['exists' => false]);
        }

        $userId = (int) $user->gfuser_id;
        $q = DB::connection('mysql2')->table('pontos_interesse')
            ->where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($data['pesquisa'])]);

        if (!empty($data['distrito']))  $q->where('distrito_id', $data['distrito']);
        if (!empty($data['municipio'])) $q->where('municipio_id', $data['municipio']);
        if (!empty($data['freguesia'])) $q->where('freguesia_id', $data['freguesia']);

        return response()->json(['exists' => $q->exists()]);
    }
}
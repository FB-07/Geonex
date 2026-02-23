<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Pontos_Interesse;
use App\Models\Place;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PointsInterestController extends Controller
{
    /**
     * Extrai os dígitos do código postal de um endereço formatado
     */
    private function extractCpDigits(?string $formattedAddress): ?string
    {
        if (!$formattedAddress) return null;

        if (!preg_match('/\b(\d{4})\s*[-\s]\s*(\d{3})\b/u', $formattedAddress, $m)) {
            return null;
        }

        return $m[1] . $m[2];
    }

    /**
     * Obtém a hierarquia (freguesia, municipio, distrito) a partir de um código postal
     */
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

    /**
     * Verifica se um ponto de interesse já existe
     */
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

        $q = (new Pontos_Interesse())->setConnection('mysql2')->newQuery()
            ->where('gfuser_id', $userId)
            ->where('nome', $data['pesquisa']);

        if ($distritoId) $q->where('distrito_id', $distritoId);
        if ($municipioId) $q->where('municipio_id', $municipioId);
        if ($freguesiaId) $q->where('freguesia_id', $freguesiaId);

        return response()->json(['exists' => $q->exists()]);
    }

    /**
     * Armazena um ponto de interesse
     */
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
            return back()->withInput()->withErrors([
                'distrito' => !$distritoId ? 'Distrito inválido.' : null,
                'municipio' => !$municipioId ? 'Município inválido.' : null,
                'freguesia' => !$freguesiaId ? 'Freguesia inválida.' : null,
            ]);
        }

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return back()->withInput()->withErrors(['pesquisa' => 'Utilizador não autenticado.']);
        }

        $userId = (int) $user->gfuser_id;

        // Verifica duplicados
        $alreadyExists = (new Pontos_Interesse())
            ->setConnection('mysql2')
            ->newQuery()
            ->where('gfuser_id', $userId)
            ->where('nome', $data['pesquisa'])
            ->where('distrito_id', $distritoId)
            ->where('municipio_id', $municipioId)
            ->where('freguesia_id', $freguesiaId)
            ->exists();

        if ($alreadyExists) {
            return back()->withInput()->withErrors([
                'pesquisa' => 'Já existe esse ponto de interesse.'
            ]);
        }

        // Configuração da API Google Places
        $apiKey = config('services.google_maps.key');
        if (!$apiKey) {
            return back()->withInput()->withErrors(['pesquisa' => 'Google Maps key não configurada.']);
        }

        $headers = [
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => 'places.id,places.displayName.text,places.formattedAddress,places.location,places.types,nextPageToken',
        ];

        $baseBody = [
            'textQuery' => $data['pesquisa'],
            'regionCode' => 'PT',
            'pageSize' => 20,
            'locationBias' => [
                'circle' => [
                    'center' => ['latitude' => 39.3999, 'longitude' => -8.2245],
                    'radius' => 50000.0,
                ],
            ],
        ];

        $allPlaces = [];
        $pageToken = null;

        for ($i = 0; $i < 3; $i++) {
            $body = $baseBody;
            if ($pageToken) {
                $body['pageToken'] = $pageToken;
            }

            $resp = Http::timeout(20)->withHeaders($headers)
                ->post('https://places.googleapis.com/v1/places:searchText', $body);

            if (!$resp->successful()) {
                $msg = $resp->json('error.message') ?? $resp->body();
                return back()->withInput()->withErrors([
                    'pesquisa' => "Google Places API ({$resp->status()}): " . mb_strimwidth((string)$msg, 0, 300, '...'),
                ]);
            }

            $places = $resp->json('places') ?? [];
            $allPlaces = array_merge($allPlaces, $places);
            $pageToken = $resp->json('nextPageToken');
            if (!$pageToken) break;
        }

        if (empty($allPlaces)) {
            return back()->withInput()->withErrors(['pesquisa' => 'Nenhum resultado encontrado.']);
        }

        // Salva no banco de dados
        DB::connection('mysql2')->beginTransaction();
        try {
            foreach ($allPlaces as $p) {
                if (!isset($p['id'], $p['location']['latitude'], $p['location']['longitude'])) continue;

                $formatted = $p['formattedAddress'] ?? null;
                $cpDigits = $this->extractCpDigits($formatted);
                if (!$cpDigits) continue;

                $formatted = $p['formattedAddress'] ?? null;
$cpDigits = $this->extractCpDigits($formatted);
$hier = null;

if ($cpDigits) {
    $hier = $this->cpToHierarchy($conn, $cpDigits);
}

// Se CP não bater com a tabela, usa o que o usuário selecionou
if (!$hier) {
    $hier = (object)[
        'distrito_id' => $distritoId,
        'municipio_id' => $municipioId,
        'freguesia_id' => $freguesiaId,
    ];
}

// Agora só salva se o CP ou fallback coincidir com o local selecionado
if ((int)$hier->freguesia_id !== (int)$freguesiaId ||
    (int)$hier->municipio_id !== (int)$municipioId ||
    (int)$hier->distrito_id !== (int)$distritoId) {
    continue; // descarta se não bater
}

// Salva o lugar
$place = (new Place())->setConnection('mysql2')->newQuery()->updateOrCreate(
    ['place_id' => $p['id']],
    [
        'name' => $p['displayName']['text'] ?? '',
        'formatted_address' => $formatted,
        'lat' => (float)$p['location']['latitude'],
        'lng' => (float)$p['location']['longitude'],
        'types' => $p['types'] ?? null,
        'distrito_id' => (int)$hier->distrito_id,
        'municipio_id' => (int)$hier->municipio_id,
        'freguesia_id' => (int)$hier->freguesia_id,
    ]
);

                (new Pontos_Interesse())->setConnection('mysql2')->newQuery()->firstOrCreate(
                    [
                        'gfuser_id' => $userId,
                        'place_id' => $place->place_id,
                    ],
                    [
                        'nome' => $data['pesquisa'],
                        'distrito_id' => $distritoId,
                        'municipio_id' => $municipioId,
                        'freguesia_id' => $freguesiaId,
                    ]
                );

                (new SearchHistory())->setConnection('mysql2')->newQuery()->create([
                    'gfuser_id' => $userId,
                    'place_id' => $place->place_id,
                    'query' => $data['pesquisa'],
                ]);
            }

            DB::connection('mysql2')->commit();
        } catch (\Throwable $e) {
            DB::connection('mysql2')->rollBack();
            Log::error('SAVE FAILED', ['msg' => $e->getMessage()]);
            return back()->withInput()->withErrors(['pesquisa' => $e->getMessage()]);
        }

        return redirect()->route('interest')->with('success', 'Pesquisa guardada com sucesso.');
    }

    /**
     * Lista pontos de interesse do usuário
     */
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
        $keys = $groups->map(fn($g) => $g->nome.'|'.$g->distrito_id.'|'.$g->municipio_id.'|'.$g->freguesia_id)->values()->all();

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
            ->groupBy(fn($r) => $r->nome.'|'.$r->distrito_id.'|'.$r->municipio_id.'|'.$r->freguesia_id);

        return view('interest.index', [
            'groupsPage' => $groupsPage,
            'placesByGroup' => $rows,
        ]);
    }

    /**
     * Lista todos os lugares
     */
    public function placesview(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int)$request->input('resultsPerPage'));
        }

        $perPage = max((int)$request->session()->get('resultsPerPage', 10), 1);
        $places = Place::orderBy('id')->paginate($perPage);

        return view('places.index', compact('places'));
    }

    /**
     * Formulário de pesquisa de interesse
     */
    public function interestaddview()
    {
        return view('interest.form-pesquisa');
    }

    /**
     * Lista nomes únicos de pontos de interesse do usuário
     */
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

    /**
     * Busca lugares por nome
     */
    public function placesByName(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'distrito' => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:120',
            'freguesia' => 'nullable|string|max:160',
        ]);

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return response()->json(['places' => []], 200);
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

        if ($distritoId) $q->where('pi.distrito_id', $distritoId);
        if ($municipioId) $q->where('pi.municipio_id', $municipioId);
        if ($freguesiaId) $q->where('pi.freguesia_id', $freguesiaId);

        $places = $q->get();

        return response()->json(['places' => $places], 200);
    }
}
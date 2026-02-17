<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Place;
use App\Models\Pontos_Interesse;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveInterestController extends Controller
{
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

        $conn = DB::connection('mysql2');

        $distritoId  = !empty($data['distrito'])  ? $conn->table('distritos')->where('nome', $data['distrito'])->value('id') : null;
        $municipioId = !empty($data['municipio']) ? $conn->table('municipios')->where('nome', $data['municipio'])->value('id') : null;
        $freguesiaId = !empty($data['freguesia']) ? $conn->table('freguesias')->where('nome', $data['freguesia'])->value('id') : null;

        $q = (new Pontos_Interesse())->setConnection('mysql2')->newQuery()
            ->where('gfuser_id', $userId)
            ->where('nome', $data['pesquisa']);

        if ($distritoId)  $q->where('distrito_id', $distritoId);
        if ($municipioId) $q->where('municipio_id', $municipioId);
        if ($freguesiaId) $q->where('freguesia_id', $freguesiaId);

        return response()->json(['exists' => $q->exists()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pesquisa'  => 'required|string|max:255',
            'distrito'  => 'required|string|max:120',
            'municipio' => 'required|string|max:120',
            'freguesia' => 'required|string|max:160',
        ]);

        $conn = DB::connection('mysql2');

        $distritoId  = $conn->table('distritos')->where('nome', $data['distrito'])->value('id');
        $municipioId = $conn->table('municipios')->where('nome', $data['municipio'])->value('id');
        $freguesiaId = $conn->table('freguesias')->where('nome', $data['freguesia'])->value('id');

        if (!$distritoId || !$municipioId || !$freguesiaId) {
            return back()->withInput()->withErrors([
                'distrito'  => !$distritoId ? 'Distrito inválido.' : null,
                'municipio' => !$municipioId ? 'Município inválido.' : null,
                'freguesia' => !$freguesiaId ? 'Freguesia inválida.' : null,
            ]);
        }

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return back()->withInput()->withErrors(['pesquisa' => 'Utilizador não autenticado.']);
        }
        $userId = (int) $user->gfuser_id;

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

        $apiKey = config('services.google_maps.key');
        if (!$apiKey) {
            return back()->withInput()->withErrors(['pesquisa' => 'Google Maps key não configurada.']);
        }

        $headers = [
            'Content-Type'     => 'application/json',
            'X-Goog-Api-Key'   => $apiKey,
            'X-Goog-FieldMask' => 'places.id,places.displayName.text,places.formattedAddress,places.location,places.types,nextPageToken',
        ];

        $baseBody = [
            'textQuery'  => $data['pesquisa'],
            'regionCode' => 'PT',
            'pageSize'   => 20,
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
            if (!$pageToken) {
                break;
            }
        }

        if (empty($allPlaces)) {
            return back()->withInput()->withErrors(['pesquisa' => 'Nenhum resultado encontrado.']);
        }

        DB::connection('mysql2')->beginTransaction();

        try {
            foreach ($allPlaces as $p) {
                if (!isset($p['id'], $p['location']['latitude'], $p['location']['longitude'])) {
                    continue;
                }

                $place = (new Place())->setConnection('mysql2')->newQuery()->updateOrCreate(
                    ['place_id' => $p['id']],
                    [
                        'name'              => $p['displayName']['text'] ?? '',
                        'formatted_address' => $p['formattedAddress'] ?? null,
                        'lat'               => (float) $p['location']['latitude'],
                        'lng'               => (float) $p['location']['longitude'],
                        'types'             => $p['types'] ?? null,
                    ]
                );

                (new Pontos_Interesse())->setConnection('mysql2')->newQuery()->firstOrCreate(
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

                (new SearchHistory())->setConnection('mysql2')->newQuery()->create([
                    'gfuser_id' => $userId,
                    'place_id'  => $place->place_id,
                    'query'     => $data['pesquisa'],
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
}

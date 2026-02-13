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

        $apiKey = config('services.google_maps.key');
        if (!$apiKey) {
            return back()->withInput()->withErrors(['pesquisa' => 'Google Maps key não configurada.']);
        }

        $body = [
            'textQuery'  => $data['pesquisa'],
            'regionCode' => 'PT',
            'locationBias' => [
                'circle' => [
                    'center' => ['latitude' => 39.3999, 'longitude' => -8.2245],
                    'radius' => 50000.0,
                ],
            ],
        ];

        $resp = Http::timeout(20)->withHeaders([
            'Content-Type'     => 'application/json',
            'X-Goog-Api-Key'   => $apiKey,
            'X-Goog-FieldMask' => 'places.id,places.displayName.text,places.formattedAddress,places.location,places.types',
        ])->post('https://places.googleapis.com/v1/places:searchText', $body);

        if (!$resp->successful()) {
            $msg = $resp->json('error.message') ?? $resp->body();
            return back()->withInput()->withErrors([
                'pesquisa' => "Google Places API ({$resp->status()}): " . mb_strimwidth((string)$msg, 0, 300, '...'),
            ]);
        }

        $results = $resp->json('places') ?? [];

        DB::connection('mysql2')->beginTransaction();

        try {
            foreach ($results as $p) {
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

                (new Pontos_Interesse())->setConnection('mysql2')->newQuery()->create([
                    'nome'         => $data['pesquisa'],
                    'gfuser_id'    => $userId,
                    'distrito_id'  => $distritoId,
                    'municipio_id' => $municipioId,
                    'freguesia_id' => $freguesiaId,
                    'place_id'     => $place->place_id,
                ]);


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

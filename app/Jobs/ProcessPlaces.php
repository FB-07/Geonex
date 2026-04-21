<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Place;
use App\Models\SearchHistory;
use App\Models\Pontos_Interesse;
use App\Models\Freguesia;
use Illuminate\Support\Facades\Log;

class ProcessPlaces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $poiId;
    protected string $searchQuery;
    protected ?int $distritoId;
    protected ?int $municipioId;
    protected ?int $freguesiaId;

    public function __construct(int $poiId, string $searchQuery, ?int $distritoId = null, ?int $municipioId = null, ?int $freguesiaId = null)
    {
        $this->poiId = $poiId;
        $this->searchQuery = $searchQuery;
        $this->distritoId = $distritoId;
        $this->municipioId = $municipioId;
        $this->freguesiaId = $freguesiaId;
    }

    public function handle()
    {
        $poi = Pontos_Interesse::find($this->poiId);
        if (!$poi) {
            Log::warning("POI ID {$this->poiId} não encontrado.");
            return;
        }

        $conn = DB::connection('mysql2');
        $apiKey = config('services.google_maps.key');
        if (!$apiKey) return;

        $freguesias = Freguesia::query()
            ->when($this->distritoId, fn($q) => $q->whereHas('municipio', fn($mq) => $mq->where('distrito_id', $this->distritoId)))
            ->when($this->municipioId, fn($q) => $q->where('municipio_id', $this->municipioId))
            ->when($this->freguesiaId, fn($q) => $q->where('id', $this->freguesiaId))
            ->get();

        $allPlaces = [];

        foreach ($freguesias as $freguesia) {
            $textQuery = $this->searchQuery . ' ' . $freguesia->nome;

            $cached = DB::table('place_search_cache')
                ->where('query', $textQuery)
                ->where('freguesia_id', $freguesia->id)
                ->first();

            if ($cached) {
                $places = json_decode($cached->response_json, true);
            } else {
                $response = Http::timeout(20)->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => 'places.id,places.displayName.text,places.formattedAddress,places.location,places.types'
                ])->post('https://places.googleapis.com/v1/places:searchText', [
                    'textQuery' => $textQuery,
                    'regionCode' => 'PT'
                ]);

                if (!$response->successful()) {
                    continue;
                }

                $places = $response->json('places') ?? [];

                DB::table('place_search_cache')->updateOrInsert([
                    'query' => $textQuery,
                    'freguesia_id' => $freguesia->id,
                ], [
                    'response_json' => json_encode($places),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }

            foreach ($places as $p) {
                if (!isset($p['id'])) continue;
                $allPlaces[$p['id']] = $p;
            }

            usleep(200000);
        }

        $searchWords = array_filter(explode(' ', $this->searchQuery));

        foreach ($allPlaces as $p) {
            if (!isset($p['formattedAddress']) || !isset($p['displayName']['text'])) continue;

            $name = mb_strtolower($p['displayName']['text']);
            $matchesQuery = true;

            foreach ($searchWords as $word) {
                $found = false;

                foreach (explode(' ', $name) as $nameWord) {
                    if (levenshtein($word, $nameWord) <= 2) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $matchesQuery = false;
                    break;
                }
            }

            if (!$matchesQuery) continue;

            $formatted = $p['formattedAddress'];
            $cpDigits = $this->extractCpDigits($formatted);
            if (!$cpDigits) continue;

            $hier = $this->cpToHierarchy($conn, $cpDigits);
            if (!$hier) continue;

            if ($this->distritoId && $hier->distrito_id != $this->distritoId) continue;
            if ($this->municipioId && $hier->municipio_id != $this->municipioId) continue;
            if ($this->freguesiaId && $hier->freguesia_id != $this->freguesiaId) continue;

            $place = Place::updateOrCreate(
                ['place_id' => $p['id']],
                [
                    'name' => $p['displayName']['text'],
                    'formatted_address' => $formatted,
                    'lat' => $p['location']['latitude'] ?? null,
                    'lng' => $p['location']['longitude'] ?? null,
                    'types' => $p['types'] ?? null,
                    'distrito_id' => $hier->distrito_id,
                    'municipio_id' => $hier->municipio_id,
                    'freguesia_id' => $hier->freguesia_id,
                ]
            );

            DB::connection('mysql2')->table('ponto_interesse_place')->updateOrInsert([
                'ponto_interesse_id' => $poi->id,
                'place_id' => $place->place_id,
            ]);

            SearchHistory::create([
                'gfuser_id' => $poi->gfuser_id,
                'place_id' => $place->place_id,
                'query' => $this->searchQuery,
            ]);
        }

        Log::info("Job ProcessPlaces concluído para POI ID {$this->poiId}");
    }

    private function extractCpDigits(?string $formattedAddress): ?string
    {
        if (!$formattedAddress) return null;
        if (!preg_match('/\b(\d{4})\s*[-\s]\s*(\d{3})\b/u', $formattedAddress, $m)) return null;
        return $m[1] . $m[2];
    }

    private function cpToHierarchy($conn, string $cpDigits): ?object
    {
        return $conn->table('codigos_postais as cp')
            ->join('freguesias as f', 'f.id', '=', 'cp.freguesia_id')
            ->join('municipios as m', 'm.id', '=', 'f.municipio_id')
            ->join('distritos as d', 'd.id', '=', 'm.distrito_id')
            ->where('cp.codigo', $cpDigits)
            ->select(['f.id as freguesia_id', 'm.id as municipio_id', 'd.id as distrito_id'])
            ->first();
    }
}
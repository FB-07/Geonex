<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class PlaceSearchController extends Controller
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

    private function resolveZonaIds($conn, array $data): array
    {
        $distritoId = null;
        $municipioId = null;
        $freguesiaId = null;

        if (!empty($data['distrito'])) {
            $distritoId = $conn->table('distritos')->where('nome', $data['distrito'])->value('id');
        }
        if (!empty($data['municipio'])) {
            $municipioId = $conn->table('municipios')->where('nome', $data['municipio'])->value('id');
        }
        if (!empty($data['freguesia'])) {
            $freguesiaId = $conn->table('freguesias')->where('nome', $data['freguesia'])->value('id');
        }

        return [$distritoId, $municipioId, $freguesiaId];
    }

    public function search(Request $request)
    {
        Log::info('HIT /api/places/search', [
            'ip' => $request->ip(),
            'payload' => $request->all(),
        ]);

        $data = $request->validate([
            'query'     => 'required|string|max:255',
            'lat'       => 'nullable|numeric',
            'lng'       => 'nullable|numeric',
            'radius'    => 'nullable|numeric|min:1|max:50000',
            'category'  => 'nullable|string|max:80',
            'distrito'  => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:120',
            'freguesia' => 'nullable|string|max:160',
        ]);

        $queryNorm = trim(mb_strtolower($data['query']));

        $connName = (new Place())->getConnectionName() ?: config('database.default');
        $conn = DB::connection($connName);

        try {
            Log::info('DB CHECK', [
                'place_connection' => $connName,
                'place_database' => $conn->getDatabaseName(),
                'has_places_table' => Schema::connection($connName)->hasTable('places'),
            ]);
        } catch (\Throwable $e) {
            Log::error('DB CHECK FAILED', ['msg' => $e->getMessage()]);
        }

        $category = $data['category'] ?? null;
        $googleTypes = $this->mapCategoryToGoogleType($category);

        if (is_array($googleTypes)) {
            $googleTypes = array_values(array_unique(array_filter($googleTypes)));
            $googleTypes = array_slice($googleTypes, 0, 20);
        }

        [$distritoId, $municipioId, $freguesiaId] = $this->resolveZonaIds($conn, $data);
        $isFilteringByZona = (bool)($distritoId || $municipioId || $freguesiaId);

        $dbQuery = Place::on($connName)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $queryNorm . '%']);

        if (!empty($googleTypes) && is_array($googleTypes)) {
            $dbQuery->where(function ($q) use ($googleTypes) {
                foreach ($googleTypes as $t) {
                    $q->orWhereRaw('JSON_CONTAINS(types, ?)', [json_encode($t)])
                      ->orWhere('types', 'LIKE', '%' . $t . '%');
                }
            });
        }

        if ($distritoId)  $dbQuery->where('distrito_id', $distritoId);
        if ($municipioId) $dbQuery->where('municipio_id', $municipioId);
        if ($freguesiaId) $dbQuery->where('freguesia_id', $freguesiaId);

        $dbResults = $dbQuery->limit(50)->get();

        if ($dbResults->count()) {
            return response()->json([
                'source' => 'database',
                'places' => $dbResults,
            ]);
        }

        $apiKey = config('services.google_maps.key');
        if (!$apiKey) {
            return response()->json(['error' => 'Google Maps key not configured'], 500);
        }

        $fieldMask = 'places.id,places.displayName.text,places.formattedAddress,places.location,places.types';

        $zonaQuery = $this->buildZonaQuery($data);

        $body = [
            'textQuery'  => $data['query'],
            'regionCode' => 'PT',
        ];

        if (!empty($googleTypes) && is_array($googleTypes)) {
            $body['includedTypes'] = $googleTypes;
        }

        if ($zonaQuery) {
            $geo = $this->geocode($apiKey, $zonaQuery);

            if ($geo) {
                $body['locationRestriction'] = [
                    'circle' => [
                        'center' => ['latitude' => $geo['lat'], 'longitude' => $geo['lng']],
                        'radius' => (float)($data['radius'] ?? 15000),
                    ],
                ];
            } else {
                $body['locationRestriction'] = $this->portugalRectangleRestriction();
            }
        } else {
            $body['locationRestriction'] = $this->portugalRectangleRestriction();
        }

        $resp = Http::timeout(20)->withHeaders([
            'Content-Type'     => 'application/json',
            'X-Goog-Api-Key'   => $apiKey,
            'X-Goog-FieldMask' => $fieldMask,
        ])->post('https://places.googleapis.com/v1/places:searchText', $body);

        if (!$resp->successful()) {
            return response()->json([
                'error'   => 'Google API error',
                'status'  => $resp->status(),
                'details' => $resp->json(),
            ], 422);
        }

        $places = $resp->json('places') ?? [];
        if (!count($places)) {
            return response()->json(['source' => 'google', 'places' => []]);
        }

        $saved = [];
        $skipped = 0;

        foreach ($places as $p) {
            $placeId = $p['id'] ?? null;
            $loc = $p['location'] ?? null;

            if (!$placeId || !$loc || !isset($loc['latitude'], $loc['longitude'])) {
                $skipped++;
                continue;
            }

            $name  = $p['displayName']['text'] ?? '';
            $addr  = $p['formattedAddress'] ?? null;
            $types = $p['types'] ?? null;

            $cpDigits = $this->extractCpDigits($addr);

            if ($isFilteringByZona && !$cpDigits) { $skipped++; continue; }

            $hier = null;
            if ($cpDigits) $hier = $this->cpToHierarchy($conn, $cpDigits);

            if ($isFilteringByZona && !$hier) { $skipped++; continue; }

            if ($isFilteringByZona && $hier) {
                if ($freguesiaId && (int)$hier->freguesia_id !== (int)$freguesiaId) { $skipped++; continue; }
                if ($municipioId && (int)$hier->municipio_id !== (int)$municipioId) { $skipped++; continue; }
                if ($distritoId && (int)$hier->distrito_id !== (int)$distritoId) { $skipped++; continue; }
            }

            try {
                $saved[] = Place::on($connName)->updateOrCreate(
                    ['place_id' => $placeId],
                    [
                        'name'              => $name,
                        'formatted_address' => $addr,
                        'lat'               => (float)$loc['latitude'],
                        'lng'               => (float)$loc['longitude'],
                        'types'             => $types,

                        'distrito_id'       => $hier->distrito_id ?? null,
                        'municipio_id'      => $hier->municipio_id ?? null,
                        'freguesia_id'      => $hier->freguesia_id ?? null,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error('SAVE FAILED', ['place_id' => $placeId, 'msg' => $e->getMessage()]);
            }
        }

        return response()->json([
            'source' => 'google',
            'places' => $saved,
            'meta'   => ['skipped' => $skipped],
        ]);
    }

    private function portugalRectangleRestriction(): array
    {
        return [
            'rectangle' => [
                'low' => ['latitude' => 36.96, 'longitude' => -9.50],
                'high' => ['latitude' => 42.15, 'longitude' => -6.19],
            ],
        ];
    }

    private function buildZonaQuery(array $data): ?string
    {
        $parts = [];

        if (!empty($data['freguesia'])) $parts[] = $data['freguesia'];
        if (!empty($data['municipio'])) $parts[] = $data['municipio'];
        if (!empty($data['distrito']))  $parts[] = $data['distrito'];

        if (!count($parts)) return null;

        $parts[] = 'Portugal';
        return implode(', ', $parts);
    }

    private function geocode(string $apiKey, string $address): ?array
    {
        $resp = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key'     => $apiKey,
        ]);

        if (!$resp->successful()) return null;

        $loc = $resp->json('results.0.geometry.location');
        if (!$loc || !isset($loc['lat'], $loc['lng'])) return null;

        return ['lat' => (float)$loc['lat'], 'lng' => (float)$loc['lng']];
    }

    private function mapCategoryToGoogleType(?string $category): ?array
    {
        if (!$category) return null;

        $category = trim(strtolower($category));

        $map = [
            'automotive' => [
                'car_dealer','car_rental','car_repair','car_wash','electric_vehicle_charging_station',
                'gas_station','parking','rest_stop'
            ],
            'business' => [
                'corporate_office','farm','ranch'
            ],
            'culture' => [
                'art_gallery','art_studio','auditorium','cultural_landmark','historical_place',
                'monument','museum','performing_arts_theater'
            ],
            'education' => [
                'library','preschool','primary_school','school','secondary_school','university'
            ],
            'entertainment' => [
                'adventure_sports_center','amphitheatre','amusement_center','amusement_park','aquarium',
                'banquet_hall','barbecue_area','botanical_garden','bowling_alley','casino','community_center',
                'concert_hall','convention_center','cultural_center','dog_park','event_venue','garden',
                'historical_landmark','marina','movie_rental','movie_theater','national_park','night_club',
                'park','planetarium','plaza','state_park','tourist_attraction','visitor_center','water_park','zoo'
            ],
            'finance' => [
                'accounting','atm','bank'
            ],
            'fooddrink' => [
                'bakery','bar','barbecue_restaurant','brazilian_restaurant','breakfast_restaurant','cafe',
                'chinese_restaurant','coffee_shop','fast_food_restaurant','french_restaurant','hamburger_restaurant',
                'ice_cream_shop','indian_restaurant','italian_restaurant','japanese_restaurant','meal_delivery',
                'meal_takeaway','mexican_restaurant','pizza_restaurant','restaurant','seafood_restaurant',
                'steak_house','sushi_restaurant','thai_restaurant','vegan_restaurant','vegetarian_restaurant','wine_bar'
            ],
            'geographic' => [
                'administrative_area_level_1','administrative_area_level_2','country','locality','postal_code','school_district'
            ],
            'government' => [
                'city_hall','courthouse','embassy','fire_station','government_office','local_government_office',
                'police','post_office'
            ],
            'health' => [
                'dental_clinic','dentist','doctor','drugstore','hospital','pharmacy','spa','wellness_center','yoga_studio'
            ],
            'housing' => [
                'apartment_building','apartment_complex','condominium_complex','housing_complex'
            ],
            'lodging' => [
                'bed_and_breakfast','campground','camping_cabin','cottage','extended_stay_hotel','guest_house','hostel',
                'hotel','inn','lodging','motel','resort_hotel','rv_park'
            ],
            'services' => [
                'barber_shop','beauty_salon','catering_service','electrician','florist','funeral_home','hair_salon',
                'insurance_agency','lawyer','locksmith','moving_company','plumber','real_estate_agency','storage',
                'travel_agency','veterinary_care'
            ],
            'shopping' => [
                'auto_parts_store','bicycle_store','book_store','cell_phone_store','clothing_store','convenience_store',
                'department_store','electronics_store','furniture_store','gift_shop','grocery_store','hardware_store',
                'home_improvement_store','jewelry_store','liquor_store','market','pet_store','shoe_store','shopping_mall',
                'supermarket','warehouse_store'
            ],
            'sports' => [
                'athletic_field','fitness_center','golf_course','gym','playground','ski_resort','sports_club',
                'sports_complex','stadium','swimming_pool'
            ],
            'transportation' => [
                'airport','bus_station','bus_stop','ferry_terminal','subway_station','taxi_stand','train_station',
                'transit_station','truck_stop'
            ],
        ];

        return $map[$category] ?? null;
    }
}
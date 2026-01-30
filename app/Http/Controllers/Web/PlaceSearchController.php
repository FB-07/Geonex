<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlaceSearchController extends Controller
{
    public function search(Request $request)
    {
        $data = $request->validate([
            'query'  => 'required|string|max:255',
            'lat'    => 'nullable|numeric',
            'lng'    => 'nullable|numeric',
            'radius' => 'nullable|numeric|min:1|max:50000',
        ]);

        $queryNorm = trim(mb_strtolower($data['query']));

        $dbResults = Place::whereRaw('LOWER(name) LIKE ?', ['%'.$queryNorm.'%'])
            ->limit(50)
            ->get();

        if ($dbResults->count()) {
            return response()->json([
                'source' => 'database',
                'places' => $dbResults,
            ]);
        }

        $apiKey = config('services.google_maps.key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Google Maps key not configured',
                'hint'  => 'Define GOOGLE_MAPS_KEY no .env e services.google_maps.key no config/services.php',
            ], 500);
        }

        $fieldMask = 'places.id,places.displayName.text,places.formattedAddress,places.location,places.types';

        $body = [
            'textQuery' => $data['query'],
        ];

        if (isset($data['lat'], $data['lng'], $data['radius'])) {
            $body = [
                'textQuery'   => $data['query'],
                'regionCode'  => 'PT',
                'locationRestriction' => [
                    'rectangle' => [
                        'low' => [ 
                            'latitude'  => 36.96,
                            'longitude' => -9.50,
                        ],
                        'high' => [ 
                            'latitude'  => 42.15,
                            'longitude' => -6.19,
                        ],
                    ],
                ],
            ];
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
            return response()->json([
                'source' => 'google',
                'places' => [],
            ]);
        }

        $saved = [];

        foreach ($places as $p) {
            $placeId = $p['id'] ?? null;
            $loc = $p['location'] ?? null;

            if (!$placeId || !$loc) continue;

            $saved[] = Place::updateOrCreate(
                ['place_id' => $placeId],
                [
                    'name'              => $p['displayName']['text'] ?? '',
                    'formatted_address' => $p['formattedAddress'] ?? null,
                    'lat'               => $loc['latitude'],
                    'lng'               => $loc['longitude'],
                    'types'             => $p['types'] ?? null,
                ]
            );
        }

        return response()->json([
            'source' => 'google',
            'places' => $saved,
        ]);
    }
}

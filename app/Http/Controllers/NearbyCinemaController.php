<?php

namespace App\Http\Controllers;

use App\Services\MapsGeolocationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class NearbyCinemaController extends Controller
{
    public function __invoke(Request $request, MapsGeolocationService $maps): JsonResponse
    {
        $coordinates = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:50'],
        ]);

        try {
            $places = $maps->nearbyPlaces(
                (float) $coordinates['latitude'],
                (float) $coordinates['longitude'],
                (float) ($coordinates['radius_km'] ?? 10)
            );
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Nearby places are unavailable right now.',
            ], 503);
        }

        return response()->json(['places' => $places]);
    }
}

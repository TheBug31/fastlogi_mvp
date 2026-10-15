<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Services\DriverTelemetryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives the GPS ping sent by the driver panel every 60 seconds while the
 * driver has orders in transit (RF-011).
 */
class DriverTelemetryController extends Controller
{
    public function __invoke(Request $request, DriverTelemetryService $telemetry): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $driver = $request->user()->deliveryDriver;

        abort_if($driver === null, 403, 'El usuario no es un repartidor.');

        $telemetry->record($driver, (float) $data['latitude'], (float) $data['longitude']);

        return response()->json([
            'ok' => true,
            'last_seen_at' => $driver->last_seen_at?->toIso8601String(),
        ]);
    }
}

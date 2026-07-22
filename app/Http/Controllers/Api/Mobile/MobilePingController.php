<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Point de terminaison pour verifier la disponibilite de l API mobile.
 * Retourne le nom de l application, l environnement et l horodatage.
 */
class MobilePingController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'API mobile disponible.',
            'data' => [
                'name' => config('app.name'),
                'environment' => config('app.env'),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}

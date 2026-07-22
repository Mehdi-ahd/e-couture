<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controleur API mobile pour marquer la fin du parcours d onboarding.
 * Met a jour la date de completion de l onboarding pour l utilisateur.
 */
class MobileOnboardingController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'mobile_onboarding_completed_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Onboarding mobile marque comme termine.',
            'data' => [
                'completed_at' => $user->mobile_onboarding_completed_at?->toIso8601String(),
            ],
        ]);
    }
}

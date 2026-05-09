<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

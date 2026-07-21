<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Mtownsend\RemoveBg\RemoveBg;

/**
 * Controleur API mobile pour consulter les informations du compte Remove.bg.
 * Permet de verifier si l API est configuree et d obtenir les details du compte.
 */
class MobileRemoveBgAccountController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $apiKey = config('removebg.api_key');

        if (! $apiKey) {
            return response()->json([
                'message' => 'Remove.bg API key is not configured.',
                'data' => [
                    'configured' => false,
                ],
            ], 200);
        }

        try {
            $removeBg = new RemoveBg($apiKey);
            $account = $removeBg->account();

            return response()->json([
                'message' => 'Remove.bg account info retrieved.',
                'data' => [
                    'configured' => true,
                    'account' => $account,
                ],
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Failed to retrieve Remove.bg account info.',
                'data' => [
                    'configured' => true,
                    'error' => $exception->getMessage(),
                ],
            ], 502);
        }
    }
}

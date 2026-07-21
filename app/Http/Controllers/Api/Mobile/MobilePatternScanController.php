<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Exceptions\Scan\RemoveBgPatternGatewayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\PatternScanRequest;
use App\Http\Resources\Api\Mobile\ScanResultResource;
use App\Services\Scan\RemoveBgPatternGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controleur API mobile pour le scan de patrons.
 * Utilise le service RemoveBg pour traiter les images et generer des decoupes.
 */
class MobilePatternScanController extends Controller
{
    public function __invoke(
        PatternScanRequest $request,
        RemoveBgPatternGateway $removeBgPatternGateway,
    ): JsonResponse {
        try {
            $result = $removeBgPatternGateway->createCutout(
                $request->file('image'),
                $request->options(),
            );
        } catch (RemoveBgPatternGatewayException $exception) {
            Log::warning('pattern_scan.failed', [
                'detail' => $exception->getMessage(),
                'status' => $exception->statusCode,
                'context' => $exception->context,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'Échec du traitement du scan.',
                'error' => [
                    'code' => 'REMOVE_BG_GATEWAY_ERROR',
                    'detail' => $exception->getMessage(),
                    'retryable' => true,
                    'status' => $exception->statusCode,
                    'context' => $exception->context,
                ],
            ], $exception->statusCode && $exception->statusCode < 500 ? 422 : 503);
        }

        return response()->json([
            'message' => 'Pattern cutout processed.',
            'data' => new ScanResultResource($result->toMobilePayload()),
        ]);
    }

    public function downloadCutout(string $scan): BinaryFileResponse
    {
        $disk = Storage::disk('public');

        abort_unless($disk->exists("mobile_pattern_scans/{$scan}/cutout.png"), 404);

        return response()->file($disk->path("mobile_pattern_scans/{$scan}/cutout.png"), [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}

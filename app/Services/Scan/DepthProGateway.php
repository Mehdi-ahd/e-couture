<?php

namespace App\Services\Scan;

use App\DTO\Scan\DepthProScanResult;
use App\DTO\Scan\PatternScanOptions;
use App\Exceptions\Scan\DepthProGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DepthProGateway
{
    public function scanPattern(UploadedFile $image, PatternScanOptions $options): DepthProScanResult
    {
        $scanId = Str::uuid()->toString();
        $baseUrl = rtrim((string) config('services.depth_pro.base_url'), '/');
        $apiKey = config('services.depth_pro.api_key');
        $headers = $apiKey ? ['X-Api-Key' => $apiKey] : [];
        $imagePath = $image->getRealPath();
        if (! $imagePath) {
            throw new DepthProGatewayException('Unable to read uploaded image.');
        }

        try {
            $response = Http::withHeaders($headers)
                ->connectTimeout((int) config('services.depth_pro.connect_timeout', 5))
                ->timeout((int) config('services.depth_pro.timeout', 120))
                ->attach(
                    'image',
                    file_get_contents($imagePath),
                    $image->getClientOriginalName() ?: 'pattern-scan.jpg',
                )
                ->post($baseUrl.'/scan-pattern', $options->toDepthProFields($scanId))
                ->throw();
        } catch (ConnectionException $exception) {
            throw new DepthProGatewayException(
                message: 'Depth Pro service is unreachable.',
                previous: $exception,
            );
        } catch (RequestException $exception) {
            throw new DepthProGatewayException(
                message: 'Depth Pro service rejected the scan request.',
                statusCode: $exception->response->status(),
                context: $exception->response->json() ?? [],
                previous: $exception,
            );
        }

        return new DepthProScanResult($response->json() ?? []);
    }
}

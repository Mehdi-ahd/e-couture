<?php

namespace App\Services\Scan;

use App\Exceptions\Scan\MeasureCvGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class MeasureCvGateway
{
    public function measure(array $payload): array
    {
        $baseUrl = rtrim((string) config('services.measure_cv.base_url'), '/');
        $apiKey = config('services.measure_cv.api_key');
        $headers = $apiKey ? ['X-Api-Key' => $apiKey] : [];

        try {
            $response = Http::withHeaders($headers)
                ->connectTimeout((int) config('services.measure_cv.connect_timeout', 5))
                ->timeout((int) config('services.measure_cv.timeout', 120))
                ->post($baseUrl.'/measure', $payload)
                ->throw();
        } catch (ConnectionException $exception) {
            throw new MeasureCvGatewayException(
                message: 'Le service de mesures est indisponible.',
                previous: $exception,
            );
        } catch (RequestException $exception) {
            throw new MeasureCvGatewayException(
                message: 'Le service de mesures a refusé la requête.',
                statusCode: $exception->response->status(),
                context: $exception->response->json() ?? [],
                previous: $exception,
            );
        }

        return $response->json() ?? [];
    }
}

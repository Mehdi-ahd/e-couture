<?php

namespace App\Services\Scan;

use App\Exceptions\Scan\MeasureCvGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Service d appel a l API Measure CV pour la prise de mesures guidee.
 * Envoie des photos sous plusieurs angles et recupere les mesures calculees.
 */
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
            $body = $exception->response->json() ?? [];
            $rawDetail = $body['detail'] ?? $body['message'] ?? null;

            if (is_array($rawDetail)) {
                $detail = json_encode($rawDetail, JSON_UNESCAPED_UNICODE);
            } elseif (is_string($rawDetail)) {
                $detail = $rawDetail;
            } else {
                $detail = 'Le service de mesures a refusé la requête.';
            }

            logger()->error('Measure CV error: {status} {body}', [
                'status' => $exception->response->status(),
                'body' => $body,
            ]);

            throw new MeasureCvGatewayException(
                message: "Mesure CV : $detail",
                statusCode: $exception->response->status(),
                context: $body,
                previous: $exception,
            );
        }

        return $response->json() ?? [];
    }
}

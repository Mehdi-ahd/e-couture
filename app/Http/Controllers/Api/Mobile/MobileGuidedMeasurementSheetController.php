<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Exceptions\Scan\MeasureCvGatewayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\GuidedMeasurementSheetRequest;
use App\Models\Client;
use App\Models\FicheMesure;
use App\Models\User;
use App\Services\Scan\MeasureCvGateway;
use Illuminate\Http\JsonResponse;

class MobileGuidedMeasurementSheetController extends Controller
{
    public function __construct(
        private readonly MeasureCvGateway $gateway,
    ) {}

    public function store(GuidedMeasurementSheetRequest $request, string $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);

        $sheet = new FicheMesure;
        $sheet->client_id = $record->id;
        $sheet->date = today();
        $sheet->methode = 'mediapipe_3angles';
        $sheet->save();

        $sheet->addMediaFromUrl($request->input('face_url'))
            ->toMediaCollection('face');

        $sheet->addMediaFromUrl($request->input('dos_url'))
            ->toMediaCollection('dos');

        $sheet->addMediaFromUrl($request->input('profil_url'))
            ->toMediaCollection('profil');

        try {
            $this->gateway->measure([
                'fiche_id' => $sheet->external_id,
                'client_id' => $record->external_id,
                'face_url' => $request->input('face_url'),
                'dos_url' => $request->input('dos_url'),
                'profil_url' => $request->input('profil_url'),
            ]);
        } catch (MeasureCvGatewayException $exception) {
            $sheet->clearMediaCollection('face');
            $sheet->clearMediaCollection('dos');
            $sheet->clearMediaCollection('profil');
            $sheet->delete();

            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->context,
            ], $exception->statusCode ?? 503);
        }

        $sheet->clearMediaCollection('face');
        $sheet->clearMediaCollection('dos');
        $sheet->clearMediaCollection('profil');

        $sheet->loadCount('mesures');

        return response()->json([
            'message' => 'Mesures prises avec succes.',
            'data' => [
                'item' => $this->serializeSheet($sheet),
            ],
        ], 201);
    }

    public function show(GuidedMeasurementSheetRequest $request, string $client, string $sheet): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);

        $item = FicheMesure::query()
            ->where('client_id', $record->id)
            ->where('external_id', $sheet)
            ->withCount('mesures')
            ->with(['mesures.typeMesure'])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'item' => $this->serializeSheet($item),
                'detail' => [
                    'measurements' => $item->mesures
                        ->sortBy(fn ($measure) => $measure->typeMesure?->nom ?? '')
                        ->map(fn ($measure) => $this->serializeMeasurement($measure))
                        ->values()
                        ->all(),
                ],
            ],
        ]);
    }

    private function findClientForUser(User $user, string $externalId): Client
    {
        return Client::query()
            ->where('prestataire_id', $user->id)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function serializeSheet(FicheMesure $sheet): array
    {
        return [
            'external_id' => $sheet->external_id,
            'date' => $sheet->date?->toDateString(),
            'date_label' => $sheet->date?->format('d/m/Y') ?? 'Sans date',
            'methode' => $sheet->methode,
            'measurements_count' => $sheet->mesures_count ?? $sheet->mesures->count(),
            'status_label' => ($sheet->mesures_count ?? $sheet->mesures->count()) > 0 ? 'Complete' : 'A completer',
        ];
    }

    private function serializeMeasurement($measure): array
    {
        return [
            'external_id' => $measure->external_id,
            'type_mesure_id' => $measure->type_mesure_id,
            'label' => $measure->typeMesure?->nom ?? 'Mesure',
            'code' => $measure->typeMesure?->code ?? '',
            'category' => $measure->typeMesure?->categorie ?? 'principale',
            'value' => (float) $measure->valeur,
            'unit' => $measure->typeMesure?->unite ?? 'cm',
            'source' => $measure->source,
            'confidence' => $measure->confiance !== null ? (float) $measure->confiance : null,
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FicheMesure;
use App\Models\Mesure;
use App\Models\TypeMesure;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileMeasurementSheetController extends Controller
{
    public function index(Request $request, string $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $sheets = $this->sheetQuery($record)->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $sheets->count(),
                    'with_measurements' => $sheets->where('mesures_count', '>', 0)->count(),
                ],
                'client' => $this->serializeClient($record),
                'available_types' => $this->serializeTypeOptions(),
                'items' => $sheets->map(fn (FicheMesure $sheet) => $this->serializeSheet($sheet))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request, string $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $validated = $this->validatePayload($request);

        $sheet = DB::transaction(function () use ($record, $validated): FicheMesure {
            $sheet = FicheMesure::query()->create([
                'client_id' => $record->id,
                'date' => $validated['date'],
                'methode' => $validated['methode'],
            ]);

            $this->replaceMeasurements($sheet, $validated['measurements'] ?? []);

            return $sheet;
        });

        $sheet = $this->sheetQuery($record)->whereKey($sheet->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Fiche de mesures enregistrée.',
            'data' => [
                'item' => $this->serializeSheet($sheet),
            ],
        ], 201);
    }

    public function show(Request $request, string $client, string $sheet): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $item = $this->findSheetForClient($record, $sheet);

        return response()->json([
            'data' => [
                'item' => $this->serializeSheet($item),
                'detail' => [
                    'measurements' => $item->mesures
                        ->sortBy(fn (Mesure $measure) => $measure->typeMesure?->nom ?? '')
                        ->map(fn (Mesure $measure) => $this->serializeMeasurement($measure))
                        ->values()
                        ->all(),
                ],
                'options' => [
                    'client' => $this->serializeClient($record),
                    'available_types' => $this->serializeTypeOptions(),
                ],
            ],
        ]);
    }

    public function update(Request $request, string $client, string $sheet): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $item = $this->findSheetForClient($record, $sheet);
        $validated = $this->validatePayload($request, true);

        DB::transaction(function () use ($item, $validated): void {
            $item->fill([
                'date' => $validated['date'] ?? $item->date,
                'methode' => $validated['methode'] ?? $item->methode,
            ])->save();

            if (array_key_exists('measurements', $validated)) {
                $this->replaceMeasurements($item, $validated['measurements'] ?? []);
            }
        });

        $item = $this->sheetQuery($record)->whereKey($item->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Fiche de mesures mise à jour.',
            'data' => [
                'item' => $this->serializeSheet($item),
            ],
        ]);
    }

    public function destroy(Request $request, string $client, string $sheet): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $item = $this->findSheetForClient($record, $sheet);
        $item->delete();

        return response()->json([
            'message' => 'Fiche de mesures supprimée.',
        ]);
    }

    public function validateSheet(Request $request, string $client, string $sheet): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $item = $this->findSheetForClient($record, $sheet);

        $item->update(['validee' => true]);

        $item = $this->sheetQuery($record)->whereKey($item->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Fiche de mesures validée.',
            'data' => [
                'item' => $this->serializeSheet($item),
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

    private function sheetQuery(Client $client)
    {
        return FicheMesure::query()
            ->where('client_id', $client->id)
            ->withCount('mesures')
            ->with(['mesures.typeMesure'])
            ->latest('date');
    }

    private function findSheetForClient(Client $client, string $externalId): FicheMesure
    {
        return $this->sheetQuery($client)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'date' => [...$required, 'date'],
            'methode' => [...$required, 'string', 'max:100'],
            'measurements' => [$isUpdate ? 'sometimes' : 'required', 'array'],
            'measurements.*.type_mesure_id' => ['required_with:measurements', 'integer', 'exists:type_mesures,id'],
            'measurements.*.valeur' => ['required_with:measurements', 'numeric'],
            'measurements.*.source' => ['required_with:measurements', 'string', 'max:100'],
            'measurements.*.confiance' => ['nullable', 'numeric', 'between:0,1'],
            'measurements.*.commentaire' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function replaceMeasurements(FicheMesure $sheet, array $measurements): void
    {
        $sheet->mesures()->delete();

        foreach ($measurements as $measure) {
            $sheet->mesures()->create([
                'type_mesure_id' => $measure['type_mesure_id'],
                'valeur' => $measure['valeur'],
                'source' => $measure['source'],
                'confiance' => $measure['confiance'] ?? null,
                'commentaire' => $measure['commentaire'] ?? null,
            ]);
        }
    }

    private function serializeClient(Client $client): array
    {
        return [
            'external_id' => $client->external_id,
            'full_name' => trim($client->prenom.' '.$client->nom),
        ];
    }

    private function serializeSheet(FicheMesure $sheet): array
    {
        return [
            'external_id' => $sheet->external_id,
            'date' => $sheet->date?->toDateString(),
            'date_label' => $sheet->date?->format('d/m/Y') ?? 'Sans date',
            'methode' => $sheet->methode,
            'measurements_count' => $sheet->mesures_count ?? $sheet->mesures->count(),
            'status_label' => $sheet->validee
                ? 'Validée'
                : (($sheet->mesures_count ?? $sheet->mesures->count()) > 0 ? 'En attente de validation' : 'À compléter'),
            'validee' => $sheet->validee,
        ];
    }

    private function serializeMeasurement(Mesure $measure): array
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
            'commentaire' => $measure->commentaire,
        ];
    }

    private function serializeTypeOptions(): array
    {
        return TypeMesure::query()
            ->where('est_actif', true)
            ->orderBy('categorie')
            ->orderBy('nom')
            ->get()
            ->map(fn (TypeMesure $type) => [
                'id' => $type->id,
                'external_id' => $type->external_id,
                'code' => $type->code,
                'label' => $type->nom,
                'unit' => $type->unite,
                'category' => $type->categorie,
            ])
            ->values()
            ->all();
    }
}

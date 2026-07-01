<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\RegleProportion;
use App\Models\TypeMesure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileRegleProportionController extends Controller
{
    public function index(): JsonResponse
    {
        $items = RegleProportion::query()
            ->with(['typeMesure'])
            ->orderBy('nom')
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $items->count(),
                    'active' => $items->where('est_active', true)->count(),
                ],
                'available_type_mesures' => TypeMesure::query()
                    ->where('est_actif', true)
                    ->orderBy('nom')
                    ->get()
                    ->map(fn (TypeMesure $tm) => [
                        'id' => $tm->id,
                        'external_id' => $tm->external_id,
                        'label' => $tm->nom,
                        'unite' => $tm->unite,
                    ])
                    ->values()
                    ->all(),
                'items' => $items->map(fn (RegleProportion $item) => $this->serializeItem($item))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $record = RegleProportion::query()->create($this->validatePayload($request));

        return response()->json([
            'message' => 'Règle de proportion ajoutée.',
            'data' => [
                'item' => $this->serializeItem($record->load(['typeMesure'])),
            ],
        ], 201);
    }

    public function show(string $regleProportion): JsonResponse
    {
        $record = RegleProportion::query()
            ->with(['typeMesure'])
            ->where('external_id', $regleProportion)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'item' => $this->serializeItem($record),
            ],
        ]);
    }

    public function update(Request $request, string $regleProportion): JsonResponse
    {
        $record = RegleProportion::query()
            ->with(['typeMesure'])
            ->where('external_id', $regleProportion)
            ->firstOrFail();

        $record->fill($this->validatePayload($request, true))->save();

        return response()->json([
            'message' => 'Règle de proportion mise à jour.',
            'data' => [
                'item' => $this->serializeItem($record->load(['typeMesure'])),
            ],
        ]);
    }

    public function destroy(string $regleProportion): JsonResponse
    {
        $record = RegleProportion::query()
            ->where('external_id', $regleProportion)
            ->firstOrFail();

        $record->delete();

        return response()->json([
            'message' => 'Règle de proportion supprimée.',
        ]);
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'type_mesure_id' => [...$required, 'integer', 'exists:type_mesures,id'],
            'nom' => [...$required, 'string', 'max:191'],
            'coefficient' => ['nullable', 'numeric', 'min:0'],
            'offset' => ['nullable', 'numeric'],
            'source_metier' => ['nullable', 'string', 'max:80'],
            'version' => ['nullable', 'integer', 'min:1'],
            'est_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function serializeItem(RegleProportion $item): array
    {
        return [
            'id' => $item->id,
            'external_id' => $item->external_id,
            'type_mesure_id' => $item->type_mesure_id,
            'type_mesure_label' => $item->typeMesure?->nom,
            'type_mesure_unite' => $item->typeMesure?->unite,
            'nom' => $item->nom,
            'coefficient' => (float) $item->coefficient,
            'offset' => (float) $item->offset,
            'source_metier' => $item->source_metier,
            'version' => $item->version ?? 1,
            'est_active' => (bool) $item->est_active,
            'updated_label' => $item->created_at?->format('d/m/Y') ?? 'A jour',
        ];
    }
}

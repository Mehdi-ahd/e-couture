<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AnnotationPatron;
use App\Models\PiecePatron;
use App\Models\TypeMesure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controleur API mobile pour la gestion des annotations sur les pieces de patron.
 * Permet de lier des types de mesures avec des positions sur une piece de patron.
 */
class MobileAnnotationPatronController extends Controller
{
    public function index(string $piece): JsonResponse
    {
        $pieceRecord = PiecePatron::query()
            ->where('external_id', $piece)
            ->firstOrFail();

        $items = AnnotationPatron::query()
            ->with(['typeMesure', 'piecePatron'])
            ->where('piece_patron_id', $pieceRecord->id)
            ->orderBy('label')
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $items->count(),
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
                'items' => $items->map(fn (AnnotationPatron $item) => $this->serializeItem($item))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request, string $piece): JsonResponse
    {
        $pieceRecord = PiecePatron::query()
            ->where('external_id', $piece)
            ->firstOrFail();

        $data = $this->validatePayload($request);
        $data['piece_patron_id'] = $pieceRecord->id;

        $record = AnnotationPatron::query()->create($data);

        return response()->json([
            'message' => 'Annotation ajoutée.',
            'data' => [
                'item' => $this->serializeItem($record->load(['typeMesure', 'piecePatron'])),
            ],
        ], 201);
    }

    public function show(string $piece, string $annotation): JsonResponse
    {
        $record = AnnotationPatron::query()
            ->with(['typeMesure', 'piecePatron'])
            ->where('external_id', $annotation)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'item' => $this->serializeItem($record),
            ],
        ]);
    }

    public function update(Request $request, string $piece, string $annotation): JsonResponse
    {
        $record = AnnotationPatron::query()
            ->with(['typeMesure', 'piecePatron'])
            ->where('external_id', $annotation)
            ->firstOrFail();

        $record->fill($this->validatePayload($request, true))->save();

        return response()->json([
            'message' => 'Annotation mise à jour.',
            'data' => [
                'item' => $this->serializeItem($record->load(['typeMesure', 'piecePatron'])),
            ],
        ]);
    }

    public function destroy(string $piece, string $annotation): JsonResponse
    {
        $record = AnnotationPatron::query()
            ->where('external_id', $annotation)
            ->firstOrFail();

        $record->delete();

        return response()->json([
            'message' => 'Annotation supprimée.',
        ]);
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'type_mesure_id' => ['nullable', 'integer', 'exists:type_mesures,id'],
            'label' => [...$required, 'string', 'max:191'],
            'position_depart' => ['nullable', 'string', 'max:191'],
            'position_fin' => ['nullable', 'string', 'max:191'],
            'orientation' => ['nullable', 'string', 'max:20'],
        ]);
    }

    private function serializeItem(AnnotationPatron $item): array
    {
        return [
            'id' => $item->id,
            'external_id' => $item->external_id,
            'piece_patron_id' => $item->piece_patron_id,
            'piece_patron_label' => $item->piecePatron?->nom,
            'type_mesure_id' => $item->type_mesure_id,
            'type_mesure_label' => $item->typeMesure?->nom,
            'type_mesure_unite' => $item->typeMesure?->unite,
            'label' => $item->label,
            'position_depart' => $item->position_depart,
            'position_fin' => $item->position_fin,
            'orientation' => $item->orientation,
        ];
    }
}

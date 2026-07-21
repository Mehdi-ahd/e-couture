<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FormeDecoupe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controleur API mobile pour la gestion des formes de decoupe.
 * Permet de creer, modifier et supprimer les formes utilisees dans les dispositions de pieces.
 */
class MobileFormeDecoupeController extends Controller
{
    public function index(): JsonResponse
    {
        $items = FormeDecoupe::query()
            ->withCount(['materiaux', 'dispositionsPiecePatron'])
            ->orderBy('nom')
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $items->count(),
                    'global' => $items->where('est_global', true)->count(),
                ],
                'items' => $items->map(fn (FormeDecoupe $item) => $this->serializeItem($item))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $record = FormeDecoupe::query()->create($this->validatePayload($request));

        return response()->json([
            'message' => 'Forme de découpe ajoutée.',
            'data' => [
                'item' => $this->serializeItem($record->loadCount(['materiaux', 'dispositionsPiecePatron'])),
            ],
        ], 201);
    }

    public function show(string $formeDecoupe): JsonResponse
    {
        $record = FormeDecoupe::query()
            ->withCount(['materiaux', 'dispositionsPiecePatron'])
            ->where('external_id', $formeDecoupe)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'item' => $this->serializeItem($record),
            ],
        ]);
    }

    public function update(Request $request, string $formeDecoupe): JsonResponse
    {
        $record = FormeDecoupe::query()
            ->where('external_id', $formeDecoupe)
            ->firstOrFail();

        $record->fill($this->validatePayload($request, true))->save();

        return response()->json([
            'message' => 'Forme de découpe mise à jour.',
            'data' => [
                'item' => $this->serializeItem($record->loadCount(['materiaux', 'dispositionsPiecePatron'])),
            ],
        ]);
    }

    public function destroy(string $formeDecoupe): JsonResponse
    {
        $record = FormeDecoupe::query()
            ->withCount(['materiaux', 'dispositionsPiecePatron'])
            ->where('external_id', $formeDecoupe)
            ->firstOrFail();

        if (($record->materiaux_count ?? 0) > 0 || ($record->dispositions_piece_patron_count ?? 0) > 0) {
            return response()->json([
                'message' => 'Cette forme est déjà liée à des matériaux ou pièces.',
            ], 422);
        }

        $record->delete();

        return response()->json([
            'message' => 'Forme de découpe supprimée.',
        ]);
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'nom' => [...$required, 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'donnees_formes' => [...$required, 'array'],
            'miniature_url' => ['nullable', 'string', 'max:500'],
            'source' => [...$required, 'string', 'max:80'],
            'est_global' => ['sometimes', 'boolean'],
        ]);
    }
    
    private function serializeItem(FormeDecoupe $item): array
    {
        return [
            'id' => $item->id,
            'external_id' => $item->external_id,
            'nom' => $item->nom,
            'description' => $item->description,
            'donnees_formes' => $item->donnees_formes ?? [],
            'miniature_url' => $item->miniature_url,
            'source' => $item->source,
            'est_global' => (bool) $item->est_global,
            'materiaux_count' => $item->materiaux_count ?? 0,
            'dispositions_count' => $item->dispositions_piece_patron_count ?? 0,
            'updated_label' => $item->created_at?->format('d/m/Y') ?? 'A jour',
        ];
    }
}

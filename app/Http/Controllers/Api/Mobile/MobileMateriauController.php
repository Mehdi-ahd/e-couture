<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FormeDecoupe;
use App\Models\Materiau;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileMateriauController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Materiau::query()
            ->with(['formeDecoupe'])
            ->withCount('dispositionsPiecePatron')
            ->orderBy('nom')
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $items->count(),
                    'global' => $items->where('est_global', true)->count(),
                ],
                'available_formes' => FormeDecoupe::query()
                    ->orderBy('nom')
                    ->get()
                    ->map(fn (FormeDecoupe $shape) => [
                        'id' => $shape->id,
                        'external_id' => $shape->external_id,
                        'label' => $shape->nom,
                    ])
                    ->values()
                    ->all(),
                'items' => $items->map(fn (Materiau $item) => $this->serializeItem($item))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $record = Materiau::query()->create($this->validatePayload($request));

        return response()->json([
            'message' => 'Matériau ajouté.',
            'data' => [
                'item' => $this->serializeItem($record->load(['formeDecoupe'])->loadCount('dispositionsPiecePatron')),
            ],
        ], 201);
    }

    public function show(string $materiau): JsonResponse
    {
        $record = Materiau::query()
            ->with(['formeDecoupe'])
            ->withCount('dispositionsPiecePatron')
            ->where('external_id', $materiau)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'item' => $this->serializeItem($record),
            ],
        ]);
    }

    public function update(Request $request, string $materiau): JsonResponse
    {
        $record = Materiau::query()
            ->where('external_id', $materiau)
            ->firstOrFail();

        $record->fill($this->validatePayload($request, true))->save();

        return response()->json([
            'message' => 'Matériau mis à jour.',
            'data' => [
                'item' => $this->serializeItem($record->load(['formeDecoupe'])->loadCount('dispositionsPiecePatron')),
            ],
        ]);
    }

    public function destroy(string $materiau): JsonResponse
    {
        $record = Materiau::query()
            ->withCount('dispositionsPiecePatron')
            ->where('external_id', $materiau)
            ->firstOrFail();

        if (($record->dispositions_piece_patron_count ?? 0) > 0) {
            return response()->json([
                'message' => 'Ce matériau est déjà utilisé dans des pièces de patron.',
            ], 422);
        }

        $record->delete();

        return response()->json([
            'message' => 'Matériau supprimé.',
        ]);
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'nom' => [...$required, 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'type' => [...$required, 'string', 'max:80'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'est_global' => ['sometimes', 'boolean'],
            'forme_decoupe_id' => ['nullable', 'integer', 'exists:formes_decoupe,id'],
        ]);
    }

    private function serializeItem(Materiau $item): array
    {
        return [
            'id' => $item->id,
            'external_id' => $item->external_id,
            'nom' => $item->nom,
            'description' => $item->description,
            'type' => $item->type,
            'image_url' => $item->image_url,
            'est_global' => (bool) $item->est_global,
            'forme_decoupe_id' => $item->forme_decoupe_id,
            'forme_decoupe_label' => $item->formeDecoupe?->nom,
            'dispositions_count' => $item->dispositions_piece_patron_count ?? 0,
            'updated_label' => $item->created_at?->format('d/m/Y') ?? 'A jour',
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\TypeVetement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Controleur API mobile pour la gestion des types de vetements.
 * Permet de creer, modifier et supprimer les categories de vetements.
 */
class MobileTypeVetementController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TypeVetement::query()
            ->withCount('modelesVetements')
            ->orderBy('nom')
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $items->count(),
                    'active' => $items->where('est_actif', true)->count(),
                ],
                'items' => $items->map(fn (TypeVetement $item) => $this->serializeItem($item))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $record = TypeVetement::query()->create($this->validatePayload($request));

        return response()->json([
            'message' => 'Type de vêtement ajouté.',
            'data' => [
                'item' => $this->serializeItem($record->loadCount('modelesVetements')),
            ],
        ], 201);
    }

    public function show(string $typeVetement): JsonResponse
    {
        $record = TypeVetement::query()
            ->withCount('modelesVetements')
            ->where('external_id', $typeVetement)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'item' => $this->serializeItem($record),
            ],
        ]);
    }

    public function update(Request $request, string $typeVetement): JsonResponse
    {
        $record = TypeVetement::query()
            ->where('external_id', $typeVetement)
            ->firstOrFail();

        $record->fill($this->validatePayload($request, true))->save();

        return response()->json([
            'message' => 'Type de vêtement mis à jour.',
            'data' => [
                'item' => $this->serializeItem($record->loadCount('modelesVetements')),
            ],
        ]);
    }

    public function destroy(string $typeVetement): JsonResponse
    {
        $record = TypeVetement::query()
            ->withCount('modelesVetements')
            ->where('external_id', $typeVetement)
            ->firstOrFail();

        if ($record->modeles_vetements_count > 0) {
            return response()->json([
                'message' => 'Ce type est déjà utilisé par des modèles.',
            ], 422);
        }

        $record->delete();

        return response()->json([
            'message' => 'Type de vêtement supprimé.',
        ]);
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];
        $recordId = $request->route('type_vetement');

        return $request->validate([
            'code' => [
                ...$required,
                'string',
                'max:50',
                Rule::unique('type_vetements', 'code')->ignore($recordId, 'external_id'),
            ],
            'nom' => [...$required, 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'est_actif' => ['sometimes', 'boolean'],
        ]);
    }

    private function serializeItem(TypeVetement $item): array
    {
        return [
            'id' => $item->id,
            'external_id' => $item->external_id,
            'code' => $item->code,
            'nom' => $item->nom,
            'description' => $item->description,
            'est_actif' => (bool) $item->est_actif,
            'modeles_count' => $item->modeles_vetements_count ?? 0,
            'updated_label' => $item->updated_at?->format('d/m/Y') ?? 'A jour',
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\TypeMesure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controleur API mobile pour la gestion des types de mesures.
 * Permet de creer, modifier et supprimer les types de mesures corporelles.
 */
class MobileTypeMesureController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TypeMesure::query()
            ->withCount(['mesures', 'mesureModeles', 'annotationPatrons', 'regleProportions'])
            ->orderBy('nom')
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $items->count(),
                    'active' => $items->where('est_actif', true)->count(),
                ],
                'items' => $items->map(fn (TypeMesure $item) => $this->serializeItem($item))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $record = TypeMesure::query()->create($this->validatePayload($request));

        return response()->json([
            'message' => 'Type de mesure ajouté.',
            'data' => [
                'item' => $this->serializeItem($record->loadCount(['mesures', 'mesureModeles', 'annotationPatrons', 'regleProportions'])),
            ],
        ], 201);
    }

    public function show(string $typeMesure): JsonResponse
    {
        $record = TypeMesure::query()
            ->withCount(['mesures', 'mesureModeles', 'annotationPatrons', 'regleProportions'])
            ->where('external_id', $typeMesure)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'item' => $this->serializeItem($record),
            ],
        ]);
    }

    public function update(Request $request, string $typeMesure): JsonResponse
    {
        $record = TypeMesure::query()
            ->where('external_id', $typeMesure)
            ->firstOrFail();

        $record->fill($this->validatePayload($request, true))->save();

        return response()->json([
            'message' => 'Type de mesure mis à jour.',
            'data' => [
                'item' => $this->serializeItem($record->loadCount(['mesures', 'mesureModeles', 'annotationPatrons', 'regleProportions'])),
            ],
        ]);
    }

    public function destroy(string $typeMesure): JsonResponse
    {
        $record = TypeMesure::query()
            ->withCount(['mesures', 'mesureModeles', 'annotationPatrons', 'regleProportions'])
            ->where('external_id', $typeMesure)
            ->firstOrFail();

        if (($record->mesures_count ?? 0) > 0) {
            return response()->json([
                'message' => 'Ce type de mesure est déjà utilisé dans des fiches de mesures.',
            ], 422);
        }

        $record->delete();

        return response()->json([
            'message' => 'Type de mesure supprimé.',
        ]);
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'code' => [...$required, 'string', 'max:50', 'unique:type_mesures,code'.($isUpdate ? ','.$request->route('typeMesure').',external_id' : '')],
            'nom' => [...$required, 'string', 'max:191'],
            'unite' => [...$required, 'string', 'max:20'],
            'categorie' => [...$required, 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'est_actif' => ['sometimes', 'boolean'],
        ]);
    }

    private function serializeItem(TypeMesure $item): array
    {
        return [
            'id' => $item->id,
            'external_id' => $item->external_id,
            'code' => $item->code,
            'nom' => $item->nom,
            'unite' => $item->unite,
            'categorie' => $item->categorie,
            'description' => $item->description,
            'est_actif' => (bool) $item->est_actif,
            'mesures_count' => $item->mesures_count ?? 0,
            'mesure_modeles_count' => $item->mesure_modeles_count ?? 0,
            'regle_proportions_count' => $item->regle_proportions_count ?? 0,
        ];
    }
}

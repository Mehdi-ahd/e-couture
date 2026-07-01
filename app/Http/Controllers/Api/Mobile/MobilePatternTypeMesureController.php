<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MesureModele;
use App\Models\Patron;
use App\Models\TypeMesure;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePatternTypeMesureController extends Controller
{
    public function index(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $patron = $this->findForUser($user, $pattern);
        $modele = $patron->modeleVetement;

        if ($modele === null) {
            return response()->json([
                'data' => [
                    'items' => [],
                ],
            ]);
        }

        $items = MesureModele::query()
            ->where('modele_vetement_id', $modele->id)
            ->with('typeMesure')
            ->get()
            ->map(fn (MesureModele $mm) => $this->serializeItem($mm));

        return response()->json([
            'data' => [
                'items' => $items->values()->all(),
            ],
        ]);
    }

    public function sync(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $patron = $this->findForUser($user, $pattern);
        $modele = $patron->modeleVetement;

        if ($modele === null) {
            return response()->json([
                'message' => 'Aucun modèle associé à ce patron.',
            ], 422);
        }

        $validated = $request->validate([
            'type_mesure_ids' => ['required', 'array'],
            'type_mesure_ids.*' => ['required', 'string', 'exists:type_mesures,external_id'],
        ]);

        $resolvedIds = TypeMesure::query()
            ->whereIn('external_id', $validated['type_mesure_ids'])
            ->pluck('id');

        if ($resolvedIds->isEmpty()) {
            MesureModele::query()
                ->where('modele_vetement_id', $modele->id)
                ->delete();
        } else {
            MesureModele::query()
                ->where('modele_vetement_id', $modele->id)
                ->whereNotIn('type_mesure_id', $resolvedIds)
                ->delete();
        }

        $existingIds = MesureModele::query()
            ->where('modele_vetement_id', $modele->id)
            ->pluck('type_mesure_id')
            ->toArray();

        foreach ($resolvedIds as $typeMesureId) {
            if (! in_array($typeMesureId, $existingIds, true)) {
                MesureModele::query()->create([
                    'modele_vetement_id' => $modele->id,
                    'type_mesure_id' => $typeMesureId,
                    'valeur' => null,
                ]);
            }
        }

        $items = MesureModele::query()
            ->where('modele_vetement_id', $modele->id)
            ->with('typeMesure')
            ->get()
            ->map(fn (MesureModele $mm) => $this->serializeItem($mm));

        return response()->json([
            'message' => 'Types de mesures synchronisés.',
            'data' => [
                'items' => $items->values()->all(),
            ],
        ]);
    }

    private function findForUser(User $user, string $externalId): Patron
    {
        /** @var Patron $patron */
        $patron = Patron::query()
            ->where('external_id', $externalId)
            ->whereHas('modeleVetement', function (Builder $query) use ($user): void {
                $query->where(function (Builder $subQuery) use ($user): void {
                    $subQuery
                        ->whereNull('prestataire_id')
                        ->orWhere('prestataire_id', $user->id);
                });
            })
            ->with('modeleVetement')
            ->firstOrFail();

        return $patron;
    }

    private function serializeItem(MesureModele $mm): array
    {
        /** @var TypeMesure $typeMesure */
        $typeMesure = $mm->typeMesure;

        return [
            'id' => $mm->id,
            'external_id' => $mm->external_id,
            'type_mesure_id' => $typeMesure->id,
            'type_mesure_external_id' => $typeMesure->external_id,
            'type_mesure_code' => $typeMesure->code,
            'type_mesure_nom' => $typeMesure->nom,
            'type_mesure_unite' => $typeMesure->unite,
            'type_mesure_categorie' => $typeMesure->categorie,
            'type_mesure_est_actif' => (bool) $typeMesure->est_actif,
            'valeur' => $mm->valeur,
            'notes' => $mm->notes,
        ];
    }
}

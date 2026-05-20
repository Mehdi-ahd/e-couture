<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FormeDecoupe;
use App\Models\Materiau;
use App\Models\Patron;
use App\Models\PiecePatron;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePatternPieceController extends Controller
{
    public function index(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findPatternForUser($user, $pattern);
        $pieces = $this->pieceQuery($record)->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $pieces->count(),
                    'placements' => $pieces->sum(fn (PiecePatron $piece) => $piece->dispositions->count()),
                ],
                'pattern' => $this->serializePattern($record),
                'available_shapes' => $this->serializeShapeOptions(),
                'available_materials' => $this->serializeMaterialOptions(),
                'items' => $pieces->map(fn (PiecePatron $piece) => $this->serializePiece($piece))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedPatternForUser($user, $pattern);
        $validated = $this->validatePayload($request);

        $piece = $record->piecePatrons()->create([
            'nom' => $validated['nom'],
            'ordre' => $validated['ordre'],
            'donnees_geometriques' => $validated['donnees_geometriques'],
        ]);

        $piece = $this->pieceQuery($record)->whereKey($piece->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Pièce ajoutée au patron.',
            'data' => [
                'item' => $this->serializePiece($piece),
            ],
        ], 201);
    }

    public function show(Request $request, string $pattern, string $piece): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findPatternForUser($user, $pattern);
        $item = $this->findPieceForPattern($record, $piece);

        return response()->json([
            'data' => [
                'item' => $this->serializePiece($item),
                'detail' => [
                    'dispositions' => $item->dispositions
                        ->sortBy('ordre')
                        ->map(fn ($disposition) => [
                            'external_id' => $disposition->external_id,
                            'forme_decoupe_id' => $disposition->forme_decoupe_id,
                            'ordre' => $disposition->ordre,
                            'forme_label' => $disposition->formeDecoupe?->nom ?? 'Forme',
                            'materiau_id' => $disposition->materiau_id,
                            'materiau_label' => $disposition->materiau?->nom ?? 'Sans matériau',
                            'position_x' => (double) $disposition->position_x,
                            'position_y' => (double) $disposition->position_y,
                            'rotation' => (double) $disposition->rotation,
                            'echelle' => (double) $disposition->echelle,
                        ])
                        ->values()
                        ->all(),
                ],
                'options' => [
                    'pattern' => $this->serializePattern($record),
                    'available_shapes' => $this->serializeShapeOptions(),
                    'available_materials' => $this->serializeMaterialOptions(),
                ],
            ],
        ]);
    }

    public function update(Request $request, string $pattern, string $piece): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedPatternForUser($user, $pattern);
        $item = $this->findPieceForPattern($record, $piece);
        $validated = $this->validatePayload($request, true);

        $item->fill([
            'nom' => $validated['nom'] ?? $item->nom,
            'ordre' => $validated['ordre'] ?? $item->ordre,
            'donnees_geometriques' => $validated['donnees_geometriques'] ?? $item->donnees_geometriques,
        ])->save();

        $item = $this->pieceQuery($record)->whereKey($item->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Pièce mise à jour.',
            'data' => [
                'item' => $this->serializePiece($item),
            ],
        ]);
    }

    public function destroy(Request $request, string $pattern, string $piece): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedPatternForUser($user, $pattern);
        $item = $this->findPieceForPattern($record, $piece);
        $item->delete();

        return response()->json([
            'message' => 'Pièce supprimée.',
        ]);
    }

    private function findPatternForUser(User $user, string $externalId): Patron
    {
        return Patron::query()
            ->where('external_id', $externalId)
            ->whereHas('modeleVetement', function (Builder $query) use ($user): void {
                $query->where(function (Builder $subQuery) use ($user): void {
                    $subQuery->whereNull('prestataire_id')->orWhere('prestataire_id', $user->id);
                });
            })
            ->with('modeleVetement')
            ->firstOrFail();
    }

    private function findOwnedPatternForUser(User $user, string $externalId): Patron
    {
        return Patron::query()
            ->where('external_id', $externalId)
            ->whereHas('modeleVetement', fn (Builder $query) => $query->where('prestataire_id', $user->id))
            ->with('modeleVetement')
            ->firstOrFail();
    }

    private function pieceQuery(Patron $pattern)
    {
        return PiecePatron::query()
            ->where('patron_id', $pattern->id)
            ->with(['dispositions.formeDecoupe', 'dispositions.materiau'])
            ->orderBy('ordre');
    }

    private function findPieceForPattern(Patron $pattern, string $externalId): PiecePatron
    {
        return $this->pieceQuery($pattern)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'nom' => [...$required, 'string', 'max:191'],
            'ordre' => [...$required, 'integer', 'min:0'],
            'donnees_geometriques' => [...$required, 'array'],
        ]);
    }

    private function serializePattern(Patron $pattern): array
    {
        return [
            'external_id' => $pattern->external_id,
            'title' => $pattern->modeleVetement?->nom ?? 'Patron',
        ];
    }

    private function serializePiece(PiecePatron $piece): array
    {
        return [
            'external_id' => $piece->external_id,
            'name' => $piece->nom,
            'order' => $piece->ordre,
            'geometry' => $piece->donnees_geometriques ?? [],
            'dispositions_count' => $piece->dispositions->count(),
            'material_labels' => $piece->dispositions->pluck('materiau.nom')->filter()->unique()->values()->all(),
            'cut_labels' => $piece->dispositions->pluck('formeDecoupe.nom')->filter()->unique()->values()->all(),
        ];
    }

    private function serializeShapeOptions(): array
    {
        return FormeDecoupe::query()
            ->orderBy('nom')
            ->get()
            ->map(fn (FormeDecoupe $shape) => [
                'id' => $shape->id,
                'external_id' => $shape->external_id,
                'label' => $shape->nom,
            ])
            ->values()
            ->all();
    }

    private function serializeMaterialOptions(): array
    {
        return Materiau::query()
            ->with('formeDecoupe')
            ->orderBy('nom')
            ->get()
            ->map(fn (Materiau $material) => [
                'id' => $material->id,
                'external_id' => $material->external_id,
                'label' => $material->nom,
                'subtitle' => $material->formeDecoupe?->nom ?? $material->type,
            ])
            ->values()
            ->all();
    }
}

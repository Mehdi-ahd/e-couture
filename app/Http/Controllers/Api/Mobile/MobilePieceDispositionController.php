<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DispositionPiecePatron;
use App\Models\FormeDecoupe;
use App\Models\Materiau;
use App\Models\PiecePatron;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePieceDispositionController extends Controller
{
    public function index(Request $request, string $piece): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findPieceForUser($user, $piece);
        $items = $this->dispositionQuery($record)->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $items->count(),
                ],
                'piece' => $this->serializePiece($record),
                'available_shapes' => $this->serializeShapeOptions(),
                'available_materials' => $this->serializeMaterialOptions(),
                'items' => $items->map(fn (DispositionPiecePatron $item) => $this->serializeDisposition($item))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request, string $piece): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedPieceForUser($user, $piece);
        $validated = $this->validatePayload($request);

        $item = $record->dispositions()->create($validated);
        $item = $this->dispositionQuery($record)->whereKey($item->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Placement ajouté.',
            'data' => [
                'item' => $this->serializeDisposition($item),
            ],
        ], 201);
    }

    public function show(Request $request, string $piece, string $disposition): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findPieceForUser($user, $piece);
        $item = $this->findDispositionForPiece($record, $disposition);

        return response()->json([
            'data' => [
                'item' => $this->serializeDisposition($item),
                'options' => [
                    'piece' => $this->serializePiece($record),
                    'available_shapes' => $this->serializeShapeOptions(),
                    'available_materials' => $this->serializeMaterialOptions(),
                ],
            ],
        ]);
    }

    public function update(Request $request, string $piece, string $disposition): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedPieceForUser($user, $piece);
        $item = $this->findDispositionForPiece($record, $disposition);
        $validated = $this->validatePayload($request, true);

        $item->fill($validated)->save();
        $item = $this->dispositionQuery($record)->whereKey($item->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Placement mis à jour.',
            'data' => [
                'item' => $this->serializeDisposition($item),
            ],
        ]);
    }

    public function destroy(Request $request, string $piece, string $disposition): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedPieceForUser($user, $piece);
        $item = $this->findDispositionForPiece($record, $disposition);
        $item->delete();

        return response()->json([
            'message' => 'Placement supprimé.',
        ]);
    }

    private function findPieceForUser(User $user, string $externalId): PiecePatron
    {
        return PiecePatron::query()
            ->where('external_id', $externalId)
            ->whereHas('patron.modeleVetement', function (Builder $query) use ($user): void {
                $query->where(function (Builder $subQuery) use ($user): void {
                    $subQuery->whereNull('prestataire_id')->orWhere('prestataire_id', $user->id);
                });
            })
            ->with('patron.modeleVetement')
            ->firstOrFail();
    }

    private function findOwnedPieceForUser(User $user, string $externalId): PiecePatron
    {
        return PiecePatron::query()
            ->where('external_id', $externalId)
            ->whereHas('patron.modeleVetement', fn (Builder $query) => $query->where('prestataire_id', $user->id))
            ->with('patron.modeleVetement')
            ->firstOrFail();
    }

    private function dispositionQuery(PiecePatron $piece)
    {
        return DispositionPiecePatron::query()
            ->where('piece_patron_id', $piece->id)
            ->with(['formeDecoupe', 'materiau'])
            ->orderBy('ordre');
    }

    private function findDispositionForPiece(PiecePatron $piece, string $externalId): DispositionPiecePatron
    {
        return $this->dispositionQuery($piece)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'forme_decoupe_id' => [...$required, 'integer', 'exists:formes_decoupe,id'],
            'materiau_id' => ['nullable', 'integer', 'exists:materiaux,id'],
            'position_x' => [...$required, 'numeric'],
            'position_y' => [...$required, 'numeric'],
            'rotation' => [...$required, 'numeric'],
            'echelle' => [...$required, 'numeric', 'gt:0'],
            'ordre' => [...$required, 'integer', 'min:0'],
        ]);
    }

    private function serializePiece(PiecePatron $piece): array
    {
        return [
            'external_id' => $piece->external_id,
            'name' => $piece->nom,
            'pattern_title' => $piece->patron?->modeleVetement?->nom ?? 'Patron',
        ];
    }

    private function serializeDisposition(DispositionPiecePatron $item): array
    {
        return [
            'external_id' => $item->external_id,
            'forme_decoupe_id' => $item->forme_decoupe_id,
            'forme_label' => $item->formeDecoupe?->nom ?? 'Forme',
            'materiau_id' => $item->materiau_id,
            'materiau_label' => $item->materiau?->nom ?? 'Sans matériau',
            'position_x' => (double) $item->position_x,
            'position_y' => (double) $item->position_y,
            'rotation' => (double) $item->rotation,
            'echelle' => (double) $item->echelle,
            'ordre' => $item->ordre,
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
            ->orderBy('nom')
            ->get()
            ->map(fn (Materiau $material) => [
                'id' => $material->id,
                'external_id' => $material->external_id,
                'label' => $material->nom,
                'subtitle' => $material->type,
            ])
            ->values()
            ->all();
    }
}

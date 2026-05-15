<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ModeleVetement;
use App\Models\Patron;
use App\Models\PiecePatron;
use App\Models\TypeVetement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePatternController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $patterns = $this->baseQueryForUser($user)
            ->latest('created_at')
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $patterns->count(),
                    'editable' => $patterns
                        ->filter(fn (Patron $pattern) => $pattern->modeleVetement?->prestataire_id === $user->id)
                        ->count(),
                ],
                'available_types' => TypeVetement::query()
                    ->where('est_actif', true)
                    ->orderBy('nom')
                    ->get()
                    ->map(fn (TypeVetement $type) => [
                        'id' => $type->id,
                        'external_id' => $type->external_id,
                        'code' => $type->code,
                        'label' => $type->nom,
                    ])
                    ->values()
                    ->all(),
                'items' => $patterns->map(fn (Patron $pattern) => $this->serializePattern($pattern, $user))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $this->validatePayload($request);

        $modele = ModeleVetement::query()->create([
            'nom' => $validated['nom'],
            'description' => $validated['description'],
            'portee' => $validated['portee'],
            'statut' => $validated['modele_statut'] ?? 'brouillon',
            'prestataire_id' => $user->id,
            'type_vetement_id' => $validated['type_vetement_id'],
        ]);

        $pattern = Patron::query()->create([
            'methode' => $validated['methode'],
            'version' => $validated['version'] ?? 1,
            'fichier_url' => $validated['fichier_url'] ?? null,
            'donnees_dessin' => $validated['donnees_dessin'] ?? null,
            'statut' => $validated['statut'] ?? 'brouillon',
            'model_vetement_id' => $modele->id,
        ]);

        $pattern = $this->baseQueryForUser($user)
            ->whereKey($pattern->getKey())
            ->firstOrFail();

        return response()->json([
            'message' => 'Patron ajoute.',
            'data' => [
                'item' => $this->serializePattern($pattern, $user),
            ],
        ], 201);
    }

    public function show(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findDetailedForUser($user, $pattern);
        $materialLabels = $record->piecesPatrons
            ->flatMap(fn (PiecePatron $piece) => $piece->dispositions->pluck('materiau.nom'))
            ->filter()
            ->unique()
            ->values();
        $cutLabels = $record->piecesPatrons
            ->flatMap(fn (PiecePatron $piece) => $piece->dispositions->pluck('formeDecoupe.nom'))
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'data' => [
                'item' => $this->serializePattern($record, $user),
                'detail' => [
                    'updated_label' => $record->modeleVetement?->updated_at?->format('d/m/Y') ?? 'A jour',
                    'owner_label' => $record->modeleVetement?->prestataire_id === $user->id
                        ? 'Atelier'
                        : 'Bibliothèque',
                    'material_labels' => $materialLabels->all(),
                    'cut_labels' => $cutLabels->all(),
                    'pieces' => $record->piecesPatrons
                        ->sortBy('ordre')
                        ->map(fn (PiecePatron $piece) => [
                            'external_id' => $piece->external_id,
                            'name' => $piece->nom,
                            'order' => $piece->ordre,
                            'dispositions_count' => $piece->dispositions->count(),
                            'material_labels' => $piece->dispositions
                                ->pluck('materiau.nom')
                                ->filter()
                                ->unique()
                                ->values()
                                ->all(),
                            'cut_labels' => $piece->dispositions
                                ->pluck('formeDecoupe.nom')
                                ->filter()
                                ->unique()
                                ->values()
                                ->all(),
                        ])
                        ->values()
                        ->all(),
                ],
            ],
        ]);
    }

    public function update(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedForUser($user, $pattern);
        $validated = $this->validatePayload($request, true);

        $record->modeleVetement?->fill([
            'nom' => $validated['nom'] ?? $record->modeleVetement?->nom,
            'description' => $validated['description'] ?? $record->modeleVetement?->description,
            'portee' => $validated['portee'] ?? $record->modeleVetement?->portee,
            'statut' => $validated['modele_statut'] ?? $record->modeleVetement?->statut,
            'type_vetement_id' => $validated['type_vetement_id'] ?? $record->modeleVetement?->type_vetement_id,
        ])?->save();

        $record->fill([
            'methode' => $validated['methode'] ?? $record->methode,
            'version' => $validated['version'] ?? $record->version,
            'fichier_url' => $validated['fichier_url'] ?? $record->fichier_url,
            'donnees_dessin' => $validated['donnees_dessin'] ?? $record->donnees_dessin,
            'statut' => $validated['statut'] ?? $record->statut,
        ])->save();

        $record = $this->baseQueryForUser($user)
            ->whereKey($record->getKey())
            ->firstOrFail();

        return response()->json([
            'message' => 'Patron mis a jour.',
            'data' => [
                'item' => $this->serializePattern($record, $user),
            ],
        ]);
    }

    public function destroy(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedForUser($user, $pattern);

        $record->forceFill(['statut' => 'archive'])->save();
        $record->modeleVetement?->forceFill(['statut' => 'archive'])->save();

        return response()->json([
            'message' => 'Patron archive.',
        ]);
    }

    private function baseQueryForUser(User $user): Builder
    {
        return Patron::query()
            ->whereHas('modeleVetement', function ($query) use ($user): void {
                $query->where(function ($subQuery) use ($user): void {
                    $subQuery
                        ->whereNull('prestataire_id')
                        ->orWhere('prestataire_id', $user->id);
                });
            })
            ->with([
                'modeleVetement.typeVetement',
                'piecesPatrons.dispositions',
            ]);
    }

    private function findForUser(User $user, string $externalId): Patron
    {
        return $this->baseQueryForUser($user)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function findDetailedForUser(User $user, string $externalId): Patron
    {
        return $this->baseQueryForUser($user)
            ->where('external_id', $externalId)
            ->with([
                'piecesPatrons.dispositions.materiau',
                'piecesPatrons.dispositions.formeDecoupe',
            ])
            ->firstOrFail();
    }

    private function findOwnedForUser(User $user, string $externalId): Patron
    {
        return $this->baseQueryForUser($user)
            ->where('external_id', $externalId)
            ->whereHas('modeleVetement', fn ($query) => $query->where('prestataire_id', $user->id))
            ->firstOrFail();
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'nom' => [...$required, 'string', 'max:191'],
            'description' => [...$required, 'string'],
            'type_vetement_id' => [...$required, 'integer', 'exists:type_vetements,id'],
            'portee' => [...$required, 'string', 'max:40'],
            'modele_statut' => ['sometimes', 'string', 'max:40'],
            'methode' => [...$required, 'string', 'max:40'],
            'version' => ['sometimes', 'integer', 'min:1'],
            'fichier_url' => ['nullable', 'string', 'max:500'],
            'donnees_dessin' => ['nullable', 'array'],
            'statut' => ['sometimes', 'string', 'max:40'],
        ]);
    }

    private function serializePattern(Patron $pattern, User $user): array
    {
        $pieceCount = $pattern->piecesPatrons->count();
        $materialCount = $pattern->piecesPatrons
            ->flatMap(fn ($piece) => $piece->dispositions)
            ->pluck('materiau_id')
            ->filter()
            ->unique()
            ->count();
        $isEditable = $pattern->modeleVetement?->prestataire_id === $user->id;

        return [
            'external_id' => $pattern->external_id,
            'model_external_id' => $pattern->modeleVetement?->external_id,
            'title' => $pattern->modeleVetement?->nom ?? 'Patron',
            'description' => $pattern->modeleVetement?->description ?? '',
            'type_vetement_id' => $pattern->modeleVetement?->type_vetement_id,
            'type_label' => $pattern->modeleVetement?->typeVetement?->nom ?? 'Type libre',
            'portee' => $pattern->modeleVetement?->portee ?? 'prive',
            'portee_label' => ucfirst((string) $pattern->modeleVetement?->portee),
            'methode' => $pattern->methode,
            'methode_label' => ucfirst((string) $pattern->methode),
            'version' => $pattern->version,
            'statut' => $pattern->statut,
            'status_label' => ucfirst((string) $pattern->statut),
            'status_tone' => $this->mapStatusTone((string) $pattern->statut),
            'pieces_count' => $pieceCount,
            'materials_count' => $materialCount,
            'pieces_label' => sprintf('%d pièce%s', $pieceCount, $pieceCount > 1 ? 's' : ''),
            'materials_label' => sprintf('%d matériau%s', $materialCount, $materialCount > 1 ? 'x' : ''),
            'fichier_url' => $pattern->fichier_url,
            'is_editable' => $isEditable,
            'updated_label' => $pattern->created_at?->format('d/m/Y') ?? 'A jour',
            'model' => [
                'id' => $pattern->modeleVetement?->id,
                'external_id' => $pattern->modeleVetement?->external_id,
                'nom' => $pattern->modeleVetement?->nom,
                'description' => $pattern->modeleVetement?->description,
                'portee' => $pattern->modeleVetement?->portee,
                'statut' => $pattern->modeleVetement?->statut,
                'prestataire_id' => $pattern->modeleVetement?->prestataire_id,
                'type_vetement_id' => $pattern->modeleVetement?->type_vetement_id,
                'created_at' => $pattern->modeleVetement?->created_at?->toISOString(),
                'updated_at' => $pattern->modeleVetement?->updated_at?->toISOString(),
            ],
            'patron' => [
                'id' => $pattern->id,
                'external_id' => $pattern->external_id,
                'methode' => $pattern->methode,
                'version' => $pattern->version,
                'fichier_url' => $pattern->fichier_url,
                'donnees_dessin' => $pattern->donnees_dessin,
                'statut' => $pattern->statut,
                'model_vetement_id' => $pattern->model_vetement_id,
                'created_at' => $pattern->created_at?->toISOString(),
            ],
        ];
    }

    private function mapStatusTone(string $status): string
    {
        return match ($status) {
            'valide', 'publie' => 'success',
            'archive' => 'warning',
            default => 'info',
        };
    }
}

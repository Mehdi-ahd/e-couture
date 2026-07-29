<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Patron;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePatternKodaController extends Controller
{
    public function upload(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findOwnedForUser($user, $pattern);

        $validated = $request->validate([
            'fichier_koda' => ['required', 'string'],
        ]);

        $record->forceFill(['fichier_koda' => $validated['fichier_koda']])->save();

        return response()->json([
            'message' => 'Fichier Koda enregistre.',
        ]);
    }

    public function download(Request $request, string $pattern): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findForUser($user, $pattern);

        if ($record->fichier_koda === null) {
            return response()->json([
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => [
                'fichier_koda' => $record->fichier_koda,
                'external_id' => $record->external_id,
                'updated_at' => $record->created_at?->toISOString(),
            ],
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
            });
    }

    private function findForUser(User $user, string $externalId): Patron
    {
        return $this->baseQueryForUser($user)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function findOwnedForUser(User $user, string $externalId): Patron
    {
        return $this->baseQueryForUser($user)
            ->where('external_id', $externalId)
            ->whereHas('modeleVetement', fn ($query) => $query->where('prestataire_id', $user->id))
            ->firstOrFail();
    }
}

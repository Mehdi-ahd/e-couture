<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CommandeVetement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePatternPreviewController extends Controller
{
    public function preview(Request $request, string $client, string $order): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $commande = $this->findForUser($user, $client, $order);

        $patron = $commande->modeleVetement?->patron;

        if ($patron === null || $patron->fichier_koda === null) {
            return response()->json([
                'data' => null,
                'message' => 'Aucun fichier patron disponible pour cette commande.',
            ]);
        }

        $ficheMesure = $commande->ficheMesure;

        $mesures = [];
        if ($ficheMesure !== null) {
            foreach ($ficheMesure->mesures as $mesure) {
                if ($mesure->typeMesure === null) {
                    continue;
                }
                $mesures[] = [
                    'type_mesure_external_id' => $mesure->typeMesure->external_id,
                    'type_mesure_code' => $mesure->typeMesure->code,
                    'type_mesure_nom' => $mesure->typeMesure->nom,
                    'type_mesure_unite' => $mesure->typeMesure->unite,
                    'valeur' => $mesure->valeur,
                ];
            }
        }

        return response()->json([
            'data' => [
                'commande' => [
                    'external_id' => $commande->external_id,
                    'reference' => sprintf('CMD-%04d', $commande->id),
                    'client_nom' => $commande->client !== null
                        ? trim(($commande->client->prenom ?? '').' '.($commande->client->nom ?? ''))
                        : '',
                    'modele_nom' => $commande->modeleVetement?->nom ?? '',
                    'fiche_date' => $ficheMesure?->date?->format('d/m/Y'),
                ],
                'fichier_koda' => $patron->fichier_koda,
                'mesures' => $mesures,
            ],
        ]);
    }

    private function findForUser(User $user, string $clientId, string $orderId): CommandeVetement
    {
        /** @var CommandeVetement $commande */
        $commande = CommandeVetement::query()
            ->whereHas('client', function (Builder $query) use ($user, $clientId): void {
                $query
                    ->where('prestataire_id', $user->id)
                    ->where('external_id', $clientId);
            })
            ->where('external_id', $orderId)
            ->with([
                'client',
                'modeleVetement.patron',
                'ficheMesure.mesures.typeMesure',
            ])
            ->firstOrFail();

        return $commande;
    }
}

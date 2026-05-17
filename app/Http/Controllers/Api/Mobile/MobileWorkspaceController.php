<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CommandeVetement;
use App\Models\Patron;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileWorkspaceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $activeClientCount = Client::query()
            ->where('prestataire_id', $user->id)
            ->where('est_actif', true)
            ->count();

        $pendingMeasurementCount = $user->fichesMesures()
            ->where(function ($query): void {
                $query
                    ->whereIn('statut', ['brouillon', 'archive'])
                    ->orWhere('statut_traitement', 'en_attente');
            })
            ->count();

        $patternsQuery = Patron::query()
            ->whereHas('modeleVetement', function ($query) use ($user): void {
                $query->where(function ($subQuery) use ($user): void {
                    $subQuery
                        ->whereNull('prestataire_id')
                        ->orWhere('prestataire_id', $user->id);
                });
            });

        $patternCount = (clone $patternsQuery)->count();

        $patterns = $patternsQuery
            ->with(['modeleVetement', 'piecesPatrons.dispositions'])
            ->latest('created_at')
            ->take(6)
            ->get();

        $recentClients = Client::query()
            ->where('prestataire_id', $user->id)
            ->where('est_actif', true)
            ->with([
                'fichesMesures' => fn ($query) => $query->latest('date')->limit(1),
                'commandesVetements' => fn ($query) => $query->latest('date_commande')->limit(1),
                'commandesVetements.modeleVetement',
            ])
            ->latest('updated_at')
            ->take(5)
            ->get();

        $recentOrders = CommandeVetement::query()
            ->whereHas('client', fn ($query) => $query->where('prestataire_id', $user->id))
            ->with(['client', 'modeleVetement'])
            ->latest('date_commande')
            ->take(4)
            ->get();

        return response()->json([
            'data' => [
                'active_client_count' => $activeClientCount,
                'pending_measurement_count' => $pendingMeasurementCount,
                'pattern_count' => $patternCount,
                'recent_clients' => $recentClients->map(function (Client $client): array {
                    $latestMeasure = $client->fichesMesures->first();
                    $latestCommand = $client->commandesVetements->first();

                    return [
                        'name' => trim($client->prenom.' '.$client->nom),
                        'telephone' => $client->telephone,
                        'email' => $client->email,
                        'look_label' => $latestCommand?->modeleVetement?->nom ?? 'Carnet client',
                        'next_action' => $this->resolveNextAction($latestMeasure?->statut),
                        'last_visit_label' => $latestMeasure?->date?->format('d/m') ?? 'Nouveau',
                    ];
                })->values()->all(),
                'recent_orders' => $recentOrders->map(function (CommandeVetement $order): array {
                    return [
                        'external_id' => $order->external_id,
                        'client_name' => trim(($order->client?->prenom ?? '').' '.($order->client?->nom ?? '')),
                        'title' => $order->modeleVetement?->nom ?? 'Commande atelier',
                        'status' => $order->statut,
                        'status_label' => ucfirst(str_replace('_', ' ', (string) $order->statut)),
                        'status_tone' => $this->mapOrderStatusTone((string) $order->statut),
                        'date_label' => $order->date_commande?->format('d/m/Y') ?? 'A planifier',
                    ];
                })->values()->all(),
                'patterns' => $patterns->map(function (Patron $patron): array {
                    $pieceCount = $patron->piecesPatrons->count();
                    $materialCount = $patron->piecesPatrons
                        ->flatMap(fn ($piece) => $piece->dispositions)
                        ->pluck('materiau_id')
                        ->filter()
                        ->unique()
                        ->count();

                    return [
                        'external_id' => $patron->external_id,
                        'title' => $patron->modeleVetement?->nom ?? 'Patron',
                        'description' => $patron->modeleVetement?->description ?? '',
                        'pieces_label' => sprintf('%d pièce%s', $pieceCount, $pieceCount > 1 ? 's' : ''),
                        'materials_label' => sprintf('%d matériau%s', $materialCount, $materialCount > 1 ? 'x' : ''),
                        'status_label' => ucfirst((string) $patron->statut),
                        'status_tone' => $this->mapStatusTone((string) $patron->statut),
                    ];
                })->values()->all(),
            ],
        ]);
    }

    private function resolveNextAction(?string $status): string
    {
        return match ($status) {
            'valide' => 'Ouvrir le patron',
            'archive' => 'Reprendre le dossier',
            'brouillon' => 'Verifier les mesures',
            default => 'Prendre les mesures',
        };
    }

    private function mapStatusTone(string $status): string
    {
        return match ($status) {
            'valide' => 'success',
            'archive' => 'warning',
            default => 'info',
        };
    }

    private function mapOrderStatusTone(string $status): string
    {
        return match ($status) {
            'fini', 'livre' => 'success',
            'en_coupe', 'en_cours' => 'info',
            'archive' => 'warning',
            default => 'neutral',
        };
    }
}

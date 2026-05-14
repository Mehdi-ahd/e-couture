<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LigneMensuration;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $clients = $this->baseQueryForUser($user)
            ->latest('updated_at')
            ->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $clients->count(),
                    'active' => $clients->where('est_actif', true)->count(),
                    'pending_measurements' => $clients
                        ->filter(fn (Client $client) => $client->fichesMesures->first()?->statut !== 'valide')
                        ->count(),
                ],
                'items' => $clients->map(fn (Client $client) => $this->serializeClient($client))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $this->validatePayload($request);

        $client = Client::query()->create([
            ...$validated,
            'prestataire_id' => $user->id,
            'date_creation' => now(),
            'est_actif' => $validated['est_actif'] ?? true,
        ]);
        $client = $this->baseQueryForUser($user)
            ->whereKey($client->getKey())
            ->firstOrFail();

        return response()->json([
            'message' => 'Client ajoute.',
            'data' => [
                'item' => $this->serializeClient($client),
            ],
        ], 201);
    }

    public function show(Request $request, string $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findDetailedForUser($user, $client);
        $latestMeasure = $record->fichesMesures->first();
        $measureLines = $latestMeasure?->lignesMensurations
            ?->sortBy(fn (LigneMensuration $line) => sprintf(
                '%02d-%s',
                $this->measurementCategoryPriority($line->typeMensuration?->categorie),
                $line->typeMensuration?->nom ?? '',
            ))
            ->values()
            ?? collect();

        $primaryLines = $measureLines
            ->filter(fn (LigneMensuration $line) => $this->isPrimaryMeasurementCategory($line->typeMensuration?->categorie))
            ->take(4)
            ->values();

        if ($primaryLines->isEmpty()) {
            $primaryLines = $measureLines->take(4)->values();
        }

        $primaryIds = $primaryLines->pluck('id')->all();
        $secondaryLines = $measureLines
            ->reject(fn (LigneMensuration $line) => in_array($line->id, $primaryIds, true))
            ->take(6)
            ->values();

        return response()->json([
            'data' => [
                'item' => $this->serializeClient($record),
                'detail' => [
                    'client_code' => sprintf('KODA-%04d', $record->id),
                    'last_updated_label' => $record->updated_at?->format('d/m/Y') ?? 'A jour',
                    'latest_measure_label' => $latestMeasure?->date?->format('d/m/Y') ?? 'Aucune mesure',
                    'measure_status_label' => ucfirst((string) ($latestMeasure?->statut ?? 'nouveau')),
                    'primary_measurements' => $primaryLines
                        ->map(fn (LigneMensuration $line) => $this->serializeMeasurement($line))
                        ->values()
                        ->all(),
                    'secondary_measurements' => $secondaryLines
                        ->map(fn (LigneMensuration $line) => $this->serializeMeasurement($line))
                        ->values()
                        ->all(),
                    'history' => $record->commandesVetements
                        ->map(fn ($order) => [
                            'external_id' => $order->external_id,
                            'title' => $order->modeleVetement?->nom ?? 'Commande atelier',
                            'reference' => sprintf('CMD-%04d', $order->id),
                            'status_label' => ucfirst(str_replace('_', ' ', (string) $order->statut)),
                            'status_tone' => $this->mapOrderTone((string) $order->statut),
                            'date_label' => $order->date_commande?->format('d/m/Y') ?? 'En attente',
                        ])
                        ->values()
                        ->all(),
                ],
            ],
        ]);
    }

    public function update(Request $request, string $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findForUser($user, $client);
        $validated = $this->validatePayload($request, true);

        $record->fill($validated)->save();

        // fiche_clients merged into clients table: prestataire owns the client
        // nothing else to update here as est_actif is stored on client

        $record = $this->baseQueryForUser($user)
            ->whereKey($record->getKey())
            ->firstOrFail();

        return response()->json([
            'message' => 'Client mis a jour.',
            'data' => [
                'item' => $this->serializeClient($record),
            ],
        ]);
    }

    public function destroy(Request $request, string $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findForUser($user, $client);

        $record->forceFill(['est_actif' => false])->save();

        // fiche_clients merged into clients table: prestataire owns the client
        // no separate fiche to update

        return response()->json([
            'message' => 'Client archive.',
        ]);
    }

    private function baseQueryForUser(User $user): Builder
    {
        return Client::query()
            ->where('prestataire_id', $user->id)
            ->withCount(['fichesMesures', 'commandesVetements'])
            ->with([
                'fichesMesures' => fn ($query) => $query->latest('date')->limit(1),
                'commandesVetements' => fn ($query) => $query->latest('date_commande')->limit(1),
                'commandesVetements.modeleVetement',
            ]);
    }

    private function findForUser(User $user, string $externalId): Client
    {
        return $this->baseQueryForUser($user)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function findDetailedForUser(User $user, string $externalId): Client
    {
        return Client::query()
            ->where('prestataire_id', $user->id)
            ->where('external_id', $externalId)
            ->withCount(['fichesMesures', 'commandesVetements'])
            ->with([
                'fichesMesures' => fn ($query) => $query
                    ->latest('date')
                    ->limit(2)
                    ->with(['lignesMensurations.typeMensuration']),
                'commandesVetements' => fn ($query) => $query
                    ->latest('date_commande')
                    ->limit(6)
                    ->with('modeleVetement'),
                // fiche_clients merged into clients table; prestataire is already filtered
            ])
            ->firstOrFail();
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'nom' => [...$required, 'string', 'max:191'],
            'prenom' => [...$required, 'string', 'max:191'],
            'telephone' => [...$required, 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'genre' => ['nullable', 'string', 'max:40'],
            'date_naissance' => ['nullable', 'date'],
            'est_actif' => ['sometimes', 'boolean'],
        ]);
    }

    private function serializeClient(Client $client): array
    {
        $latestMeasure = $client->fichesMesures->first();
        $latestCommand = $client->commandesVetements->first();

        return [
            'external_id' => $client->external_id,
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'full_name' => trim($client->prenom.' '.$client->nom),
            'telephone' => $client->telephone,
            'email' => $client->email,
            'genre' => $client->genre,
            'date_naissance' => $client->date_naissance?->toDateString(),
            'est_actif' => $client->est_actif,
            'measurement_count' => $client->fiches_mesures_count ?? 0,
            'order_count' => $client->commandes_vetements_count ?? 0,
            'look_label' => $latestCommand?->modeleVetement?->nom ?? 'Carnet client',
            'next_action' => $this->resolveNextAction($latestMeasure?->statut),
            'last_visit_label' => $latestMeasure?->date?->format('d/m/Y') ?? 'Aucune prise',
            'last_measure_status' => $latestMeasure?->statut ?? 'nouveau',
        ];
    }

    private function resolveNextAction(?string $status): string
    {
        return match ($status) {
            'valide' => 'Ouvrir le dossier',
            'archive' => 'Relancer le suivi',
            'brouillon' => 'Verifier les mesures',
            default => 'Prendre les mesures',
        };
    }

    private function serializeMeasurement(LigneMensuration $line): array
    {
        return [
            'external_id' => $line->external_id,
            'label' => $line->typeMensuration?->nom ?? 'Mesure',
            'code' => $line->typeMensuration?->code ?? '',
            'category' => $line->typeMensuration?->categorie ?? 'principale',
            'value' => (float) $line->valeur,
            'unit' => $line->typeMensuration?->unite ?? 'cm',
            'source' => $line->source,
            'confidence' => $line->confiance !== null ? (float) $line->confiance : null,
        ];
    }

    private function isPrimaryMeasurementCategory(?string $category): bool
    {
        return in_array($category, ['principale', 'primaire', 'base'], true);
    }

    private function measurementCategoryPriority(?string $category): int
    {
        return match ($category) {
            'principale', 'primaire', 'base' => 1,
            'secondaire', 'derivee' => 2,
            default => 3,
        };
    }

    private function mapOrderTone(string $status): string
    {
        return match ($status) {
            'fini', 'livre' => 'success',
            'en_coupe', 'en_cours' => 'info',
            default => 'neutral',
        };
    }
}

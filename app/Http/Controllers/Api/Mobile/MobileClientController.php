<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FicheClient;
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
        ]);

        FicheClient::query()->firstOrCreate(
            [
                'couturier_id' => $user->id,
                'client_id' => $client->id,
            ],
            [
                'date_creation' => now(),
                'est_actif' => true,
            ],
        );

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
        $record = $this->findForUser($user, $client);

        return response()->json([
            'data' => [
                'item' => $this->serializeClient($record),
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

        FicheClient::query()
            ->where('couturier_id', $user->id)
            ->where('client_id', $record->id)
            ->update([
                'est_actif' => $record->est_actif,
            ]);

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

        FicheClient::query()
            ->where('couturier_id', $user->id)
            ->where('client_id', $record->id)
            ->update([
                'est_actif' => false,
            ]);

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
}

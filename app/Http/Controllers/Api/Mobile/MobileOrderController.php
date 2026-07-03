<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CommandeVetement;
use App\Models\FicheMesure;
use App\Models\ModeleVetement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileOrderController extends Controller
{
    public function index(Request $request, string $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $orders = $this->orderQuery($record, $user)->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $orders->count(),
                    'active' => $orders->where('statut', '!=', 'archive')->count(),
                ],
                'client' => $this->serializeClient($record),
                'available_models' => $this->serializeModelOptions($user),
                'available_sheets' => $this->serializeSheetOptions($record),
                'items' => $orders->map(fn (CommandeVetement $order) => $this->serializeOrder($order))->values()->all(),
            ],
        ]);
    }

    public function store(Request $request, string $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $validated = $this->validatePayload($request);

        $model = $this->findModelForUser($user, (int) $validated['modele_vetement_id']);
        $sheet = isset($validated['fiche_mesure_id'])
            ? $this->findSheetForClient($record, (int) $validated['fiche_mesure_id'])
            : null;

        $order = CommandeVetement::query()->create([
            'client_id' => $record->id,
            'modele_vetement_id' => $model->id,
            'fiche_mesure_id' => $sheet?->id,
            'statut' => $validated['statut'],
            'notes' => $validated['notes'] ?? null,
            'date_commande' => $validated['date_commande'],
            'date_livraison' => $validated['date_livraison'] ?? null,
        ]);

        $order = $this->orderQuery($record, $user)->whereKey($order->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Commande enregistrée.',
            'data' => [
                'item' => $this->serializeOrder($order),
            ],
        ], 201);
    }

    public function show(Request $request, string $client, string $order): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $item = $this->findOrderForClient($record, $user, $order);

        return response()->json([
            'data' => [
                'item' => $this->serializeOrder($item),
                'detail' => [
                    'notes' => $item->notes ?? '',
                    'model_description' => $item->modeleVetement?->description ?? '',
                ],
                'options' => [
                    'client' => $this->serializeClient($record),
                    'available_models' => $this->serializeModelOptions($user),
                    'available_sheets' => $this->serializeSheetOptions($record),
                ],
            ],
        ]);
    }

    public function update(Request $request, string $client, string $order): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $item = $this->findOrderForClient($record, $user, $order);
        $validated = $this->validatePayload($request, true);

        if (isset($validated['modele_vetement_id'])) {
            $item->modele_vetement_id = $this->findModelForUser($user, (int) $validated['modele_vetement_id'])->id;
        }

        if (array_key_exists('fiche_mesure_id', $validated)) {
            $item->fiche_mesure_id = $validated['fiche_mesure_id'] !== null
                ? $this->findSheetForClient($record, (int) $validated['fiche_mesure_id'])->id
                : null;
        }

        $item->fill([
            'statut' => $validated['statut'] ?? $item->statut,
            'notes' => $validated['notes'] ?? $item->notes,
            'date_commande' => $validated['date_commande'] ?? $item->date_commande,
            'date_livraison' => $validated['date_livraison'] ?? $item->date_livraison,
        ])->save();

        $item = $this->orderQuery($record, $user)->whereKey($item->getKey())->firstOrFail();

        return response()->json([
            'message' => 'Commande mise à jour.',
            'data' => [
                'item' => $this->serializeOrder($item),
            ],
        ]);
    }

    public function destroy(Request $request, string $client, string $order): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->findClientForUser($user, $client);
        $item = $this->findOrderForClient($record, $user, $order);
        $item->forceFill(['statut' => 'archive'])->save();

        return response()->json([
            'message' => 'Commande archivée.',
        ]);
    }

    private function findClientForUser(User $user, string $externalId): Client
    {
        return Client::query()
            ->where('prestataire_id', $user->id)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function orderQuery(Client $client, User $user): Builder
    {
        return CommandeVetement::query()
            ->where('client_id', $client->id)
            ->whereHas('modeleVetement', function (Builder $query) use ($user): void {
                $query->where(function (Builder $subQuery) use ($user): void {
                    $subQuery->whereNull('prestataire_id')->orWhere('prestataire_id', $user->id);
                });
            })
            ->with(['modeleVetement.typeVetement', 'ficheMesure'])
            ->latest('date_commande');
    }

    private function findOrderForClient(Client $client, User $user, string $externalId): CommandeVetement
    {
        return $this->orderQuery($client, $user)
            ->where('external_id', $externalId)
            ->firstOrFail();
    }

    private function findModelForUser(User $user, int $id): ModeleVetement
    {
        return ModeleVetement::query()
            ->whereKey($id)
            ->where(function (Builder $query) use ($user): void {
                $query->whereNull('prestataire_id')->orWhere('prestataire_id', $user->id);
            })
            ->firstOrFail();
    }

    private function findSheetForClient(Client $client, int $id): FicheMesure
    {
        return FicheMesure::query()
            ->where('client_id', $client->id)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes'] : ['required'];

        return $request->validate([
            'modele_vetement_id' => [...$required, 'integer', 'exists:modele_vetements,id'],
            'fiche_mesure_id' => ['nullable', 'integer', 'exists:fiche_mesures,id'],
            'statut' => [...$required, 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'date_commande' => [...$required, 'date'],
            'date_livraison' => ['nullable', 'date'],
        ]);
    }

    private function serializeClient(Client $client): array
    {
        return [
            'external_id' => $client->external_id,
            'full_name' => trim($client->prenom.' '.$client->nom),
        ];
    }

    private function serializeOrder(CommandeVetement $order): array
    {
        return [
            'external_id' => $order->external_id,
            'title' => $order->modeleVetement?->nom ?? 'Commande atelier',
            'reference' => sprintf('CMD-%04d', $order->id),
            'status' => $order->statut,
            'status_label' => ucfirst(str_replace('_', ' ', (string) $order->statut)),
            'status_tone' => $this->mapStatusTone((string) $order->statut),
            'date_commande' => $order->date_commande?->toDateString(),
            'date_commande_label' => $order->date_commande?->format('d/m/Y') ?? 'A planifier',
            'date_livraison' => $order->date_livraison?->toDateString(),
            'date_livraison_label' => $order->date_livraison?->format('d/m/Y') ?? 'Non définie',
            'notes' => $order->notes ?? '',
            'modele_vetement_id' => $order->modele_vetement_id,
            'modele_label' => $order->modeleVetement?->nom ?? 'Modèle',
            'fiche_mesure_id' => $order->fiche_mesure_id,
            'fiche_label' => $order->ficheMesure?->date?->format('d/m/Y') ?? 'Sans fiche',
        ];
    }

    private function serializeModelOptions(User $user): array
    {
        return ModeleVetement::query()
            ->with('typeVetement')
            ->where(function (Builder $query) use ($user): void {
                $query->whereNull('prestataire_id')->orWhere('prestataire_id', $user->id);
            })
            ->orderBy('nom')
            ->get()
            ->map(fn (ModeleVetement $model) => [
                'id' => $model->id,
                'external_id' => $model->external_id,
                'label' => $model->nom,
                'subtitle' => $model->typeVetement?->nom ?? 'Type libre',
            ])
            ->values()
            ->all();
    }

    private function serializeSheetOptions(Client $client): array
    {
        return FicheMesure::query()
            ->where('client_id', $client->id)
            ->latest('date')
            ->get()
            ->map(fn (FicheMesure $sheet) => [
                'id' => $sheet->id,
                'external_id' => $sheet->external_id,
                'label' => $sheet->date?->format('d/m/Y') ?? 'Sans date',
                'subtitle' => ucfirst((string) $sheet->methode),
            ])
            ->values()
            ->all();
    }

    private function mapStatusTone(string $status): string
    {
        return match ($status) {
            'livree', 'valide', 'terminee' => 'success',
            'archive' => 'warning',
            default => 'info',
        };
    }
}

<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AnnotationPatron;
use App\Models\Client;
use App\Models\CommandeVetement;
use App\Models\Evenement;
use App\Models\FicheMesure;
use App\Models\Mesure;
use App\Models\MesureModele;
use App\Models\ModeleVetement;
use App\Models\Paiement;
use App\Models\Patron;
use App\Models\PiecePatron;
use App\Models\TypeMesure;
use App\Models\TypeVetement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileSyncController extends Controller
{
    private const SCHEMA_VERSION = 1;

    private const BOOTSTRAP_ORDER = [
        'clients',
        'modeles',
        'commandes',
        'paiements',
        'evenements',
        'fiche_mesures',
        'mesures',
        'patrons',
        'piece_patrons',
        'annotation_patrons',
    ];

    private const OUTPUT_TO_CONFIG = [
        'clients' => 'clients',
        'modeles' => 'modele_vetements',
        'commandes' => 'commande_vetements',
        'paiements' => 'paiements',
        'evenements' => 'evenements',
        'fiche_mesures' => 'fiche_mesures',
        'mesures' => 'mesures',
        'patrons' => 'patrons',
        'piece_patrons' => 'piece_patrons',
        'annotation_patrons' => 'annotation_patrons',
    ];

    private array $entityConfig = [];

    private array $relationResolvers = [];

    public function __construct()
    {
        $this->entityConfig = [

            'clients' => [
                'model' => Client::class,
                'scope' => fn (Builder $q, int $userId) => $q->where('prestataire_id', $userId),
                'with' => [],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.clients', 50),
                'sync_size' => config('sync.page_sizes.sync.clients', 100),
            ],
            'modele_vetements' => [
                'model' => ModeleVetement::class,
                'scope' => fn (Builder $q, int $userId) => $q,
                'with' => ['typeVetement'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.modele_vetements', 20),
                'sync_size' => config('sync.page_sizes.sync.modele_vetements', 100),
            ],
            'commande_vetements' => [
                'model' => CommandeVetement::class,
                'scope' => fn (Builder $q, int $userId) => $q->whereHas('client', fn (Builder $c) => $c->where('prestataire_id', $userId)),
                'with' => ['client', 'modeleVetement', 'ficheMesure'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.commande_vetements', 20),
                'sync_size' => config('sync.page_sizes.sync.commande_vetements', 100),
            ],
            'fiche_mesures' => [
                'model' => FicheMesure::class,
                'scope' => fn (Builder $q, int $userId) => $q->whereHas('client', fn (Builder $c) => $c->where('prestataire_id', $userId)),
                'with' => ['client'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.fiche_mesures', 20),
                'sync_size' => config('sync.page_sizes.sync.fiche_mesures', 100),
            ],
            'mesures' => [
                'model' => Mesure::class,
                'scope' => fn (Builder $q, int $userId) => $q->whereHas('ficheMesure.client', fn (Builder $c) => $c->where('prestataire_id', $userId)),
                'with' => ['typeMesure'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.mesures', 20),
                'sync_size' => config('sync.page_sizes.sync.mesures', 200),
            ],
            'patrons' => [
                'model' => Patron::class,
                'scope' => fn (Builder $q, int $userId) => $q,
                'with' => ['modeleVetement'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.patrons', 20),
                'sync_size' => config('sync.page_sizes.sync.patrons', 100),
            ],
            'piece_patrons' => [
                'model' => PiecePatron::class,
                'scope' => fn (Builder $q, int $userId) => $q,
                'with' => [],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.piece_patrons', 50),
                'sync_size' => config('sync.page_sizes.sync.piece_patrons', 200),
            ],
            'annotation_patrons' => [
                'model' => AnnotationPatron::class,
                'scope' => fn (Builder $q, int $userId) => $q,
                'with' => ['piecePatron', 'typeMesure'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.annotation_patrons', 50),
                'sync_size' => config('sync.page_sizes.sync.annotation_patrons', 200),
            ],
            'mesure_modeles' => [
                'model' => MesureModele::class,
                'scope' => fn (Builder $q, int $userId) => $q->whereHas('modeleVetement', fn (Builder $m) => $m->where('prestataire_id', $userId)),
                'with' => ['typeMesure'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.mesure_modeles', 50),
                'sync_size' => config('sync.page_sizes.sync.mesure_modeles', 200),
            ],
            'paiements' => [
                'model' => Paiement::class,
                'scope' => fn (Builder $q, int $userId) => $q->whereHas('commande.client', fn (Builder $c) => $c->where('prestataire_id', $userId)),
                'with' => ['commande'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.paiements', 50),
                'sync_size' => config('sync.page_sizes.sync.paiements', 200),
            ],
            'evenements' => [
                'model' => Evenement::class,
                'scope' => fn (Builder $q, int $userId) => $q->whereHas('commande.client', fn (Builder $c) => $c->where('prestataire_id', $userId)),
                'with' => ['commande'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.evenements', 50),
                'sync_size' => config('sync.page_sizes.sync.evenements', 200),
            ],
        ];

        $this->relationResolvers = [
            'commande_vetements' => [
                'client_id' => ['model' => Client::class, 'field' => 'client_id'],
                'modele_vetement_id' => ['model' => ModeleVetement::class, 'field' => 'modele_vetement_id'],
                'fiche_mesure_id' => ['model' => FicheMesure::class, 'field' => 'fiche_mesure_id'],
            ],
            'modele_vetements' => [
                'type_vetement_id' => ['model' => TypeVetement::class, 'field' => 'type_vetement_id'],
            ],
            'fiche_mesures' => [
                'client_id' => ['model' => Client::class, 'field' => 'client_id'],
            ],
            'mesures' => [
                'fiche_id' => ['model' => FicheMesure::class, 'field' => 'fiche_mesure_id'],
                'type_mesure_external_id' => ['model' => TypeMesure::class, 'field' => 'type_mesure_id'],
            ],
            'patrons' => [
                'modele_vetement_id' => ['model' => ModeleVetement::class, 'field' => 'modele_vetement_id'],
            ],
            'piece_patrons' => [
                'patron_id' => ['model' => Patron::class, 'field' => 'patron_id'],
            ],
            'mesure_modeles' => [
                'modele_vetement_id' => ['model' => ModeleVetement::class, 'field' => 'modele_vetement_id'],
                'type_mesure_external_id' => ['model' => TypeMesure::class, 'field' => 'type_mesure_id'],
            ],
            'paiements' => [
                'commande_external_id' => ['model' => CommandeVetement::class, 'field' => 'commande_id'],
            ],
            'evenements' => [
                'commande_external_id' => ['model' => CommandeVetement::class, 'field' => 'commande_id'],
            ],
            'annotation_patrons' => [
                'piece_patron_id' => ['model' => PiecePatron::class, 'field' => 'piece_patron_id'],
                'type_mesure_external_id' => ['model' => TypeMesure::class, 'field' => 'type_mesure_id'],
            ],
        ];
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schema_version' => 'nullable|integer|min:1',
            'device_id' => 'nullable|string',
        ]);

        /** @var User $user */
        $user = $request->user();
        $hasClients = $user->clients()->exists();

        return response()->json([
            'schema_version' => $validated['schema_version'] ?? self::SCHEMA_VERSION,
            'server_time' => now()->toIso8601String(),
            'minimum_client_version' => '1.0.0',
            'bootstrap_required' => ! $hasClients,
        ]);
    }

    public function bootstrap(Request $request): JsonResponse
    {
        $request->validate([
            'schema_version' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();

        $tableCounts = $this->computeExpectedCounts($user);

        $initialState = [
            'mode' => 'bootstrap',
            'created_at' => now()->toIso8601String(),
        ];
        foreach (self::BOOTSTRAP_ORDER as $table) {
            $initialState[$table] = ['last_pk' => 0, 'done' => false];
        }

        return $this->buildPaginatedResponse(
            user: $user,
            cursorState: $initialState,
            expected: $tableCounts,
        );
    }

    public function next(Request $request): JsonResponse
    {
        if ($request->has('cursor')) {
            return $this->nextCursor($request);
        }

        $validated = $request->validate([
            'entity' => 'required|string',
            'page' => 'required|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:500',
        ]);

        $entity = $validated['entity'];
        $page = (int) $validated['page'];
        $pageSize = (int) ($validated['page_size'] ?? 50);

        $config = $this->entityConfig[$entity] ?? null;
        if ($config === null) {
            return response()->json(['message' => 'Unknown entity.'], 422);
        }

        $user = $request->user();
        $modelClass = $config['model'];
        $scope = $config['scope'];

        $query = $modelClass::query();
        $scope($query, $user->id);

        $totalCount = (clone $query)->count();
        $totalPages = (int) ceil($totalCount / $pageSize);
        $items = $query->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        return response()->json([
            'data' => [
                'items' => $items->toArray(),
                'page' => $page,
                'page_size' => $pageSize,
                'total_count' => $totalCount,
                'total_pages' => $totalPages,
                'finished' => $page >= $totalPages,
            ],
        ]);
    }

    private function nextCursor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cursor' => 'required|string',
        ]);

        $cursorState = json_decode(base64_decode($validated['cursor']), true);

        if (! $cursorState || ($cursorState['mode'] ?? null) !== 'bootstrap') {
            return response()->json(['message' => 'Curseur invalide.'], 422);
        }

        $user = $request->user();
        $tableCounts = $this->computeExpectedCounts($user);

        return $this->buildPaginatedResponse(
            user: $user,
            cursorState: $cursorState,
            expected: $tableCounts,
        );
    }

    public function delta(Request $request): JsonResponse
    {
        if ($request->has('cursor')) {
            $validated = $request->validate([
                'cursor' => 'required|string',
                'device_id' => 'nullable|string',
            ]);

            $cursorState = json_decode(base64_decode($validated['cursor']), true);

            if (! $cursorState || ($cursorState['mode'] ?? null) !== 'delta') {
                return response()->json(['message' => 'Curseur invalide.'], 422);
            }

            $user = $request->user();
            $tableCounts = $this->computeExpectedCountsSince($user, $cursorState['last_server_updated_at'] ?? now()->subDay()->toIso8601String());

            return $this->buildPaginatedResponse(
                user: $user,
                cursorState: $cursorState,
                expected: $tableCounts,
            );
        }

        $validated = $request->validate([
            'entities' => 'required|array',
            'entities.*.name' => 'required|string',
            'entities.*.since' => 'required|date_format:Y-m-d\TH:i:s.v\Z',
        ]);

        $since = $validated['entities'][0]['since'] ?? now()->subDay()->toIso8601String();
        $user = $request->user();

        $initialState = [
            'mode' => 'delta',
            'last_server_updated_at' => $since,
        ];
        foreach (self::BOOTSTRAP_ORDER as $table) {
            $initialState[$table] = ['last_pk' => 0, 'done' => false];
        }

        $tableCounts = $this->computeExpectedCountsSince($user, $since);

        return $this->buildPaginatedResponse(
            user: $user,
            cursorState: $initialState,
            expected: $tableCounts,
        );
    }

    private function buildPaginatedResponse(
        User $user,
        array $cursorState,
        array $expected,
    ): JsonResponse {
        $activeTable = $this->findActiveTable($cursorState);

        if ($activeTable === null) {
            $cursorState['created_at'] = now()->toIso8601String();

            return response()->json([
                'schema_version' => self::SCHEMA_VERSION,
                'minimum_client_version' => '1.0.0',
                'cursor' => base64_encode(json_encode($cursorState)),
                'has_more' => false,
                'received' => [],
                'expected' => $expected,
                'tables' => [],
            ]);
        }

        $configKey = self::OUTPUT_TO_CONFIG[$activeTable];
        $config = $this->entityConfig[$configKey];
        $mode = $cursorState['mode'] ?? 'bootstrap';
        $lastPk = $cursorState[$activeTable]['last_pk'] ?? 0;
        $since = $cursorState['last_server_updated_at'] ?? null;

        $pageSize = $mode === 'bootstrap'
            ? ($config['bootstrap_size'] ?? 50)
            : ($config['sync_size'] ?? 100);

        $rows = $this->fetchPage($config, $mode, $lastPk, $since, $user, $pageSize);

        $data = $this->mapRows($activeTable, $rows);

        $newLastPk = $rows->isNotEmpty() ? $rows->last()->id : $lastPk;

        $hasMoreInTable = $this->hasMoreData($config, $mode, $newLastPk, $since, $user);

        $cursorState[$activeTable] = [
            'last_pk' => $newLastPk,
            'done' => ! $hasMoreInTable,
        ];

        $nextActive = $this->findActiveTable($cursorState);
        $hasMoreOverall = $nextActive !== null;

        return response()->json([
            'schema_version' => self::SCHEMA_VERSION,
            'minimum_client_version' => '1.0.0',
            'cursor' => base64_encode(json_encode($cursorState)),
            'has_more' => $hasMoreOverall,
            'received' => [$activeTable => count($rows)],
            'expected' => $expected,
            'tables' => [$activeTable => $data],
        ]);
    }

    private function findActiveTable(array $cursorState): ?string
    {
        foreach (self::BOOTSTRAP_ORDER as $table) {
            $state = $cursorState[$table] ?? null;
            if ($state === null || ! ($state['done'] ?? false)) {
                return $table;
            }
        }

        return null;
    }

    private function fetchPage(
        array $config,
        string $mode,
        int $lastPk,
        ?string $since,
        User $user,
        int $pageSize,
    ): Collection {
        $modelClass = $config['model'];
        $scope = $config['scope'];

        $query = $modelClass::query();

        if (! empty($config['with'])) {
            $query->with($config['with']);
        }

        $scope($query, $user->id);

        if ($mode === 'delta' && $since !== null) {
            $query->where(function (Builder $q) use ($since): void {
                $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since)
                    ->orWhereNotNull('deleted_at');
            })->orderBy('updated_at')->orderBy('id');
        } else {
            $query->orderBy('id');
        }

        return $query->where('id', '>', $lastPk)->take($pageSize)->get();
    }

    private function hasMoreData(
        array $config,
        string $mode,
        int $lastPk,
        ?string $since,
        User $user,
    ): bool {
        $modelClass = $config['model'];
        $scope = $config['scope'];

        $query = $modelClass::query();
        $scope($query, $user->id);

        if ($mode === 'delta' && $since !== null) {
            $query->where(function (Builder $q) use ($since): void {
                $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since)
                    ->orWhereNotNull('deleted_at');
            });
        }

        return $query->where('id', '>', $lastPk)->exists();
    }

    private function computeExpectedCounts(User $user): array
    {
        $counts = [];
        foreach (self::BOOTSTRAP_ORDER as $table) {
            $configKey = self::OUTPUT_TO_CONFIG[$table];
            $config = $this->entityConfig[$configKey];
            $modelClass = $config['model'];
            $scope = $config['scope'];

            $query = $modelClass::query();
            $scope($query, $user->id);

            $counts[$table] = $query->count();
        }

        return $counts;
    }

    private function computeExpectedCountsSince(User $user, string $since): array
    {
        $counts = [];
        foreach (self::BOOTSTRAP_ORDER as $table) {
            $configKey = self::OUTPUT_TO_CONFIG[$table];
            $config = $this->entityConfig[$configKey];
            $modelClass = $config['model'];
            $scope = $config['scope'];

            $query = $modelClass::query();
            $scope($query, $user->id);

            $query->where(function (Builder $q) use ($since): void {
                $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since)
                    ->orWhereNotNull('deleted_at');
            });

            $counts[$table] = $query->count();
        }

        return $counts;
    }

    private function mapRows(string $tableKey, Collection $rows): array
    {
        return match ($tableKey) {
            'clients' => $rows->map(fn (Client $c) => $this->toClientArray($c))->values()->all(),
            'modeles' => $rows->map(fn (ModeleVetement $m) => $this->toModeleArray($m))->values()->all(),
            'commandes' => $rows->map(fn (CommandeVetement $c) => $this->toCommandeArray($c))->values()->all(),
            'paiements' => $rows->map(fn (Paiement $p) => $this->toPaiementArray($p))->values()->all(),
            'evenements' => $rows->map(fn (Evenement $e) => $this->toEvenementArray($e))->values()->all(),
            'fiche_mesures' => $rows->map(fn (FicheMesure $f) => $this->toFicheMesureArray($f))->values()->all(),
            'mesures' => $rows->map(fn (Mesure $m) => $this->toMesureArray($m))->values()->all(),
            'patrons' => $rows->map(fn (Patron $p) => $this->toPatronArray($p))->values()->all(),
            'piece_patrons' => $rows->map(fn (PiecePatron $p) => $this->toPiecePatronArray($p))->values()->all(),
            'annotation_patrons' => $rows->map(fn (AnnotationPatron $a) => $this->toAnnotationPatronArray($a))->values()->all(),
            default => [],
        };
    }

    private function toClientArray(Client $client): array
    {
        $data = $client->toArray();
        $data['latitude'] = $data['latitude'] !== null ? (float) $data['latitude'] : null;
        $data['longitude'] = $data['longitude'] !== null ? (float) $data['longitude'] : null;
        $data['archived'] = ! ($client->est_actif ?? true);
        $data['server_updated_at'] = $client->updated_at?->toIso8601String() ?? $client->created_at?->toIso8601String();
        $data['created_at'] = $client->created_at?->toIso8601String();
        $data['updated_at'] = $client->updated_at?->toIso8601String();
        $data['deleted_at'] = $client->deleted_at?->toIso8601String();

        return $data;
    }

    private function toModeleArray(ModeleVetement $modele): array
    {
        $data = $modele->toArray();
        $data['server_updated_at'] = $modele->updated_at?->toIso8601String() ?? $modele->created_at?->toIso8601String();
        $data['created_at'] = $modele->created_at?->toIso8601String();
        $data['updated_at'] = $modele->updated_at?->toIso8601String();
        $data['deleted_at'] = $modele->deleted_at?->toIso8601String();

        return $data;
    }

    private function toCommandeArray(CommandeVetement $commande): array
    {
        $data = $commande->toArray();
        $data['prix_total'] = (float) ($data['prix_total'] ?? 0);
        $data['client_external_id'] = $commande->client?->external_id;
        $data['modele_external_id'] = $commande->modeleVetement?->external_id;
        $data['fiche_mesure_external_id'] = $commande->ficheMesure?->external_id;
        $data['server_updated_at'] = $commande->updated_at?->toIso8601String() ?? $commande->created_at?->toIso8601String();
        $data['created_at'] = $commande->created_at?->toIso8601String();
        $data['updated_at'] = $commande->updated_at?->toIso8601String();
        $data['deleted_at'] = $commande->deleted_at?->toIso8601String();

        return $data;
    }

    private function toPaiementArray(Paiement $paiement): array
    {
        $data = $paiement->toArray();
        $data['montant'] = (float) ($data['montant'] ?? 0);
        $data['commande_external_id'] = $paiement->commande?->external_id;
        $data['server_updated_at'] = $paiement->updated_at?->toIso8601String() ?? $paiement->created_at?->toIso8601String();
        $data['created_at'] = $paiement->created_at?->toIso8601String();
        $data['updated_at'] = $paiement->updated_at?->toIso8601String();
        $data['deleted_at'] = $paiement->deleted_at?->toIso8601String();

        return $data;
    }

    private function toEvenementArray(Evenement $evenement): array
    {
        $data = $evenement->toArray();
        $data['commande_external_id'] = $evenement->commande?->external_id;
        $data['server_updated_at'] = $evenement->updated_at?->toIso8601String() ?? $evenement->created_at?->toIso8601String();
        $data['created_at'] = $evenement->created_at?->toIso8601String();
        $data['updated_at'] = $evenement->updated_at?->toIso8601String();
        $data['deleted_at'] = $evenement->deleted_at?->toIso8601String();

        return $data;
    }

    private function toFicheMesureArray(FicheMesure $fiche): array
    {
        $data = $fiche->toArray();
        $data['client_external_id'] = $fiche->client?->external_id;
        $data['server_updated_at'] = $fiche->updated_at?->toIso8601String() ?? $fiche->created_at?->toIso8601String();
        $data['created_at'] = $fiche->created_at?->toIso8601String();
        $data['updated_at'] = $fiche->updated_at?->toIso8601String();
        $data['deleted_at'] = $fiche->deleted_at?->toIso8601String();

        return $data;
    }

    private function toMesureArray(Mesure $mesure): array
    {
        $data = $mesure->toArray();
        $data['valeur'] = (float) ($data['valeur'] ?? 0);
        $data['confiance'] = $data['confiance'] !== null ? (float) $data['confiance'] : null;
        $data['fiche_mesure_external_id'] = $mesure->ficheMesure?->external_id;
        $data['type_mesure_external_id'] = $mesure->typeMesure?->external_id;
        $data['type_mesure_code'] = $mesure->typeMesure?->code;
        $data['server_updated_at'] = $mesure->updated_at?->toIso8601String() ?? $mesure->created_at?->toIso8601String();
        $data['created_at'] = $mesure->created_at?->toIso8601String();
        $data['updated_at'] = $mesure->updated_at?->toIso8601String();
        $data['deleted_at'] = $mesure->deleted_at?->toIso8601String();

        return $data;
    }

    private function toPatronArray(Patron $patron): array
    {
        $data = $patron->toArray();
        $data['modele_external_id'] = $patron->modeleVetement?->external_id;
        $data['server_updated_at'] = $patron->updated_at?->toIso8601String() ?? $patron->created_at?->toIso8601String();
        $data['created_at'] = $patron->created_at?->toIso8601String();
        $data['updated_at'] = $patron->updated_at?->toIso8601String();
        $data['deleted_at'] = $patron->deleted_at?->toIso8601String();

        return $data;
    }

    private function toPiecePatronArray(PiecePatron $piece): array
    {
        $data = $piece->toArray();
        $data['patron_external_id'] = $piece->patron?->external_id;
        $data['server_updated_at'] = $piece->updated_at?->toIso8601String() ?? $piece->created_at?->toIso8601String();
        $data['created_at'] = $piece->created_at?->toIso8601String();
        $data['updated_at'] = $piece->updated_at?->toIso8601String();
        $data['deleted_at'] = $piece->deleted_at?->toIso8601String();

        return $data;
    }

    private function toAnnotationPatronArray(AnnotationPatron $annotation): array
    {
        $data = $annotation->toArray();
        $data['piece_patron_external_id'] = $annotation->piecePatron?->external_id;
        $data['type_mesure_external_id'] = $annotation->typeMesure?->external_id;
        $data['type_mesure_code'] = $annotation->typeMesure?->code;
        $data['server_updated_at'] = $annotation->updated_at?->toIso8601String() ?? $annotation->created_at?->toIso8601String();
        $data['created_at'] = $annotation->created_at?->toIso8601String();
        $data['updated_at'] = $annotation->updated_at?->toIso8601String();
        $data['deleted_at'] = $annotation->deleted_at?->toIso8601String();

        return $data;
    }

    public function push(Request $request): JsonResponse
    {
        // Support both legacy (mutation_id/entity/action/data) and v1 (uuid/table/operation/external_id/payload) formats
        $validated = $request->validate([
            'device_id' => 'nullable|string',
            'mutations' => 'required|array',
            'mutations.*.uuid' => 'required_without:mutations.*.mutation_id|string',
            'mutations.*.table' => 'required_without:mutations.*.entity|string',
            'mutations.*.operation' => 'required_without:mutations.*.action|in:create,update,delete',
            'mutations.*.external_id' => 'nullable|string',
            'mutations.*.payload' => 'required_without:mutations.*.data|array',
            'mutations.*.mutation_id' => 'required_without:mutations.*.uuid|string',
            'mutations.*.entity' => 'required_without:mutations.*.table|string',
            'mutations.*.action' => 'required_without:mutations.*.operation|in:create,update,delete',
            'mutations.*.data' => 'required_without:mutations.*.payload|array',
        ]);

        $user = $request->user();
        $accepted = [];
        $conflicts = [];
        $failed = [];

        DB::beginTransaction();
        try {
            foreach ($validated['mutations'] as $mutation) {
                // Normalise v1 → legacy format for internal processing
                $normalised = [
                    'mutation_id' => $mutation['uuid'] ?? $mutation['mutation_id'],
                    'entity' => $mutation['table'] ?? $mutation['entity'],
                    'action' => $mutation['operation'] ?? $mutation['action'],
                    'data' => $mutation['payload'] ?? $mutation['data'],
                ];

                try {
                    $result = $this->processMutation($normalised, $user);
                    $status = $result['status'] ?? '';
                    if ($status === 'completed' || $status === 'duplicate') {
                        $accepted[] = $normalised['mutation_id'];
                    } elseif ($status === 'error' && str_contains($result['reason'] ?? '', 'Conflict')) {
                        $conflicts[] = [
                            'uuid' => $normalised['mutation_id'],
                            'table' => $normalised['entity'],
                            'external_id' => $mutation['external_id'] ?? $normalised['data']['external_id'] ?? '',
                            'server_version' => ['error' => $result['reason'] ?? ''],
                        ];
                    } else {
                        $failed[] = [
                            'uuid' => $normalised['mutation_id'],
                            'table' => $normalised['entity'],
                            'error' => $result['reason'] ?? 'Unknown error',
                        ];
                    }
                } catch (\Throwable $e) {
                    $failed[] = [
                        'uuid' => $normalised['mutation_id'],
                        'table' => $normalised['entity'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $failed[] = [
                'uuid' => 'unknown',
                'table' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }

        $cursor = base64_encode(json_encode([
            'mode' => 'delta',
            'last_server_updated_at' => now()->toIso8601String(),
        ]));

        return response()->json([
            'accepted' => $accepted,
            'conflicts' => $conflicts,
            'failed' => $failed,
            'cursor' => $cursor,
        ]);
    }

    private function processMutation(array $mutation, User $user): array
    {
        $mutationId = $mutation['mutation_id'];
        $entity = $mutation['entity'];
        $action = $mutation['action'];
        $data = $mutation['data'];

        $alreadyProcessed = DB::table('sync_event_log')
            ->where('mutation_id', $mutationId)
            ->exists();

        if ($alreadyProcessed) {
            return [
                'mutation_id' => $mutationId,
                'uuid' => $data['external_id'] ?? null,
                'external_id' => $data['external_id'] ?? null,
                'server_id' => $data['external_id'] ?? null,
                'status' => 'duplicate',
            ];
        }

        if ($entity === 'type_vetements' || $entity === 'type_mesures') {
            return [
                'mutation_id' => $mutationId,
                'uuid' => $data['external_id'] ?? null,
                'status' => 'rejected',
                'reason' => 'Catalog entities cannot be modified via sync push.',
            ];
        }

        $config = $this->entityConfig[$entity] ?? null;
        if ($config === null) {
            return [
                'mutation_id' => $mutationId,
                'uuid' => $data['external_id'] ?? null,
                'status' => 'rejected',
                'reason' => "Unknown entity: {$entity}",
            ];
        }

        $modelClass = $config['model'];
        $uuid = $data['external_id'] ?? null;

        $data = $this->resolveRelationIds($entity, $data);

        try {
            match ($action) {
                'create' => $this->applyCreate($modelClass, $data, $user),
                'update' => $this->applyUpdate($modelClass, $uuid, $data, $user),
                'delete' => $this->applyDelete($modelClass, $uuid, $user),
            };
        } catch (\Throwable $e) {
            return [
                'mutation_id' => $mutationId,
                'uuid' => $uuid,
                'status' => 'error',
                'reason' => $e->getMessage(),
            ];
        }

        DB::table('sync_event_log')->insert([
            'mutation_id' => $mutationId,
            'entity' => $entity,
            'action' => $action,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $serverId = null;
        try {
            $serverId = $modelClass::query()->where('external_id', $uuid)->value('id');
        } catch (\Throwable) {
            // Not all models have an 'id' column accessible this way — safe to ignore.
        }

        return [
            'mutation_id' => $mutationId,
            'uuid' => $uuid,
            'external_id' => $uuid,
            'server_id' => $serverId,
            'status' => 'completed',
        ];
    }

    private function applyCreate(string $modelClass, array $data, User $user): void
    {
        unset($data['id']);

        $data['external_id'] ??= (string) Str::uuid();

        if ($modelClass::query()->where('external_id', $data['external_id'])->exists()) {
            return;
        }

        $instance = new $modelClass;

        if (in_array('prestataire_id', $instance->getFillable(), true)) {
            $data['prestataire_id'] = $user->id;
        }

        $instance->fill($data);
        $instance->save();
    }

    private function applyUpdate(string $modelClass, ?string $uuid, array $data, User $user): void
    {
        if ($uuid === null) {
            throw new \RuntimeException('external_id is required for update.');
        }

        $query = $modelClass::query()->where('external_id', $uuid);

        $entityName = array_search($modelClass, array_column($this->entityConfig, 'model'), true);
        if ($entityName !== false && isset($this->entityConfig[$entityName]['scope'])) {
            ($this->entityConfig[$entityName]['scope'])($query, $user->id);
        }

        $instance = $query->first();

        if ($instance === null) {
            throw new \RuntimeException("Record {$uuid} not found or access denied.");
        }

        $clientUpdatedAt = $data['updated_at'] ?? null;
        if ($clientUpdatedAt && $instance->updated_at) {
            $serverTs = $instance->updated_at->timestamp;
            $clientTs = strtotime($clientUpdatedAt);

            if ($serverTs > $clientTs) {
                throw new \RuntimeException("Conflict: server version is newer for {$uuid}.");
            }
        }

        $instance->fill($data);
        $instance->save();
    }

    private function applyDelete(string $modelClass, ?string $uuid, User $user): void
    {
        if ($uuid === null) {
            throw new \RuntimeException('external_id is required for delete.');
        }

        $query = $modelClass::query()->where('external_id', $uuid);

        $entityName = array_search($modelClass, array_column($this->entityConfig, 'model'), true);
        if ($entityName !== false && isset($this->entityConfig[$entityName]['scope'])) {
            ($this->entityConfig[$entityName]['scope'])($query, $user->id);
        }

        $instance = $query->first();

        if ($instance === null) {
            return;
        }

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($modelClass), true)) {
            $instance->delete();
        } else {
            $instance->forceDelete();
        }
    }

    private function resolveRelationIds(string $entity, array $data): array
    {
        $resolvers = $this->relationResolvers[$entity] ?? [];

        // Field name normalisation — map Flutter field names to Laravel column names
        $data = match ($entity) {
            'commande_vetements' => self::mapFields($data, [
                'status' => 'statut',
                'due_date' => 'date_livraison',
            ]),
            'modele_vetements' => self::mapFields($data, [
                'modele_statut' => 'statut',
                'type_vetement_id' => 'type_vetement_id', // keep it, resolved below
            ]),
            'fiche_mesures' => self::mapFields($data, [
                'status' => 'statut',
            ]),
            'clients' => self::mapFields($data, [
                'sexe' => 'genre',
            ]),
            default => $data,
        };

        foreach ($resolvers as $incomingKey => $target) {
            $value = $data[$incomingKey] ?? null;

            if ($value === null || is_numeric($value) || ! is_string($value)) {
                continue;
            }

            $pk = $target['model']::query()
                ->where('external_id', $value)
                ->value('id');

            if ($pk !== null) {
                $data[$target['field']] = $pk;
            }

            unset($data[$incomingKey]);
        }

        return $data;
    }

    private static function mapFields(array $data, array $mapping): array
    {
        foreach ($mapping as $from => $to) {
            if (array_key_exists($from, $data) && $from !== $to) {
                $data[$to] = $data[$from];
                unset($data[$from]);
            }
        }

        return $data;
    }

    private static function deltaQuery(string $modelClass, ?callable $scope, string $since): array
    {
        $ts = $since;

        $query = $modelClass::query()
            ->where(function (Builder $q) use ($ts) {
                $q->where('updated_at', '>', $ts)
                    ->orWhere('created_at', '>', $ts);
            });

        if ($scope !== null) {
            $scope($query);
        }

        $all = $query->get();

        $created = $all->filter(fn (Model $m) => $m->created_at && $m->created_at->toIso8601String() > $ts)->values();
        $updated = $all->filter(fn (Model $m) => $m->updated_at && $m->updated_at->toIso8601String() > $ts && (! $m->created_at || $m->updated_at->toIso8601String() > $m->created_at->toIso8601String()))->values();

        $deleted = [];

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($modelClass))) {
            $trashed = $modelClass::onlyTrashed()
                ->where('deleted_at', '>', $ts);

            if ($scope !== null) {
                $scope($trashed);
            }

            $deleted = $trashed->get()->toArray();
        }

        return [
            'created' => $created->toArray(),
            'updated' => $updated->toArray(),
            'deleted' => $deleted,
        ];
    }
}

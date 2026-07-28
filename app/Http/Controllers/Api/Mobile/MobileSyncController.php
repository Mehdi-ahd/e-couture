<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CommandeVetement;
use App\Models\FicheMesure;
use App\Models\Mesure;
use App\Models\MesureModele;
use App\Models\ModeleVetement;
use App\Models\Patron;
use App\Models\PiecePatron;
use App\Models\TypeMesure;
use App\Models\TypeVetement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileSyncController extends Controller
{
    private const SCHEMA_VERSION = 1;

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
            'mesure_modeles' => [
                'model' => MesureModele::class,
                'scope' => fn (Builder $q, int $userId) => $q->whereHas('modeleVetement', fn (Builder $m) => $m->where('prestataire_id', $userId)),
                'with' => ['typeMesure'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.mesure_modeles', 50),
                'sync_size' => config('sync.page_sizes.sync.mesure_modeles', 200),
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
        ];
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schema_version' => 'nullable|integer|min:1',
            'device_id' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
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
        $validated = $request->validate([
            'schema_version' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();
        $user->loadCount(['clients as clients_count']);

        $pageSize = config('sync.page_sizes.bootstrap.clients', 100);

        $query = Client::query()
            ->where('prestataire_id', $user->id)
            ->orderBy('id')
            ->take($pageSize);

        $clients = $query->get();
        $totalCount = Client::where('prestataire_id', $user->id)->count();

        $lastPk = $clients->isNotEmpty() ? $clients->last()->id : 0;
        $hasMore = $totalCount > $pageSize;

        $cursor = base64_encode(json_encode([
            'mode' => 'bootstrap',
            'clients' => ['last_pk' => $lastPk, 'done' => ! $hasMore],
            'created_at' => now()->toIso8601String(),
        ]));

        return response()->json([
            'schema_version' => $validated['schema_version'] ?? self::SCHEMA_VERSION,
            'minimum_client_version' => '1.0.0',
            'cursor' => $cursor,
            'has_more' => $hasMore,
            'received' => ['clients' => $clients->count()],
            'expected' => ['clients' => $totalCount],
            'tables' => [
                'clients' => $clients->toArray(),
            ],
        ]);
    }

    public function next(Request $request): JsonResponse
    {
        // Support both legacy (entity+page) and v1 (cursor) formats
        if ($request->has('cursor')) {
            return $this->nextCursor($request);
        }

        // Legacy page-based next
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

        $cursor = json_decode(base64_decode($validated['cursor']), true);

        if (! $cursor || ($cursor['mode'] ?? null) !== 'bootstrap') {
            return response()->json(['message' => 'Curseur invalide.'], 422);
        }

        $user = $request->user();

        if (($cursor['clients']['done'] ?? false)) {
            return response()->json([
                'schema_version' => self::SCHEMA_VERSION,
                'minimum_client_version' => '1.0.0',
                'cursor' => $validated['cursor'],
                'has_more' => false,
                'received' => ['clients' => 0],
                'expected' => ['clients' => Client::where('prestataire_id', $user->id)->count()],
                'tables' => [],
            ]);
        }

        $pageSize = config('sync.page_sizes.bootstrap.clients', 100);
        $lastPk = $cursor['clients']['last_pk'] ?? 0;

        $query = Client::query()
            ->where('prestataire_id', $user->id)
            ->where('id', '>', $lastPk)
            ->orderBy('id')
            ->take($pageSize);

        $clients = $query->get();
        $totalCount = Client::where('prestataire_id', $user->id)->where('id', '>', $lastPk)->count();
        $newLastPk = $clients->isNotEmpty() ? $clients->last()->id : $lastPk;
        $hasMore = $totalCount > $pageSize;

        $cursor = base64_encode(json_encode([
            'mode' => 'bootstrap',
            'clients' => ['last_pk' => $newLastPk, 'done' => ! $hasMore],
            'created_at' => now()->toIso8601String(),
        ]));

        return response()->json([
            'schema_version' => self::SCHEMA_VERSION,
            'minimum_client_version' => '1.0.0',
            'cursor' => $cursor,
            'has_more' => $hasMore,
            'received' => ['clients' => $clients->count()],
            'expected' => ['client' => Client::where('prestataire_id', $user->id)->count()],
            'tables' => [
                'clients' => $clients->toArray(),
            ],
        ]);
    }

    public function delta(Request $request): JsonResponse
    {
        // Support both legacy (entities[]+since) and v1 (cursor) formats
        if ($request->has('cursor')) {
            $validated = $request->validate([
                'cursor' => 'required|string',
                'device_id' => 'nullable|string',
            ]);

            $cursor = json_decode(base64_decode($validated['cursor']), true);
            $since = $cursor['last_server_updated_at'] ?? now()->subDay()->toIso8601String();
        } else {
            $validated = $request->validate([
                'entities' => 'required|array',
                'entities.*.name' => 'required|string',
                'entities.*.since' => 'required|date_format:Y-m-d\TH:i:s.v\Z',
            ]);
            $since = $validated['entities'][0]['since'] ?? now()->subDay()->toIso8601String();
        }

        $user = $request->user();
        $result = [];
        $received = [];
        $expected = [];

        $entityNames = $request->has('entities')
            ? array_column($validated['entities'], 'name')
            : array_keys($this->entityConfig);

        foreach ($entityNames as $name) {
            if ($name === 'type_vetements' || $name === 'type_mesures') {
                $model = $name === 'type_vetements' ? TypeVetement::class : TypeMesure::class;
                $rows = self::deltaQuery($model, null, $since);

                continue;
            }

            if (! isset($this->entityConfig[$name])) {
                continue;
            }

            $config = $this->entityConfig[$name];
            $entityScope = $config['scope'];
            $scope = function (Builder $q) use ($entityScope, $user) {
                $entityScope($q, $user->id);
            };

            $delta = self::deltaQuery($config['model'], $scope, $since);
            $all = array_merge($delta['created'], $delta['updated'], $delta['deleted']);
            if (! empty($all)) {
                $result[$name] = $all;
            }
            $received[$name] = count($all);
            $expected[$name] = $config['model']::count();
        }

        $cursor = $this->buildDeltaCursor($since);

        return response()->json([
            'cursor' => $cursor,
            'has_more' => false,
            'received' => $received,
            'expected' => $expected,
            'tables' => $result,
        ]);
    }

    private function buildDeltaCursor(string $since): string
    {
        return base64_encode(json_encode([
            'mode' => 'delta',
            'last_server_updated_at' => now()->toIso8601String(),
        ]));
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

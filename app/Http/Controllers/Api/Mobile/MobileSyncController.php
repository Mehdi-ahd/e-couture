<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CommandeVetement;
use App\Models\FicheMesure;
use App\Models\Mesure;
use App\Models\ModeleVetement;
use App\Models\Patron;
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
                'scope' => fn (Builder $q, int $userId) => $q->where('prestataire_id', $userId),
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
                'scope' => fn (Builder $q, int $userId) => $q->whereHas('modeleVetement', fn (Builder $m) => $m->where('prestataire_id', $userId)),
                'with' => ['modeleVetement'],
                'bootstrap_size' => config('sync.page_sizes.bootstrap.patrons', 20),
                'sync_size' => config('sync.page_sizes.sync.patrons', 100),
            ],
        ];
    }

    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        $entities = [];

        foreach ($this->entityConfig as $name => $config) {
            $scope = $config['scope'];
            $query = $config['model']::query()->orderByDesc('updated_at');
            $scope($query, $user->id);

            $totalCount = $query->count();
            $size = $config['bootstrap_size'];

            if ($size === null) {
                $items = $query->get()->toArray();
                $entities[$name] = [
                    'items' => $items,
                    'total_count' => $totalCount,
                    'total_pages' => 1,
                    'bootstrap_complete' => true,
                ];
            } else {
                $totalPages = (int) ceil($totalCount / $size);
                $items = (clone $query)->take($size)->get()->toArray();
                $entities[$name] = [
                    'items' => $items,
                    'total_count' => $totalCount,
                    'total_pages' => max($totalPages, 1),
                    'bootstrap_complete' => $totalPages <= 1,
                ];
            }
        }

        // Catalog tables — always complete
        $entities['type_vetements'] = [
            'items' => TypeVetement::query()->orderBy('nom')->get()->toArray(),
            'total_count' => TypeVetement::count(),
            'total_pages' => 1,
            'bootstrap_complete' => true,
        ];
        $entities['type_mesures'] = [
            'items' => TypeMesure::query()->orderBy('nom')->get()->toArray(),
            'total_count' => TypeMesure::count(),
            'total_pages' => 1,
            'bootstrap_complete' => true,
        ];

        return response()->json([
            'data' => [
                'schema_version' => self::SCHEMA_VERSION,
                'server_time' => now()->toIso8601String(),
                'user' => $user->toArray(),
                'entities' => $entities,
            ],
        ]);
    }

    public function next(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity' => 'required|string',
            'page' => 'required|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:500',
        ]);

        $entity = $validated['entity'];

        if (! isset($this->entityConfig[$entity])) {
            return response()->json(['message' => 'Entité inconnue.'], 422);
        }

        $user = $request->user();
        $config = $this->entityConfig[$entity];
        $pageSize = $validated['page_size'] ?? $config['sync_size'];
        $scope = $config['scope'];

        $query = $config['model']::query()->orderByDesc('updated_at');
        $scope($query, $user->id);

        $totalCount = $query->count();
        $totalPages = (int) ceil($totalCount / $pageSize);
        $page = min($validated['page'], max($totalPages, 1));

        $items = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->toArray();

        return response()->json([
            'data' => [
                'items' => $items,
                'page' => $page,
                'page_size' => $pageSize,
                'total_count' => $totalCount,
                'total_pages' => $totalPages,
                'finished' => $page >= $totalPages,
            ],
        ]);
    }

    public function delta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entities' => 'required|array',
            'entities.*.name' => 'required|string',
            'entities.*.since' => 'required|date_format:Y-m-d\TH:i:s.v\Z',
        ]);

        $user = $request->user();
        $result = [];

        foreach ($validated['entities'] as $e) {
            $name = $e['name'];
            $since = $e['since'];

            if ($name === 'type_vetements' || $name === 'type_mesures') {
                $model = $name === 'type_vetements' ? TypeVetement::class : TypeMesure::class;
                $result[$name] = self::deltaQuery($model, null, $since);

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

            $result[$name] = self::deltaQuery($config['model'], $scope, $since);
        }

        return response()->json([
            'data' => [
                'entities' => $result,
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }

    public function push(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mutations' => 'required|array',
            'mutations.*.mutation_id' => 'required|uuid',
            'mutations.*.entity' => 'required|string',
            'mutations.*.action' => 'required|in:create,update,delete',
            'mutations.*.data' => 'required|array',
        ]);

        $user = $request->user();
        $results = [];

        DB::beginTransaction();
        try {
            foreach ($validated['mutations'] as $mutation) {
                $results[] = $this->processMutation($mutation, $user);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['message' => 'Erreur lors du traitement.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['data' => ['results' => $results]]);
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

        return [
            'mutation_id' => $mutationId,
            'uuid' => $uuid,
            'status' => 'applied',
        ];
    }

    private function applyCreate(string $modelClass, array $data, User $user): void
    {
        $instance = new $modelClass;

        $data['external_id'] ??= (string) Str::uuid();

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

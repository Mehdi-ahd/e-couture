<?php

use App\Models\Client;
use App\Models\ModeleVetement;
use App\Models\TypeVetement;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test-device')->plainTextToken;
});

afterEach(function () {
    $this->user->tokens()->delete();
    $this->user->delete();
});

test('bootstrap returns schema_version, server_time, user and all entities', function () {
    Client::factory(3)->create(['prestataire_id' => $this->user->id]);

    $typeVet = TypeVetement::factory()->create();

    ModeleVetement::factory(2)->create([
        'prestataire_id' => $this->user->id,
        'type_vetement_id' => $typeVet->id,
    ]);

    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/bootstrap');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'schema_version',
                'server_time',
                'user',
                'entities' => [
                    'clients' => ['items', 'total_count', 'total_pages', 'bootstrap_complete'],
                    'modele_vetements' => ['items', 'total_count', 'total_pages', 'bootstrap_complete'],
                    'commande_vetements' => ['items', 'total_count', 'total_pages', 'bootstrap_complete'],
                    'fiche_mesures' => ['items', 'total_count', 'total_pages', 'bootstrap_complete'],
                    'mesures' => ['items', 'total_count', 'total_pages', 'bootstrap_complete'],
                    'patrons' => ['items', 'total_count', 'total_pages', 'bootstrap_complete'],
                    'type_vetements' => ['items', 'total_count', 'total_pages', 'bootstrap_complete'],
                    'type_mesures' => ['items', 'total_count', 'total_pages', 'bootstrap_complete'],
                ],
            ],
        ]);

    $data = $response->json('data');
    expect($data['schema_version'])->toBe(1);
    expect($data['user']['id'])->toBe($this->user->id);
    expect($data['entities']['clients']['total_count'])->toBe(3);
    expect($data['entities']['modele_vetements']['total_count'])->toBe(2);
});

test('bootstrap only returns data belonging to the authenticated user', function () {
    $otherUser = User::factory()->create();
    Client::factory(5)->create(['prestataire_id' => $otherUser->id]);
    Client::factory(2)->create(['prestataire_id' => $this->user->id]);

    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/bootstrap');

    expect($response->json('data.entities.clients.total_count'))->toBe(2);

    $otherUser->tokens()->delete();
    $otherUser->delete();
});

test('next returns paginated results', function () {
    Client::factory(150)->create(['prestataire_id' => $this->user->id]);

    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/next', [
            'entity' => 'clients',
            'page' => 1,
            'page_size' => 50,
        ]);

    $response->assertOk();
    $data = $response->json('data');
    expect(count($data['items']))->toBe(50);
    expect($data['page'])->toBe(1);
    expect($data['total_count'])->toBe(150);
    expect($data['total_pages'])->toBe(3);
    expect($data['finished'])->toBeFalse();

    // Last page
    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/next', [
            'entity' => 'clients',
            'page' => 3,
            'page_size' => 50,
        ]);

    expect($response->json('data.finished'))->toBeTrue();
});

test('next rejects unknown entity', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/next', [
            'entity' => 'nonexistent',
            'page' => 1,
        ]);

    $response->assertStatus(422);
});

test('push creates and tracks mutation_id for idempotency', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/push', [
            'mutations' => [
                [
                    'mutation_id' => '00000000-0000-0000-0000-000000000001',
                    'entity' => 'clients',
                    'action' => 'create',
                    'data' => [
                        'nom' => 'Test Client',
                        'prenom' => 'Sync',
                        'telephone' => '+22990000099',
                    ],
                ],
            ],
        ]);

    $response->assertOk();
    expect($response->json('data.results.0.status'))->toBe('applied');

    $client = Client::where('nom', 'Test Client')->first();
    expect($client)->not->toBeNull();
    expect((int) $client->prestataire_id)->toBe($this->user->id);

    // Push same mutation_id again → duplicate
    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/push', [
            'mutations' => [
                [
                    'mutation_id' => '00000000-0000-0000-0000-000000000001',
                    'entity' => 'clients',
                    'action' => 'create',
                    'data' => [
                        'nom' => 'Duplicate',
                        'prenom' => 'Should Not Create',
                        'telephone' => '+22990000100',
                    ],
                ],
            ],
        ]);

    expect($response->json('data.results.0.status'))->toBe('duplicate');

    $duplicate = Client::where('nom', 'Duplicate')->first();
    expect($duplicate)->toBeNull();
});

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

test('bootstrap returns v1 protocol format', function () {
    Client::factory(3)->create(['prestataire_id' => $this->user->id]);

    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/bootstrap');

    $response->assertOk()
        ->assertJsonStructure([
            'schema_version',
            'minimum_client_version',
            'cursor',
            'has_more',
            'received',
            'expected',
            'tables' => [
                'clients',
            ],
        ]);

    expect($response->json('schema_version'))->toBe(1);
    expect($response->json('received.clients'))->toBe(3);
    expect($response->json('expected.clients'))->toBe(3);
    expect(count($response->json('tables.clients')))->toBe(3);
});

test('bootstrap only returns data belonging to the authenticated user', function () {
    $otherUser = User::factory()->create();
    Client::factory(5)->create(['prestataire_id' => $otherUser->id]);
    Client::factory(2)->create(['prestataire_id' => $this->user->id]);

    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/bootstrap');

    expect($response->json('received.clients'))->toBe(2);
    expect($response->json('expected.clients'))->toBe(2);

    $otherUser->tokens()->delete();
    $otherUser->delete();
});

test('next returns paginated results', function () {
    Client::factory(150)->create(['prestataire_id' => $this->user->id]);

    // Bootstrap to get cursor
    $bootstrap = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/bootstrap');

    $cursor = $bootstrap->json('cursor');
    expect($cursor)->not->toBeNull();

    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/next', [
            'cursor' => $cursor,
        ]);

    $response->assertOk();
    expect($response->json('received.clients'))->toBeGreaterThan(0);
    expect($response->json('has_more'))->toBeBool();
    expect($response->json('tables'))->toHaveKey('clients');

    // Test legacy page-based next still works
    $legacy = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/next', [
            'entity' => 'clients',
            'page' => 1,
            'page_size' => 50,
        ]);

    $legacy->assertOk();
    expect(count($legacy->json('data.items')))->toBe(50);
    expect($legacy->json('data.page'))->toBe(1);
    expect($legacy->json('data.total_count'))->toBe(150);
    expect($legacy->json('data.total_pages'))->toBe(3);
    expect($legacy->json('data.finished'))->toBeFalse();
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
    // v1 protocol format
    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/push', [
            'mutations' => [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'table' => 'clients',
                    'operation' => 'create',
                    'external_id' => 'EXT_TEST_1',
                    'payload' => [
                        'nom' => 'Test Client',
                        'prenom' => 'Sync',
                        'telephone' => '+22990000099',
                    ],
                ],
            ],
        ]);

    $response->assertOk();
    expect($response->json('accepted'))->toHaveCount(1);
    expect($response->json('accepted.0'))->toBe('00000000-0000-0000-0000-000000000001');
    expect($response->json('conflicts'))->toHaveCount(0);
    expect($response->json('failed'))->toHaveCount(0);

    $client = Client::where('nom', 'Test Client')->first();
    expect($client)->not->toBeNull();
    expect((int) $client->prestataire_id)->toBe($this->user->id);

    // Push same uuid again → duplicate
    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/push', [
            'mutations' => [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'table' => 'clients',
                    'operation' => 'create',
                    'external_id' => 'EXT_TEST_1',
                    'payload' => [
                        'nom' => 'Duplicate',
                        'prenom' => 'Should Not Create',
                        'telephone' => '+22990000100',
                    ],
                ],
            ],
        ]);

    expect($response->json('accepted'))->toHaveCount(1);
    expect($response->json('accepted.0'))->toBe('00000000-0000-0000-0000-000000000001');

    $duplicate = Client::where('nom', 'Duplicate')->first();
    expect($duplicate)->toBeNull();
});

test('push accepts legacy mutation format', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/mobile/sync/push', [
            'mutations' => [
                [
                    'mutation_id' => '00000000-0000-0000-0000-111111111111',
                    'entity' => 'clients',
                    'action' => 'create',
                    'data' => [
                        'nom' => 'Legacy Client',
                        'prenom' => 'Format',
                        'telephone' => '+22990000111',
                    ],
                ],
            ],
        ]);

    $response->assertOk();
    expect($response->json('accepted'))->toHaveCount(1);

    $client = Client::where('nom', 'Legacy Client')->first();
    expect($client)->not->toBeNull();
});

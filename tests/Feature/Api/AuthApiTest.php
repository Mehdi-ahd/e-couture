<?php

use App\Models\User;

test('api users can register and receive a sanctum token', function () {
    $response = $this->postJson('/api/auth/register', [
        'nom' => 'Doe',
        'prenom' => 'Jane',
        'telephone' => '+22990000002',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'pixel-8',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.user.primary_role', User::ROLE_COUTURIER)
        ->assertJsonPath('data.user.roles.0', User::ROLE_COUTURIER)
        ->assertJsonStructure([
            'message',
            'data' => [
                'token',
                'token_type',
                'user' => [
                    'external_id',
                    'nom',
                    'prenom',
                    'full_name',
                    'email',
                    'telephone',
                    'est_actif',
                    'roles',
                    'primary_role',
                    'email_verified_at',
                    'last_login_at',
                ],
            ],
        ]);

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($user->hasRole(User::ROLE_COUTURIER))->toBeTrue();
});

test('api users can login and read their profile', function () {
    $user = User::factory()->create([
        'email' => 'couturier@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => 'couturier@example.com',
        'password' => 'password',
        'device_name' => 'iphone-15',
    ]);

    $token = $loginResponse->json('data.token');

    $loginResponse
        ->assertOk()
        ->assertJsonPath('data.user.external_id', $user->external_id);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('data.user.email', 'couturier@example.com');
});

test('api users can logout and invalidate the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('pixel-8')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/auth/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Deconnexion effectuee avec succes.');

    expect($user->fresh()->tokens)->toHaveCount(0);
});

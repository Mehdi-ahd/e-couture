<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'nom' => 'Doe',
        'prenom' => 'Jane',
        'telephone' => '+22990000001',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    expect($user->nom)->toBe('Doe');
    expect($user->prenom)->toBe('Jane');
    expect($user->telephone)->toBe('+22990000001');
    expect($user->hasRole(User::ROLE_COUTURIER))->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false));
});

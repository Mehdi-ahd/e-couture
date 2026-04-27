<?php

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
    $this->assertDatabaseHas('users', [
        'nom' => 'Doe',
        'prenom' => 'Jane',
        'telephone' => '+22990000001',
        'type' => 'CLIENT',
    ]);
    $response->assertRedirect(route('dashboard', absolute: false));
});

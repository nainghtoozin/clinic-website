<?php

test('registration screen is not accessible', function () {
    $response = $this->get('/register');

    // Registration has been disabled for clinic security.
    // Staff accounts should be created by administrators.
    $response->assertStatus(404);
});

test('registration POST is not accessible', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // Registration has been disabled for clinic security.
    $response->assertStatus(404);
});

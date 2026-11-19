<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('login screen prefills demo credentials in local environment', function () {
    app()->detectEnvironment(fn () => 'local');

    $this->get('/login')
        ->assertSee('value="admin@example.com"', false)
        ->assertSee('value="password"', false);
});

test('login screen does not prefill credentials outside local environment', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/login')
        ->assertDontSee('admin@example.com')
        ->assertDontSee('value="password"', false);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

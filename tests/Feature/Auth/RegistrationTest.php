<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $this->seed(RolePermissionSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users receive the user role and not the admin role', function () {
    $this->seed(RolePermissionSeeder::class);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $pengguna = User::where('email', 'test@example.com')->firstOrFail();

    expect($pengguna->getRoleNames()->all())->toBe(['user'])
        ->and($pengguna->hasRole('admin'))->toBeFalse()
        ->and($pengguna->can('posts.view'))->toBeTrue()
        ->and($pengguna->can('posts.create'))->toBeFalse();
});

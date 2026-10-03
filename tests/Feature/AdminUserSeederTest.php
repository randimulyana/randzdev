<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config([
        'admin.name' => 'Administrator',
        'admin.email' => 'admin@randz.dev',
        'admin.password' => 'development-secret',
    ]);
});

it('creates the admin account with a hashed password and the admin role', function () {
    $this->seed([RolePermissionSeeder::class, AdminUserSeeder::class]);

    $admin = User::where('email', 'admin@randz.dev')->firstOrFail();

    expect($admin->name)->toBe('Administrator')
        ->and($admin->password)->not->toBe('development-secret')
        ->and(Hash::check('development-secret', $admin->password))->toBeTrue()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and($admin->can('permissions.delete'))->toBeTrue();
});

it('does not duplicate the admin account when run repeatedly', function () {
    $this->seed([RolePermissionSeeder::class, AdminUserSeeder::class, AdminUserSeeder::class]);

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseCount('model_has_roles', 1);
});

it('skips account creation when no password is configured', function () {
    config(['admin.password' => null]);

    $this->seed([RolePermissionSeeder::class, AdminUserSeeder::class]);

    $this->assertDatabaseMissing('users', ['email' => 'admin@randz.dev']);
});

it('refuses to create the admin account in production', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->app['env'] = 'production';

    $this->artisan('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true])
        ->assertSuccessful();

    $this->assertDatabaseMissing('users', ['email' => 'admin@randz.dev']);
});

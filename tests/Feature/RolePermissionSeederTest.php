<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('the seeder creates exactly the defined permissions and roles', function () {
    expect(Permission::pluck('name')->sort()->values()->all())
        ->toBe(collect(RolePermissionSeeder::PERMISSIONS)->sort()->values()->all());

    expect(Role::pluck('name')->sort()->values()->all())
        ->toBe(['admin', 'author', 'user']);
});

test('the seeder can be run repeatedly without duplicating records', function () {
    $this->seed(RolePermissionSeeder::class);

    $this->assertDatabaseCount('permissions', 41);
    $this->assertDatabaseCount('roles', 3);
    $this->assertDatabaseCount('role_has_permissions', 41 + 28 + 4);
});

test('admin is granted every permission', function () {
    $pengguna = User::factory()->create()->assignRole('admin');

    expect($pengguna->hasRole('admin'))->toBeTrue()
        ->and($pengguna->getAllPermissions()->pluck('name')->sort()->values()->all())
        ->toBe(Permission::pluck('name')->sort()->values()->all());
});

test('admin can manage everything', function (string $permission) {
    $pengguna = User::factory()->create()->assignRole('admin');

    expect($pengguna->hasPermissionTo($permission))->toBeTrue()
        ->and($pengguna->can($permission))->toBeTrue();
})->with([
    'posts.create', 'posts.update', 'posts.delete',
    'projects.create', 'projects.update', 'projects.delete',
    'learning_paths.create', 'learning_paths.update', 'learning_paths.delete',
    'lessons.create', 'lessons.update', 'lessons.delete',
    'categories.create', 'categories.update', 'categories.delete',
    'technologies.create', 'technologies.update', 'technologies.delete',
    'tags.create', 'tags.update', 'tags.delete',
    'users.create', 'users.update', 'users.delete',
    'roles.create', 'roles.update', 'roles.delete',
    'permissions.create', 'permissions.update', 'permissions.delete',
]);

test('author can manage content', function (string $permission) {
    $pengguna = User::factory()->create()->assignRole('author');

    expect($pengguna->hasRole('author'))->toBeTrue()
        ->and($pengguna->hasRole('admin'))->toBeFalse()
        ->and($pengguna->hasPermissionTo($permission))->toBeTrue()
        ->and($pengguna->can($permission))->toBeTrue();
})->with([
    'posts.create', 'posts.update', 'posts.delete',
    'projects.create', 'projects.update', 'projects.delete',
    'learning_paths.view', 'learning_paths.create', 'learning_paths.update', 'learning_paths.delete',
    'lessons.create', 'lessons.update', 'lessons.delete',
    'categories.view', 'categories.create', 'categories.update', 'categories.delete',
    'technologies.view', 'technologies.create', 'technologies.update', 'technologies.delete',
    'tags.view', 'tags.create', 'tags.update', 'tags.delete',
]);

test('author cannot manage users, roles, or permissions', function (string $permission) {
    $pengguna = User::factory()->create()->assignRole('author');

    expect($pengguna->hasPermissionTo($permission))->toBeFalse()
        ->and($pengguna->can($permission))->toBeFalse();
})->with([
    'users.view', 'users.create', 'users.update', 'users.delete',
    'roles.view', 'roles.create', 'roles.update', 'roles.delete',
    'permissions.view', 'permissions.create', 'permissions.update', 'permissions.delete',
    'contact_messages.view',
]);

test('user can view content', function (string $permission) {
    $pengguna = User::factory()->create()->assignRole('user');

    expect($pengguna->hasRole('user'))->toBeTrue()
        ->and($pengguna->hasRole('admin'))->toBeFalse()
        ->and($pengguna->hasPermissionTo($permission))->toBeTrue()
        ->and($pengguna->can($permission))->toBeTrue();
})->with([
    'posts.view', 'projects.view', 'learning_paths.view', 'lessons.view',
]);

test('user cannot modify content', function (string $permission) {
    $pengguna = User::factory()->create()->assignRole('user');

    expect($pengguna->hasPermissionTo($permission))->toBeFalse()
        ->and($pengguna->can($permission))->toBeFalse();
})->with([
    'posts.create', 'posts.update', 'posts.delete',
    'projects.create', 'projects.update', 'projects.delete',
    'learning_paths.create', 'learning_paths.update', 'learning_paths.delete',
    'lessons.create', 'lessons.update', 'lessons.delete',
    'categories.create', 'categories.update', 'categories.delete',
    'technologies.create', 'technologies.update', 'technologies.delete',
    'tags.create', 'tags.update', 'tags.delete',
]);

test('user without a role has no permissions', function () {
    $pengguna = User::factory()->create();

    expect($pengguna->getAllPermissions())->toBeEmpty()
        ->and($pengguna->can('posts.view'))->toBeFalse();
});

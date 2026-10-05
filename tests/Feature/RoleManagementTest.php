<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

// ==========================================
// 1. Authorization & Access Tests
// ==========================================

test('guest is redirected to login for role and permission routes', function () {
    $customRole = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $this->get(route('admin.roles.index'))->assertRedirect(route('login'));
    $this->get(route('admin.roles.create'))->assertRedirect(route('login'));
    $this->post(route('admin.roles.store'), ['name' => 'reviewer'])->assertRedirect(route('login'));
    $this->get(route('admin.roles.edit', $customRole))->assertRedirect(route('login'));
    $this->put(route('admin.roles.update', $customRole), ['name' => 'reviewer'])->assertRedirect(route('login'));
    $this->delete(route('admin.roles.destroy', $customRole))->assertRedirect(route('login'));
    $this->get(route('admin.permissions.index'))->assertRedirect(route('login'));
});

test('regular user receives 403 for role and permission routes', function () {
    $customRole = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs($this->user)->get(route('admin.roles.index'))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.roles.create'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.roles.store'), ['name' => 'reviewer'])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.roles.edit', $customRole))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.roles.update', $customRole), ['name' => 'reviewer'])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.roles.destroy', $customRole))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.permissions.index'))->assertForbidden();
});

test('author receives 403 for role and permission routes', function () {
    $customRole = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs($this->author)->get(route('admin.roles.index'))->assertForbidden();
    $this->actingAs($this->author)->get(route('admin.roles.create'))->assertForbidden();
    $this->actingAs($this->author)->post(route('admin.roles.store'), ['name' => 'reviewer'])->assertForbidden();
    $this->actingAs($this->author)->get(route('admin.roles.edit', $customRole))->assertForbidden();
    $this->actingAs($this->author)->put(route('admin.roles.update', $customRole), ['name' => 'reviewer'])->assertForbidden();
    $this->actingAs($this->author)->delete(route('admin.roles.destroy', $customRole))->assertForbidden();
    $this->actingAs($this->author)->get(route('admin.permissions.index'))->assertForbidden();
});

test('admin can access role and permission routes', function () {
    $customRole = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs($this->admin)->get(route('admin.roles.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.roles.create'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.roles.edit', $customRole))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.permissions.index'))->assertOk();
});

// ==========================================
// 2. Role Listing Tests
// ==========================================

test('admin can view roles index with system roles and counts', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.roles.index'));

    $response->assertOk()
        ->assertViewIs('admin.roles.index')
        ->assertSee('admin')
        ->assertSee('author')
        ->assertSee('user')
        ->assertSee('System Role')
        ->assertViewHas('roles', function ($roles) {
            return $roles->count() >= 3;
        });
});

// ==========================================
// 3. Create Role Tests
// ==========================================

test('admin can create a custom role with permissions', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
        'name' => 'community_moderator',
        'permissions' => ['posts.view', 'posts.update'],
    ]);

    $response->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success', 'Role created successfully.');

    $role = Role::where('name', 'community_moderator')->first();
    expect($role)->not->toBeNull()
        ->and($role->hasPermissionTo('posts.view'))->toBeTrue()
        ->and($role->hasPermissionTo('posts.update'))->toBeTrue()
        ->and($role->hasPermissionTo('posts.delete'))->toBeFalse();
});

test('role creation fails when role name is duplicate', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
        'name' => 'admin',
    ]);

    $response->assertSessionHasErrors('name');
});

test('role creation fails when name format is invalid', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
        'name' => 'Invalid Role Name With Spaces!',
    ]);

    $response->assertSessionHasErrors('name');
});

test('role creation fails when non-existent permission is submitted', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
        'name' => 'hacker',
        'permissions' => ['non_existent.permission'],
    ]);

    $response->assertSessionHasErrors('permissions.0');
});

// ==========================================
// 4. Edit & Update Role Tests
// ==========================================

test('admin can update a custom role name and permissions', function () {
    $role = Role::create(['name' => 'old_moderator', 'guard_name' => 'web']);
    $role->syncPermissions(['posts.view']);

    $response = $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
        'name' => 'new_moderator',
        'permissions' => ['posts.view', 'posts.create'],
    ]);

    $response->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success', 'Role updated successfully.');

    $role->refresh();
    expect($role->name)->toBe('new_moderator')
        ->and($role->hasPermissionTo('posts.create'))->toBeTrue();
});

test('updating permissions affects user authorization immediately', function () {
    $role = Role::create(['name' => 'guest_editor', 'guard_name' => 'web']);
    $testUser = User::factory()->create()->assignRole('guest_editor');

    expect($testUser->can('categories.create'))->toBeFalse();

    $response = $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
        'name' => 'guest_editor',
        'permissions' => ['categories.create'],
    ]);

    $response->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success', 'Role updated successfully.');

    $testUser = User::find($testUser->id);
    expect($testUser->can('categories.create'))->toBeTrue();
});

// ==========================================
// 5. System Role Protection Tests
// ==========================================

test('system roles cannot be renamed', function (string $systemRoleName) {
    $role = Role::findByName($systemRoleName);

    $response = $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
        'name' => 'renamed_'.$systemRoleName,
        'permissions' => $role->permissions->pluck('name')->toArray(),
    ]);

    $response->assertSessionHasErrors('name');

    $role->refresh();
    expect($role->name)->toBe($systemRoleName);
})->with(['admin', 'author', 'user']);

test('admin role permissions cannot be altered or reduced', function () {
    $adminRole = Role::findByName('admin');
    $initialPermCount = $adminRole->permissions()->count();

    $response = $this->actingAs($this->admin)->put(route('admin.roles.update', $adminRole), [
        'permissions' => ['posts.view'], // attempting to strip all other permissions
    ]);

    $response->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('error', 'The admin role permissions are protected and cannot be modified.');

    $adminRole->refresh();
    expect($adminRole->permissions()->count())->toBe($initialPermCount);
});

test('author and user system roles can have their permission set managed', function () {
    $authorRole = Role::findByName('author');

    $this->actingAs($this->admin)->put(route('admin.roles.update', $authorRole), [
        'permissions' => ['posts.view', 'posts.create'],
    ]);

    $authorRole->refresh();
    expect($authorRole->hasPermissionTo('posts.view'))->toBeTrue()
        ->and($authorRole->hasPermissionTo('posts.create'))->toBeTrue()
        ->and($authorRole->hasPermissionTo('posts.delete'))->toBeFalse();
});

test('system roles cannot be deleted', function (string $systemRoleName) {
    $role = Role::findByName($systemRoleName);

    $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));

    $response->assertForbidden();

    expect(Role::where('name', $systemRoleName)->exists())->toBeTrue();
})->with(['admin', 'author', 'user']);

// ==========================================
// 6. Delete Custom Role & Orphan Protection
// ==========================================

test('admin can delete an unused custom role', function () {
    $role = Role::create(['name' => 'temp_role', 'guard_name' => 'web']);

    $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));

    $response->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success', 'Role deleted successfully.');

    expect(Role::where('name', 'temp_role')->exists())->toBeFalse();
});

test('custom role cannot be deleted if assigned to users', function () {
    $role = Role::create(['name' => 'assigned_role', 'guard_name' => 'web']);
    User::factory()->create()->assignRole('assigned_role');

    $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));

    $response->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('error');

    expect(Role::where('name', 'assigned_role')->exists())->toBeTrue();
});

// ==========================================
// 7. Permission Matrix Tests
// ==========================================

test('permission matrix page displays all roles and permission domains', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.permissions.index'));

    $response->assertOk()
        ->assertViewIs('admin.permissions.index')
        ->assertSee('Permission Matrix')
        ->assertSee('Posts (Blog)')
        ->assertSee('posts.view')
        ->assertSee('admin')
        ->assertSee('author')
        ->assertSee('user');
});

test('permission endpoints for create, update, or delete do not exist', function () {
    $this->actingAs($this->admin)->post('/admin/permissions', [])->assertStatus(405);
    $this->actingAs($this->admin)->put('/admin/permissions/1', [])->assertNotFound();
    $this->actingAs($this->admin)->delete('/admin/permissions/1')->assertNotFound();
});

// ==========================================
// 8. User Management Integration Tests
// ==========================================

test('newly created custom role is available in user creation and edit', function () {
    Role::create(['name' => 'auditor', 'guard_name' => 'web']);

    $createResponse = $this->actingAs($this->admin)->get(route('admin.users.create'));
    $createResponse->assertOk()
        ->assertSee('Auditor');

    $testUser = User::factory()->create()->assignRole('user');
    $editResponse = $this->actingAs($this->admin)->get(route('admin.users.edit', $testUser));
    $editResponse->assertOk()
        ->assertSee('Auditor');
});

test('admin can create and assign user to custom role', function () {
    Role::create(['name' => 'coordinator', 'guard_name' => 'web']);

    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'Coordinator Jane',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'coordinator',
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole('coordinator'))->toBeTrue();
});

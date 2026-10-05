<?php

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

// --- Authorization Tests ---

test('guest is redirected to login for user management routes', function () {
    $targetUser = User::factory()->create();

    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    $this->get(route('admin.users.create'))->assertRedirect(route('login'));
    $this->post(route('admin.users.store'), [])->assertRedirect(route('login'));
    $this->get(route('admin.users.edit', $targetUser))->assertRedirect(route('login'));
    $this->put(route('admin.users.update', $targetUser), [])->assertRedirect(route('login'));
    $this->delete(route('admin.users.destroy', $targetUser))->assertRedirect(route('login'));
});

test('regular user receives 403 for user management routes', function () {
    $targetUser = User::factory()->create();

    $this->actingAs($this->user)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.users.create'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.users.store'), [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.users.edit', $targetUser))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.users.update', $targetUser), [
        'name' => 'Updated Name',
        'email' => $targetUser->email,
        'role' => 'user',
    ])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.users.destroy', $targetUser))->assertForbidden();
});

test('author receives 403 for user management routes', function () {
    $targetUser = User::factory()->create();

    $this->actingAs($this->author)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($this->author)->get(route('admin.users.create'))->assertForbidden();
    $this->actingAs($this->author)->post(route('admin.users.store'), [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ])->assertForbidden();
    $this->actingAs($this->author)->get(route('admin.users.edit', $targetUser))->assertForbidden();
    $this->actingAs($this->author)->put(route('admin.users.update', $targetUser), [
        'name' => 'Updated Name',
        'email' => $targetUser->email,
        'role' => 'user',
    ])->assertForbidden();
    $this->actingAs($this->author)->delete(route('admin.users.destroy', $targetUser))->assertForbidden();
});

test('admin can access users index', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertViewIs('admin.users.index');
});

// --- Index Tests ---

test('admin can view users list with data', function () {
    $targetUser = User::factory()->create([
        'name' => 'Alice Johnson',
        'email' => 'alice@example.com',
    ]);
    $targetUser->assignRole('author');

    $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

    $response->assertOk()
        ->assertSee('Alice Johnson')
        ->assertSee('alice@example.com')
        ->assertSee('author');
});

test('users are paginated at 15 per page', function () {
    User::factory()->count(20)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

    $response->assertOk()
        ->assertViewHas('users', function ($users) {
            return $users->perPage() === 15;
        });
});

test('roles are displayed accurately with badges', function () {
    $customAuthor = User::factory()->create()->assignRole('author');
    $customUser = User::factory()->create()->assignRole('user');

    $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

    $response->assertOk()
        ->assertSee('admin')
        ->assertSee('author')
        ->assertSee('user');
});

test('email verification status is displayed correctly', function () {
    $verified = User::factory()->create(['email_verified_at' => now()]);
    $unverified = User::factory()->unverified()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

    $response->assertOk()
        ->assertSee('Verified')
        ->assertSee('Unverified');
});

// --- Create Tests ---

test('admin can view create page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertViewIs('admin.users.create')
        ->assertViewHas('roles', ['admin', 'author', 'user']);
});

test('admin can create a user with a specific role', function (string $role) {
    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => "New {$role} User",
        'email' => "test_{$role}@example.com",
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => $role,
    ]);

    $response->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success', 'User created successfully.');

    $user = User::where('email', "test_{$role}@example.com")->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe("New {$role} User")
        ->and($user->hasRole($role))->toBeTrue()
        ->and(Hash::check('secret123', $user->password))->toBeTrue();
})->with(['admin', 'author', 'user']);

test('user creation fails with duplicate email', function () {
    $existing = User::factory()->create(['email' => 'duplicate@example.com']);

    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'Duplicate Candidate',
        'email' => 'duplicate@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'user',
    ]);

    $response->assertSessionHasErrors('email');
});

test('user creation fails with invalid role', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'Hacker Candidate',
        'email' => 'hacker@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'superadmin',
    ]);

    $response->assertSessionHasErrors('role');
});

test('user creation fails with validation errors', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'different',
        'role' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

// --- Edit & Update Tests ---

test('admin can view edit page', function () {
    $target = User::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.users.edit', $target))
        ->assertOk()
        ->assertViewIs('admin.users.edit')
        ->assertViewHas('user')
        ->assertViewHas('roles', ['admin', 'author', 'user']);
});

test('admin can update user details and role', function () {
    $target = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ])->assignRole('user');

    $response = $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'author',
    ]);

    $response->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success', 'User updated successfully.');

    $target->refresh();
    expect($target->name)->toBe('Updated Name')
        ->and($target->email)->toBe('updated@example.com')
        ->and($target->hasRole('author'))->toBeTrue()
        ->and($target->hasRole('user'))->toBeFalse();
});

test('updating user without password preserves existing password', function () {
    $target = User::factory()->create([
        'password' => 'oldpassword123',
    ]);
    $oldHash = $target->password;

    $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
        'name' => $target->name,
        'email' => $target->email,
        'role' => 'user',
        'password' => '',
        'password_confirmation' => '',
    ])->assertRedirect(route('admin.users.index'));

    $target->refresh();
    expect(Hash::check('oldpassword123', $target->password))->toBeTrue();
});

test('updating user with new password updates password successfully', function () {
    $target = User::factory()->create([
        'password' => 'oldpassword123',
    ]);

    $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
        'name' => $target->name,
        'email' => $target->email,
        'role' => 'user',
        'password' => 'brandnewpassword123',
        'password_confirmation' => 'brandnewpassword123',
    ])->assertRedirect(route('admin.users.index'));

    $target->refresh();
    expect(Hash::check('brandnewpassword123', $target->password))->toBeTrue();
});

test('updating user fails with duplicate email', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $target = User::factory()->create(['email' => 'target@example.com']);

    $response = $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
        'name' => $target->name,
        'email' => 'taken@example.com',
        'role' => 'user',
    ]);

    $response->assertSessionHasErrors('email');
});

test('updating user fails with invalid role', function () {
    $target = User::factory()->create();

    $response = $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
        'name' => $target->name,
        'email' => $target->email,
        'role' => 'invalid_role',
    ]);

    $response->assertSessionHasErrors('role');
});

test('admin cannot demote themselves to non-admin role', function (string $attemptedRole) {
    $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
        'name' => $this->admin->name,
        'email' => $this->admin->email,
        'role' => $attemptedRole,
    ]);

    $response->assertSessionHasErrors('role')
        ->assertSessionHas('error', 'You cannot change your own role away from admin.');

    $this->admin->refresh();
    expect($this->admin->hasRole('admin'))->toBeTrue()
        ->and($this->admin->hasRole($attemptedRole))->toBeFalse();
})->with(['author', 'user']);

// --- Delete Tests ---

test('admin can delete eligible user', function () {
    $target = User::factory()->create()->assignRole('user');

    $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $target));

    $response->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success', 'User deleted successfully.');

    expect(User::find($target->id))->toBeNull();
});

test('admin cannot delete themselves', function () {
    $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

    $response->assertForbidden();

    expect(User::find($this->admin->id))->not->toBeNull();
});

test('admin cannot delete user with posts', function () {
    $target = User::factory()->create()->assignRole('author');
    Post::factory()->create(['author_id' => $target->id]);

    $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $target));

    $response->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('error', 'Cannot delete this user because they still own associated content. Reassign or remove the associated content first.');

    expect(User::find($target->id))->not->toBeNull();
});

test('admin cannot delete user with created learning paths', function () {
    $target = User::factory()->create()->assignRole('author');
    LearningPath::factory()->create(['created_by' => $target->id]);

    $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $target));

    $response->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('error', 'Cannot delete this user because they still own associated content. Reassign or remove the associated content first.');

    expect(User::find($target->id))->not->toBeNull();
});

test('admin cannot delete user with created lessons', function () {
    $target = User::factory()->create()->assignRole('author');
    $path = LearningPath::factory()->create(['created_by' => $this->admin->id]);
    Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'created_by' => $target->id,
    ]);

    $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $target));

    $response->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('error', 'Cannot delete this user because they still own associated content. Reassign or remove the associated content first.');

    expect(User::find($target->id))->not->toBeNull();
});

// --- Navigation Tests ---

test('navigation displays Users link for admin', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

    $response->assertOk()
        ->assertSee(route('admin.users.index'))
        ->assertSee('Users');
});

test('navigation hides Users link for author and regular user', function () {
    $authorResponse = $this->actingAs($this->author)->get(route('dashboard'));
    $authorResponse->assertOk()
        ->assertDontSee(route('admin.users.index'));

    $userResponse = $this->actingAs($this->user)->get(route('dashboard'));
    $userResponse->assertOk()
        ->assertDontSee(route('admin.users.index'));
});

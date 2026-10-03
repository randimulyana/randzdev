<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

test('guests are redirected to login for tag routes', function () {
    $tag = Tag::factory()->create();

    $this->get(route('admin.tags.index'))->assertRedirect(route('login'));
    $this->get(route('admin.tags.create'))->assertRedirect(route('login'));
    $this->post(route('admin.tags.store'), ['name' => 'PHP', 'slug' => 'php'])->assertRedirect(route('login'));
    $this->get(route('admin.tags.edit', $tag))->assertRedirect(route('login'));
    $this->put(route('admin.tags.update', $tag), ['name' => 'PHP', 'slug' => 'php'])->assertRedirect(route('login'));
    $this->delete(route('admin.tags.destroy', $tag))->assertRedirect(route('login'));
});

test('unauthorized users cannot access tag routes', function () {
    $tag = Tag::factory()->create();

    $this->actingAs($this->user)->get(route('admin.tags.index'))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.tags.create'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.tags.store'), [
        'name' => 'PHP',
        'slug' => 'php',
    ])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.tags.edit', $tag))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.tags.update', $tag), [
        'name' => 'PHP Updated',
        'slug' => 'php-updated',
    ])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.tags.destroy', $tag))->assertForbidden();
});

test('authorized users can view tag index page with post counts', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $tag = Tag::factory()->create(['name' => 'Tutorials', 'slug' => 'tutorials']);
    $posts = Post::factory()->count(2)->create();
    $tag->posts()->attach($posts);

    $response = $this->actingAs($user)->get(route('admin.tags.index'));

    $response->assertOk()
        ->assertViewIs('admin.tags.index')
        ->assertSee('Tutorials')
        ->assertSee('tutorials')
        ->assertSee('2');
})->with(['admin', 'author']);

test('authorized users can view tag create page', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $this->actingAs($user)
        ->get(route('admin.tags.create'))
        ->assertOk()
        ->assertViewIs('admin.tags.create')
        ->assertSee('Create Tag');
})->with(['admin', 'author']);

test('authorized users can store a new tag', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $response = $this->actingAs($user)->post(route('admin.tags.store'), [
        'name' => 'Tailwind CSS',
        'slug' => 'tailwind-css',
    ]);

    $response->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag created successfully.');

    $this->assertDatabaseHas('tags', [
        'name' => 'Tailwind CSS',
        'slug' => 'tailwind-css',
    ]);
})->with(['admin', 'author']);

test('authorized users can view tag edit page', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $tag = Tag::factory()->create(['name' => 'Alpine.js', 'slug' => 'alpine-js']);

    $this->actingAs($user)
        ->get(route('admin.tags.edit', $tag))
        ->assertOk()
        ->assertViewIs('admin.tags.edit')
        ->assertSee('Alpine.js')
        ->assertSee('alpine-js');
})->with(['admin', 'author']);

test('authorized users can update an existing tag', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $tag = Tag::factory()->create(['name' => 'Vue', 'slug' => 'vue']);

    $response = $this->actingAs($user)->put(route('admin.tags.update', $tag), [
        'name' => 'Vue.js',
        'slug' => 'vue-js',
    ]);

    $response->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag updated successfully.');

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Vue.js',
        'slug' => 'vue-js',
    ]);
})->with(['admin', 'author']);

test('authorized users can delete a tag', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->delete(route('admin.tags.destroy', $tag));

    $response->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag deleted successfully.');

    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
})->with(['admin', 'author']);

test('tag requires name and slug to be stored', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tags.store'), [
        'name' => '',
        'slug' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'slug']);
});

test('tag slug must be unique on creation', function () {
    Tag::factory()->create(['slug' => 'existing-tag']);

    $response = $this->actingAs($this->admin)->post(route('admin.tags.store'), [
        'name' => 'Duplicate Tag',
        'slug' => 'existing-tag',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('tag update allows keeping the same slug', function () {
    $tag = Tag::factory()->create(['name' => 'Original', 'slug' => 'original-slug']);

    $response = $this->actingAs($this->admin)->put(route('admin.tags.update', $tag), [
        'name' => 'Updated Tag',
        'slug' => 'original-slug',
    ]);

    $response->assertRedirect(route('admin.tags.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Updated Tag',
        'slug' => 'original-slug',
    ]);
});

test('tag update rejects duplicate slug from another tag', function () {
    Tag::factory()->create(['slug' => 'other-tag']);
    $tag = Tag::factory()->create(['slug' => 'my-tag']);

    $response = $this->actingAs($this->admin)->put(route('admin.tags.update', $tag), [
        'name' => 'Updated Tag',
        'slug' => 'other-tag',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

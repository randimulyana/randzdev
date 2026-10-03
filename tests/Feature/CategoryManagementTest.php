<?php

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

test('guests are redirected to login for category routes', function () {
    $category = Category::factory()->create();

    $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
    $this->get(route('admin.categories.create'))->assertRedirect(route('login'));
    $this->post(route('admin.categories.store'), ['name' => 'Backend', 'slug' => 'backend'])->assertRedirect(route('login'));
    $this->get(route('admin.categories.edit', $category))->assertRedirect(route('login'));
    $this->put(route('admin.categories.update', $category), ['name' => 'Backend', 'slug' => 'backend'])->assertRedirect(route('login'));
    $this->delete(route('admin.categories.destroy', $category))->assertRedirect(route('login'));
});

test('unauthorized users cannot access category routes', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->user)->get(route('admin.categories.index'))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.categories.create'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.categories.store'), [
        'name' => 'Backend',
        'slug' => 'backend',
    ])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.categories.edit', $category))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.categories.update', $category), [
        'name' => 'Backend Updated',
        'slug' => 'backend-updated',
    ])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.categories.destroy', $category))->assertForbidden();
});

test('authorized users can view category index page with project counts', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $category = Category::factory()->create(['name' => 'Web Dev', 'slug' => 'web-dev']);
    Project::factory()->count(2)->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)->get(route('admin.categories.index'));

    $response->assertOk()
        ->assertViewIs('admin.categories.index')
        ->assertSee('Web Dev')
        ->assertSee('web-dev')
        ->assertSee('2');
})->with(['admin', 'author']);

test('authorized users can view category create page', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $this->actingAs($user)
        ->get(route('admin.categories.create'))
        ->assertOk()
        ->assertViewIs('admin.categories.create')
        ->assertSee('Create Category');
})->with(['admin', 'author']);

test('authorized users can store a new category', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $response = $this->actingAs($user)->post(route('admin.categories.store'), [
        'name' => 'Mobile Apps',
        'slug' => 'mobile-apps',
        'description' => 'Android and iOS apps',
        'meta_title' => 'Mobile Apps Category',
        'meta_description' => 'Meta description for mobile apps',
    ]);

    $response->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success', 'Category created successfully.');

    $this->assertDatabaseHas('categories', [
        'name' => 'Mobile Apps',
        'slug' => 'mobile-apps',
        'description' => 'Android and iOS apps',
        'meta_title' => 'Mobile Apps Category',
        'meta_description' => 'Meta description for mobile apps',
    ]);
})->with(['admin', 'author']);

test('authorized users can view category edit page', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $category = Category::factory()->create(['name' => 'DevOps', 'slug' => 'devops']);

    $this->actingAs($user)
        ->get(route('admin.categories.edit', $category))
        ->assertOk()
        ->assertViewIs('admin.categories.edit')
        ->assertSee('DevOps')
        ->assertSee('devops');
})->with(['admin', 'author']);

test('authorized users can update an existing category', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $category = Category::factory()->create(['name' => 'Cloud', 'slug' => 'cloud']);

    $response = $this->actingAs($user)->put(route('admin.categories.update', $category), [
        'name' => 'Cloud Infrastructure',
        'slug' => 'cloud-infrastructure',
        'description' => 'Cloud services and DevOps',
    ]);

    $response->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success', 'Category updated successfully.');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Cloud Infrastructure',
        'slug' => 'cloud-infrastructure',
        'description' => 'Cloud services and DevOps',
    ]);
})->with(['admin', 'author']);

test('authorized users can delete a category', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->delete(route('admin.categories.destroy', $category));

    $response->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success', 'Category deleted successfully.');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
})->with(['admin', 'author']);

test('category requires name and slug to be stored', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
        'name' => '',
        'slug' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'slug']);
});

test('category slug must be unique on creation', function () {
    Category::factory()->create(['slug' => 'existing-slug']);

    $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
        'name' => 'Duplicate Slug Category',
        'slug' => 'existing-slug',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('category update allows keeping the same slug', function () {
    $category = Category::factory()->create(['name' => 'Original', 'slug' => 'original-slug']);

    $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
        'name' => 'Updated Name',
        'slug' => 'original-slug',
    ]);

    $response->assertRedirect(route('admin.categories.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
        'slug' => 'original-slug',
    ]);
});

test('category update rejects duplicate slug from another category', function () {
    Category::factory()->create(['slug' => 'other-category']);
    $category = Category::factory()->create(['slug' => 'my-category']);

    $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
        'name' => 'Updated Category',
        'slug' => 'other-category',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('deleting a category nullifies foreign key on projects without deleting project', function () {
    $category = Category::factory()->create();
    $project = Project::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('projects', ['id' => $project->id]);
    expect($project->fresh()->category_id)->toBeNull();
});

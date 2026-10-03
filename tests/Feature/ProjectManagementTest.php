<?php

use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

test('guests are redirected to login for project routes', function () {
    $project = Project::factory()->create();

    $this->get(route('admin.projects.index'))->assertRedirect(route('login'));
    $this->get(route('admin.projects.create'))->assertRedirect(route('login'));
    $this->post(route('admin.projects.store'), ['title' => 'Test', 'slug' => 'test', 'status' => 'completed'])->assertRedirect(route('login'));
    $this->get(route('admin.projects.edit', $project))->assertRedirect(route('login'));
    $this->put(route('admin.projects.update', $project), ['title' => 'Test', 'slug' => 'test', 'status' => 'completed'])->assertRedirect(route('login'));
    $this->delete(route('admin.projects.destroy', $project))->assertRedirect(route('login'));
});

test('unauthorized users cannot perform project management actions', function () {
    $project = Project::factory()->create();

    $this->actingAs($this->user)->get(route('admin.projects.create'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.projects.store'), [
        'title' => 'Hacked Project',
        'slug' => 'hacked-project',
        'status' => 'completed',
    ])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.projects.edit', $project))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.projects.update', $project), [
        'title' => 'Updated Project',
        'slug' => 'updated-project',
        'status' => 'completed',
    ])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.projects.destroy', $project))->assertForbidden();
});

test('authorized users can view project index with category and technologies', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $category = Category::factory()->create(['name' => 'Web App']);
    $technology = Technology::factory()->create(['name' => 'Laravel']);
    $project = Project::factory()->create([
        'title' => 'Portfolio Platform',
        'slug' => 'portfolio-platform',
        'category_id' => $category->id,
        'status' => 'completed',
        'featured' => true,
    ]);
    $project->technologies()->attach($technology);

    $response = $this->actingAs($user)->get(route('admin.projects.index'));

    $response->assertOk()
        ->assertViewIs('admin.projects.index')
        ->assertSee('Portfolio Platform')
        ->assertSee('portfolio-platform')
        ->assertSee('Web App')
        ->assertSee('Laravel')
        ->assertSee('Completed')
        ->assertSee('Featured');
})->with(['admin', 'author']);

test('authorized users can view project create page with categories and technologies', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $category = Category::factory()->create(['name' => 'Backend Development']);
    $technology = Technology::factory()->create(['name' => 'PHP']);

    $response = $this->actingAs($user)->get(route('admin.projects.create'));

    $response->assertOk()
        ->assertViewIs('admin.projects.create')
        ->assertSee('Create Project')
        ->assertSee('Backend Development')
        ->assertSee('PHP');
})->with(['admin', 'author']);

test('authorized users can store a new project with category and technologies', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $category = Category::factory()->create();
    $technologies = Technology::factory()->count(2)->create();

    $response = $this->actingAs($user)->post(route('admin.projects.store'), [
        'category_id' => $category->id,
        'title' => 'E-Commerce Store',
        'slug' => 'e-commerce-store',
        'short_description' => 'A modern e-commerce platform',
        'description' => 'Full case study of the store',
        'thumbnail' => '/images/ecommerce.png',
        'role' => 'Lead Developer',
        'status' => 'completed',
        'repository_url' => 'https://github.com/randzdev/ecommerce',
        'live_url' => 'https://ecommerce.example.com',
        'started_at' => '2026-01-01',
        'completed_at' => '2026-03-01',
        'featured' => '1',
        'published_at' => '2026-03-01',
        'technologies' => $technologies->pluck('id')->all(),
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project created successfully.');

    $this->assertDatabaseHas('projects', [
        'title' => 'E-Commerce Store',
        'slug' => 'e-commerce-store',
        'category_id' => $category->id,
        'role' => 'Lead Developer',
        'status' => 'completed',
        'featured' => 1,
    ]);

    $project = Project::where('slug', 'e-commerce-store')->first();
    expect($project)->not->toBeNull()
        ->and($project->technologies)->toHaveCount(2)
        ->and($project->technologies->pluck('id')->all())->toEqualCanonicalizing($technologies->pluck('id')->all());
})->with(['admin', 'author']);

test('authorized users can view project edit page', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $category = Category::factory()->create();
    $technology = Technology::factory()->create();
    $project = Project::factory()->create([
        'title' => 'Original Project',
        'slug' => 'original-project',
        'category_id' => $category->id,
    ]);
    $project->technologies()->attach($technology);

    $response = $this->actingAs($user)->get(route('admin.projects.edit', $project));

    $response->assertOk()
        ->assertViewIs('admin.projects.edit')
        ->assertSee('Original Project')
        ->assertSee('original-project');
})->with(['admin', 'author']);

test('authorized users can update an existing project and sync technologies', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $tech1 = Technology::factory()->create();
    $tech2 = Technology::factory()->create();
    $tech3 = Technology::factory()->create();

    $project = Project::factory()->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'category_id' => $category1->id,
        'featured' => false,
    ]);
    $project->technologies()->attach([$tech1->id, $tech2->id]);

    $response = $this->actingAs($user)->put(route('admin.projects.update', $project), [
        'category_id' => $category2->id,
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'short_description' => 'Updated short description',
        'description' => 'Updated description',
        'status' => 'in_progress',
        'featured' => '1',
        'technologies' => [$tech2->id, $tech3->id],
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project updated successfully.');

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'category_id' => $category2->id,
        'status' => 'in_progress',
        'featured' => 1,
    ]);

    $refreshedProject = $project->fresh();
    expect($refreshedProject->technologies)->toHaveCount(2)
        ->and($refreshedProject->technologies->pluck('id')->all())->toEqualCanonicalizing([$tech2->id, $tech3->id]);
})->with(['admin', 'author']);

test('authorized users can soft-delete a project without deleting category or technologies', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $category = Category::factory()->create();
    $technology = Technology::factory()->create();
    $project = Project::factory()->create(['category_id' => $category->id]);
    $project->technologies()->attach($technology);

    $response = $this->actingAs($user)->delete(route('admin.projects.destroy', $project));

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project deleted successfully.');

    $this->assertSoftDeleted('projects', ['id' => $project->id]);
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('technologies', ['id' => $technology->id]);
})->with(['admin', 'author']);

test('project requires title, slug, and valid status to be stored', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => '',
        'slug' => '',
        'status' => 'invalid_status',
    ]);

    $response->assertSessionHasErrors(['title', 'slug', 'status']);
});

test('project slug must be unique on creation', function () {
    Project::factory()->create(['slug' => 'existing-slug']);

    $response = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Duplicate Slug',
        'slug' => 'existing-slug',
        'status' => 'completed',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('project update allows keeping the same slug', function () {
    $project = Project::factory()->create(['slug' => 'same-slug']);

    $response = $this->actingAs($this->admin)->put(route('admin.projects.update', $project), [
        'title' => 'Updated Title',
        'slug' => 'same-slug',
        'status' => 'completed',
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'title' => 'Updated Title',
        'slug' => 'same-slug',
    ]);
});

test('project update rejects duplicate slug from another project', function () {
    Project::factory()->create(['slug' => 'another-slug']);
    $project = Project::factory()->create(['slug' => 'my-slug']);

    $response = $this->actingAs($this->admin)->put(route('admin.projects.update', $project), [
        'title' => 'Updated Title',
        'slug' => 'another-slug',
        'status' => 'completed',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('project validates category and technology exist', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Test Project',
        'slug' => 'test-project',
        'status' => 'completed',
        'category_id' => 999999,
        'technologies' => [888888],
    ]);

    $response->assertSessionHasErrors(['category_id', 'technologies.0']);
});

test('project validates repository and live urls', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Test Project',
        'slug' => 'test-project',
        'status' => 'completed',
        'repository_url' => 'not-a-valid-url',
        'live_url' => 'also-not-a-valid-url',
    ]);

    $response->assertSessionHasErrors(['repository_url', 'live_url']);
});

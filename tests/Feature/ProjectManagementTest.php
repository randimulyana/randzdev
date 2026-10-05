<?php

use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
    $this->patch(route('admin.projects.publish', $project))->assertRedirect(route('login'));
    $this->patch(route('admin.projects.unpublish', $project))->assertRedirect(route('login'));
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
    $this->actingAs($this->user)->patch(route('admin.projects.publish', $project))->assertForbidden();
    $this->actingAs($this->user)->patch(route('admin.projects.unpublish', $project))->assertForbidden();
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

test('authorized user can publish a draft project', function () {
    $project = Project::factory()->create([
        'status' => 'draft',
        'published_at' => null,
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.projects.publish', $project));

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project published successfully.');

    $project->refresh();
    expect($project->status)->toBe('completed')
        ->and($project->published_at)->not->toBeNull();
});

test('authorized user can publish an in-progress project without changing its status', function () {
    $project = Project::factory()->create([
        'status' => 'in_progress',
        'published_at' => null,
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.projects.publish', $project));

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project published successfully.');

    $project->refresh();
    expect($project->status)->toBe('in_progress')
        ->and($project->published_at)->not->toBeNull();
});

test('published project receives published_at', function () {
    $project = Project::factory()->create([
        'status' => 'completed',
        'published_at' => null,
    ]);

    $response = $this->actingAs($this->author)->patch(route('admin.projects.publish', $project));

    $response->assertRedirect(route('admin.projects.index'));

    $project->refresh();
    expect($project->published_at)->not->toBeNull();
});

test('authorized user cannot publish project without projects.update permission', function () {
    $project = Project::factory()->create([
        'status' => 'draft',
    ]);

    $this->actingAs($this->user)
        ->patch(route('admin.projects.publish', $project))
        ->assertForbidden();
});

test('authorized user can unpublish a published project', function () {
    $project = Project::factory()->create([
        'status' => 'completed',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.projects.unpublish', $project));

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project unpublished successfully.');

    $project->refresh();
    expect($project->status)->toBe('draft')
        ->and($project->published_at)->toBeNull();
});

test('unpublished project becomes draft and has null published_at', function () {
    $project = Project::factory()->create([
        'status' => 'in_progress',
        'published_at' => now()->subDay(),
    ]);

    $this->actingAs($this->author)->patch(route('admin.projects.unpublish', $project));

    $project->refresh();
    expect($project->status)->toBe('draft')
        ->and($project->published_at)->toBeNull();
});

test('unauthorized user cannot unpublish project', function () {
    $project = Project::factory()->create([
        'status' => 'completed',
        'published_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->patch(route('admin.projects.unpublish', $project))
        ->assertForbidden();
});

test('project automatically generates slug from title', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Innovative Cloud Infrastructure',
        'slug' => '',
        'status' => 'completed',
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project created successfully.');

    $this->assertDatabaseHas('projects', [
        'title' => 'Innovative Cloud Infrastructure',
        'slug' => 'innovative-cloud-infrastructure',
    ]);
});

test('project generates unique slug when generated slug collides', function () {
    Project::factory()->create([
        'title' => 'Awesome Portfolio',
        'slug' => 'awesome-portfolio',
    ]);

    $response1 = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Awesome Portfolio',
        'slug' => '',
        'status' => 'completed',
    ]);
    $response1->assertRedirect(route('admin.projects.index'));

    $this->assertDatabaseHas('projects', [
        'slug' => 'awesome-portfolio-1',
    ]);

    $response2 = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Awesome Portfolio',
        'slug' => '',
        'status' => 'completed',
    ]);
    $response2->assertRedirect(route('admin.projects.index'));

    $this->assertDatabaseHas('projects', [
        'slug' => 'awesome-portfolio-2',
    ]);
});

test('explicit slug is preserved on create', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Custom Named Project',
        'slug' => 'my-custom-explicit-slug',
        'status' => 'completed',
    ]);

    $response->assertRedirect(route('admin.projects.index'));

    $this->assertDatabaseHas('projects', [
        'slug' => 'my-custom-explicit-slug',
    ]);
});

test('update with empty slug preserves existing slug', function () {
    $project = Project::factory()->create([
        'title' => 'Original App',
        'slug' => 'original-app-slug',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.projects.update', $project), [
        'title' => 'Renamed App Title',
        'slug' => '',
        'status' => 'completed',
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project updated successfully.');

    $project->refresh();
    expect($project->title)->toBe('Renamed App Title')
        ->and($project->slug)->toBe('original-app-slug');
});

test('authorized user can upload thumbnail', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('project-cover.jpg', 800, 600);

    $response = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Project with Custom Thumbnail',
        'status' => 'completed',
        'thumbnail_file' => $file,
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project created successfully.');

    $project = Project::where('title', 'Project with Custom Thumbnail')->first();
    expect($project)->not->toBeNull()
        ->and($project->thumbnail)->toStartWith('/storage/projects/');

    $relativeStoragePath = str_replace('/storage/', '', $project->thumbnail);
    Storage::disk('public')->assertExists($relativeStoragePath);
});

test('thumbnail validation rejects invalid files and files larger than 2MB', function () {
    $pdf = UploadedFile::fake()->create('contract.pdf', 500);

    $response1 = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'PDF Thumbnail Project',
        'status' => 'completed',
        'thumbnail_file' => $pdf,
    ]);

    $response1->assertSessionHasErrors(['thumbnail_file']);

    $oversized = UploadedFile::fake()->image('huge.jpg')->size(3000);

    $response2 = $this->actingAs($this->admin)->post(route('admin.projects.store'), [
        'title' => 'Oversized Thumbnail Project',
        'status' => 'completed',
        'thumbnail_file' => $oversized,
    ]);

    $response2->assertSessionHasErrors(['thumbnail_file']);
});

test('replacing local thumbnail deletes old local file', function () {
    Storage::fake('public');

    $oldPath = 'projects/old-screenshot.jpg';
    Storage::disk('public')->put($oldPath, 'dummy old project image');

    $project = Project::factory()->create([
        'thumbnail' => '/storage/'.$oldPath,
    ]);

    $newFile = UploadedFile::fake()->image('new-screenshot.png', 800, 600);

    $response = $this->actingAs($this->admin)->put(route('admin.projects.update', $project), [
        'title' => $project->title,
        'slug' => $project->slug,
        'status' => $project->status,
        'thumbnail_file' => $newFile,
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project updated successfully.');

    Storage::disk('public')->assertMissing($oldPath);

    $project->refresh();
    expect($project->thumbnail)->toStartWith('/storage/projects/')
        ->and($project->thumbnail)->not->toBe('/storage/'.$oldPath);

    $newStoragePath = str_replace('/storage/', '', $project->thumbnail);
    Storage::disk('public')->assertExists($newStoragePath);
});

test('replacing external thumbnail does not attempt filesystem deletion', function () {
    Storage::fake('public');

    $project = Project::factory()->create([
        'thumbnail' => 'https://images.unsplash.com/photo-project-sample',
    ]);

    $newFile = UploadedFile::fake()->image('new-image.jpg', 600, 400);

    $response = $this->actingAs($this->admin)->put(route('admin.projects.update', $project), [
        'title' => $project->title,
        'slug' => $project->slug,
        'status' => $project->status,
        'thumbnail_file' => $newFile,
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project updated successfully.');

    $project->refresh();
    expect($project->thumbnail)->toStartWith('/storage/projects/');
});

test('existing external thumbnail remains compatible when no upload is provided', function () {
    $project = Project::factory()->create([
        'thumbnail' => 'https://example.com/existing-thumbnail.png',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.projects.update', $project), [
        'title' => 'Updated Project Title',
        'slug' => $project->slug,
        'status' => $project->status,
        'thumbnail' => 'https://example.com/existing-thumbnail.png',
    ]);

    $response->assertRedirect(route('admin.projects.index'))
        ->assertSessionHas('success', 'Project updated successfully.');

    $project->refresh();
    expect($project->thumbnail)->toBe('https://example.com/existing-thumbnail.png');
});

test('admin project index displays publish or unpublish action buttons depending on project status', function () {
    $draftProject = Project::factory()->create([
        'title' => 'Draft Unfinished App',
        'slug' => 'draft-unfinished-app',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $publishedProject = Project::factory()->create([
        'title' => 'Live Portfolio Showcase',
        'slug' => 'live-portfolio-showcase',
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.projects.index'));

    $response->assertOk()
        ->assertSee('Publish')
        ->assertSee('Unpublish')
        ->assertSee(route('admin.projects.publish', $draftProject))
        ->assertSee(route('admin.projects.unpublish', $publishedProject));
});

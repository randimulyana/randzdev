<?php

use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use App\Models\User;

test('guest can access public projects listing', function () {
    $response = $this->get(route('projects.index'));

    $response->assertOk()
        ->assertViewIs('projects.index');
});

test('authenticated user can access public projects listing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('projects.index'));

    $response->assertOk()
        ->assertViewIs('projects.index');
});

test('only published projects are visible on public projects listing', function () {
    $published = Project::factory()->create([
        'title' => 'Visible Project',
        'slug' => 'visible-project',
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);

    $draft = Project::factory()->create([
        'title' => 'Draft Project',
        'slug' => 'draft-project',
        'status' => 'draft',
        'published_at' => now()->subDay(),
    ]);

    $unpublishedNullDate = Project::factory()->create([
        'title' => 'No Published Date Project',
        'slug' => 'no-date-project',
        'status' => 'completed',
        'published_at' => null,
    ]);

    $futurePublished = Project::factory()->create([
        'title' => 'Future Project',
        'slug' => 'future-project',
        'status' => 'completed',
        'published_at' => now()->addDays(5),
    ]);

    $softDeleted = Project::factory()->create([
        'title' => 'Deleted Project',
        'slug' => 'deleted-project',
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);
    $softDeleted->delete();

    $response = $this->get(route('projects.index'));

    $response->assertOk()
        ->assertSee('Visible Project')
        ->assertDontSee('Draft Project')
        ->assertDontSee('No Published Date Project')
        ->assertDontSee('Future Project')
        ->assertDontSee('Deleted Project');
});

test('category filter shows only projects from specified category', function () {
    $webCat = Category::factory()->create(['name' => 'Web App', 'slug' => 'web-app']);
    $mobileCat = Category::factory()->create(['name' => 'Mobile App', 'slug' => 'mobile-app']);

    $webProject = Project::factory()->create([
        'title' => 'Web Dashboard',
        'slug' => 'web-dashboard',
        'category_id' => $webCat->id,
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);

    $mobileProject = Project::factory()->create([
        'title' => 'iOS Messenger',
        'slug' => 'ios-messenger',
        'category_id' => $mobileCat->id,
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('projects.index', ['category' => 'web-app']));

    $response->assertOk()
        ->assertSee('Web Dashboard')
        ->assertDontSee('iOS Messenger');
});

test('public projects listing paginates when exceeding page size', function () {
    Project::factory()->count(12)->create([
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('projects.index'));

    $response->assertOk();
    expect($response->viewData('projects'))->toHaveCount(9);
});

test('guest can view published project detail', function () {
    $category = Category::factory()->create(['name' => 'SaaS Applications']);
    $tech1 = Technology::factory()->create(['name' => 'Laravel']);
    $tech2 = Technology::factory()->create(['name' => 'Tailwind CSS']);

    $project = Project::factory()->create([
        'title' => 'Portfolio Platform',
        'slug' => 'portfolio-platform',
        'category_id' => $category->id,
        'short_description' => 'A clean portfolio platform',
        'description' => 'Comprehensive case study breakdown',
        'role' => 'Full-Stack Lead',
        'status' => 'completed',
        'repository_url' => 'https://github.com/randzdev/portfolio',
        'live_url' => 'https://randz.dev',
        'started_at' => '2026-01-01',
        'completed_at' => '2026-03-01',
        'featured' => true,
        'published_at' => now()->subDay(),
    ]);
    $project->technologies()->attach([$tech1->id, $tech2->id]);

    $response = $this->get(route('projects.show', $project));

    $response->assertOk()
        ->assertViewIs('projects.show')
        ->assertSee('Portfolio Platform')
        ->assertSee('A clean portfolio platform')
        ->assertSee('Comprehensive case study breakdown')
        ->assertSee('SaaS Applications')
        ->assertSee('Laravel')
        ->assertSee('Tailwind CSS')
        ->assertSee('Full-Stack Lead')
        ->assertSee('https://github.com/randzdev/portfolio')
        ->assertSee('https://randz.dev')
        ->assertSee(route('projects.index'));
});

test('draft project returns 404 on public detail route', function () {
    $draft = Project::factory()->create([
        'title' => 'Secret Draft Project',
        'slug' => 'secret-draft',
        'status' => 'draft',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('projects.show', $draft));

    $response->assertNotFound();
});

test('unpublished project without date returns 404 on public detail route', function () {
    $project = Project::factory()->create([
        'slug' => 'unpublished-no-date',
        'status' => 'completed',
        'published_at' => null,
    ]);

    $response = $this->get(route('projects.show', $project));

    $response->assertNotFound();
});

test('future published project returns 404 on public detail route', function () {
    $project = Project::factory()->create([
        'slug' => 'future-scheduled',
        'status' => 'completed',
        'published_at' => now()->addDays(3),
    ]);

    $response = $this->get(route('projects.show', $project));

    $response->assertNotFound();
});

test('soft-deleted project returns 404 on public detail route', function () {
    $project = Project::factory()->create([
        'slug' => 'deleted-work',
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);
    $project->delete();

    $response = $this->get(route('projects.show', $project));

    $response->assertNotFound();
});

test('non-existent slug returns 404 on public detail route', function () {
    $response = $this->get(route('projects.show', 'unknown-project-slug'));

    $response->assertNotFound();
});

test('optional repository and live links are omitted when null', function () {
    $project = Project::factory()->create([
        'title' => 'Closed Source Project',
        'slug' => 'closed-source',
        'status' => 'completed',
        'repository_url' => null,
        'live_url' => null,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('projects.show', $project));

    $response->assertOk()
        ->assertDontSee('Source Code')
        ->assertDontSee('Live Demo');
});

test('repository link is rendered when repository url is present and live demo is omitted when null', function () {
    $project = Project::factory()->create([
        'title' => 'Backend API Project',
        'slug' => 'backend-api-project',
        'status' => 'completed',
        'repository_url' => 'https://github.com/randzdev/backend-api',
        'live_url' => null,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('projects.show', $project));

    $response->assertOk()
        ->assertSee('Source Code')
        ->assertSee('https://github.com/randzdev/backend-api')
        ->assertDontSee('Live Demo');
});

test('category filter returns empty state when no projects match', function () {
    $response = $this->get(route('projects.index', ['category' => 'non-existent-category']));

    $response->assertOk()
        ->assertSee('No projects found');
});

<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;

test('A: guest can access search page', function () {
    $response = $this->get(route('search'));

    $response->assertOk()
        ->assertViewIs('search.index');
});

test('B: authenticated user can access search page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('search'));

    $response->assertOk()
        ->assertViewIs('search.index');
});

test('C: empty query returns success and initial search state without fabricated results', function () {
    Post::factory()->create(['title' => 'Sample Post']);
    Project::factory()->create(['title' => 'Sample Project']);

    $response = $this->get(route('search'));

    $response->assertOk()
        ->assertViewIs('search.index')
        ->assertViewHas('query', '')
        ->assertViewHas('posts', null)
        ->assertViewHas('projects', null)
        ->assertSee('Search articles and projects')
        ->assertDontSee('Sample Post')
        ->assertDontSee('Sample Project');
});

test('D: search post by title', function () {
    Post::factory()->create(['title' => 'Mastering Laravel Concurrency']);
    Post::factory()->create(['title' => 'Introduction to Vue 3']);

    $response = $this->get(route('search', ['q' => 'Concurrency']));

    $response->assertOk()
        ->assertSee('Mastering Laravel Concurrency')
        ->assertDontSee('Introduction to Vue 3');
});

test('E: search post by excerpt', function () {
    Post::factory()->create([
        'title' => 'Async PHP Guide',
        'excerpt' => 'Exploring fibers and event loops in modern PHP',
    ]);
    Post::factory()->create([
        'title' => 'CSS Flexbox Guide',
        'excerpt' => 'A guide to layout structures',
    ]);

    $response = $this->get(route('search', ['q' => 'event loops']));

    $response->assertOk()
        ->assertSee('Async PHP Guide')
        ->assertDontSee('CSS Flexbox Guide');
});

test('F: search post by content', function () {
    Post::factory()->create([
        'title' => 'Database Optimization',
        'content' => 'Deep dive into composite index strategies and EXPLAIN ANALYZE',
    ]);
    Post::factory()->create([
        'title' => 'Tailwind Setup',
        'content' => 'Configuring styles in modern projects',
    ]);

    $response = $this->get(route('search', ['q' => 'EXPLAIN ANALYZE']));

    $response->assertOk()
        ->assertSee('Database Optimization')
        ->assertDontSee('Tailwind Setup');
});

test('G: search post by category name', function () {
    $category = Category::factory()->create(['name' => 'DevOps & CI/CD']);
    Post::factory()->create([
        'title' => 'GitHub Actions Setup',
        'category_id' => $category->id,
    ]);
    $otherCategory = Category::factory()->create(['name' => 'Design']);
    Post::factory()->create([
        'title' => 'Figma Basics',
        'category_id' => $otherCategory->id,
    ]);

    $response = $this->get(route('search', ['q' => 'DevOps']));

    $response->assertOk()
        ->assertSee('GitHub Actions Setup')
        ->assertDontSee('Figma Basics');
});

test('H: search post by tag name', function () {
    $tag = Tag::factory()->create(['name' => 'Kubernetes']);
    $matching = Post::factory()->create(['title' => 'Cluster Management']);
    $matching->tags()->attach($tag);

    Post::factory()->create(['title' => 'CSS Tips']);

    $response = $this->get(route('search', ['q' => 'Kubernetes']));

    $response->assertOk()
        ->assertSee('Cluster Management')
        ->assertDontSee('CSS Tips');
});

test('I: search project by title', function () {
    Project::factory()->create(['title' => 'Microservice Payment Gateway']);
    Project::factory()->create(['title' => 'Personal Portfolio Website']);

    $response = $this->get(route('search', ['q' => 'Payment Gateway']));

    $response->assertOk()
        ->assertSee('Microservice Payment Gateway')
        ->assertDontSee('Personal Portfolio Website');
});

test('J: search returns both matching posts and projects', function () {
    Post::factory()->create(['title' => 'Building with Docker in 2026']);
    Project::factory()->create(['title' => 'Docker Orchestrator Toolkit']);
    Post::factory()->create(['title' => 'Unrelated Blog Entry']);
    Project::factory()->create(['title' => 'Unrelated App']);

    $response = $this->get(route('search', ['q' => 'Docker']));

    $response->assertOk()
        ->assertSee('Building with Docker in 2026')
        ->assertSee('Docker Orchestrator Toolkit')
        ->assertDontSee('Unrelated Blog Entry')
        ->assertDontSee('Unrelated App');
});

test('K: draft post is excluded from search results', function () {
    Post::factory()->draft()->create(['title' => 'Draft Laravel Architecture']);
    Post::factory()->create(['title' => 'Published Laravel Architecture']);

    $response = $this->get(route('search', ['q' => 'Architecture']));

    $response->assertOk()
        ->assertSee('Published Laravel Architecture')
        ->assertDontSee('Draft Laravel Architecture');
    expect($response->viewData('posts')->total())->toBe(1);
});

test('L: archived post is excluded from search results', function () {
    Post::factory()->archived()->create(['title' => 'Archived Legacy System']);
    Post::factory()->create(['title' => 'Active Legacy System']);

    $response = $this->get(route('search', ['q' => 'Legacy System']));

    $response->assertOk()
        ->assertSee('Active Legacy System')
        ->assertDontSee('Archived Legacy System');
    expect($response->viewData('posts')->total())->toBe(1);
});

test('M: future published post is excluded from search results', function () {
    Post::factory()->create([
        'title' => 'Future Secret Release',
        'status' => 'published',
        'published_at' => now()->addDays(2),
    ]);
    Post::factory()->create([
        'title' => 'Current Secret Release',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('search', ['q' => 'Secret Release']));

    $response->assertOk()
        ->assertSee('Current Secret Release')
        ->assertDontSee('Future Secret Release');
    expect($response->viewData('posts')->total())->toBe(1);
});

test('N: post with null published_at is excluded from search results', function () {
    Post::factory()->create([
        'title' => 'Unpublished Null Date Post',
        'status' => 'published',
        'published_at' => null,
    ]);
    Post::factory()->create([
        'title' => 'Published Valid Date Post',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('search', ['q' => 'Date Post']));

    $response->assertOk()
        ->assertSee('Published Valid Date Post')
        ->assertDontSee('Unpublished Null Date Post');
    expect($response->viewData('posts')->total())->toBe(1);
});

test('O: soft-deleted post is excluded from search results', function () {
    $post = Post::factory()->create(['title' => 'Soft Deleted Framework Guide']);
    $post->delete();
    Post::factory()->create(['title' => 'Active Framework Guide']);

    $response = $this->get(route('search', ['q' => 'Framework Guide']));

    $response->assertOk()
        ->assertSee('Active Framework Guide')
        ->assertDontSee('Soft Deleted Framework Guide');
    expect($response->viewData('posts')->total())->toBe(1);
});

test('P: draft project is excluded from search results', function () {
    Project::factory()->draft()->create(['title' => 'Draft Secret Project']);
    Project::factory()->create(['title' => 'Published Secret Project']);

    $response = $this->get(route('search', ['q' => 'Secret Project']));

    $response->assertOk()
        ->assertSee('Published Secret Project')
        ->assertDontSee('Draft Secret Project');
    expect($response->viewData('projects')->total())->toBe(1);
});

test('Q: future project is excluded from search results', function () {
    Project::factory()->create([
        'title' => 'Future Launch Project',
        'status' => 'completed',
        'published_at' => now()->addDays(5),
    ]);
    Project::factory()->create([
        'title' => 'Current Launch Project',
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('search', ['q' => 'Launch Project']));

    $response->assertOk()
        ->assertSee('Current Launch Project')
        ->assertDontSee('Future Launch Project');
    expect($response->viewData('projects')->total())->toBe(1);
});

test('R: soft-deleted project is excluded from search results', function () {
    $project = Project::factory()->create(['title' => 'Deleted Inventory App']);
    $project->delete();
    Project::factory()->create(['title' => 'Active Inventory App']);

    $response = $this->get(route('search', ['q' => 'Inventory App']));

    $response->assertOk()
        ->assertSee('Active Inventory App')
        ->assertDontSee('Deleted Inventory App');
    expect($response->viewData('projects')->total())->toBe(1);
});

test('S: post pagination preserves search query q and posts_page', function () {
    Post::factory()->count(15)->create(['title' => fn () => 'PaginationPost '.fake()->unique()->word()]);

    $response = $this->get(route('search', ['q' => 'PaginationPost', 'posts_page' => 2]));

    $response->assertOk();
    $posts = $response->viewData('posts');
    expect($posts->currentPage())->toBe(2);
    expect($posts->previousPageUrl())->toContain('q=PaginationPost');
});

test('T: project pagination preserves search query q and projects_page', function () {
    Project::factory()->count(15)->create(['title' => fn () => 'PaginationProject '.fake()->unique()->word()]);

    $response = $this->get(route('search', ['q' => 'PaginationProject', 'projects_page' => 2]));

    $response->assertOk();
    $projects = $response->viewData('projects');
    expect($projects->currentPage())->toBe(2);
    expect($projects->previousPageUrl())->toContain('q=PaginationProject');
});

test('U: zero-result search displays clear empty state with query term', function () {
    $response = $this->get(route('search', ['q' => 'NonExistentSearchStringXYZ']));

    $response->assertOk()
        ->assertSee('No results found for')
        ->assertSee('NonExistentSearchStringXYZ');
});

test('V: whitespace-only query is handled correctly as empty query', function () {
    Post::factory()->create(['title' => 'Whitespace Test Post']);

    $response = $this->get(route('search', ['q' => '   ']));

    $response->assertOk()
        ->assertViewHas('query', '')
        ->assertViewHas('posts', null)
        ->assertViewHas('projects', null)
        ->assertSee('Search articles and projects')
        ->assertDontSee('Whitespace Test Post');
});

test('W: search page navigation link exists in public layout', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee(route('search'));
});

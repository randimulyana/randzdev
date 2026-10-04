<?php

use App\Models\Category;
use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;

test('guest can access public home route', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertViewIs('home');
});

test('authenticated user can access public home route', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertViewIs('home');
});

test('hero section renders profile headline, name, and role from configuration', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee(config('portfolio.profile.name'))
        ->assertSee(config('portfolio.profile.role'))
        ->assertSee(config('portfolio.profile.headline'))
        ->assertSee(route('projects.index'))
        ->assertSee(route('learn.index'));
});

test('only featured and published projects appear on home page', function () {
    $featuredPublished = Project::factory()->create([
        'title' => 'Featured Published Project',
        'slug' => 'featured-published-project',
        'status' => 'completed',
        'featured' => true,
        'published_at' => now()->subDay(),
    ]);

    $nonFeaturedPublished = Project::factory()->create([
        'title' => 'Standard Published Project',
        'slug' => 'standard-published-project',
        'status' => 'completed',
        'featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $featuredDraft = Project::factory()->create([
        'title' => 'Featured Draft Project',
        'slug' => 'featured-draft-project',
        'status' => 'draft',
        'featured' => true,
        'published_at' => now()->subDay(),
    ]);

    $featuredFuture = Project::factory()->create([
        'title' => 'Featured Future Project',
        'slug' => 'featured-future-project',
        'status' => 'completed',
        'featured' => true,
        'published_at' => now()->addDays(3),
    ]);

    $featuredSoftDeleted = Project::factory()->create([
        'title' => 'Featured Deleted Project',
        'slug' => 'featured-deleted-project',
        'status' => 'completed',
        'featured' => true,
        'published_at' => now()->subDay(),
    ]);
    $featuredSoftDeleted->delete();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Featured Published Project')
        ->assertDontSee('Standard Published Project')
        ->assertDontSee('Featured Draft Project')
        ->assertDontSee('Featured Future Project')
        ->assertDontSee('Featured Deleted Project');
});

test('featured projects section enforces a limit of 3 ordered by published_at desc', function () {
    $p1 = Project::factory()->create([
        'title' => 'Oldest Featured Project',
        'status' => 'completed',
        'featured' => true,
        'published_at' => now()->subDays(10),
    ]);
    $p2 = Project::factory()->create([
        'title' => 'Recent Featured Project 1',
        'status' => 'completed',
        'featured' => true,
        'published_at' => now()->subDays(1),
    ]);
    $p3 = Project::factory()->create([
        'title' => 'Recent Featured Project 2',
        'status' => 'completed',
        'featured' => true,
        'published_at' => now()->subDays(2),
    ]);
    $p4 = Project::factory()->create([
        'title' => 'Recent Featured Project 3',
        'status' => 'completed',
        'featured' => true,
        'published_at' => now()->subDays(3),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Recent Featured Project 1')
        ->assertSee('Recent Featured Project 2')
        ->assertSee('Recent Featured Project 3')
        ->assertDontSee('Oldest Featured Project');
});

test('empty state renders when no projects are featured even if published projects exist', function () {
    Project::factory()->create([
        'title' => 'Regular Published Project Only',
        'status' => 'completed',
        'featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('No featured projects available yet.')
        ->assertDontSee('Regular Published Project Only');
});

test('only published learning paths appear on home page with published lesson count', function () {
    $publishedPath = LearningPath::factory()->create([
        'title' => 'Published Go Track',
        'slug' => 'published-go-track',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Lesson::factory()->create([
        'learning_path_id' => $publishedPath->id,
        'title' => 'Go Lesson 1',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Lesson::factory()->create([
        'learning_path_id' => $publishedPath->id,
        'title' => 'Go Lesson Draft',
        'status' => 'draft',
        'published_at' => now()->subDay(),
    ]);

    $draftPath = LearningPath::factory()->create([
        'title' => 'Draft Rust Track',
        'slug' => 'draft-rust-track',
        'status' => 'draft',
        'published_at' => now()->subDay(),
    ]);

    $futurePath = LearningPath::factory()->create([
        'title' => 'Future Python Track',
        'slug' => 'future-python-track',
        'status' => 'published',
        'published_at' => now()->addDays(2),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Published Go Track')
        ->assertSee('1 lesson')
        ->assertDontSee('Draft Rust Track')
        ->assertDontSee('Future Python Track');
});

test('learning paths section enforces a limit of 3 ordered by published_at desc', function () {
    $paths = [];
    for ($i = 1; $i <= 4; $i++) {
        $paths[] = LearningPath::factory()->create([
            'title' => "Learning Path Track {$i}",
            'status' => 'published',
            'published_at' => now()->subDays(5 - $i),
        ]);
    }

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Learning Path Track 4')
        ->assertSee('Learning Path Track 3')
        ->assertSee('Learning Path Track 2')
        ->assertDontSee('Learning Path Track 1');
});

test('empty state renders when no learning paths are published', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('No learning paths published yet.');
});

test('only published posts appear on home page', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();

    $publishedPost = Post::factory()->create([
        'author_id' => $author->id,
        'category_id' => $category->id,
        'title' => 'Architecting Clean Laravel Systems',
        'slug' => 'architecting-clean-laravel-systems',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draftPost = Post::factory()->create([
        'author_id' => $author->id,
        'category_id' => $category->id,
        'title' => 'Unpublished Internal Thoughts',
        'slug' => 'unpublished-internal-thoughts',
        'status' => 'draft',
        'published_at' => now()->subDay(),
    ]);

    $futurePost = Post::factory()->create([
        'author_id' => $author->id,
        'category_id' => $category->id,
        'title' => 'Future Announcement Post',
        'slug' => 'future-announcement-post',
        'status' => 'published',
        'published_at' => now()->addDays(4),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Architecting Clean Laravel Systems')
        ->assertDontSee('Unpublished Internal Thoughts')
        ->assertDontSee('Future Announcement Post');
});

test('latest articles section enforces a limit of 3 ordered by published_at desc', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();

    for ($i = 1; $i <= 4; $i++) {
        Post::factory()->create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => "Article Number {$i}",
            'status' => 'published',
            'published_at' => now()->subDays(5 - $i),
        ]);
    }

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Article Number 4')
        ->assertSee('Article Number 3')
        ->assertSee('Article Number 2')
        ->assertDontSee('Article Number 1');
});

test('empty state renders when no posts are published', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('No articles published yet.');
});

test('categorized skills are rendered without percentage bars', function () {
    $response = $this->get(route('home'));

    $response->assertOk();

    foreach (config('portfolio.skills') as $categoryName => $skills) {
        $response->assertSee($categoryName);
        foreach ($skills as $skill) {
            $response->assertSee($skill);
        }
    }

    // Ensure no percentage symbols or progress bars
    $response->assertDontSee('style="width:')
        ->assertDontSee('%</span>', false);
});

test('developer journey preview is rendered with link to about page', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Developer Journey')
        ->assertSee(route('about'));
});

test('seo metadata and canonical url are rendered on home page', function () {
    $response = $this->get(route('home'));

    $expectedTitle = config('portfolio.profile.name').' — '.e(config('portfolio.profile.role')).' — '.config('app.name');
    $expectedDescription = config('portfolio.profile.headline');

    $response->assertOk()
        ->assertSee('<title>'.$expectedTitle.'</title>', false)
        ->assertSee('<meta name="description" content="'.$expectedDescription.'">', false)
        ->assertSee('<link rel="canonical" href="'.route('home').'">', false);
});

test('navbar displays home as active link on home page', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('text-indigo-600 font-semibold', false);
});

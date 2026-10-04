<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

test('guest can access public blog listing', function () {
    $response = $this->get(route('blog.index'));

    $response->assertOk()
        ->assertViewIs('blog.index');
});

test('authenticated user can access public blog listing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('blog.index'));

    $response->assertOk()
        ->assertViewIs('blog.index');
});

test('only published posts are visible on public blog listing', function () {
    $author = User::factory()->create(['name' => 'Randz Author']);
    $category = Category::factory()->create(['name' => 'Architecture']);

    $published = Post::factory()->create([
        'title' => 'Visible Post',
        'slug' => 'visible-post',
        'author_id' => $author->id,
        'category_id' => $category->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draft = Post::factory()->create([
        'title' => 'Draft Post Hidden',
        'slug' => 'draft-post-hidden',
        'status' => 'draft',
        'published_at' => now()->subDay(),
    ]);

    $unpublishedNullDate = Post::factory()->create([
        'title' => 'No Published Date Post',
        'slug' => 'no-date-post',
        'status' => 'published',
        'published_at' => null,
    ]);

    $futurePublished = Post::factory()->create([
        'title' => 'Future Post Hidden',
        'slug' => 'future-post-hidden',
        'status' => 'published',
        'published_at' => now()->addDays(5),
    ]);

    $archived = Post::factory()->create([
        'title' => 'Archived Post Hidden',
        'slug' => 'archived-post-hidden',
        'status' => 'archived',
        'published_at' => now()->subDay(),
    ]);

    $softDeleted = Post::factory()->create([
        'title' => 'Deleted Post Hidden',
        'slug' => 'deleted-post-hidden',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $softDeleted->delete();

    $response = $this->get(route('blog.index'));

    $response->assertOk()
        ->assertSee('Visible Post')
        ->assertDontSee('Draft Post Hidden')
        ->assertDontSee('No Published Date Post')
        ->assertDontSee('Future Post Hidden')
        ->assertDontSee('Archived Post Hidden')
        ->assertDontSee('Deleted Post Hidden');
});

test('category filter shows only posts from specified category', function () {
    $laravelCat = Category::factory()->create(['name' => 'Laravel Framework', 'slug' => 'laravel-framework']);
    $vueCat = Category::factory()->create(['name' => 'Vue Framework', 'slug' => 'vue-framework']);

    $laravelPost = Post::factory()->create([
        'title' => 'Mastering Eloquent Queries',
        'slug' => 'mastering-eloquent-queries',
        'category_id' => $laravelCat->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $vuePost = Post::factory()->create([
        'title' => 'Vue 3 Reactivity Fundamentals',
        'slug' => 'vue-3-reactivity-fundamentals',
        'category_id' => $vueCat->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.index', ['category' => 'laravel-framework']));

    $response->assertOk()
        ->assertSee('Mastering Eloquent Queries')
        ->assertDontSee('Vue 3 Reactivity Fundamentals');
});

test('tag filter shows only posts with specified tag', function () {
    $tag1 = Tag::factory()->create(['name' => 'DevOps', 'slug' => 'devops']);
    $tag2 = Tag::factory()->create(['name' => 'Testing', 'slug' => 'testing']);

    $devopsPost = Post::factory()->create([
        'title' => 'Docker Compose for Local Development',
        'slug' => 'docker-compose-local',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $devopsPost->tags()->attach($tag1);

    $testingPost = Post::factory()->create([
        'title' => 'Pest PHP Testing Guide',
        'slug' => 'pest-testing-guide',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $testingPost->tags()->attach($tag2);

    $response = $this->get(route('blog.index', ['tag' => 'devops']));

    $response->assertOk()
        ->assertSee('Docker Compose for Local Development')
        ->assertDontSee('Pest PHP Testing Guide');
});

test('combined category and tag filters work together', function () {
    $cat = Category::factory()->create(['slug' => 'backend']);
    $tag = Tag::factory()->create(['slug' => 'php']);
    $otherTag = Tag::factory()->create(['slug' => 'python']);

    $matchPost = Post::factory()->create([
        'title' => 'Modern PHP Backend',
        'slug' => 'modern-php-backend',
        'category_id' => $cat->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $matchPost->tags()->attach($tag);

    $catOnlyPost = Post::factory()->create([
        'title' => 'Python Backend Guide',
        'slug' => 'python-backend-guide',
        'category_id' => $cat->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $catOnlyPost->tags()->attach($otherTag);

    $response = $this->get(route('blog.index', ['category' => 'backend', 'tag' => 'php']));

    $response->assertOk()
        ->assertSee('Modern PHP Backend')
        ->assertDontSee('Python Backend Guide');
});

test('public blog listing paginates when exceeding page size and keeps query string', function () {
    $category = Category::factory()->create(['slug' => 'test-cat']);
    Post::factory()->count(12)->create([
        'category_id' => $category->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.index', ['category' => 'test-cat']));

    $response->assertOk();
    expect($response->viewData('posts'))->toHaveCount(9)
        ->and($response->viewData('posts')->nextPageUrl())->toContain('category=test-cat');
});

test('guest can view published post detail by slug', function () {
    $author = User::factory()->create(['name' => 'Randz Lead']);
    $category = Category::factory()->create(['name' => 'Web Architecture']);
    $tag = Tag::factory()->create(['name' => 'Laravel']);

    $post = Post::factory()->create([
        'title' => 'Building Scalable APIs with Laravel',
        'slug' => 'building-scalable-apis-with-laravel',
        'author_id' => $author->id,
        'category_id' => $category->id,
        'excerpt' => 'An in-depth article on designing scalable APIs.',
        'content' => "Step 1: Planning the schema.\nStep 2: Implementing controllers.",
        'featured_image' => '/images/posts/scalable-apis.png',
        'type' => 'tutorial',
        'reading_time' => 5,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $post->tags()->attach($tag);

    $response = $this->get(route('blog.show', $post));

    $response->assertOk()
        ->assertViewIs('blog.show')
        ->assertSee('Building Scalable APIs with Laravel')
        ->assertSee('An in-depth article on designing scalable APIs.')
        ->assertSee('Planning the schema.')
        ->assertSee('Implementing controllers.')
        ->assertSee('Randz Lead')
        ->assertSee('Web Architecture')
        ->assertSee('Laravel')
        ->assertSee('Tutorial')
        ->assertSee('5 min read')
        ->assertSee('/images/posts/scalable-apis.png')
        ->assertSee(route('blog.index'));
});

test('draft post returns 404 on public detail route', function () {
    $draft = Post::factory()->create([
        'title' => 'Secret Draft Post',
        'slug' => 'secret-draft-post',
        'status' => 'draft',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.show', $draft));

    $response->assertNotFound();
});

test('archived post returns 404 on public detail route', function () {
    $archived = Post::factory()->create([
        'title' => 'Archived Old Post',
        'slug' => 'archived-old-post',
        'status' => 'archived',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.show', $archived));

    $response->assertNotFound();
});

test('future scheduled post returns 404 on public detail route', function () {
    $future = Post::factory()->create([
        'title' => 'Future Post',
        'slug' => 'future-post',
        'status' => 'published',
        'published_at' => now()->addDays(2),
    ]);

    $response = $this->get(route('blog.show', $future));

    $response->assertNotFound();
});

test('unpublished post without date returns 404 on public detail route', function () {
    $post = Post::factory()->create([
        'title' => 'No Date Post',
        'slug' => 'no-date-post',
        'status' => 'published',
        'published_at' => null,
    ]);

    $response = $this->get(route('blog.show', $post));

    $response->assertNotFound();
});

test('soft-deleted post returns 404 on public detail route', function () {
    $post = Post::factory()->create([
        'title' => 'Deleted Post',
        'slug' => 'deleted-post',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $post->delete();

    $response = $this->get(route('blog.show', $post));

    $response->assertNotFound();
});

test('non-existent slug returns 404 on public detail route', function () {
    $response = $this->get(route('blog.show', 'unknown-post-slug'));

    $response->assertNotFound();
});

test('featured image is omitted when null on detail view', function () {
    $post = Post::factory()->create([
        'title' => 'Post Without Image',
        'slug' => 'post-without-image',
        'featured_image' => null,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.show', $post));

    $response->assertOk()
        ->assertDontSee('<img src="', false);
});

test('category filter returns empty state when no posts match', function () {
    $response = $this->get(route('blog.index', ['category' => 'non-existent-category']));

    $response->assertOk()
        ->assertSee('No articles found');
});

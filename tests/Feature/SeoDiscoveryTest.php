<?php

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Project;

test('guest can access sitemap.xml with correct headers and valid XML', function () {
    $response = $this->get(route('sitemap'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/xml');

    $xml = simplexml_load_string($response->getContent());
    expect($xml)->not->toBeFalse();
    expect($xml->getName())->toBe('urlset');
});

test('sitemap contains all static public pages', function () {
    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain(route('home'))
        ->toContain(route('about'))
        ->toContain(route('projects.index'))
        ->toContain(route('blog.index'))
        ->toContain(route('learn.index'))
        ->toContain(route('contact'));
});

test('sitemap includes published projects and excludes drafts, future, and soft-deleted projects', function () {
    $published = Project::factory()->create([
        'title' => 'Published SEO Project',
        'slug' => 'published-seo-project',
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);

    $draft = Project::factory()->draft()->create([
        'title' => 'Draft Project',
        'slug' => 'draft-project',
    ]);

    $future = Project::factory()->create([
        'title' => 'Future Project',
        'slug' => 'future-project',
        'status' => 'completed',
        'published_at' => now()->addDays(2),
    ]);

    $softDeleted = Project::factory()->create([
        'title' => 'Deleted Project',
        'slug' => 'deleted-project',
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);
    $softDeleted->delete();

    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain(route('projects.show', $published))
        ->not->toContain(route('projects.show', $draft))
        ->not->toContain(route('projects.show', $future))
        ->not->toContain(route('projects.show', $softDeleted));
});

test('sitemap includes published posts and excludes drafts, archived, future, and soft-deleted posts', function () {
    $published = Post::factory()->create([
        'title' => 'Published SEO Post',
        'slug' => 'published-seo-post',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draft = Post::factory()->draft()->create([
        'title' => 'Draft SEO Post',
        'slug' => 'draft-seo-post',
    ]);

    $archived = Post::factory()->archived()->create([
        'title' => 'Archived SEO Post',
        'slug' => 'archived-seo-post',
    ]);

    $future = Post::factory()->create([
        'title' => 'Future SEO Post',
        'slug' => 'future-seo-post',
        'status' => 'published',
        'published_at' => now()->addDays(3),
    ]);

    $softDeleted = Post::factory()->create([
        'title' => 'Deleted SEO Post',
        'slug' => 'deleted-seo-post',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $softDeleted->delete();

    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain(route('blog.show', $published))
        ->not->toContain(route('blog.show', $draft))
        ->not->toContain(route('blog.show', $archived))
        ->not->toContain(route('blog.show', $future))
        ->not->toContain(route('blog.show', $softDeleted));
});

test('sitemap includes published learning paths and lessons but excludes drafts, future, and orphaned lessons', function () {
    $path = LearningPath::factory()->create([
        'title' => 'Published Path',
        'slug' => 'published-path',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $publishedLesson = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Published Lesson',
        'slug' => 'published-lesson',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draftLesson = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Draft Lesson',
        'slug' => 'draft-lesson',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $futureLesson = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Future Lesson',
        'slug' => 'future-lesson',
        'status' => 'published',
        'published_at' => now()->addDays(2),
    ]);

    $deletedLesson = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Deleted Lesson',
        'slug' => 'deleted-lesson',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $deletedLesson->delete();

    $draftPath = LearningPath::factory()->create([
        'title' => 'Draft Path',
        'slug' => 'draft-path',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $lessonInDraftPath = Lesson::factory()->create([
        'learning_path_id' => $draftPath->id,
        'title' => 'Lesson In Draft Path',
        'slug' => 'lesson-in-draft-path',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain(route('learn.show', $path))
        ->toContain(route('learn.lesson', [$path, $publishedLesson]))
        ->not->toContain(route('learn.show', $draftPath))
        ->not->toContain(route('learn.lesson', [$path, $draftLesson]))
        ->not->toContain(route('learn.lesson', [$path, $futureLesson]))
        ->not->toContain(route('learn.lesson', [$path, $deletedLesson]))
        ->not->toContain(route('learn.lesson', [$draftPath, $lessonInDraftPath]));
});

test('robots.txt is accessible and disallows admin and dashboard while dynamically declaring sitemap from application URL', function () {
    $expectedSitemapUrl = rtrim((string) config('app.url'), '/').'/sitemap.xml';

    $response = $this->get('/robots.txt');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/plain');

    $content = $response->getContent();

    expect($content)->toContain('User-agent: *')
        ->toContain('Disallow: /admin')
        ->toContain('Disallow: /dashboard')
        ->toContain('Allow: /')
        ->toContain('Sitemap: '.$expectedSitemapUrl);
});

test('robots.txt dynamically adapts when application URL is configured differently', function () {
    config(['app.url' => 'https://randz.dev']);

    $response = $this->get('/robots.txt');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/plain');

    $content = $response->getContent();

    expect($content)->toContain('Sitemap: https://randz.dev/sitemap.xml')
        ->not->toContain('http://localhost');
});

test('homepage contains valid Open Graph and Twitter metadata', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('<meta property="og:site_name" content="')
        ->toContain('<meta property="og:title" content="')
        ->toContain('<meta property="og:description" content="')
        ->toContain('<meta property="og:url" content="'.route('home').'">')
        ->toContain('<meta property="og:type" content="website">')
        ->toContain('<meta name="twitter:card" content="summary">')
        ->toContain('<meta name="twitter:title" content="')
        ->toContain('<meta name="twitter:description" content="');
});

test('blog detail contains valid Open Graph and Twitter metadata including image when present', function () {
    $postWithImage = Post::factory()->create([
        'title' => 'Guide to Modern PHP',
        'slug' => 'guide-to-modern-php',
        'excerpt' => 'Learn how modern PHP features improve code quality.',
        'featured_image' => 'https://example.com/images/php-guide.jpg',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.show', $postWithImage));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('property="og:title" content="Guide to Modern PHP"')
        ->toContain('property="og:description" content="Learn how modern PHP features improve code quality."')
        ->toContain('property="og:url" content="'.route('blog.show', $postWithImage).'"')
        ->toContain('property="og:type" content="article"')
        ->toContain('property="og:image" content="https://example.com/images/php-guide.jpg"')
        ->toContain('name="twitter:card" content="summary_large_image"')
        ->toContain('name="twitter:title" content="Guide to Modern PHP"')
        ->toContain('name="twitter:description" content="Learn how modern PHP features improve code quality."')
        ->toContain('name="twitter:image" content="https://example.com/images/php-guide.jpg"');
});

test('blog detail omits image meta tags when post has no featured image', function () {
    $postWithoutImage = Post::factory()->create([
        'title' => 'Article Without Image',
        'slug' => 'article-without-image',
        'featured_image' => null,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.show', $postWithoutImage));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('property="og:title" content="Article Without Image"')
        ->toContain('name="twitter:card" content="summary"')
        ->not->toContain('property="og:image"')
        ->not->toContain('name="twitter:image"');
});

test('project detail contains valid Open Graph and Twitter metadata including image when present', function () {
    $project = Project::factory()->create([
        'title' => 'Microservice Dashboard',
        'slug' => 'microservice-dashboard',
        'short_description' => 'A scalable real-time monitoring dashboard.',
        'thumbnail' => 'https://example.com/images/dashboard.jpg',
        'status' => 'completed',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('projects.show', $project));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('property="og:title" content="Microservice Dashboard"')
        ->toContain('property="og:description" content="A scalable real-time monitoring dashboard."')
        ->toContain('property="og:url" content="'.route('projects.show', $project).'"')
        ->toContain('property="og:type" content="website"')
        ->toContain('property="og:image" content="https://example.com/images/dashboard.jpg"')
        ->toContain('name="twitter:card" content="summary_large_image"')
        ->toContain('name="twitter:title" content="Microservice Dashboard"')
        ->toContain('name="twitter:description" content="A scalable real-time monitoring dashboard."')
        ->toContain('name="twitter:image" content="https://example.com/images/dashboard.jpg"');
});

test('learning lesson contains valid Open Graph and Twitter metadata', function () {
    $path = LearningPath::factory()->create([
        'title' => 'Laravel Mastery',
        'slug' => 'laravel-mastery',
        'thumbnail' => 'https://example.com/images/path-thumb.jpg',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Service Containers and Providers',
        'slug' => 'service-containers-and-providers',
        'excerpt' => 'Deep dive into IoC and binding services in Laravel.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('learn.lesson', [$path, $lesson]));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('property="og:title" content="Service Containers and Providers — Laravel Mastery"')
        ->toContain('property="og:description" content="Deep dive into IoC and binding services in Laravel."')
        ->toContain('property="og:url" content="'.route('learn.lesson', [$path, $lesson]).'"')
        ->toContain('property="og:type" content="article"')
        ->toContain('property="og:image" content="https://example.com/images/path-thumb.jpg"')
        ->toContain('name="twitter:card" content="summary_large_image"')
        ->toContain('name="twitter:title" content="Service Containers and Providers — Laravel Mastery"')
        ->toContain('name="twitter:description" content="Deep dive into IoC and binding services in Laravel."')
        ->toContain('name="twitter:image" content="https://example.com/images/path-thumb.jpg"');
});

<?php

use App\Models\Category;
use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use App\Support\Markdown;

test('markdown renderer returns empty html string for null or empty content', function () {
    expect((string) Markdown::render(null))->toBe('')
        ->and((string) Markdown::render(''))->toBe('')
        ->and((string) Markdown::render('   '))->toBe('');
});

test('markdown renderer supports standard markdown elements', function () {
    $markdown = <<<'MD'
# Heading 1
## Heading 2

This is a regular paragraph with **bold** and *italic* text.

- Unordered item 1
- Unordered item 2

1. Ordered item 1
2. Ordered item 2

> This is a blockquote.

Here is `inline code` and a [valid link](https://randz.dev).
MD;

    $html = (string) Markdown::render($markdown);

    expect($html)->toContain('<h1>Heading 1</h1>')
        ->and($html)->toContain('<h2>Heading 2</h2>')
        ->and($html)->toContain('<p>This is a regular paragraph with <strong>bold</strong> and <em>italic</em> text.</p>')
        ->and($html)->toContain('<ul>')
        ->and($html)->toContain('<li>Unordered item 1</li>')
        ->and($html)->toContain('<ol>')
        ->and($html)->toContain('<li>Ordered item 1</li>')
        ->and($html)->toContain('<blockquote>')
        ->and($html)->toContain('<code>inline code</code>')
        ->and($html)->toContain('<a href="https://randz.dev">valid link</a>');
});

test('markdown renderer handles fenced code blocks with language identifiers', function () {
    $markdown = <<<'MD'
```php
echo "Hello World";
```
MD;

    $html = (string) Markdown::render($markdown);

    expect($html)->toContain('<pre><code class="language-php">echo &quot;Hello World&quot;;')
        ->and($html)->toContain('</code></pre>');
});

test('markdown renderer handles fenced code blocks without language identifier', function () {
    $markdown = <<<'MD'
```
plain text code block
```
MD;

    $html = (string) Markdown::render($markdown);

    expect($html)->toContain('<pre><code>plain text code block')
        ->and($html)->toContain('</code></pre>');
});

test('markdown renderer neutralizes raw script tags and xss attempts', function () {
    $malicious = <<<'MD'
<script>alert('xss')</script>
<img src=x onerror=alert('xss')>
MD;

    $html = (string) Markdown::render($malicious);

    expect($html)->not->toContain('<script>')
        ->and($html)->not->toContain("alert('xss')</script>")
        ->and($html)->not->toContain('<img src=x onerror=');
});

test('markdown renderer strips unsafe javascript links', function () {
    $maliciousLink = '[click here](javascript:alert("xss"))';

    $html = (string) Markdown::render($maliciousLink);

    expect($html)->not->toContain('javascript:alert')
        ->and($html)->toContain('<a>click here</a>');
});

test('markdown renderer preserves code fences containing script tags safely as inert text', function () {
    $codeFenceScript = <<<'MD'
```html
<script>alert('xss')</script>
```
MD;

    $html = (string) Markdown::render($codeFenceScript);

    expect($html)->toContain('<pre><code class="language-html">&lt;script&gt;alert(\'xss\')&lt;/script&gt;')
        ->and($html)->not->toContain('<script>alert(');
});

test('public blog detail page renders post markdown content as html', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();

    $markdownContent = <<<'MD'
## Building Web Apps

Welcome to the article.

```php
function greet() {
    return 'Hello World';
}
```
MD;

    $post = Post::factory()->create([
        'title' => 'Markdown Post Guide',
        'slug' => 'markdown-post-guide',
        'author_id' => $author->id,
        'category_id' => $category->id,
        'content' => $markdownContent,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.show', $post));

    $response->assertOk()
        ->assertSee('markdown-content')
        ->assertSee('<h2>Building Web Apps</h2>', false)
        ->assertSee('<pre><code class="language-php">', false)
        ->assertSee('Hello World');
});

test('public lesson detail page renders lesson markdown content as html', function () {
    $learningPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $markdownContent = <<<'MD'
## Lesson Overview

Learn how to write JavaScript functions.

```javascript
const greet = () => "Hello Learn";
```
MD;

    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'title' => 'JavaScript Functions Lesson',
        'slug' => 'javascript-functions-lesson',
        'content' => $markdownContent,
        'order' => 1,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('learn.lesson', [$learningPath, $lesson]));

    $response->assertOk()
        ->assertSee('markdown-content')
        ->assertSee('<h2>Lesson Overview</h2>', false)
        ->assertSee('<pre><code class="language-javascript">', false)
        ->assertSee('Hello Learn');
});

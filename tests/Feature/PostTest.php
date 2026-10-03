<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\QueryException;

test('post belongs to author', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['author_id' => $author->id]);

    expect($post->author->id)->toBe($author->id);
});

test('post belongs to category', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->create(['category_id' => $category->id]);

    expect($post->category->id)->toBe($category->id);
});

test('post belongs to many tags', function () {
    $post = Post::factory()->create();
    $tags = Tag::factory()->count(3)->create();

    $post->tags()->attach($tags);

    expect($post->tags)->toHaveCount(3)
        ->and($post->tags->pluck('id')->all())->toEqualCanonicalizing($tags->pluck('id')->all());
});

test('post slug must be unique', function () {
    Post::factory()->create(['slug' => 'first-blog-post']);

    expect(fn () => Post::factory()->create(['slug' => 'first-blog-post']))
        ->toThrow(QueryException::class);
});

test('post uses slug for route model binding', function () {
    $post = new Post;

    expect($post->getRouteKeyName())->toBe('slug');
});

test('deleting category sets post category_id to null', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->create(['category_id' => $category->id]);

    $category->delete();

    expect($post->fresh()->category_id)->toBeNull();
});

test('user has many posts', function () {
    $user = User::factory()->create();
    $posts = Post::factory()->count(2)->create(['author_id' => $user->id]);

    expect($user->posts)->toHaveCount(2)
        ->and($user->posts->pluck('id')->all())->toEqualCanonicalizing($posts->pluck('id')->all());
});

test('category has many posts', function () {
    $category = Category::factory()->create();
    $posts = Post::factory()->count(2)->create(['category_id' => $category->id]);

    expect($category->posts)->toHaveCount(2)
        ->and($category->posts->pluck('id')->all())->toEqualCanonicalizing($posts->pluck('id')->all());
});

test('scope published returns only published posts with past or current published_at', function () {
    $publishedPost = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Post::factory()->draft()->create();

    Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    Post::factory()->archived()->create([
        'published_at' => now()->subDay(),
    ]);

    $results = Post::published()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($publishedPost->id);
});

test('post can be soft deleted without deleting category or tags', function () {
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();
    $post = Post::factory()->create(['category_id' => $category->id]);
    $post->tags()->attach($tag);

    $post->delete();

    expect($post->trashed())->toBeTrue();
    $this->assertSoftDeleted('posts', ['id' => $post->id]);
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('tags', ['id' => $tag->id]);
});

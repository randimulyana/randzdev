<?php

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\QueryException;

test('tag belongs to many posts', function () {
    $tag = Tag::factory()->create();
    $posts = Post::factory()->count(2)->create();

    $tag->posts()->attach($posts);

    expect($tag->posts)->toHaveCount(2)
        ->and($tag->posts->pluck('id')->all())->toEqualCanonicalizing($posts->pluck('id')->all());
});

test('tag slug must be unique', function () {
    Tag::factory()->create(['slug' => 'laravel']);

    expect(fn () => Tag::factory()->create(['slug' => 'laravel']))
        ->toThrow(QueryException::class);
});

test('tag uses slug for route model binding', function () {
    $tag = new Tag;

    expect($tag->getRouteKeyName())->toBe('slug');
});

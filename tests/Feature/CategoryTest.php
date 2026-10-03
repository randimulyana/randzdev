<?php

use App\Models\Category;
use App\Models\Project;
use Illuminate\Database\QueryException;

test('category has many projects', function () {
    $category = Category::factory()->create();
    $projects = Project::factory()->count(3)->create([
        'category_id' => $category->id,
    ]);

    expect($category->projects)->toHaveCount(3)
        ->and($category->projects->pluck('id')->all())->toEqualCanonicalizing($projects->pluck('id')->all());
});

test('category slug must be unique', function () {
    Category::factory()->create(['slug' => 'web-development']);

    expect(fn () => Category::factory()->create(['slug' => 'web-development']))
        ->toThrow(QueryException::class);
});

test('deleting a category sets project category_id to null', function () {
    $category = Category::factory()->create();
    $project = Project::factory()->create(['category_id' => $category->id]);

    $category->delete();

    expect($project->fresh()->category_id)->toBeNull();
});

test('category uses slug for route model binding', function () {
    $category = new Category;

    expect($category->getRouteKeyName())->toBe('slug');
});

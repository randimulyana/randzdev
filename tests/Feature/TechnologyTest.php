<?php

use App\Models\Project;
use App\Models\Technology;
use Illuminate\Database\QueryException;

test('technology belongs to many projects', function () {
    $technology = Technology::factory()->create();
    $projects = Project::factory()->count(2)->create();

    $technology->projects()->attach($projects->pluck('id'));

    expect($technology->projects)->toHaveCount(2)
        ->and($technology->projects->pluck('id')->all())->toEqualCanonicalizing($projects->pluck('id')->all());
});

test('technology slug must be unique', function () {
    Technology::factory()->create(['slug' => 'laravel']);

    expect(fn () => Technology::factory()->create(['slug' => 'laravel']))
        ->toThrow(QueryException::class);
});

test('technology uses slug for route model binding', function () {
    $technology = new Technology;

    expect($technology->getRouteKeyName())->toBe('slug');
});

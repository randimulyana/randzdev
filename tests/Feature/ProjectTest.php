<?php

use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;

test('project belongs to category', function () {
    $category = Category::factory()->create();
    $project = Project::factory()->create(['category_id' => $category->id]);

    expect($project->category)->not->toBeNull()
        ->and($project->category->id)->toBe($category->id);
});

test('project can exist without category', function () {
    $project = Project::factory()->withoutCategory()->create();

    expect($project->category_id)->toBeNull()
        ->and($project->category)->toBeNull();
});

test('project belongs to many technologies', function () {
    $project = Project::factory()->create();
    $technologies = Technology::factory()->count(3)->create();

    $project->technologies()->attach($technologies->pluck('id'));

    expect($project->technologies)->toHaveCount(3)
        ->and($project->technologies->pluck('id')->all())->toEqualCanonicalizing($technologies->pluck('id')->all());
});

test('same technology cannot be attached twice to same project', function () {
    $project = Project::factory()->create();
    $technology = Technology::factory()->create();

    $project->technologies()->attach($technology->id);

    expect(fn () => $project->technologies()->attach($technology->id))
        ->toThrow(QueryException::class);
});

test('project slug must be unique', function () {
    Project::factory()->create(['slug' => 'portfolio-platform']);

    expect(fn () => Project::factory()->create(['slug' => 'portfolio-platform']))
        ->toThrow(QueryException::class);
});

test('project uses slug for route model binding', function () {
    $project = new Project;

    expect($project->getRouteKeyName())->toBe('slug');
});

test('project supports soft deletes', function () {
    $project = Project::factory()->create();

    $project->delete();

    $this->assertSoftDeleted('projects', ['id' => $project->id]);
    expect(Project::find($project->id))->toBeNull()
        ->and(Project::withTrashed()->find($project->id))->not->toBeNull();
});

test('project casts attributes correctly', function () {
    $project = Project::factory()->featured()->create([
        'started_at' => '2026-01-01',
        'completed_at' => '2026-03-01',
        'published_at' => '2026-03-01 10:00:00',
    ]);

    expect($project->featured)->toBeTrue()
        ->and($project->started_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($project->completed_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($project->published_at)->toBeInstanceOf(CarbonInterface::class);
});

<?php

use App\Models\LearningPath;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->otherAuthor = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

test('guests are redirected to login for learning path routes', function () {
    $learningPath = LearningPath::factory()->create();

    $this->get(route('admin.learning-paths.index'))->assertRedirect(route('login'));
    $this->get(route('admin.learning-paths.create'))->assertRedirect(route('login'));
    $this->post(route('admin.learning-paths.store'), ['title' => 'Test', 'slug' => 'test'])->assertRedirect(route('login'));
    $this->get(route('admin.learning-paths.edit', $learningPath))->assertRedirect(route('login'));
    $this->put(route('admin.learning-paths.update', $learningPath), ['title' => 'Test', 'slug' => 'test'])->assertRedirect(route('login'));
    $this->delete(route('admin.learning-paths.destroy', $learningPath))->assertRedirect(route('login'));
});

test('regular users cannot access learning path routes in admin CMS', function () {
    $learningPath = LearningPath::factory()->create();

    $this->actingAs($this->user)->get(route('admin.learning-paths.index'))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.learning-paths.create'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.learning-paths.store'), [
        'title' => 'Forbidden Path',
        'slug' => 'forbidden-path',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.learning-paths.edit', $learningPath))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.learning-paths.update', $learningPath), [
        'title' => 'Updated Path',
        'slug' => 'updated-path',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.learning-paths.destroy', $learningPath))->assertForbidden();
});

test('admin and author can view learning paths index', function (string $role) {
    $actor = $role === 'admin' ? $this->admin : $this->author;

    $learningPath = LearningPath::factory()->create([
        'title' => 'PHP for Beginners',
        'slug' => 'php-for-beginners',
        'difficulty' => 'beginner',
        'status' => 'published',
        'created_by' => $this->author->id,
    ]);

    $response = $this->actingAs($actor)->get(route('admin.learning-paths.index'));

    $response->assertOk()
        ->assertSee('PHP for Beginners')
        ->assertSee('Beginner')
        ->assertSee('Published');
})->with(['admin', 'author']);

test('admin and author can access create learning path page', function (string $role) {
    $actor = $role === 'admin' ? $this->admin : $this->author;

    $this->actingAs($actor)
        ->get(route('admin.learning-paths.create'))
        ->assertOk()
        ->assertSee('Create Learning Path');
})->with(['admin', 'author']);

test('admin and author can create a learning path and creator is set to authenticated user', function (string $role) {
    $actor = $role === 'admin' ? $this->admin : $this->author;

    $data = [
        'title' => 'Laravel Mastery',
        'slug' => 'laravel-mastery',
        'description' => 'Comprehensive guide to mastering Laravel.',
        'thumbnail' => 'https://example.com/thumb.jpg',
        'difficulty' => 'intermediate',
        'estimated_duration' => '10 hours',
        'status' => 'published',
        'published_at' => now()->format('Y-m-d H:i:s'),
    ];

    $response = $this->actingAs($actor)->post(route('admin.learning-paths.store'), $data);

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('learning_paths', [
        'title' => 'Laravel Mastery',
        'slug' => 'laravel-mastery',
        'difficulty' => 'intermediate',
        'status' => 'published',
        'created_by' => $actor->id,
    ]);
})->with(['admin', 'author']);

test('validation prevents creating learning path with invalid data', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => '',
        'slug' => '',
        'difficulty' => 'expert', // Invalid difficulty
        'status' => 'invalid_status', // Invalid status
    ]);

    $response->assertSessionHasErrors(['title', 'slug', 'difficulty', 'status']);
});

test('slug must be globally unique across learning paths', function () {
    LearningPath::factory()->create(['slug' => 'existing-path']);

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Another Path',
        'slug' => 'existing-path',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('publishing lifecycle: draft has null published_at, published has valid timestamp', function () {
    // 1. Create as draft without published_at -> published_at must be null
    $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Draft Course',
        'slug' => 'draft-course',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);

    $draft = LearningPath::where('slug', 'draft-course')->first();
    expect($draft->published_at)->toBeNull();

    // 2. Create as published without published_at -> published_at defaults to now()
    $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Published Course',
        'slug' => 'published-course',
        'difficulty' => 'advanced',
        'status' => 'published',
    ]);

    $published = LearningPath::where('slug', 'published-course')->first();
    expect($published->published_at)->not->toBeNull();
});

test('admin can view edit page of any learning path', function () {
    $learningPath = LearningPath::factory()->create(['created_by' => $this->author->id]);

    $this->actingAs($this->admin)
        ->get(route('admin.learning-paths.edit', $learningPath))
        ->assertOk()
        ->assertSee($learningPath->title);
});

test('author can view edit page of own learning path but forbidden on another author path', function () {
    $ownPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $otherPath = LearningPath::factory()->create(['created_by' => $this->otherAuthor->id]);

    $this->actingAs($this->author)
        ->get(route('admin.learning-paths.edit', $ownPath))
        ->assertOk();

    $this->actingAs($this->author)
        ->get(route('admin.learning-paths.edit', $otherPath))
        ->assertForbidden();
});

test('admin can update any learning path', function () {
    $learningPath = LearningPath::factory()->create(['created_by' => $this->author->id]);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.update', $learningPath), [
        'title' => 'Updated by Admin',
        'slug' => $learningPath->slug,
        'difficulty' => 'advanced',
        'status' => 'published',
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'));
    $this->assertDatabaseHas('learning_paths', [
        'id' => $learningPath->id,
        'title' => 'Updated by Admin',
        'updated_by' => $this->admin->id,
    ]);
});

test('author can update own learning path but cannot update another author learning path', function () {
    $ownPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $otherPath = LearningPath::factory()->create(['created_by' => $this->otherAuthor->id]);

    // Updating own path -> success
    $this->actingAs($this->author)
        ->put(route('admin.learning-paths.update', $ownPath), [
            'title' => 'Updated by Owner',
            'slug' => $ownPath->slug,
            'difficulty' => 'beginner',
            'status' => 'draft',
        ])
        ->assertRedirect(route('admin.learning-paths.index'));

    $this->assertDatabaseHas('learning_paths', [
        'id' => $ownPath->id,
        'title' => 'Updated by Owner',
        'updated_by' => $this->author->id,
    ]);

    // Updating other author's path -> forbidden
    $this->actingAs($this->author)
        ->put(route('admin.learning-paths.update', $otherPath), [
            'title' => 'Hacked by Other Author',
            'slug' => $otherPath->slug,
            'difficulty' => 'beginner',
            'status' => 'draft',
        ])
        ->assertForbidden();
});

test('updating learning path slug ignores the current record', function () {
    $learningPath = LearningPath::factory()->create(['slug' => 'keep-my-slug']);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.update', $learningPath), [
        'title' => 'Same Slug Update',
        'slug' => 'keep-my-slug',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHasNoErrors();
});

test('admin can delete learning path and soft delete is applied', function () {
    $learningPath = LearningPath::factory()->create(['created_by' => $this->author->id]);

    $response = $this->actingAs($this->admin)->delete(route('admin.learning-paths.destroy', $learningPath));

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('learning_paths', ['id' => $learningPath->id]);
});

test('author can delete own learning path but cannot delete other author learning path', function () {
    $ownPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $otherPath = LearningPath::factory()->create(['created_by' => $this->otherAuthor->id]);

    $this->actingAs($this->author)
        ->delete(route('admin.learning-paths.destroy', $ownPath))
        ->assertRedirect(route('admin.learning-paths.index'));

    $this->assertSoftDeleted('learning_paths', ['id' => $ownPath->id]);

    $this->actingAs($this->author)
        ->delete(route('admin.learning-paths.destroy', $otherPath))
        ->assertForbidden();

    $this->assertNotSoftDeleted('learning_paths', ['id' => $otherPath->id]);
});

test('scopePublished only returns published learning paths with past published_at and without soft deletes', function () {
    $published = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $future = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $draft = LearningPath::factory()->draft()->create();

    $softDeleted = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $softDeleted->delete();

    $result = LearningPath::published()->pluck('id');

    expect($result)->toContain($published->id)
        ->not->toContain($future->id)
        ->not->toContain($draft->id)
        ->not->toContain($softDeleted->id);
});

<?php

use App\Models\LearningPath;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
    $this->patch(route('admin.learning-paths.publish', $learningPath))->assertRedirect(route('login'));
    $this->patch(route('admin.learning-paths.unpublish', $learningPath))->assertRedirect(route('login'));
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
    $this->actingAs($this->user)->patch(route('admin.learning-paths.publish', $learningPath))->assertForbidden();
    $this->actingAs($this->user)->patch(route('admin.learning-paths.unpublish', $learningPath))->assertForbidden();
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

test('slug must be globally unique across learning paths when explicitly provided', function () {
    LearningPath::factory()->create(['slug' => 'existing-path']);

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Another Path',
        'slug' => 'existing-path',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('learning path automatically generates slug from title when slug is omitted', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'PHP untuk Pemula',
        'slug' => '',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('learning_paths', [
        'title' => 'PHP untuk Pemula',
        'slug' => 'php-untuk-pemula',
    ]);
});

test('learning path handles slug collisions deterministically with numeric suffixes', function () {
    LearningPath::factory()->create([
        'title' => 'Web Development Basics',
        'slug' => 'web-development-basics',
    ]);

    // First collision -> web-development-basics-1
    $response1 = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Web Development Basics',
        'slug' => '',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);
    $response1->assertRedirect(route('admin.learning-paths.index'));

    $this->assertDatabaseHas('learning_paths', [
        'slug' => 'web-development-basics-1',
    ]);

    // Second collision -> web-development-basics-2
    $response2 = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Web Development Basics',
        'slug' => '',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);
    $response2->assertRedirect(route('admin.learning-paths.index'));

    $this->assertDatabaseHas('learning_paths', [
        'slug' => 'web-development-basics-2',
    ]);
});

test('explicit custom slug is preserved on create', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Modern PHP Programming',
        'slug' => 'custom-php-mastery',
        'difficulty' => 'advanced',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'));

    $this->assertDatabaseHas('learning_paths', [
        'slug' => 'custom-php-mastery',
    ]);
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

test('updating learning path with empty slug preserves the existing slug', function () {
    $learningPath = LearningPath::factory()->create([
        'title' => 'Original Learning Path Title',
        'slug' => 'preserve-this-exact-slug',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.update', $learningPath), [
        'title' => 'Completely Different Title',
        'slug' => '',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHasNoErrors();

    $learningPath->refresh();
    expect($learningPath->title)->toBe('Completely Different Title')
        ->and($learningPath->slug)->toBe('preserve-this-exact-slug');
});

test('updating learning path slug ignores the current record when keeping same slug', function () {
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

test('updating learning path rejects duplicate slug from another learning path', function () {
    LearningPath::factory()->create(['slug' => 'existing-other-slug']);
    $learningPath = LearningPath::factory()->create(['slug' => 'my-own-slug']);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.update', $learningPath), [
        'title' => 'Duplicate Attempt',
        'slug' => 'existing-other-slug',
        'difficulty' => 'beginner',
        'status' => 'draft',
    ]);

    $response->assertSessionHasErrors(['slug']);
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

test('authorized user can publish a draft learning path', function () {
    $learningPath = LearningPath::factory()->create([
        'status' => 'draft',
        'published_at' => null,
        'created_by' => $this->author->id,
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.learning-paths.publish', $learningPath));

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success', 'Learning path published successfully.');

    $learningPath->refresh();
    expect($learningPath->status)->toBe('published')
        ->and($learningPath->published_at)->not->toBeNull();
});

test('publish operation is idempotent and preserves existing published_at', function () {
    $originalTimestamp = now()->subDays(5);
    $learningPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => $originalTimestamp,
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.learning-paths.publish', $learningPath));

    $response->assertRedirect(route('admin.learning-paths.index'));

    $learningPath->refresh();
    expect($learningPath->status)->toBe('published')
        ->and($learningPath->published_at->timestamp)->toBe($originalTimestamp->timestamp);
});

test('author can publish own learning path but is forbidden on other author learning path', function () {
    $ownPath = LearningPath::factory()->create([
        'status' => 'draft',
        'created_by' => $this->author->id,
    ]);

    $otherPath = LearningPath::factory()->create([
        'status' => 'draft',
        'created_by' => $this->otherAuthor->id,
    ]);

    // Author publishing own path -> allowed
    $this->actingAs($this->author)
        ->patch(route('admin.learning-paths.publish', $ownPath))
        ->assertRedirect(route('admin.learning-paths.index'));

    $ownPath->refresh();
    expect($ownPath->status)->toBe('published');

    // Author publishing other author's path -> forbidden
    $this->actingAs($this->author)
        ->patch(route('admin.learning-paths.publish', $otherPath))
        ->assertForbidden();
});

test('authorized user can unpublish a published learning path', function () {
    $learningPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'created_by' => $this->author->id,
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.learning-paths.unpublish', $learningPath));

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success', 'Learning path unpublished successfully.');

    $learningPath->refresh();
    expect($learningPath->status)->toBe('draft')
        ->and($learningPath->published_at)->toBeNull();
});

test('author can unpublish own learning path but is forbidden on other author learning path', function () {
    $ownPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $this->author->id,
    ]);

    $otherPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now(),
        'created_by' => $this->otherAuthor->id,
    ]);

    // Author unpublishing own path -> allowed
    $this->actingAs($this->author)
        ->patch(route('admin.learning-paths.unpublish', $ownPath))
        ->assertRedirect(route('admin.learning-paths.index'));

    $ownPath->refresh();
    expect($ownPath->status)->toBe('draft');

    // Author unpublishing other author's path -> forbidden
    $this->actingAs($this->author)
        ->patch(route('admin.learning-paths.unpublish', $otherPath))
        ->assertForbidden();
});

test('unpublished learning path is inaccessible on public detail route and hidden from public listing', function () {
    $learningPath = LearningPath::factory()->create([
        'title' => 'Initially Published Track',
        'slug' => 'initially-published-track',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    // Publicly accessible initially
    $this->get(route('learn.show', $learningPath))->assertOk();
    $this->get(route('learn.index'))->assertSee('Initially Published Track');

    // Admin unpublishes the learning path
    $this->actingAs($this->admin)->patch(route('admin.learning-paths.unpublish', $learningPath));

    // After unpublish: public show returns 404, index does not see it
    $this->get(route('learn.show', $learningPath))->assertNotFound();
    $this->get(route('learn.index'))->assertDontSee('Initially Published Track');
});

test('authorized user can upload thumbnail for learning path', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('learning-cover.webp', 800, 600);

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Go for Backend Engineers',
        'difficulty' => 'intermediate',
        'status' => 'draft',
        'thumbnail_file' => $file,
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success');

    $learningPath = LearningPath::where('title', 'Go for Backend Engineers')->first();
    expect($learningPath)->not->toBeNull()
        ->and($learningPath->thumbnail)->toStartWith('/storage/learning-paths/');

    $relativeStoragePath = str_replace('/storage/', '', $learningPath->thumbnail);
    Storage::disk('public')->assertExists($relativeStoragePath);
});

test('thumbnail validation rejects invalid files and oversized images', function () {
    $textDoc = UploadedFile::fake()->create('notes.txt', 100);

    $res1 = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Text Thumbnail Track',
        'difficulty' => 'beginner',
        'status' => 'draft',
        'thumbnail_file' => $textDoc,
    ]);
    $res1->assertSessionHasErrors(['thumbnail_file']);

    $oversized = UploadedFile::fake()->image('giant.jpg')->size(3000);

    $res2 = $this->actingAs($this->admin)->post(route('admin.learning-paths.store'), [
        'title' => 'Giant Thumbnail Track',
        'difficulty' => 'beginner',
        'status' => 'draft',
        'thumbnail_file' => $oversized,
    ]);
    $res2->assertSessionHasErrors(['thumbnail_file']);
});

test('replacing local thumbnail deletes old local file from storage', function () {
    Storage::fake('public');

    $oldPath = 'learning-paths/old-banner.jpg';
    Storage::disk('public')->put($oldPath, 'dummy old banner content');

    $learningPath = LearningPath::factory()->create([
        'thumbnail' => '/storage/'.$oldPath,
    ]);

    $newFile = UploadedFile::fake()->image('new-banner.png', 800, 600);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.update', $learningPath), [
        'title' => $learningPath->title,
        'slug' => $learningPath->slug,
        'difficulty' => $learningPath->difficulty,
        'status' => $learningPath->status,
        'thumbnail_file' => $newFile,
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success');

    Storage::disk('public')->assertMissing($oldPath);

    $learningPath->refresh();
    expect($learningPath->thumbnail)->toStartWith('/storage/learning-paths/')
        ->and($learningPath->thumbnail)->not->toBe('/storage/'.$oldPath);

    $newStoragePath = str_replace('/storage/', '', $learningPath->thumbnail);
    Storage::disk('public')->assertExists($newStoragePath);
});

test('replacing external thumbnail does not attempt filesystem deletion', function () {
    Storage::fake('public');

    $learningPath = LearningPath::factory()->create([
        'thumbnail' => 'https://images.unsplash.com/photo-course-sample',
    ]);

    $newFile = UploadedFile::fake()->image('new-replacement.jpg', 600, 400);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.update', $learningPath), [
        'title' => $learningPath->title,
        'slug' => $learningPath->slug,
        'difficulty' => $learningPath->difficulty,
        'status' => $learningPath->status,
        'thumbnail_file' => $newFile,
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success');

    $learningPath->refresh();
    expect($learningPath->thumbnail)->toStartWith('/storage/learning-paths/');
});

test('existing external thumbnail remains compatible when no upload is provided', function () {
    $learningPath = LearningPath::factory()->create([
        'thumbnail' => 'https://example.com/existing-thumbnail.png',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.update', $learningPath), [
        'title' => 'Updated Learning Path Title',
        'slug' => $learningPath->slug,
        'difficulty' => $learningPath->difficulty,
        'status' => $learningPath->status,
        'thumbnail' => 'https://example.com/existing-thumbnail.png',
    ]);

    $response->assertRedirect(route('admin.learning-paths.index'))
        ->assertSessionHas('success');

    $learningPath->refresh();
    expect($learningPath->thumbnail)->toBe('https://example.com/existing-thumbnail.png');
});

test('admin learning paths index displays publish and unpublish action buttons depending on status', function () {
    $draftPath = LearningPath::factory()->create([
        'title' => 'Draft Curriculum',
        'slug' => 'draft-curriculum',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $publishedPath = LearningPath::factory()->create([
        'title' => 'Published Curriculum',
        'slug' => 'published-curriculum',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.learning-paths.index'));

    $response->assertOk()
        ->assertSee('Publish')
        ->assertSee('Unpublish')
        ->assertSee(route('admin.learning-paths.publish', $draftPath))
        ->assertSee(route('admin.learning-paths.unpublish', $publishedPath));
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

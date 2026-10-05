<?php

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->otherAuthor = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

test('guests are redirected to login for nested lesson routes', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create(['learning_path_id' => $learningPath->id]);

    $this->get(route('admin.learning-paths.lessons.create', $learningPath))->assertRedirect(route('login'));
    $this->post(route('admin.learning-paths.lessons.store', $learningPath), ['title' => 'Test', 'slug' => 'test'])->assertRedirect(route('login'));
    $this->get(route('admin.learning-paths.lessons.edit', [$learningPath, $lesson]))->assertRedirect(route('login'));
    $this->put(route('admin.learning-paths.lessons.update', [$learningPath, $lesson]), ['title' => 'Test', 'slug' => 'test'])->assertRedirect(route('login'));
    $this->delete(route('admin.learning-paths.lessons.destroy', [$learningPath, $lesson]))->assertRedirect(route('login'));
});

test('regular users cannot access lesson routes in admin CMS', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create(['learning_path_id' => $learningPath->id]);

    $this->actingAs($this->user)->get(route('admin.learning-paths.lessons.create', $learningPath))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.learning-paths.lessons.store', $learningPath), [
        'title' => 'Forbidden Lesson',
        'slug' => 'forbidden-lesson',
        'content' => 'Content here',
        'order' => 1,
        'status' => 'draft',
    ])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.learning-paths.lessons.edit', [$learningPath, $lesson]))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.learning-paths.lessons.update', [$learningPath, $lesson]), [
        'title' => 'Updated Lesson',
        'slug' => 'updated-lesson',
        'content' => 'Content here',
        'order' => 1,
        'status' => 'draft',
    ])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.learning-paths.lessons.destroy', [$learningPath, $lesson]))->assertForbidden();
});

test('admin can create a lesson in any learning path', function () {
    $learningPath = LearningPath::factory()->create(['created_by' => $this->author->id]);

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.lessons.store', $learningPath), [
        'title' => 'Variables in PHP',
        'slug' => 'variables-in-php',
        'excerpt' => 'Understanding variables.',
        'content' => 'Full lesson content about variables.',
        'order' => 1,
        'status' => 'published',
        'published_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertRedirect(route('admin.learning-paths.edit', $learningPath))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('lessons', [
        'learning_path_id' => $learningPath->id,
        'title' => 'Variables in PHP',
        'slug' => 'variables-in-php',
        'order' => 1,
        'status' => 'published',
        'created_by' => $this->admin->id,
    ]);
});

test('author can create a lesson in own learning path but cannot in other author learning path', function () {
    $ownPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $otherPath = LearningPath::factory()->create(['created_by' => $this->otherAuthor->id]);

    // Create in own path -> allowed
    $this->actingAs($this->author)
        ->post(route('admin.learning-paths.lessons.store', $ownPath), [
            'title' => 'My First Lesson',
            'slug' => 'my-first-lesson',
            'content' => 'Lesson content here.',
            'order' => 1,
            'status' => 'draft',
        ])
        ->assertRedirect(route('admin.learning-paths.edit', $ownPath));

    $this->assertDatabaseHas('lessons', [
        'learning_path_id' => $ownPath->id,
        'slug' => 'my-first-lesson',
        'created_by' => $this->author->id,
    ]);

    // Create in other author's path -> forbidden
    $this->actingAs($this->author)
        ->post(route('admin.learning-paths.lessons.store', $otherPath), [
            'title' => 'Intruder Lesson',
            'slug' => 'intruder-lesson',
            'content' => 'Should fail.',
            'order' => 1,
            'status' => 'draft',
        ])
        ->assertForbidden();
});

test('scoped binding ensures mismatch between parent learning path and lesson returns 404', function () {
    $path1 = LearningPath::factory()->create();
    $path2 = LearningPath::factory()->create();

    $lessonOfPath1 = Lesson::factory()->create(['learning_path_id' => $path1->id]);

    // Attempt to access lesson of path1 under path2 URL -> must be 404
    $this->actingAs($this->admin)
        ->get(route('admin.learning-paths.lessons.edit', [$path2, $lessonOfPath1]))
        ->assertNotFound();

    $this->actingAs($this->admin)
        ->put(route('admin.learning-paths.lessons.update', [$path2, $lessonOfPath1]), [
            'title' => 'Mismatch update',
            'slug' => 'mismatch-update',
            'content' => 'Content',
            'order' => 1,
            'status' => 'draft',
        ])
        ->assertNotFound();

    $this->actingAs($this->admin)
        ->delete(route('admin.learning-paths.lessons.destroy', [$path2, $lessonOfPath1]))
        ->assertNotFound();
});

test('slug must be unique within the same learning path', function () {
    $learningPath = LearningPath::factory()->create();
    Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'slug' => 'duplicate-slug',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.lessons.store', $learningPath), [
        'title' => 'Duplicate Slug Lesson',
        'slug' => 'duplicate-slug',
        'content' => 'Some content',
        'order' => 2,
        'status' => 'draft',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('same slug is allowed across different learning paths', function () {
    $path1 = LearningPath::factory()->create();
    $path2 = LearningPath::factory()->create();

    Lesson::factory()->create([
        'learning_path_id' => $path1->id,
        'slug' => 'intro',
    ]);

    // Creating lesson with slug 'intro' in path2 should succeed
    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.lessons.store', $path2), [
        'title' => 'Introduction to Path 2',
        'slug' => 'intro',
        'content' => 'Introductory content',
        'order' => 1,
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.learning-paths.edit', $path2))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('lessons', [
        'learning_path_id' => $path2->id,
        'slug' => 'intro',
    ]);
});

test('order defaults to next sequential number when omitted', function () {
    $learningPath = LearningPath::factory()->create();
    Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'order' => 5,
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.lessons.store', $learningPath), [
        'title' => 'Lesson with Auto Order',
        'slug' => 'lesson-auto-order',
        'content' => 'Content here',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.learning-paths.edit', $learningPath));

    $this->assertDatabaseHas('lessons', [
        'learning_path_id' => $learningPath->id,
        'slug' => 'lesson-auto-order',
        'order' => 6,
    ]);
});

test('lessons relationship is ordered ascending by order field', function () {
    $learningPath = LearningPath::factory()->create();

    $lesson3 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 3]);
    $lesson1 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 1]);
    $lesson2 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 2]);

    $orderedIds = $learningPath->fresh()->lessons->pluck('id')->all();

    expect($orderedIds)->toBe([$lesson1->id, $lesson2->id, $lesson3->id]);
});

test('admin can update lesson in any learning path', function () {
    $learningPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $lesson = Lesson::factory()->create(['learning_path_id' => $learningPath->id]);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.lessons.update', [$learningPath, $lesson]), [
        'title' => 'Updated by Admin',
        'slug' => $lesson->slug,
        'content' => 'Updated content by admin.',
        'order' => 10,
        'status' => 'published',
    ]);

    $response->assertRedirect(route('admin.learning-paths.edit', $learningPath));

    $this->assertDatabaseHas('lessons', [
        'id' => $lesson->id,
        'title' => 'Updated by Admin',
        'order' => 10,
        'updated_by' => $this->admin->id,
    ]);
});

test('author can update lesson in own learning path but cannot in other author learning path', function () {
    $ownPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $ownLesson = Lesson::factory()->create(['learning_path_id' => $ownPath->id]);

    $otherPath = LearningPath::factory()->create(['created_by' => $this->otherAuthor->id]);
    $otherLesson = Lesson::factory()->create(['learning_path_id' => $otherPath->id]);

    // Updating own lesson -> success
    $this->actingAs($this->author)
        ->put(route('admin.learning-paths.lessons.update', [$ownPath, $ownLesson]), [
            'title' => 'Updated Lesson by Owner',
            'slug' => $ownLesson->slug,
            'content' => 'New content',
            'order' => 2,
            'status' => 'published',
        ])
        ->assertRedirect(route('admin.learning-paths.edit', $ownPath));

    $this->assertDatabaseHas('lessons', [
        'id' => $ownLesson->id,
        'title' => 'Updated Lesson by Owner',
        'updated_by' => $this->author->id,
    ]);

    // Updating lesson in other author's path -> forbidden
    $this->actingAs($this->author)
        ->put(route('admin.learning-paths.lessons.update', [$otherPath, $otherLesson]), [
            'title' => 'Hacked by Other Author',
            'slug' => $otherLesson->slug,
            'content' => 'Malicious update',
            'order' => 2,
            'status' => 'draft',
        ])
        ->assertForbidden();
});

test('admin and author can delete lesson in appropriate learning paths with soft delete', function () {
    $ownPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $ownLesson = Lesson::factory()->create(['learning_path_id' => $ownPath->id]);

    $otherPath = LearningPath::factory()->create(['created_by' => $this->otherAuthor->id]);
    $otherLesson = Lesson::factory()->create(['learning_path_id' => $otherPath->id]);

    // Author deletes own lesson -> success
    $this->actingAs($this->author)
        ->delete(route('admin.learning-paths.lessons.destroy', [$ownPath, $ownLesson]))
        ->assertRedirect(route('admin.learning-paths.edit', $ownPath));

    $this->assertSoftDeleted('lessons', ['id' => $ownLesson->id]);

    // Author tries to delete other author's lesson -> forbidden
    $this->actingAs($this->author)
        ->delete(route('admin.learning-paths.lessons.destroy', [$otherPath, $otherLesson]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('lessons', ['id' => $otherLesson->id]);

    // Admin deletes other author's lesson -> success
    $this->actingAs($this->admin)
        ->delete(route('admin.learning-paths.lessons.destroy', [$otherPath, $otherLesson]))
        ->assertRedirect(route('admin.learning-paths.edit', $otherPath));

    $this->assertSoftDeleted('lessons', ['id' => $otherLesson->id]);
});

test('scopePublished on Lesson returns only published lessons with past published_at without soft deletes', function () {
    $published = Lesson::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $future = Lesson::factory()->create([
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $draft = Lesson::factory()->draft()->create();

    $softDeleted = Lesson::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $softDeleted->delete();

    $result = Lesson::published()->pluck('id');

    expect($result)->toContain($published->id)
        ->not->toContain($future->id)
        ->not->toContain($draft->id)
        ->not->toContain($softDeleted->id);
});

test('guests are redirected to login for lesson index, publish, unpublish, and move routes', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create(['learning_path_id' => $learningPath->id]);

    $this->get(route('admin.learning-paths.lessons.index', $learningPath))->assertRedirect(route('login'));
    $this->patch(route('admin.learning-paths.lessons.publish', [$learningPath, $lesson]))->assertRedirect(route('login'));
    $this->patch(route('admin.learning-paths.lessons.unpublish', [$learningPath, $lesson]))->assertRedirect(route('login'));
    $this->patch(route('admin.learning-paths.lessons.move-up', [$learningPath, $lesson]))->assertRedirect(route('login'));
    $this->patch(route('admin.learning-paths.lessons.move-down', [$learningPath, $lesson]))->assertRedirect(route('login'));
});

test('regular users cannot access lesson index, publish, unpublish, and move routes', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create(['learning_path_id' => $learningPath->id]);

    $this->actingAs($this->user)->get(route('admin.learning-paths.lessons.index', $learningPath))->assertForbidden();
    $this->actingAs($this->user)->patch(route('admin.learning-paths.lessons.publish', [$learningPath, $lesson]))->assertForbidden();
    $this->actingAs($this->user)->patch(route('admin.learning-paths.lessons.unpublish', [$learningPath, $lesson]))->assertForbidden();
    $this->actingAs($this->user)->patch(route('admin.learning-paths.lessons.move-up', [$learningPath, $lesson]))->assertForbidden();
    $this->actingAs($this->user)->patch(route('admin.learning-paths.lessons.move-down', [$learningPath, $lesson]))->assertForbidden();
});

test('admin can view lesson index and create and edit forms', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create(['learning_path_id' => $learningPath->id]);

    $this->actingAs($this->admin)->get(route('admin.learning-paths.lessons.index', $learningPath))->assertOk()->assertSee($lesson->title);
    $this->actingAs($this->admin)->get(route('admin.learning-paths.lessons.create', $learningPath))->assertOk()->assertSee('Add Lesson to');
    $this->actingAs($this->admin)->get(route('admin.learning-paths.lessons.edit', [$learningPath, $lesson]))->assertOk()->assertSee('Edit Lesson');
});

test('author can access lesson index for own path but cannot for other author path', function () {
    $ownPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $otherPath = LearningPath::factory()->create(['created_by' => $this->otherAuthor->id]);

    $this->actingAs($this->author)->get(route('admin.learning-paths.lessons.index', $ownPath))->assertOk();
    $this->actingAs($this->author)->get(route('admin.learning-paths.lessons.index', $otherPath))->assertForbidden();
});

test('lesson validation enforces required title, content, and valid status', function () {
    $learningPath = LearningPath::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.lessons.store', $learningPath), [
        'title' => '',
        'content' => '',
        'status' => 'invalid-status',
    ]);

    $response->assertSessionHasErrors(['title', 'content', 'status']);
});

test('slug is automatically generated from title when omitted on create', function () {
    $learningPath = LearningPath::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.lessons.store', $learningPath), [
        'title' => 'Pengenalan PHP Modern',
        'slug' => '',
        'content' => 'Introductory text',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.learning-paths.edit', $learningPath));

    $this->assertDatabaseHas('lessons', [
        'learning_path_id' => $learningPath->id,
        'title' => 'Pengenalan PHP Modern',
        'slug' => 'pengenalan-php-modern',
    ]);
});

test('slug collision is resolved deterministically with suffix numbers on create', function () {
    $learningPath = LearningPath::factory()->create();

    Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'slug' => 'routing-dasar',
    ]);

    Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'slug' => 'routing-dasar-1',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.learning-paths.lessons.store', $learningPath), [
        'title' => 'Routing Dasar',
        'slug' => '',
        'content' => 'Lesson about routing.',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.learning-paths.edit', $learningPath));

    $this->assertDatabaseHas('lessons', [
        'learning_path_id' => $learningPath->id,
        'slug' => 'routing-dasar-2',
    ]);
});

test('omitting slug on update preserves existing slug and does not regenerate from changed title', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'title' => 'Old Lesson Title',
        'slug' => 'original-stable-slug',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.lessons.update', [$learningPath, $lesson]), [
        'title' => 'Completely Different New Title',
        'slug' => '',
        'content' => 'Updated content',
        'order' => $lesson->order,
        'status' => 'published',
    ]);

    $response->assertRedirect(route('admin.learning-paths.edit', $learningPath));

    $lesson->refresh();
    expect($lesson->title)->toBe('Completely Different New Title');
    expect($lesson->slug)->toBe('original-stable-slug');
});

test('explicit new slug on update works and passes uniqueness ignoring self', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'slug' => 'old-slug',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.learning-paths.lessons.update', [$learningPath, $lesson]), [
        'title' => $lesson->title,
        'slug' => 'new-custom-slug',
        'content' => 'Updated content',
        'order' => $lesson->order,
        'status' => 'published',
    ]);

    $response->assertRedirect(route('admin.learning-paths.edit', $learningPath));

    expect($lesson->fresh()->slug)->toBe('new-custom-slug');
});

test('publish action updates status and published_at and is idempotent', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'draft',
        'published_at' => null,
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.learning-paths.lessons.publish', [$learningPath, $lesson]));
    $response->assertSessionHas('success');

    $lesson->refresh();
    expect($lesson->status)->toBe('published');
    expect($lesson->published_at)->not->toBeNull();

    $originalPublishedAt = $lesson->published_at;

    $this->travel(5)->days();
    $this->actingAs($this->admin)->patch(route('admin.learning-paths.lessons.publish', [$learningPath, $lesson]));

    $lesson->refresh();
    expect($lesson->published_at->timestamp)->toBe($originalPublishedAt->timestamp);
});

test('unpublish action reverts status to draft and clears published_at', function () {
    $learningPath = LearningPath::factory()->create();
    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.learning-paths.lessons.unpublish', [$learningPath, $lesson]));
    $response->assertSessionHas('success');

    $lesson->refresh();
    expect($lesson->status)->toBe('draft');
    expect($lesson->published_at)->toBeNull();
});

test('unpublished lesson is immediately hidden from public access', function () {
    $learningPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('learn.lesson', [$learningPath, $lesson]))->assertOk();

    $this->actingAs($this->admin)->patch(route('admin.learning-paths.lessons.unpublish', [$learningPath, $lesson]));

    $this->get(route('learn.lesson', [$learningPath, $lesson]))->assertNotFound();
});

test('move up and move down swap adjacent lessons sequentially', function () {
    $learningPath = LearningPath::factory()->create();

    $lesson1 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 1]);
    $lesson2 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 2]);
    $lesson3 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 3]);

    $response = $this->actingAs($this->admin)->patch(route('admin.learning-paths.lessons.move-up', [$learningPath, $lesson2]));
    $response->assertSessionHas('success');

    expect($lesson2->fresh()->order)->toBe(1);
    expect($lesson1->fresh()->order)->toBe(2);
    expect($lesson3->fresh()->order)->toBe(3);

    $this->actingAs($this->admin)->patch(route('admin.learning-paths.lessons.move-down', [$learningPath, $lesson2]));

    expect($lesson2->fresh()->order)->toBe(2);
    expect($lesson1->fresh()->order)->toBe(1);
    expect($lesson3->fresh()->order)->toBe(3);
});

test('moving first lesson up and last lesson down are safe no-ops', function () {
    $learningPath = LearningPath::factory()->create();

    $lesson1 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 1]);
    $lesson2 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 2]);

    $this->actingAs($this->admin)->patch(route('admin.learning-paths.lessons.move-up', [$learningPath, $lesson1]));
    expect($lesson1->fresh()->order)->toBe(1);

    $this->actingAs($this->admin)->patch(route('admin.learning-paths.lessons.move-down', [$learningPath, $lesson2]));
    expect($lesson2->fresh()->order)->toBe(2);
});

test('deleting a lesson soft deletes and leaves parent learning path intact', function () {
    $learningPath = LearningPath::factory()->create();

    $lesson1 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 1]);
    $lesson2 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 2]);
    $lesson3 = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 3]);

    $this->actingAs($this->admin)->delete(route('admin.learning-paths.lessons.destroy', [$learningPath, $lesson2]));

    $this->assertSoftDeleted('lessons', ['id' => $lesson2->id]);
    expect(LearningPath::find($learningPath->id))->not->toBeNull();
});

test('updating lesson order directly updates order value', function () {
    $learningPath = LearningPath::factory()->create();

    $lesson = Lesson::factory()->create(['learning_path_id' => $learningPath->id, 'order' => 1]);

    $this->actingAs($this->admin)->put(route('admin.learning-paths.lessons.update', [$learningPath, $lesson]), [
        'title' => $lesson->title,
        'slug' => $lesson->slug,
        'content' => $lesson->content,
        'order' => 5,
        'status' => $lesson->status,
    ]);

    expect($lesson->fresh()->order)->toBe(5);
});

test('author can publish, unpublish, and move lessons in own learning path but not in other author path', function () {
    $ownPath = LearningPath::factory()->create(['created_by' => $this->author->id]);
    $ownLesson1 = Lesson::factory()->create(['learning_path_id' => $ownPath->id, 'order' => 1, 'status' => 'draft']);
    $ownLesson2 = Lesson::factory()->create(['learning_path_id' => $ownPath->id, 'order' => 2, 'status' => 'draft']);

    $otherPath = LearningPath::factory()->create(['created_by' => $this->otherAuthor->id]);
    $otherLesson = Lesson::factory()->create(['learning_path_id' => $otherPath->id, 'order' => 1, 'status' => 'draft']);

    $this->actingAs($this->author)->patch(route('admin.learning-paths.lessons.publish', [$ownPath, $ownLesson1]))->assertRedirect();
    $this->actingAs($this->author)->patch(route('admin.learning-paths.lessons.unpublish', [$ownPath, $ownLesson1]))->assertRedirect();
    $this->actingAs($this->author)->patch(route('admin.learning-paths.lessons.move-down', [$ownPath, $ownLesson1]))->assertRedirect();

    $this->actingAs($this->author)->patch(route('admin.learning-paths.lessons.publish', [$otherPath, $otherLesson]))->assertForbidden();
    $this->actingAs($this->author)->patch(route('admin.learning-paths.lessons.unpublish', [$otherPath, $otherLesson]))->assertForbidden();
    $this->actingAs($this->author)->patch(route('admin.learning-paths.lessons.move-up', [$otherPath, $otherLesson]))->assertForbidden();
});

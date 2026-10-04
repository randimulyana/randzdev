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

<?php

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\User;

test('guest can access public learn listing', function () {
    $response = $this->get(route('learn.index'));

    $response->assertOk()
        ->assertViewIs('learn.index');
});

test('authenticated user can access public learn listing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('learn.index'));

    $response->assertOk()
        ->assertViewIs('learn.index');
});

test('only published learning paths appear on public learn listing', function () {
    $published = LearningPath::factory()->create([
        'title' => 'Visible Learning Path',
        'slug' => 'visible-learning-path',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draft = LearningPath::factory()->create([
        'title' => 'Draft Learning Path Hidden',
        'slug' => 'draft-path-hidden',
        'status' => 'draft',
        'published_at' => now()->subDay(),
    ]);

    $nullDate = LearningPath::factory()->create([
        'title' => 'No Date Learning Path Hidden',
        'slug' => 'no-date-path-hidden',
        'status' => 'published',
        'published_at' => null,
    ]);

    $future = LearningPath::factory()->create([
        'title' => 'Future Learning Path Hidden',
        'slug' => 'future-path-hidden',
        'status' => 'published',
        'published_at' => now()->addDays(2),
    ]);

    $archived = LearningPath::factory()->create([
        'title' => 'Archived Learning Path Hidden',
        'slug' => 'archived-path-hidden',
        'status' => 'archived',
        'published_at' => now()->subDay(),
    ]);

    $softDeleted = LearningPath::factory()->create([
        'title' => 'Deleted Learning Path Hidden',
        'slug' => 'deleted-path-hidden',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $softDeleted->delete();

    $response = $this->get(route('learn.index'));

    $response->assertOk()
        ->assertSee('Visible Learning Path')
        ->assertDontSee('Draft Learning Path Hidden')
        ->assertDontSee('No Date Learning Path Hidden')
        ->assertDontSee('Future Learning Path Hidden')
        ->assertDontSee('Archived Learning Path Hidden')
        ->assertDontSee('Deleted Learning Path Hidden');
});

test('published lesson count on learning path card only counts published lessons', function () {
    $learningPath = LearningPath::factory()->create([
        'title' => 'PHP Masterclass',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'draft',
        'published_at' => null,
    ]);

    Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'published',
        'published_at' => now()->addDays(3),
    ]);

    Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'archived',
        'published_at' => now()->subDay(),
    ]);

    $deletedLesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $deletedLesson->delete();

    $response = $this->get(route('learn.index'));

    $response->assertOk()
        ->assertSee('1 lesson')
        ->assertDontSee('5 lessons');
});

test('public learn listing paginates learning paths according to convention', function () {
    LearningPath::factory()->count(11)->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('learn.index'));

    $response->assertOk()
        ->assertViewHas('learningPaths', function ($paths) {
            return $paths->count() === 9 && $paths->total() === 11;
        });
});

test('empty state renders when no published learning paths exist', function () {
    $response = $this->get(route('learn.index'));

    $response->assertOk()
        ->assertSee('No learning paths found')
        ->assertSee('No published learning paths are available at the moment.');
});

test('guest can view published learning path detail', function () {
    $learningPath = LearningPath::factory()->create([
        'title' => 'Laravel Deep Dive',
        'slug' => 'laravel-deep-dive',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('learn.show', $learningPath));

    $response->assertOk()
        ->assertViewIs('learn.show')
        ->assertSee('Laravel Deep Dive');
});

test('unpublished learning path returns 404', function () {
    $draft = LearningPath::factory()->create([
        'slug' => 'draft-path',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $future = LearningPath::factory()->create([
        'slug' => 'future-path',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $archived = LearningPath::factory()->create([
        'slug' => 'archived-path',
        'status' => 'archived',
        'published_at' => now()->subDay(),
    ]);

    $softDeleted = LearningPath::factory()->create([
        'slug' => 'deleted-path',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $softDeleted->delete();

    $this->get(route('learn.show', 'draft-path'))->assertNotFound();
    $this->get(route('learn.show', 'future-path'))->assertNotFound();
    $this->get(route('learn.show', 'archived-path'))->assertNotFound();
    $this->get(route('learn.show', 'deleted-path'))->assertNotFound();
});

test('learning path detail only displays published lessons in order asc id asc', function () {
    $learningPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson2 = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'title' => 'Lesson Two',
        'order' => 2,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson1 = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'title' => 'Lesson One',
        'order' => 1,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draftLesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'title' => 'Draft Lesson Hidden',
        'order' => 0,
        'status' => 'draft',
        'published_at' => null,
    ]);

    $response = $this->get(route('learn.show', $learningPath));

    $response->assertOk()
        ->assertSeeInOrder(['Lesson One', 'Lesson Two'])
        ->assertDontSee('Draft Lesson Hidden');
});

test('start learning points to first published lesson and is absent when no lessons exist', function () {
    $learningPathWithLessons = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $firstLesson = Lesson::factory()->create([
        'learning_path_id' => $learningPathWithLessons->id,
        'order' => 1,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $responseWithLessons = $this->get(route('learn.show', $learningPathWithLessons));
    $responseWithLessons->assertOk()
        ->assertSee('Start Learning')
        ->assertSee(route('learn.lesson', [$learningPathWithLessons, $firstLesson]));

    $learningPathEmpty = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $responseEmpty = $this->get(route('learn.show', $learningPathEmpty));
    $responseEmpty->assertOk()
        ->assertDontSee('Start Learning')
        ->assertSee('Curriculum in preparation')
        ->assertSee('No lessons published yet');
});

test('guest can view published lesson detail', function () {
    $learningPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'title' => 'Variables and Types',
        'content' => 'Sample lesson body text.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('learn.lesson', [$learningPath, $lesson]));

    $response->assertOk()
        ->assertViewIs('learn.lesson')
        ->assertSee('Variables and Types')
        ->assertSee('Sample lesson body text.');
});

test('unpublished lesson returns 404', function () {
    $learningPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draft = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'slug' => 'draft-lesson',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $future = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'slug' => 'future-lesson',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $archived = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'slug' => 'archived-lesson',
        'status' => 'archived',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('learn.lesson', [$learningPath, 'draft-lesson']))->assertNotFound();
    $this->get(route('learn.lesson', [$learningPath, 'future-lesson']))->assertNotFound();
    $this->get(route('learn.lesson', [$learningPath, 'archived-lesson']))->assertNotFound();
});

test('lesson under unpublished learning path returns 404', function () {
    $draftPath = LearningPath::factory()->create([
        'status' => 'draft',
        'published_at' => null,
    ]);

    $lesson = Lesson::factory()->create([
        'learning_path_id' => $draftPath->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('learn.lesson', [$draftPath, $lesson]))->assertNotFound();
});

test('lesson requested under mismatched learning path returns 404', function () {
    $path1 = LearningPath::factory()->create([
        'slug' => 'path-one',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $path2 = LearningPath::factory()->create([
        'slug' => 'path-two',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lessonOfPath2 = Lesson::factory()->create([
        'learning_path_id' => $path2->id,
        'slug' => 'lesson-belonging-to-two',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('learn.lesson', [$path1, $lessonOfPath2]))->assertNotFound();
});

test('sequence number and previous next navigation work deterministically according to order asc id asc', function () {
    $path = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson1 = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'First Lesson',
        'order' => 1,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson2 = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Second Lesson Same Order Earlier ID',
        'order' => 2,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson3 = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Third Lesson Same Order Later ID',
        'order' => 2,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draftMiddle = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Draft In Middle',
        'order' => 2,
        'status' => 'draft',
        'published_at' => null,
    ]);

    $lesson4 = Lesson::factory()->create([
        'learning_path_id' => $path->id,
        'title' => 'Fourth Lesson',
        'order' => 3,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    // Check first lesson: no previous, next is lesson2, sequence 1 of 4
    $res1 = $this->get(route('learn.lesson', [$path, $lesson1]));
    $res1->assertOk()
        ->assertSee('Lesson 1 of 4')
        ->assertDontSee('Previous Lesson')
        ->assertSee('Next Lesson')
        ->assertSee(route('learn.lesson', [$path, $lesson2]));

    // Check second lesson: previous is lesson1, next is lesson3, sequence 2 of 4
    $res2 = $this->get(route('learn.lesson', [$path, $lesson2]));
    $res2->assertOk()
        ->assertSee('Lesson 2 of 4')
        ->assertSee('Previous Lesson')
        ->assertSee(route('learn.lesson', [$path, $lesson1]))
        ->assertSee('Next Lesson')
        ->assertSee(route('learn.lesson', [$path, $lesson3]));

    // Check third lesson: previous is lesson2, next is lesson4 (skipping draft), sequence 3 of 4
    $res3 = $this->get(route('learn.lesson', [$path, $lesson3]));
    $res3->assertOk()
        ->assertSee('Lesson 3 of 4')
        ->assertSee('Previous Lesson')
        ->assertSee(route('learn.lesson', [$path, $lesson2]))
        ->assertSee('Next Lesson')
        ->assertSee(route('learn.lesson', [$path, $lesson4]));

    // Check fourth lesson: previous is lesson3, next is absent, sequence 4 of 4
    $res4 = $this->get(route('learn.lesson', [$path, $lesson4]));
    $res4->assertOk()
        ->assertSee('Lesson 4 of 4')
        ->assertSee('Previous Lesson')
        ->assertSee(route('learn.lesson', [$path, $lesson3]))
        ->assertDontSee('Next Lesson');
});

test('lesson content containing html or script-like text is rendered safely escaped', function () {
    $learningPath = LearningPath::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $maliciousContent = '<script>alert("xss")</script><div class="injected">Dangerous HTML</div>';

    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
        'content' => $maliciousContent,
    ]);

    $response = $this->get(route('learn.lesson', [$learningPath, $lesson]));

    $response->assertOk()
        ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertDontSee('<div class="injected">', false);
});

test('seo meta tags and canonical url are rendered across public learning pages', function () {
    $learningPath = LearningPath::factory()->create([
        'title' => 'Mastering Algorithms',
        'description' => 'Comprehensive algorithm track for engineering candidates.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $lesson = Lesson::factory()->create([
        'learning_path_id' => $learningPath->id,
        'title' => 'Binary Search',
        'excerpt' => 'Understanding logarithmic time complexity search.',
        'content' => 'Binary search content body.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    // Test Catalog SEO
    $catalogRes = $this->get(route('learn.index'));
    $catalogRes->assertOk()
        ->assertSee('<title>Learning Paths — '.config('app.name', 'Randz.dev').'</title>', false)
        ->assertSee('<meta name="description" content="Explore structured programming learning paths from beginner to advanced.">', false)
        ->assertSee('<link rel="canonical" href="'.route('learn.index').'">', false);

    // Test Path Detail SEO
    $pathRes = $this->get(route('learn.show', $learningPath));
    $pathRes->assertOk()
        ->assertSee('<title>Mastering Algorithms — '.config('app.name', 'Randz.dev').'</title>', false)
        ->assertSee('<meta name="description" content="Comprehensive algorithm track for engineering candidates.">', false)
        ->assertSee('<link rel="canonical" href="'.route('learn.show', $learningPath).'">', false);

    // Test Lesson Detail SEO
    $lessonRes = $this->get(route('learn.lesson', [$learningPath, $lesson]));
    $lessonRes->assertOk()
        ->assertSee('<title>Binary Search — Mastering Algorithms — '.config('app.name', 'Randz.dev').'</title>', false)
        ->assertSee('<meta name="description" content="Understanding logarithmic time complexity search.">', false)
        ->assertSee('<link rel="canonical" href="'.route('learn.lesson', [$learningPath, $lesson]).'">', false);
});

test('public layout renders learn navigation link with active state', function () {
    $responseCatalog = $this->get(route('learn.index'));

    $responseCatalog->assertOk()
        ->assertSee(route('learn.index'))
        ->assertSee('text-indigo-600 font-semibold');
});

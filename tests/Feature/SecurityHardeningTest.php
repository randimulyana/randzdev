<?php

use App\Models\Category;
use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author1 = User::factory()->create()->assignRole('author');
    $this->author2 = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

/*
|--------------------------------------------------------------------------
| A. AUTHORIZATION HARDENING
|--------------------------------------------------------------------------
*/

test('guests are redirected to login across all admin routes', function () {
    $routes = [
        route('admin.dashboard'),
        route('admin.posts.index'),
        route('admin.projects.index'),
        route('admin.learning-paths.index'),
        route('admin.categories.index'),
        route('admin.tags.index'),
        route('admin.technologies.index'),
        route('admin.users.index'),
        route('admin.roles.index'),
        route('admin.contact-messages.index'),
    ];

    foreach ($routes as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
});

test('authenticated users without admin or author role receive 403 on all admin routes', function () {
    $routes = [
        route('admin.dashboard'),
        route('admin.posts.index'),
        route('admin.projects.index'),
        route('admin.learning-paths.index'),
        route('admin.categories.index'),
        route('admin.tags.index'),
        route('admin.technologies.index'),
        route('admin.users.index'),
        route('admin.roles.index'),
        route('admin.contact-messages.index'),
    ];

    foreach ($routes as $url) {
        $this->actingAs($this->user)->get($url)->assertForbidden();
    }
});

test('authors cannot access admin-only sections', function () {
    $adminOnlyRoutes = [
        route('admin.dashboard'),
        route('admin.users.index'),
        route('admin.roles.index'),
        route('admin.contact-messages.index'),
    ];

    foreach ($adminOnlyRoutes as $url) {
        $this->actingAs($this->author1)->get($url)->assertForbidden();
    }
});

test('author cannot edit, update, or delete another authors post', function () {
    $category = Category::factory()->create();
    $postByAuthor2 = Post::factory()->create([
        'author_id' => $this->author2->id,
        'category_id' => $category->id,
    ]);

    // Author 1 cannot view edit screen
    $this->actingAs($this->author1)
        ->get(route('admin.posts.edit', $postByAuthor2))
        ->assertForbidden();

    // Author 1 cannot update
    $this->actingAs($this->author1)
        ->put(route('admin.posts.update', $postByAuthor2), [
            'title' => 'Malicious Hijack',
            'category_id' => $category->id,
            'content' => 'Updated content',
            'status' => 'draft',
        ])
        ->assertForbidden();

    // Author 1 cannot delete
    $this->actingAs($this->author1)
        ->delete(route('admin.posts.destroy', $postByAuthor2))
        ->assertForbidden();

    expect($postByAuthor2->fresh()->title)->not->toBe('Malicious Hijack');
});

test('author cannot edit, update, or delete another authors learning path', function () {
    $pathByAuthor2 = LearningPath::factory()->create([
        'created_by' => $this->author2->id,
    ]);

    $this->actingAs($this->author1)
        ->get(route('admin.learning-paths.edit', $pathByAuthor2))
        ->assertForbidden();

    $this->actingAs($this->author1)
        ->put(route('admin.learning-paths.update', $pathByAuthor2), [
            'title' => 'Malicious Hijack LP',
            'description' => 'Updated description',
            'status' => 'draft',
        ])
        ->assertForbidden();

    $this->actingAs($this->author1)
        ->delete(route('admin.learning-paths.destroy', $pathByAuthor2))
        ->assertForbidden();

    expect($pathByAuthor2->fresh()->title)->not->toBe('Malicious Hijack LP');
});

test('author cannot create or mutate lessons in another authors learning path', function () {
    $pathByAuthor2 = LearningPath::factory()->create([
        'created_by' => $this->author2->id,
    ]);
    $lessonByAuthor2 = Lesson::factory()->create([
        'learning_path_id' => $pathByAuthor2->id,
        'created_by' => $this->author2->id,
    ]);

    // Create lesson under author2's path
    $this->actingAs($this->author1)
        ->post(route('admin.learning-paths.lessons.store', $pathByAuthor2), [
            'title' => 'Injected Lesson',
            'slug' => 'injected-lesson',
            'content' => 'Content here',
            'order' => 1,
            'status' => 'draft',
        ])
        ->assertForbidden();

    // Update lesson
    $this->actingAs($this->author1)
        ->put(route('admin.learning-paths.lessons.update', [$pathByAuthor2, $lessonByAuthor2]), [
            'title' => 'Malicious Hijack Lesson',
            'slug' => 'malicious-hijack-lesson',
            'content' => 'Updated content',
            'order' => 1,
            'status' => 'draft',
        ])
        ->assertForbidden();

    // Delete lesson
    $this->actingAs($this->author1)
        ->delete(route('admin.learning-paths.lessons.destroy', [$pathByAuthor2, $lessonByAuthor2]))
        ->assertForbidden();

    expect($lessonByAuthor2->fresh()->title)->not->toBe('Malicious Hijack Lesson');
});

/*
|--------------------------------------------------------------------------
| B. IDOR & SCOPED ROUTE BINDING
|--------------------------------------------------------------------------
*/

test('cannot access or manipulate lesson belonging to learning path A via learning path B URL', function () {
    $pathA = LearningPath::factory()->create(['created_by' => $this->admin->id]);
    $pathB = LearningPath::factory()->create(['created_by' => $this->admin->id]);
    $lessonInPathA = Lesson::factory()->create([
        'learning_path_id' => $pathA->id,
        'created_by' => $this->admin->id,
    ]);

    // Scoped route model binding should return 404
    $this->actingAs($this->admin)
        ->get(route('admin.learning-paths.lessons.edit', [$pathB, $lessonInPathA]))
        ->assertNotFound();

    $this->actingAs($this->admin)
        ->put(route('admin.learning-paths.lessons.update', [$pathB, $lessonInPathA]), [
            'title' => 'IDOR Attempt',
            'slug' => 'idor-attempt',
            'content' => 'Content',
            'order' => 1,
            'status' => 'published',
        ])
        ->assertNotFound();

    $this->actingAs($this->admin)
        ->delete(route('admin.learning-paths.lessons.destroy', [$pathB, $lessonInPathA]))
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| C. PUBLIC CONTENT VISIBILITY (DRAFT / FUTURE / SOFT-DELETED)
|--------------------------------------------------------------------------
*/

test('guests cannot view draft posts on public route', function () {
    $draftPost = Post::factory()->create([
        'status' => 'draft',
        'published_at' => null,
    ]);

    $this->get(route('blog.show', $draftPost->slug))
        ->assertNotFound();
});

test('guests cannot view future scheduled posts on public route', function () {
    $futurePost = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->addDays(5),
    ]);

    $this->get(route('blog.show', $futurePost->slug))
        ->assertNotFound();
});

test('guests cannot view soft-deleted posts on public route', function () {
    $deletedPost = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $deletedPost->delete();

    $this->get(route('blog.show', $deletedPost->slug))
        ->assertNotFound();
});

test('guests cannot view draft projects on public route', function () {
    $draftProject = Project::factory()->create([
        'status' => 'draft',
    ]);

    $this->get(route('projects.show', $draftProject->slug))
        ->assertNotFound();
});

test('guests cannot view soft-deleted projects on public route', function () {
    $deletedProject = Project::factory()->create([
        'status' => 'published',
    ]);
    $deletedProject->delete();

    $this->get(route('projects.show', $deletedProject->slug))
        ->assertNotFound();
});

test('guests cannot view draft learning paths on public route', function () {
    $draftPath = LearningPath::factory()->create([
        'status' => 'draft',
    ]);

    $this->get(route('learn.show', $draftPath->slug))
        ->assertNotFound();
});

test('guests cannot view draft lessons on public route', function () {
    $publishedPath = LearningPath::factory()->create([
        'status' => 'published',
    ]);
    $draftLesson = Lesson::factory()->create([
        'learning_path_id' => $publishedPath->id,
        'status' => 'draft',
    ]);

    $this->get(route('learn.lesson', [$publishedPath->slug, $draftLesson->slug]))
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| D. FILE UPLOAD SECURITY & PATH TRAVERSAL
|--------------------------------------------------------------------------
*/

test('rejects non-image file uploads for post featured image', function () {
    $category = Category::factory()->create();
    $fakeScript = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Post with malicious file',
            'type' => 'article',
            'category_id' => $category->id,
            'content' => 'Content here',
            'status' => 'draft',
            'featured_image_file' => $fakeScript,
        ])
        ->assertSessionHasErrors(['featured_image_file']);
});

test('rejects oversized file uploads for project thumbnail', function () {
    $oversizedImage = UploadedFile::fake()->image('huge.jpg')->size(3000); // 3MB > 2048KB

    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), [
            'title' => 'Project with oversized image',
            'description' => 'Description',
            'status' => 'draft',
            'thumbnail_file' => $oversizedImage,
        ])
        ->assertSessionHasErrors(['thumbnail_file']);
});

test('path traversal attempts in local cleanup are ignored safely', function () {
    Storage::fake('public');
    Storage::disk('public')->put('secret.txt', 'do not delete');

    // Attempting to update project with new image while old thumbnail is malicious traversal path
    $project = Project::factory()->create([
        'thumbnail' => '/storage/projects/../../secret.txt',
    ]);

    $newImage = UploadedFile::fake()->image('valid.png');

    $this->actingAs($this->admin)
        ->put(route('admin.projects.update', $project), [
            'title' => $project->title,
            'description' => $project->description,
            'status' => 'draft',
            'thumbnail_file' => $newImage,
        ])
        ->assertRedirect(route('admin.projects.index'));

    // Verify secret.txt was not deleted by path traversal
    Storage::disk('public')->assertExists('secret.txt');
});

/*
|--------------------------------------------------------------------------
| E. ADMIN ACCOUNT SELF-PROTECTION (DEMOTION & DELETION)
|--------------------------------------------------------------------------
*/

test('admin cannot delete own account', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin))
        ->assertForbidden();

    expect(User::find($this->admin->id))->not->toBeNull();
});

test('admin cannot demote own role away from admin', function () {
    $response = $this->actingAs($this->admin)
        ->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'user',
        ]);

    $response->assertSessionHasErrors(['role']);
    expect($this->admin->fresh()->hasRole('admin'))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| F. MASS ASSIGNMENT & SENSITIVE ATTRIBUTES
|--------------------------------------------------------------------------
*/

test('author cannot spoof author_id when creating post', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->author1)
        ->post(route('admin.posts.store'), [
            'title' => 'Authored Post Test',
            'type' => 'article',
            'category_id' => $category->id,
            'content' => 'Content here',
            'status' => 'draft',
            'author_id' => $this->author2->id, // Spoofed ID
        ])
        ->assertRedirect(route('admin.posts.index'));

    $post = Post::where('title', 'Authored Post Test')->first();
    expect($post)->not->toBeNull()
        ->and($post->author_id)->toBe($this->author1->id);
});

/*
|--------------------------------------------------------------------------
| G. BRUTE FORCE / RATE LIMITING
|--------------------------------------------------------------------------
*/

test('login requests are rate limited after repeated failed attempts', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    // 6th attempt should be blocked with rate limiting validation error
    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $errors = session('errors')->get('email');
    expect(implode(' ', $errors))->toContain('Too many login attempts');
});

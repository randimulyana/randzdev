<?php

use App\Models\ContactMessage;
use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guest is redirected to login when accessing admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('regular user receives 403 when accessing admin dashboard', function () {
    $user = User::factory()->create()->assignRole('user');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('author receives 403 when accessing admin dashboard', function () {
    $author = User::factory()->create()->assignRole('author');

    $this->actingAs($author)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin can access admin dashboard', function () {
    $admin = User::factory()->create()->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewIs('admin.dashboard');
});

test('metrics display correct values on admin dashboard', function () {
    $admin = User::factory()->create()->assignRole('admin');

    Post::factory()->count(3)->create(['status' => 'published', 'published_at' => now()->subHour()]);
    Project::factory()->count(2)->create();
    $learningPath = LearningPath::factory()->create();
    Lesson::factory()->count(4)->create(['learning_path_id' => $learningPath->id]);
    ContactMessage::factory()->count(2)->create(['read_at' => null]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('metrics', function (array $metrics) {
            return $metrics['total_posts'] === 3
                && $metrics['total_projects'] === 2
                && $metrics['learning_paths'] === 1
                && $metrics['lessons'] === 4
                && $metrics['total_messages'] === 2;
        });
});

test('published and draft post counts are correct', function () {
    $admin = User::factory()->create()->assignRole('admin');

    Post::factory()->count(4)->create(['status' => 'published', 'published_at' => now()->subDay()]);
    Post::factory()->count(2)->draft()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('metrics', function (array $metrics) {
            return $metrics['total_posts'] === 6
                && $metrics['published_posts'] === 4
                && $metrics['draft_posts'] === 2;
        });
});

test('learning path and lesson counts are correct', function () {
    $admin = User::factory()->create()->assignRole('admin');

    $path1 = LearningPath::factory()->create();
    $path2 = LearningPath::factory()->create();
    Lesson::factory()->count(3)->create(['learning_path_id' => $path1->id]);
    Lesson::factory()->count(2)->create(['learning_path_id' => $path2->id]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('metrics', function (array $metrics) {
            return $metrics['learning_paths'] === 2
                && $metrics['lessons'] === 5;
        });
});

test('user count is correct on admin dashboard', function () {
    $admin = User::factory()->create()->assignRole('admin');

    User::factory()->count(5)->create();
    $totalUsers = User::count();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('metrics', function (array $metrics) use ($totalUsers) {
            return $metrics['total_users'] === $totalUsers;
        });
});

test('contact message total and unread counts are correct', function () {
    $admin = User::factory()->create()->assignRole('admin');

    ContactMessage::factory()->count(3)->create(['read_at' => null]);
    ContactMessage::factory()->count(2)->create(['read_at' => now()]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('metrics', function (array $metrics) {
            return $metrics['total_messages'] === 5
                && $metrics['unread_messages'] === 3;
        });
});

test('recent posts are displayed on admin dashboard', function () {
    $admin = User::factory()->create()->assignRole('admin');

    $posts = Post::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    foreach ($posts as $post) {
        $response->assertSee($post->title);
    }
});

test('recent projects are displayed on admin dashboard', function () {
    $admin = User::factory()->create()->assignRole('admin');

    $projects = Project::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    foreach ($projects as $project) {
        $response->assertSee($project->title);
    }
});

test('recent contact messages are displayed on admin dashboard', function () {
    $admin = User::factory()->create()->assignRole('admin');

    $messages = ContactMessage::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    foreach ($messages as $message) {
        $response->assertSee($message->subject);
    }
});

test('dashboard route redirects admin to admin dashboard', function () {
    $admin = User::factory()->create()->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('dashboard route does not redirect regular user to admin dashboard', function () {
    $user = User::factory()->create()->assignRole('user');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('dashboard');
});

test('dashboard route does not redirect author to admin dashboard', function () {
    $author = User::factory()->create()->assignRole('author');

    $this->actingAs($author)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('dashboard');
});

test('dashboard quick action visibility follows permissions', function () {
    $admin = User::factory()->create()->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee(route('admin.posts.create'))
        ->assertSee(route('admin.projects.create'))
        ->assertSee(route('admin.learning-paths.create'))
        ->assertSee(route('admin.contact-messages.index'));
});

test('navigation menu displays expected links based on user permissions', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $author = User::factory()->create()->assignRole('author');
    $user = User::factory()->create()->assignRole('user');

    // Admin sees admin dashboard and all CMS resources
    $adminResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
    $adminResponse->assertOk()
        ->assertSee(route('admin.dashboard'))
        ->assertSee(route('admin.posts.index'))
        ->assertSee(route('admin.projects.index'))
        ->assertSee(route('admin.categories.index'))
        ->assertSee(route('admin.technologies.index'))
        ->assertSee(route('admin.tags.index'))
        ->assertSee(route('admin.learning-paths.index'))
        ->assertSee(route('admin.contact-messages.index'));

    // Author sees content resources, but NOT admin dashboard or contact messages
    $authorResponse = $this->actingAs($author)->get(route('dashboard'));
    $authorResponse->assertOk()
        ->assertDontSee('Admin Dashboard')
        ->assertDontSee('href="'.route('admin.dashboard').'"', false)
        ->assertSee(route('admin.posts.index'))
        ->assertSee(route('admin.projects.index'))
        ->assertSee(route('admin.learning-paths.index'))
        ->assertSee(route('admin.categories.index'))
        ->assertSee(route('admin.technologies.index'))
        ->assertSee(route('admin.tags.index'))
        ->assertDontSee(route('admin.contact-messages.index'));

    // Regular user does not see admin CMS links
    $userResponse = $this->actingAs($user)->get(route('dashboard'));
    $userResponse->assertOk()
        ->assertDontSee('Admin Dashboard')
        ->assertDontSee('href="'.route('admin.dashboard').'"', false)
        ->assertDontSee(route('admin.posts.index'))
        ->assertDontSee(route('admin.contact-messages.index'));
});

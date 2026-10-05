<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->otherAuthor = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

test('guests are redirected to login for post routes', function () {
    $post = Post::factory()->create();

    $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
    $this->get(route('admin.posts.create'))->assertRedirect(route('login'));
    $this->post(route('admin.posts.store'), ['title' => 'Test', 'slug' => 'test'])->assertRedirect(route('login'));
    $this->get(route('admin.posts.edit', $post))->assertRedirect(route('login'));
    $this->put(route('admin.posts.update', $post), ['title' => 'Test', 'slug' => 'test'])->assertRedirect(route('login'));
    $this->delete(route('admin.posts.destroy', $post))->assertRedirect(route('login'));
});

test('unauthorized users cannot access post routes', function () {
    $post = Post::factory()->create();

    $this->actingAs($this->user)->get(route('admin.posts.index'))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.posts.create'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.posts.store'), [
        'title' => 'Forbidden Post',
        'slug' => 'forbidden-post',
        'content' => 'Some content',
        'type' => 'article',
        'status' => 'draft',
    ])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.posts.edit', $post))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.posts.update', $post), [
        'title' => 'Updated Post',
        'slug' => 'updated-post',
        'content' => 'Some content',
        'type' => 'article',
        'status' => 'draft',
    ])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.posts.destroy', $post))->assertForbidden();
});

test('authorized users can view post index with details', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $category = Category::factory()->create(['name' => 'Architecture']);
    $tag = Tag::factory()->create(['name' => 'Clean Code']);
    $post = Post::factory()->create([
        'title' => 'Laravel Best Practices',
        'slug' => 'laravel-best-practices',
        'author_id' => $this->author->id,
        'category_id' => $category->id,
        'type' => 'guide',
        'status' => 'published',
    ]);
    $post->tags()->attach($tag);

    $response = $this->actingAs($user)->get(route('admin.posts.index'));

    $response->assertOk()
        ->assertViewIs('admin.posts.index')
        ->assertSee('Laravel Best Practices')
        ->assertSee('laravel-best-practices')
        ->assertSee($this->author->name)
        ->assertSee('Architecture')
        ->assertSee('Clean Code')
        ->assertSee('Guide')
        ->assertSee('Published');
})->with(['admin', 'author']);

test('admin can view post create page with eligible authors selector', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.posts.create'));

    $response->assertOk()
        ->assertViewIs('admin.posts.create')
        ->assertSee('Create Post')
        ->assertSee($this->author->name)
        ->assertSee($this->admin->name)
        ->assertDontSee($this->user->name);
});

test('author can view post create page without other author selector options', function () {
    $response = $this->actingAs($this->author)->get(route('admin.posts.create'));

    $response->assertOk()
        ->assertViewIs('admin.posts.create')
        ->assertSee('Create Post')
        ->assertDontSee('Author')
        ->assertDontSee($this->otherAuthor->name);
});

test('admin can store a new post specifying eligible author', function () {
    $category = Category::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
        'author_id' => $this->author->id,
        'category_id' => $category->id,
        'title' => 'Mastering Docker',
        'slug' => 'mastering-docker',
        'excerpt' => 'A guide to Docker for developers.',
        'content' => 'Comprehensive content about containerizing PHP apps.',
        'type' => 'tutorial',
        'status' => 'published',
        'tags' => $tags->pluck('id')->all(),
    ]);

    $response->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('success', 'Post created successfully.');

    $this->assertDatabaseHas('posts', [
        'title' => 'Mastering Docker',
        'slug' => 'mastering-docker',
        'author_id' => $this->author->id,
        'category_id' => $category->id,
        'type' => 'tutorial',
        'status' => 'published',
    ]);

    $post = Post::where('slug', 'mastering-docker')->first();
    expect($post)->not->toBeNull()
        ->and($post->published_at)->not->toBeNull()
        ->and($post->tags)->toHaveCount(2);
});

test('admin cannot assign post to regular user without author role', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
        'author_id' => $this->user->id,
        'title' => 'Invalid Post Author',
        'slug' => 'invalid-post-author',
        'content' => 'Some content',
        'type' => 'article',
        'status' => 'draft',
    ]);

    $response->assertSessionHasErrors(['author_id']);
});

test('author creates post and is automatically assigned as author', function () {
    $response = $this->actingAs($this->author)->post(route('admin.posts.store'), [
        'author_id' => $this->otherAuthor->id, // attempt to assign to someone else
        'title' => 'Author Post Title',
        'slug' => 'author-post-title',
        'content' => 'Content written by the author.',
        'type' => 'article',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('success', 'Post created successfully.');

    $post = Post::where('slug', 'author-post-title')->first();
    expect($post->author_id)->toBe($this->author->id);
});

test('draft post defaults published_at to null when not provided', function () {
    $this->actingAs($this->author)->post(route('admin.posts.store'), [
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'content' => 'Draft content here.',
        'type' => 'note',
        'status' => 'draft',
    ]);

    $post = Post::where('slug', 'draft-post')->first();
    expect($post->status)->toBe('draft')
        ->and($post->published_at)->toBeNull();
});

test('published post sets published_at to now when not provided', function () {
    $this->actingAs($this->author)->post(route('admin.posts.store'), [
        'title' => 'Published Post',
        'slug' => 'published-post',
        'content' => 'Published content here.',
        'type' => 'article',
        'status' => 'published',
    ]);

    $post = Post::where('slug', 'published-post')->first();
    expect($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull();
});

test('reading time is calculated automatically when left empty', function () {
    $words = implode(' ', array_fill(0, 450, 'word'));

    $this->actingAs($this->author)->post(route('admin.posts.store'), [
        'title' => 'Reading Time Post',
        'slug' => 'reading-time-post',
        'content' => $words,
        'type' => 'article',
        'status' => 'draft',
    ]);

    $post = Post::where('slug', 'reading-time-post')->first();
    expect($post->reading_time)->toBe(3); // 450 / 200 = 2.25 => ceil = 3
});

test('author can view edit page of their own post', function () {
    $post = Post::factory()->create(['author_id' => $this->author->id]);

    $this->actingAs($this->author)
        ->get(route('admin.posts.edit', $post))
        ->assertOk()
        ->assertViewIs('admin.posts.edit')
        ->assertSee($post->title);
});

test('author cannot view edit page of another author post', function () {
    $post = Post::factory()->create(['author_id' => $this->otherAuthor->id]);

    $this->actingAs($this->author)
        ->get(route('admin.posts.edit', $post))
        ->assertForbidden();
});

test('author can update their own post', function () {
    $post = Post::factory()->create([
        'author_id' => $this->author->id,
        'title' => 'Original Post',
        'slug' => 'original-post',
        'content' => 'Original content',
    ]);

    $response = $this->actingAs($this->author)->put(route('admin.posts.update', $post), [
        'title' => 'Updated Post',
        'slug' => 'updated-post',
        'content' => 'Updated content',
        'type' => 'tutorial',
        'status' => 'published',
    ]);

    $response->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('success', 'Post updated successfully.');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Updated Post',
        'slug' => 'updated-post',
        'author_id' => $this->author->id,
    ]);
});

test('author updating own post cannot change author_id', function () {
    $post = Post::factory()->create([
        'author_id' => $this->author->id,
        'slug' => 'my-post',
    ]);

    $this->actingAs($this->author)->put(route('admin.posts.update', $post), [
        'author_id' => $this->otherAuthor->id,
        'title' => 'My Post Renamed',
        'slug' => 'my-post',
        'content' => 'Content',
        'type' => 'article',
        'status' => 'draft',
    ]);

    expect($post->fresh()->author_id)->toBe($this->author->id);
});

test('author cannot update another author post', function () {
    $post = Post::factory()->create(['author_id' => $this->otherAuthor->id]);

    $this->actingAs($this->author)->put(route('admin.posts.update', $post), [
        'title' => 'Hijacked Post',
        'slug' => 'hijacked-post',
        'content' => 'Hijacked content',
        'type' => 'article',
        'status' => 'draft',
    ])->assertForbidden();
});

test('author can delete their own post', function () {
    $post = Post::factory()->create(['author_id' => $this->author->id]);

    $response = $this->actingAs($this->author)->delete(route('admin.posts.destroy', $post));

    $response->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('success', 'Post deleted successfully.');

    $this->assertSoftDeleted('posts', ['id' => $post->id]);
});

test('author cannot delete another author post', function () {
    $post = Post::factory()->create(['author_id' => $this->otherAuthor->id]);

    $this->actingAs($this->author)->delete(route('admin.posts.destroy', $post))->assertForbidden();

    $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
});

test('admin can update any author post and reassign author', function () {
    $post = Post::factory()->create(['author_id' => $this->author->id]);

    $response = $this->actingAs($this->admin)->put(route('admin.posts.update', $post), [
        'author_id' => $this->otherAuthor->id,
        'title' => 'Reassigned Title',
        'slug' => $post->slug,
        'content' => 'Content',
        'type' => 'guide',
        'status' => 'published',
    ]);

    $response->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('success', 'Post updated successfully.');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'author_id' => $this->otherAuthor->id,
        'title' => 'Reassigned Title',
    ]);
});

test('admin can delete any author post', function () {
    $post = Post::factory()->create(['author_id' => $this->author->id]);

    $response = $this->actingAs($this->admin)->delete(route('admin.posts.destroy', $post));

    $response->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('success', 'Post deleted successfully.');

    $this->assertSoftDeleted('posts', ['id' => $post->id]);
});

test('post requires title, slug, content, type, status to be stored', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
        'title' => '',
        'slug' => '',
        'content' => '',
        'type' => 'invalid_type',
        'status' => 'invalid_status',
    ]);

    $response->assertSessionHasErrors(['title', 'slug', 'content', 'type', 'status']);
});

test('post slug must be unique on creation', function () {
    Post::factory()->create(['slug' => 'unique-slug']);

    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
        'title' => 'Duplicate Slug',
        'slug' => 'unique-slug',
        'content' => 'Content',
        'type' => 'article',
        'status' => 'draft',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('post update allows keeping the same slug', function () {
    $post = Post::factory()->create(['slug' => 'same-slug']);

    $response = $this->actingAs($this->admin)->put(route('admin.posts.update', $post), [
        'title' => 'New Title Same Slug',
        'slug' => 'same-slug',
        'content' => 'Content',
        'type' => 'article',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.posts.index'))
        ->assertSessionHasNoErrors();
});

test('post update rejects duplicate slug from another post', function () {
    Post::factory()->create(['slug' => 'existing-other-slug']);
    $post = Post::factory()->create(['slug' => 'my-post-slug']);

    $response = $this->actingAs($this->admin)->put(route('admin.posts.update', $post), [
        'title' => 'Collision Title',
        'slug' => 'existing-other-slug',
        'content' => 'Content',
        'type' => 'article',
        'status' => 'draft',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('post validates category and tags exist', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
        'title' => 'Non Existent Relations',
        'slug' => 'non-existent-relations',
        'content' => 'Content',
        'type' => 'article',
        'status' => 'draft',
        'category_id' => 999999,
        'tags' => [888888],
    ]);

    $response->assertSessionHasErrors(['category_id', 'tags.0']);
});

// ==========================================
// Search & Filter Tests
// ==========================================

test('admin can search posts by title keyword', function () {
    $match = Post::factory()->create([
        'title' => 'Mastering Laravel Architecture',
        'author_id' => $this->author->id,
    ]);
    $other = Post::factory()->create([
        'title' => 'Getting Started with Docker Containers',
        'author_id' => $this->author->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['search' => 'Laravel Architecture']));

    $response->assertOk()
        ->assertSee('Mastering Laravel Architecture')
        ->assertDontSee('Getting Started with Docker Containers');
});

test('whitespace-only search is treated as empty search', function () {
    $postA = Post::factory()->create(['title' => 'First Post Alpha', 'author_id' => $this->author->id]);
    $postB = Post::factory()->create(['title' => 'Second Post Beta', 'author_id' => $this->author->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['search' => '   ']));

    $response->assertOk()
        ->assertSee('First Post Alpha')
        ->assertSee('Second Post Beta');
});

test('admin can filter posts by status', function (string $targetStatus) {
    $draft = Post::factory()->create(['title' => 'Draft Item', 'status' => 'draft', 'author_id' => $this->author->id]);
    $published = Post::factory()->create(['title' => 'Published Item', 'status' => 'published', 'author_id' => $this->author->id]);
    $archived = Post::factory()->create(['title' => 'Archived Item', 'status' => 'archived', 'author_id' => $this->author->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['status' => $targetStatus]));

    $response->assertOk();

    if ($targetStatus === 'draft') {
        $response->assertSee('Draft Item')
            ->assertDontSee('Published Item')
            ->assertDontSee('Archived Item');
    } elseif ($targetStatus === 'published') {
        $response->assertSee('Published Item')
            ->assertDontSee('Draft Item')
            ->assertDontSee('Archived Item');
    } elseif ($targetStatus === 'archived') {
        $response->assertSee('Archived Item')
            ->assertDontSee('Draft Item')
            ->assertDontSee('Published Item');
    }
})->with(['draft', 'published', 'archived']);

test('admin can filter posts by category', function () {
    $catDev = Category::factory()->create(['name' => 'Development']);
    $catDesign = Category::factory()->create(['name' => 'Design']);

    $postDev = Post::factory()->create(['title' => 'Dev Specific Post', 'category_id' => $catDev->id, 'author_id' => $this->author->id]);
    $postDesign = Post::factory()->create(['title' => 'Design Specific Post', 'category_id' => $catDesign->id, 'author_id' => $this->author->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['category_id' => $catDev->id]));

    $response->assertOk()
        ->assertSee('Dev Specific Post')
        ->assertDontSee('Design Specific Post');
});

test('admin can filter posts by author', function () {
    $postAuthor1 = Post::factory()->create(['title' => 'Post by Author One', 'author_id' => $this->author->id]);
    $postAuthor2 = Post::factory()->create(['title' => 'Post by Author Two', 'author_id' => $this->otherAuthor->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['author_id' => $this->author->id]));

    $response->assertOk()
        ->assertSee('Post by Author One')
        ->assertDontSee('Post by Author Two');
});

test('admin can combine multiple filters with conjunctive AND condition', function () {
    $cat = Category::factory()->create();

    $exactMatch = Post::factory()->create([
        'title' => 'VueJS Advanced Patterns',
        'status' => 'published',
        'category_id' => $cat->id,
        'author_id' => $this->author->id,
    ]);

    $wrongStatus = Post::factory()->create([
        'title' => 'VueJS Beginners Tutorial',
        'status' => 'draft',
        'category_id' => $cat->id,
        'author_id' => $this->author->id,
    ]);

    $wrongAuthor = Post::factory()->create([
        'title' => 'VueJS Components Architecture',
        'status' => 'published',
        'category_id' => $cat->id,
        'author_id' => $this->otherAuthor->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', [
        'search' => 'VueJS',
        'status' => 'published',
        'category_id' => $cat->id,
        'author_id' => $this->author->id,
    ]));

    $response->assertOk()
        ->assertSee('VueJS Advanced Patterns')
        ->assertDontSee('VueJS Beginners Tutorial')
        ->assertDontSee('VueJS Components Architecture');
});

test('pagination links preserve active search and filter query parameters', function () {
    Post::factory()->count(25)->create([
        'title' => 'Searchable Paginated Article',
        'status' => 'published',
        'author_id' => $this->author->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', [
        'search' => 'Searchable',
        'status' => 'published',
    ]));

    $response->assertOk()
        ->assertSee('page=2')
        ->assertSee('search=Searchable')
        ->assertSee('status=published');
});

test('unmatched search and filter displays filter-specific empty state with reset link', function () {
    Post::factory()->create(['title' => 'Existing Normal Post']);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', [
        'search' => 'NonExistentString12345',
    ]));

    $response->assertOk()
        ->assertSee('No posts found matching your search or filters.')
        ->assertSee(route('admin.posts.index'));
});

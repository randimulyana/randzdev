<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Post::class);

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $categoryId = $request->input('category_id');
        $authorId = $request->input('author_id');

        $posts = Post::query()
            ->with(['author', 'category', 'tags'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when(in_array($status, ['draft', 'published', 'archived'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(! empty($categoryId), function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when(! empty($authorId), function ($query) use ($authorId) {
                $query->where('author_id', $authorId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();
        $authors = User::role(['admin', 'author'])->orderBy('name')->get(['id', 'name']);

        return view('admin.posts.index', compact('posts', 'categories', 'authors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Post::class);

        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $authors = auth()->user()?->hasRole('admin')
            ? User::role(['admin', 'author'])->orderBy('name')->get()
            : collect([auth()->user()]);

        return view('admin.posts.create', compact('categories', 'tags', 'authors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['tags', 'featured_image_file']);

        if (! $request->user()->hasRole('admin') || empty($data['author_id'])) {
            $data['author_id'] = $request->user()->id;
        }

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        if ($request->hasFile('featured_image_file')) {
            $data['featured_image'] = $this->handleFeaturedImage($request);
        }

        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? now();
        } elseif ($data['status'] === 'draft' && ! isset($data['published_at'])) {
            $data['published_at'] = null;
        }

        if (empty($data['reading_time'])) {
            $data['reading_time'] = max(1, (int) ceil(str_word_count(strip_tags($data['content'])) / 200));
        }

        $post = Post::create($data);
        $post->tags()->sync($request->validated('tags', []));

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        $post->load('tags');
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $authors = auth()->user()?->hasRole('admin')
            ? User::role(['admin', 'author'])->orderBy('name')->get()
            : collect([$post->author]);

        return view('admin.posts.edit', compact('post', 'categories', 'tags', 'authors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->safe()->except(['tags', 'featured_image_file']);

        if (! $request->user()->hasRole('admin')) {
            unset($data['author_id']);
        }

        if (empty($data['slug'])) {
            $data['slug'] = $post->slug;
        }

        if ($request->hasFile('featured_image_file')) {
            $data['featured_image'] = $this->handleFeaturedImage($request, $post);
        }

        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? $post->published_at ?? now();
        } elseif ($data['status'] === 'draft' && ! isset($data['published_at'])) {
            $data['published_at'] = null;
        }

        if (empty($data['reading_time'])) {
            $data['reading_time'] = max(1, (int) ceil(str_word_count(strip_tags($data['content'])) / 200));
        }

        $post->update($data);
        $post->tags()->sync($request->validated('tags', []));

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Publish the specified post.
     */
    public function publish(Post $post): RedirectResponse
    {
        Gate::authorize('publish', $post);

        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        return redirect()
            ->back(fallback: route('admin.posts.index'))
            ->with('success', 'Post published successfully.');
    }

    /**
     * Unpublish the specified post.
     */
    public function unpublish(Post $post): RedirectResponse
    {
        Gate::authorize('unpublish', $post);

        $post->update([
            'status' => 'draft',
        ]);

        return redirect()
            ->back(fallback: route('admin.posts.index'))
            ->with('success', 'Post unpublished successfully.');
    }

    /**
     * Generate a unique slug from title.
     */
    protected function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        if ($baseSlug === '') {
            $baseSlug = 'post';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Post::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Handle featured image upload and cleanup of old local image.
     */
    protected function handleFeaturedImage(Request $request, ?Post $post = null): ?string
    {
        if ($request->hasFile('featured_image_file')) {
            $file = $request->file('featured_image_file');
            $path = $file->store('posts', 'public');
            $newUrl = Storage::url($path);

            if ($post && ! empty($post->featured_image)) {
                $this->deleteStoredImageIfLocal($post->featured_image);
            }

            return $newUrl;
        }

        if ($request->has('featured_image')) {
            return $request->input('featured_image');
        }

        return $post?->featured_image;
    }

    /**
     * Delete stored image file if it is in the public local disk.
     */
    protected function deleteStoredImageIfLocal(string $url): void
    {
        $storagePrefix = '/storage/';
        if (str_starts_with($url, $storagePrefix)) {
            $relativePath = substr($url, strlen($storagePrefix));
            if (str_starts_with($relativePath, 'posts/') && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }
}

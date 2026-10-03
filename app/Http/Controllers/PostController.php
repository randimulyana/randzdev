<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Post::class);

        $posts = Post::query()
            ->with(['author', 'category', 'tags'])
            ->latest()
            ->paginate(15);

        return view('admin.posts.index', compact('posts'));
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
        $data = $request->safe()->except('tags');

        if (! $request->user()->hasRole('admin') || empty($data['author_id'])) {
            $data['author_id'] = $request->user()->id;
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
        $data = $request->safe()->except('tags');

        if (! $request->user()->hasRole('admin')) {
            unset($data['author_id']);
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
}

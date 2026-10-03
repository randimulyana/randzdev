<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Tag::class);

        $tags = Tag::query()
            ->withCount('posts')
            ->latest()
            ->paginate(15);

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Tag::class);

        return view('admin.tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag): View
    {
        Gate::authorize('update', $tag);

        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}

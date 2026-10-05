<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLearningPathRequest;
use App\Http\Requests\UpdateLearningPathRequest;
use App\Models\LearningPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LearningPathController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', LearningPath::class);

        $learningPaths = LearningPath::query()
            ->with('creator')
            ->withCount('lessons')
            ->latest()
            ->paginate(15);

        return view('admin.learning-paths.index', compact('learningPaths'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', LearningPath::class);

        return view('admin.learning-paths.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLearningPathRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('thumbnail_file');
        $data['created_by'] = $request->user()->id;

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $data['thumbnail'] = $this->handleThumbnail($request);

        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? now();
        } elseif ($data['status'] === 'draft' && ! isset($data['published_at'])) {
            $data['published_at'] = null;
        }

        LearningPath::create($data);

        return redirect()
            ->route('admin.learning-paths.index')
            ->with('success', 'Learning path created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LearningPath $learningPath): View
    {
        Gate::authorize('update', $learningPath);

        $learningPath->load('lessons');

        return view('admin.learning-paths.edit', compact('learningPath'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLearningPathRequest $request, LearningPath $learningPath): RedirectResponse
    {
        $data = $request->safe()->except('thumbnail_file');
        $data['updated_by'] = $request->user()->id;

        if (empty($data['slug'])) {
            $data['slug'] = $learningPath->slug;
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $data['thumbnail'] = $this->handleThumbnail($request, $learningPath);

        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? $learningPath->published_at ?? now();
        } elseif ($data['status'] === 'draft' && ! isset($data['published_at'])) {
            $data['published_at'] = null;
        }

        $learningPath->update($data);

        return redirect()
            ->route('admin.learning-paths.index')
            ->with('success', 'Learning path updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningPath $learningPath): RedirectResponse
    {
        Gate::authorize('delete', $learningPath);

        $learningPath->delete();

        return redirect()
            ->route('admin.learning-paths.index')
            ->with('success', 'Learning path deleted successfully.');
    }

    /**
     * Publish the specified learning path.
     */
    public function publish(LearningPath $learningPath): RedirectResponse
    {
        Gate::authorize('publish', $learningPath);

        $learningPath->update([
            'status' => 'published',
            'published_at' => $learningPath->published_at ?? now(),
        ]);

        return redirect()
            ->back(fallback: route('admin.learning-paths.index'))
            ->with('success', 'Learning path published successfully.');
    }

    /**
     * Unpublish the specified learning path.
     */
    public function unpublish(LearningPath $learningPath): RedirectResponse
    {
        Gate::authorize('unpublish', $learningPath);

        $learningPath->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return redirect()
            ->back(fallback: route('admin.learning-paths.index'))
            ->with('success', 'Learning path unpublished successfully.');
    }

    /**
     * Generate a unique slug from title.
     */
    protected function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        if ($baseSlug === '') {
            $baseSlug = 'learning-path';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (LearningPath::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Handle thumbnail file upload and cleanup of old local image.
     */
    protected function handleThumbnail(Request $request, ?LearningPath $learningPath = null): ?string
    {
        if ($request->hasFile('thumbnail_file')) {
            $file = $request->file('thumbnail_file');
            $path = $file->store('learning-paths', 'public');
            $newUrl = Storage::url($path);

            if ($learningPath && ! empty($learningPath->thumbnail)) {
                $this->deleteStoredThumbnailIfLocal($learningPath->thumbnail);
            }

            return $newUrl;
        }

        if ($request->has('thumbnail')) {
            return $request->input('thumbnail');
        }

        return $learningPath?->thumbnail;
    }

    /**
     * Delete stored thumbnail file if it is in the public local disk.
     */
    protected function deleteStoredThumbnailIfLocal(string $url): void
    {
        $storagePrefix = '/storage/';
        if (str_starts_with($url, $storagePrefix)) {
            $relativePath = substr($url, strlen($storagePrefix));
            if (str_starts_with($relativePath, 'learning-paths/') && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }
}

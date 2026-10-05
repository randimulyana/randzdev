<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Project::class);

        $projects = Project::query()
            ->with(['category', 'technologies'])
            ->latest()
            ->paginate(15);

        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Project::class);

        $categories = Category::orderBy('name')->get();
        $technologies = Technology::orderBy('name')->get();

        return view('admin.projects.create', compact('categories', 'technologies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['technologies', 'thumbnail_file']);

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $data['thumbnail'] = $this->handleThumbnail($request);

        $project = Project::create($data);

        $project->technologies()->sync($request->validated('technologies', []));

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        $project->load('technologies');
        $categories = Category::orderBy('name')->get();
        $technologies = Technology::orderBy('name')->get();

        return view('admin.projects.edit', compact('project', 'categories', 'technologies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->safe()->except(['technologies', 'thumbnail_file']);

        if (empty($data['slug'])) {
            $data['slug'] = $project->slug;
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $data['thumbnail'] = $this->handleThumbnail($request, $project);

        $project->update($data);

        $project->technologies()->sync($request->validated('technologies', []));

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Publish the specified project.
     */
    public function publish(Project $project): RedirectResponse
    {
        Gate::authorize('publish', $project);

        $project->update([
            'status' => $project->status === 'draft' ? 'completed' : $project->status,
            'published_at' => $project->published_at ?? now(),
        ]);

        return redirect()
            ->back(fallback: route('admin.projects.index'))
            ->with('success', 'Project published successfully.');
    }

    /**
     * Unpublish the specified project.
     */
    public function unpublish(Project $project): RedirectResponse
    {
        Gate::authorize('unpublish', $project);

        $project->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return redirect()
            ->back(fallback: route('admin.projects.index'))
            ->with('success', 'Project unpublished successfully.');
    }

    /**
     * Generate a unique slug from title.
     */
    protected function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Project::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Handle thumbnail file upload and cleanup of old local image.
     */
    protected function handleThumbnail(Request $request, ?Project $project = null): ?string
    {
        if ($request->hasFile('thumbnail_file')) {
            $file = $request->file('thumbnail_file');
            $path = $file->store('projects', 'public');
            $newUrl = Storage::url($path);

            if ($project && ! empty($project->thumbnail)) {
                $this->deleteStoredThumbnailIfLocal($project->thumbnail);
            }

            return $newUrl;
        }

        if ($request->has('thumbnail')) {
            return $request->input('thumbnail');
        }

        return $project?->thumbnail;
    }

    /**
     * Delete stored thumbnail file if it is in the public local disk.
     */
    protected function deleteStoredThumbnailIfLocal(string $url): void
    {
        $storagePrefix = '/storage/';
        if (str_starts_with($url, $storagePrefix)) {
            $relativePath = substr($url, strlen($storagePrefix));
            if (str_starts_with($relativePath, 'projects/') && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }
}

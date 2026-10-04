<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLearningPathRequest;
use App\Http\Requests\UpdateLearningPathRequest;
use App\Models\LearningPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
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
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

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
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

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
}

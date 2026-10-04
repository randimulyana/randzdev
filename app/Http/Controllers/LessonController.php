<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\LearningPath;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * Display a listing of the lessons for the given learning path.
     */
    public function index(LearningPath $learningPath): RedirectResponse
    {
        return redirect()->route('admin.learning-paths.edit', $learningPath);
    }

    /**
     * Show the form for creating a new lesson.
     */
    public function create(LearningPath $learningPath): View
    {
        Gate::authorize('create', [Lesson::class, $learningPath]);

        $defaultOrder = (int) ($learningPath->lessons()->max('order') ?? 0) + 1;

        return view('admin.lessons.create', compact('learningPath', $defaultOrder));
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(StoreLessonRequest $request, LearningPath $learningPath): RedirectResponse
    {
        $data = $request->validated();
        $data['learning_path_id'] = $learningPath->id;
        $data['created_by'] = $request->user()->id;

        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? now();
        } elseif ($data['status'] === 'draft' && ! isset($data['published_at'])) {
            $data['published_at'] = null;
        }

        Lesson::create($data);

        return redirect()
            ->route('admin.learning-paths.edit', $learningPath)
            ->with('success', 'Lesson created successfully.');
    }

    /**
     * Show the form for editing the specified lesson.
     */
    public function edit(LearningPath $learningPath, Lesson $lesson): View
    {
        Gate::authorize('update', $lesson);

        return view('admin.lessons.edit', compact('learningPath', $lesson));
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(UpdateLessonRequest $request, LearningPath $learningPath, Lesson $lesson): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? $lesson->published_at ?? now();
        } elseif ($data['status'] === 'draft' && ! isset($data['published_at'])) {
            $data['published_at'] = null;
        }

        $lesson->update($data);

        return redirect()
            ->route('admin.learning-paths.edit', $learningPath)
            ->with('success', 'Lesson updated successfully.');
    }

    /**
     * Remove the specified lesson from storage.
     */
    public function destroy(LearningPath $learningPath, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('delete', $lesson);

        $lesson->delete();

        return redirect()
            ->route('admin.learning-paths.edit', $learningPath)
            ->with('success', 'Lesson deleted successfully.');
    }
}

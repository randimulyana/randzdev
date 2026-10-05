<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\LearningPath;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * Display a listing of the lessons for the given learning path.
     */
    public function index(Request $request, LearningPath $learningPath): View
    {
        Gate::authorize('viewAny', Lesson::class);

        if (! $request->user()->hasRole('admin') && $learningPath->created_by !== $request->user()->id) {
            abort(403);
        }

        $lessons = $learningPath->lessons()
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.lessons.index', compact('learningPath', 'lessons'));
    }

    /**
     * Show the form for creating a new lesson.
     */
    public function create(LearningPath $learningPath): View
    {
        Gate::authorize('create', [Lesson::class, $learningPath]);

        $defaultOrder = (int) ($learningPath->lessons()->max('order') ?? 0) + 1;

        return view('admin.lessons.create', compact('learningPath', 'defaultOrder'));
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(StoreLessonRequest $request, LearningPath $learningPath): RedirectResponse
    {
        $data = $request->validated();
        $data['learning_path_id'] = $learningPath->id;
        $data['created_by'] = $request->user()->id;

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $learningPath->id);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

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

        return view('admin.lessons.edit', compact('learningPath', 'lesson'));
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(UpdateLessonRequest $request, LearningPath $learningPath, Lesson $lesson): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        if (empty($data['slug'])) {
            $data['slug'] = $lesson->slug;
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

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

    /**
     * Publish the specified lesson.
     */
    public function publish(LearningPath $learningPath, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('publish', $lesson);

        $lesson->update([
            'status' => 'published',
            'published_at' => $lesson->published_at ?? now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Lesson published successfully.');
    }

    /**
     * Unpublish the specified lesson.
     */
    public function unpublish(LearningPath $learningPath, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('unpublish', $lesson);

        $lesson->update([
            'status' => 'draft',
            'published_at' => null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Lesson unpublished successfully.');
    }

    /**
     * Move the specified lesson up in the sequence order.
     */
    public function moveUp(LearningPath $learningPath, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('update', $lesson);

        $this->swapOrder($learningPath, $lesson, -1);

        return back()->with('success', 'Lesson moved up successfully.');
    }

    /**
     * Move the specified lesson down in the sequence order.
     */
    public function moveDown(LearningPath $learningPath, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('update', $lesson);

        $this->swapOrder($learningPath, $lesson, 1);

        return back()->with('success', 'Lesson moved down successfully.');
    }

    /**
     * Generate a unique slug within the given learning path.
     */
    protected function generateUniqueSlug(string $title, int $learningPathId, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Lesson::where('learning_path_id', $learningPathId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Swap the order of target lesson with adjacent lesson in sequence.
     */
    protected function swapOrder(LearningPath $learningPath, Lesson $targetLesson, int $direction): void
    {
        $lessons = $learningPath->lessons()->orderBy('order', 'asc')->orderBy('id', 'asc')->get();

        $currentIndex = $lessons->search(fn (Lesson $item) => $item->id === $targetLesson->id);
        if ($currentIndex === false) {
            return;
        }

        $swapIndex = $currentIndex + $direction;
        if ($swapIndex < 0 || $swapIndex >= $lessons->count()) {
            return;
        }

        $adjacentLesson = $lessons[$swapIndex];

        $targetOrder = $targetLesson->order;
        $adjacentOrder = $adjacentLesson->order;

        if ($targetOrder === $adjacentOrder) {
            if ($direction < 0) {
                $targetLesson->updateQuietly(['order' => max(1, $adjacentOrder - 1)]);
            } else {
                $targetLesson->updateQuietly(['order' => $adjacentOrder + 1]);
            }
        } else {
            $targetLesson->updateQuietly(['order' => $adjacentOrder]);
            $adjacentLesson->updateQuietly(['order' => $targetOrder]);
        }
    }
}

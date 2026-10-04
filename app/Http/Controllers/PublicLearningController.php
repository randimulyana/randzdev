<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicLearningController extends Controller
{
    /**
     * Display a listing of published learning paths.
     */
    public function index(Request $request): View
    {
        $learningPaths = LearningPath::query()
            ->published()
            ->withCount([
                'lessons' => fn ($query) => $query->published(),
            ])
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('learn.index', compact('learningPaths'));
    }

    /**
     * Display the specified published learning path and its published curriculum.
     */
    public function show(string $slug): View
    {
        $learningPath = LearningPath::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'lessons' => function ($query) {
                    $query->published()
                        ->orderBy('order', 'asc')
                        ->orderBy('id', 'asc');
                },
            ])
            ->firstOrFail();

        $firstLesson = $learningPath->lessons->first();

        return view('learn.show', compact('learningPath', 'firstLesson'));
    }

    /**
     * Display the specified published lesson within the published learning path.
     */
    public function lesson(string $learningPathSlug, string $lessonSlug): View
    {
        $learningPath = LearningPath::query()
            ->published()
            ->where('slug', $learningPathSlug)
            ->firstOrFail();

        $lesson = $learningPath->lessons()
            ->published()
            ->where('slug', $lessonSlug)
            ->firstOrFail();

        $orderedLessonIds = $learningPath->lessons()
            ->published()
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->all();

        $currentIndex = array_search($lesson->id, $orderedLessonIds);
        $currentLessonIndex = $currentIndex !== false ? $currentIndex + 1 : 1;
        $totalPublishedLessons = count($orderedLessonIds);

        $previousLessonId = ($currentIndex !== false && $currentIndex > 0) ? $orderedLessonIds[$currentIndex - 1] : null;
        $nextLessonId = ($currentIndex !== false && $currentIndex < $totalPublishedLessons - 1) ? $orderedLessonIds[$currentIndex + 1] : null;

        $previousLesson = $previousLessonId ? $learningPath->lessons()->find($previousLessonId) : null;
        $nextLesson = $nextLessonId ? $learningPath->lessons()->find($nextLessonId) : null;

        return view('learn.lesson', compact(
            'learningPath',
            'lesson',
            'previousLesson',
            'nextLesson',
            'currentLessonIndex',
            'totalPublishedLessons'
        ));
    }
}

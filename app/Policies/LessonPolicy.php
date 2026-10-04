<?php

namespace App\Policies;

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Determine whether the user can view any lessons in admin CMS.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']) && $user->can('lessons.view');
    }

    /**
     * Determine whether the user can view the lesson in admin CMS.
     */
    public function view(User $user, Lesson $lesson): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('author')
            && $user->can('lessons.view')
            && $lesson->learningPath->created_by === $user->id;
    }

    /**
     * Determine whether the user can create lessons within the learning path.
     */
    public function create(User $user, ?LearningPath $learningPath = null): bool
    {
        if (! $user->can('lessons.create')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $learningPath !== null && $learningPath->created_by === $user->id;
    }

    /**
     * Determine whether the user can update the lesson.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('lessons.update')
            && $lesson->learningPath->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the lesson.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('lessons.delete')
            && $lesson->learningPath->created_by === $user->id;
    }
}

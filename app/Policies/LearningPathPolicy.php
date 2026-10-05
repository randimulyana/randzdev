<?php

namespace App\Policies;

use App\Models\LearningPath;
use App\Models\User;

class LearningPathPolicy
{
    /**
     * Determine whether the user can view any learning paths in admin CMS.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']) && $user->can('learning_paths.view');
    }

    /**
     * Determine whether the user can view the learning path in admin CMS.
     */
    public function view(User $user, LearningPath $learningPath): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('author') && $user->can('learning_paths.view') && $learningPath->created_by === $user->id);
    }

    /**
     * Determine whether the user can create learning paths.
     */
    public function create(User $user): bool
    {
        return $user->can('learning_paths.create');
    }

    /**
     * Determine whether the user can update the learning path.
     */
    public function update(User $user, LearningPath $learningPath): bool
    {
        return $user->hasRole('admin') || ($user->can('learning_paths.update') && $learningPath->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the learning path.
     */
    public function delete(User $user, LearningPath $learningPath): bool
    {
        return $user->hasRole('admin') || ($user->can('learning_paths.delete') && $learningPath->created_by === $user->id);
    }

    /**
     * Determine whether the user can publish the learning path.
     */
    public function publish(User $user, LearningPath $learningPath): bool
    {
        return $this->update($user, $learningPath);
    }

    /**
     * Determine whether the user can unpublish the learning path.
     */
    public function unpublish(User $user, LearningPath $learningPath): bool
    {
        return $this->update($user, $learningPath);
    }
}

<?php

namespace App\Policies;

use App\Models\Technology;
use App\Models\User;

class TechnologyPolicy
{
    /**
     * Determine whether the user can view any technologies.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('technologies.view');
    }

    /**
     * Determine whether the user can view the technology.
     */
    public function view(User $user, Technology $technology): bool
    {
        return $user->can('technologies.view');
    }

    /**
     * Determine whether the user can create technologies.
     */
    public function create(User $user): bool
    {
        return $user->can('technologies.create');
    }

    /**
     * Determine whether the user can update the technology.
     */
    public function update(User $user, Technology $technology): bool
    {
        return $user->can('technologies.update');
    }

    /**
     * Determine whether the user can delete the technology.
     */
    public function delete(User $user, Technology $technology): bool
    {
        return $user->can('technologies.delete');
    }
}

<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any posts.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']) && $user->can('posts.view');
    }

    /**
     * Determine whether the user can view the post.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('author') && $user->can('posts.view') && $post->author_id === $user->id);
    }

    /**
     * Determine whether the user can create posts.
     */
    public function create(User $user): bool
    {
        return $user->can('posts.create');
    }

    /**
     * Determine whether the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->hasRole('admin') || ($user->can('posts.update') && $post->author_id === $user->id);
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->hasRole('admin') || ($user->can('posts.delete') && $post->author_id === $user->id);
    }

    /**
     * Determine whether the user can publish the post.
     */
    public function publish(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }

    /**
     * Determine whether the user can unpublish the post.
     */
    public function unpublish(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}

<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    /**
     * Determine whether the user can view any contact messages.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('contact_messages.view');
    }

    /**
     * Determine whether the user can view the contact message.
     */
    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->can('contact_messages.view');
    }
}

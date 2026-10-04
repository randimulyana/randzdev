<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of contact messages.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', ContactMessage::class);

        $contactMessages = ContactMessage::query()
            ->latest()
            ->paginate(15);

        return view('admin.contact-messages.index', compact('contactMessages'));
    }

    /**
     * Display the specified contact message and mark as read if unread.
     */
    public function show(ContactMessage $contactMessage): View
    {
        Gate::authorize('view', $contactMessage);

        if (is_null($contactMessage->read_at)) {
            $contactMessage->update(['read_at' => now()]);
        }

        return view('admin.contact-messages.show', compact('contactMessage'));
    }
}

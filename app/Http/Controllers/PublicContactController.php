<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicContactController extends Controller
{
    /**
     * Display the public contact page and form.
     */
    public function show(): View
    {
        $profile = config('portfolio.profile', []);
        $social = config('portfolio.social', []);

        return view('contact', compact('profile', 'social'));
    }

    /**
     * Store a new contact message from the public contact form.
     */
    public function store(ContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create($request->validated());

        return redirect()
            ->route('contact')
            ->with('success', 'Thank you for reaching out! Your message has been sent successfully.');
    }
}

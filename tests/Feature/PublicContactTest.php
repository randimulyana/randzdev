<?php

use App\Models\ContactMessage;
use App\Models\User;

test('guest can access public contact page', function () {
    $response = $this->get(route('contact'));

    $response->assertOk()
        ->assertViewIs('contact');
});

test('authenticated user can access public contact page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('contact'));

    $response->assertOk()
        ->assertViewIs('contact');
});

test('contact form fields render correctly', function () {
    $response = $this->get(route('contact'));

    $response->assertOk()
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="subject"', false)
        ->assertSee('name="message"', false)
        ->assertSee('Send Message');
});

test('contact information renders from configuration', function () {
    $response = $this->get(route('contact'));

    $response->assertOk();

    if (! empty(config('portfolio.social.email'))) {
        $response->assertSee(config('portfolio.social.email'));
    }

    if (! empty(config('portfolio.profile.location'))) {
        $response->assertSee(config('portfolio.profile.location'));
    }

    if (! empty(config('portfolio.social.github'))) {
        $response->assertSee(config('portfolio.social.github'));
    }

    if (! empty(config('portfolio.social.linkedin'))) {
        $response->assertSee(config('portfolio.social.linkedin'));
    }
});

test('seo metadata and canonical url are rendered on contact page', function () {
    $response = $this->get(route('contact'));

    $expectedTitle = 'Contact — '.config('app.name');
    $expectedDescription = 'Get in touch with '.config('portfolio.profile.name', 'Randi').' for software engineering inquiries, collaborations, or questions.';

    $response->assertOk()
        ->assertSee('<title>'.$expectedTitle.'</title>', false)
        ->assertSee('<meta name="description" content="'.$expectedDescription.'">', false)
        ->assertSee('<link rel="canonical" href="'.route('contact').'">', false);
});

test('navbar displays contact as active link on contact page', function () {
    $response = $this->get(route('contact'));

    $response->assertOk()
        ->assertSee(route('contact'))
        ->assertSee('text-indigo-600 font-semibold', false);
});

test('valid submission creates database record with null read_at and redirects with success flash', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'subject' => 'Project Inquiry',
        'message' => 'Hello, I would like to discuss a potential web engineering project.',
    ];

    $response = $this->post(route('contact.store'), $payload);

    $response->assertRedirect(route('contact'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'subject' => 'Project Inquiry',
        'message' => 'Hello, I would like to discuss a potential web engineering project.',
        'read_at' => null,
    ]);

    $message = ContactMessage::first();
    expect($message->read_at)->toBeNull();
});

test('name, email, subject, and message are required', function () {
    $response = $this->post(route('contact.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    $this->assertDatabaseCount('contact_messages', 0);
});

test('invalid email format is rejected', function () {
    $response = $this->post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'not-an-email',
        'subject' => 'Question',
        'message' => 'A valid message body.',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertDatabaseCount('contact_messages', 0);
});

test('fields exceeding maximum length limits are rejected', function () {
    $response = $this->post(route('contact.store'), [
        'name' => str_repeat('a', 256),
        'email' => str_repeat('a', 244).'@example.com', // 256 chars
        'subject' => str_repeat('c', 256),
        'message' => str_repeat('d', 5001),
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    $this->assertDatabaseCount('contact_messages', 0);
});

test('rate limiting allows up to 5 submissions per minute then throttles', function () {
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->post(route('contact.store'), [
            'name' => "Sender {$i}",
            'email' => "sender{$i}@example.com",
            'subject' => "Subject {$i}",
            'message' => "Message content number {$i}",
        ]);

        $response->assertRedirect(route('contact'));
    }

    $this->assertDatabaseCount('contact_messages', 5);

    // 6th attempt should be throttled
    $throttledResponse = $this->post(route('contact.store'), [
        'name' => 'Throttled Sender',
        'email' => 'throttled@example.com',
        'subject' => 'Throttled Subject',
        'message' => 'This submission should be throttled.',
    ]);

    $throttledResponse->assertStatus(429);
    $this->assertDatabaseCount('contact_messages', 5);
});

test('submitted content with html or script tags is safely stored and escaped when rendered', function () {
    $xssPayload = '<script>alert("xss")</script><b>Bold Text</b>';

    $response = $this->post(route('contact.store'), [
        'name' => 'Security Tester',
        'email' => 'tester@example.com',
        'subject' => $xssPayload,
        'message' => $xssPayload,
    ]);

    $response->assertRedirect(route('contact'));

    $message = ContactMessage::where('email', 'tester@example.com')->first();
    expect($message)->not->toBeNull()
        ->and($message->subject)->toBe($xssPayload)
        ->and($message->message)->toBe($xssPayload);
});

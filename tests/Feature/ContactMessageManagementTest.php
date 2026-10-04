<?php

use App\Models\ContactMessage;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

test('guests are redirected to login for admin contact messages routes', function () {
    $message = ContactMessage::factory()->create();

    $this->get(route('admin.contact-messages.index'))->assertRedirect(route('login'));
    $this->get(route('admin.contact-messages.show', $message))->assertRedirect(route('login'));
});

test('users without contact_messages.view permission receive 403 forbidden', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->user)->get(route('admin.contact-messages.index'))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.contact-messages.show', $message))->assertForbidden();

    $this->actingAs($this->author)->get(route('admin.contact-messages.index'))->assertForbidden();
    $this->actingAs($this->author)->get(route('admin.contact-messages.show', $message))->assertForbidden();
});

test('admin can access contact messages index', function () {
    $message = ContactMessage::factory()->create([
        'name' => 'Alice Cooper',
        'email' => 'alice@example.com',
        'subject' => 'Interesting Collaboration',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.contact-messages.index'));

    $response->assertOk()
        ->assertViewIs('admin.contact-messages.index')
        ->assertSee('Alice Cooper')
        ->assertSee('alice@example.com')
        ->assertSee('Interesting Collaboration')
        ->assertSee('Unread');
});

test('admin contact messages list is paginated with 15 records per page', function () {
    ContactMessage::factory()->count(20)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.contact-messages.index'));

    $response->assertOk()
        ->assertViewHas('contactMessages', function ($messages) {
            return $messages->count() === 15 && $messages->total() === 20;
        });
});

test('messages are ordered newest first', function () {
    $oldest = ContactMessage::factory()->create([
        'subject' => 'Oldest Message Ever',
        'created_at' => now()->subDays(5),
    ]);

    $newest = ContactMessage::factory()->create([
        'subject' => 'Newest Message Ever',
        'created_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.contact-messages.index'));

    $response->assertOk();

    $content = $response->getContent();
    $posNewest = strpos($content, 'Newest Message Ever');
    $posOldest = strpos($content, 'Oldest Message Ever');

    expect($posNewest)->toBeLessThan($posOldest);
});

test('empty state renders when no contact messages exist', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.contact-messages.index'));

    $response->assertOk()
        ->assertSee('No contact messages found.');
});

test('admin can view message detail', function () {
    $message = ContactMessage::factory()->create([
        'name' => 'Bob Marley',
        'email' => 'bob@example.com',
        'subject' => 'Music and Code',
        'message' => 'Detailed message text discussing systems and melodies.',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.contact-messages.show', $message));

    $response->assertOk()
        ->assertViewIs('admin.contact-messages.show')
        ->assertSee('Bob Marley')
        ->assertSee('bob@example.com')
        ->assertSee('Music and Code')
        ->assertSee('Detailed message text discussing systems and melodies.');
});

test('viewing an unread message sets read_at timestamp to now', function () {
    $message = ContactMessage::factory()->create(['read_at' => null]);
    expect($message->read_at)->toBeNull();

    $this->actingAs($this->admin)->get(route('admin.contact-messages.show', $message));

    $message->refresh();
    expect($message->read_at)->not->toBeNull();
});

test('viewing an already read message preserves existing read_at timestamp', function () {
    $pastReadAt = now()->subDays(3);
    $message = ContactMessage::factory()->create(['read_at' => $pastReadAt]);

    $this->actingAs($this->admin)->get(route('admin.contact-messages.show', $message));

    $message->refresh();
    expect($message->read_at->toDateTimeString())->toBe($pastReadAt->toDateTimeString());
});

test('dashboard displays contact messages link for authorized admin', function () {
    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.contact-messages.index'))
        ->assertSee('Contact Messages');
});

test('dashboard hides contact messages link for regular user', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));

    $response->assertOk()
        ->assertDontSee(route('admin.contact-messages.index'));
});

test('html content in message is safely escaped on detail view', function () {
    $xssContent = '<script>alert("hacked")</script><b>Bold Text</b>';

    $message = ContactMessage::factory()->create([
        'subject' => $xssContent,
        'message' => $xssContent,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.contact-messages.show', $message));

    $response->assertOk()
        ->assertSee('&lt;script&gt;alert(&quot;hacked&quot;)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert("hacked")</script>', false);
});

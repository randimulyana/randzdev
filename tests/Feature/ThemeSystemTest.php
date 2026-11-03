<?php

use App\Models\User;

test('public layout renders pre-paint theme initializer before body and accessible theme toggle', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('<script data-theme-init>', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-theme-menu-trigger', false)
        ->assertSee('aria-label="Select theme: Light, Dark, or System"', false)
        ->assertSee('data-set-theme="light"', false)
        ->assertSee('data-set-theme="dark"', false)
        ->assertSee('data-set-theme="system"', false);

    $html = $response->getContent();
    $initPosition = strpos($html, '<script data-theme-init>');
    $headEndPosition = strpos($html, '</head>');

    expect($initPosition)->toBeInt()
        ->and($headEndPosition)->toBeInt()
        ->and($initPosition)->toBeLessThan($headEndPosition);
});

test('guest authentication layout renders pre-paint theme initializer and theme toggle', function () {
    $response = $this->get(route('login'));

    $response->assertOk()
        ->assertSee('<script data-theme-init>', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-set-theme="light"', false)
        ->assertSee('data-set-theme="dark"', false)
        ->assertSee('data-set-theme="system"', false);

    $html = $response->getContent();
    expect(strpos($html, '<script data-theme-init>'))->toBeLessThan(strpos($html, '</head>'));
});

test('authenticated app layout renders pre-paint theme initializer and theme toggle', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('<script data-theme-init>', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-set-theme="light"', false)
        ->assertSee('data-set-theme="dark"', false)
        ->assertSee('data-set-theme="system"', false);

    $html = $response->getContent();
    expect(strpos($html, '<script data-theme-init>'))->toBeLessThan(strpos($html, '</head>'));
});

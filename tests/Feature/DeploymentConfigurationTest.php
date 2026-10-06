<?php

use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\App;

test('health check endpoint /up returns 200 OK for uptime monitoring', function () {
    $response = $this->get('/up');

    $response->assertOk();
});

test('robots.txt endpoint is accessible and references sitemap', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *')
        ->assertSee('Sitemap: '.url('/sitemap.xml'));
});

test('sitemap.xml endpoint is accessible with valid xml content type', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/xml; charset=UTF-8')
        ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
        ->assertSee('<urlset', false);
});

test('env.example contains essential production configuration keys', function () {
    $envExamplePath = base_path('.env.example');
    expect(file_exists($envExamplePath))->toBeTrue();

    $content = file_get_contents($envExamplePath);

    $requiredKeys = [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'APP_DEBUG',
        'APP_URL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
        'SESSION_DRIVER',
        'SESSION_SECURE_COOKIE',
        'FILESYSTEM_DISK',
        'CACHE_STORE',
        'QUEUE_CONNECTION',
        'LOG_CHANNEL',
        'LOG_LEVEL',
    ];

    foreach ($requiredKeys as $key) {
        expect($content)->toContain($key);
    }
});

test('AdminUserSeeder refuses execution when app environment is production', function () {
    // Simulate production environment
    App::detectEnvironment(fn () => 'production');

    expect(App::isProduction())->toBeTrue();

    $seeder = new AdminUserSeeder;
    $seeder->run();

    // Since it's production, admin account should not be seeded
    $this->assertDatabaseMissing('users', [
        'email' => config('admin.email'),
    ]);

    // Reset back to testing environment
    App::detectEnvironment(fn () => 'testing');
});

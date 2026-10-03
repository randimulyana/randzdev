<?php

use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
    $this->author = User::factory()->create()->assignRole('author');
    $this->user = User::factory()->create()->assignRole('user');
});

test('guests are redirected to login for technology routes', function () {
    $technology = Technology::factory()->create();

    $this->get(route('admin.technologies.index'))->assertRedirect(route('login'));
    $this->get(route('admin.technologies.create'))->assertRedirect(route('login'));
    $this->post(route('admin.technologies.store'), ['name' => 'Laravel', 'slug' => 'laravel'])->assertRedirect(route('login'));
    $this->get(route('admin.technologies.edit', $technology))->assertRedirect(route('login'));
    $this->put(route('admin.technologies.update', $technology), ['name' => 'Laravel', 'slug' => 'laravel'])->assertRedirect(route('login'));
    $this->delete(route('admin.technologies.destroy', $technology))->assertRedirect(route('login'));
});

test('unauthorized users cannot access technology routes', function () {
    $technology = Technology::factory()->create();

    $this->actingAs($this->user)->get(route('admin.technologies.index'))->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.technologies.create'))->assertForbidden();
    $this->actingAs($this->user)->post(route('admin.technologies.store'), [
        'name' => 'Laravel',
        'slug' => 'laravel',
    ])->assertForbidden();
    $this->actingAs($this->user)->get(route('admin.technologies.edit', $technology))->assertForbidden();
    $this->actingAs($this->user)->put(route('admin.technologies.update', $technology), [
        'name' => 'Laravel Updated',
        'slug' => 'laravel-updated',
    ])->assertForbidden();
    $this->actingAs($this->user)->delete(route('admin.technologies.destroy', $technology))->assertForbidden();
});

test('authorized users can view technology index page with project counts', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $technology = Technology::factory()->create(['name' => 'Vue.js', 'slug' => 'vue-js']);
    $projects = Project::factory()->count(2)->create();
    $technology->projects()->attach($projects);

    $response = $this->actingAs($user)->get(route('admin.technologies.index'));

    $response->assertOk()
        ->assertViewIs('admin.technologies.index')
        ->assertSee('Vue.js')
        ->assertSee('vue-js')
        ->assertSee('2');
})->with(['admin', 'author']);

test('authorized users can view technology create page', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $this->actingAs($user)
        ->get(route('admin.technologies.create'))
        ->assertOk()
        ->assertViewIs('admin.technologies.create')
        ->assertSee('Create Technology');
})->with(['admin', 'author']);

test('authorized users can store a new technology', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;

    $response = $this->actingAs($user)->post(route('admin.technologies.store'), [
        'name' => 'Tailwind CSS',
        'slug' => 'tailwind-css',
        'icon' => 'tailwind',
    ]);

    $response->assertRedirect(route('admin.technologies.index'))
        ->assertSessionHas('success', 'Technology created successfully.');

    $this->assertDatabaseHas('technologies', [
        'name' => 'Tailwind CSS',
        'slug' => 'tailwind-css',
        'icon' => 'tailwind',
    ]);
})->with(['admin', 'author']);

test('authorized users can view technology edit page', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $technology = Technology::factory()->create(['name' => 'Docker', 'slug' => 'docker', 'icon' => 'docker']);

    $this->actingAs($user)
        ->get(route('admin.technologies.edit', $technology))
        ->assertOk()
        ->assertViewIs('admin.technologies.edit')
        ->assertSee('Docker')
        ->assertSee('docker');
})->with(['admin', 'author']);

test('authorized users can update an existing technology', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $technology = Technology::factory()->create(['name' => 'React', 'slug' => 'react']);

    $response = $this->actingAs($user)->put(route('admin.technologies.update', $technology), [
        'name' => 'React.js',
        'slug' => 'react-js',
        'icon' => 'react',
    ]);

    $response->assertRedirect(route('admin.technologies.index'))
        ->assertSessionHas('success', 'Technology updated successfully.');

    $this->assertDatabaseHas('technologies', [
        'id' => $technology->id,
        'name' => 'React.js',
        'slug' => 'react-js',
        'icon' => 'react',
    ]);
})->with(['admin', 'author']);

test('authorized users can delete a technology', function (string $role) {
    $user = $role === 'admin' ? $this->admin : $this->author;
    $technology = Technology::factory()->create();

    $response = $this->actingAs($user)->delete(route('admin.technologies.destroy', $technology));

    $response->assertRedirect(route('admin.technologies.index'))
        ->assertSessionHas('success', 'Technology deleted successfully.');

    $this->assertDatabaseMissing('technologies', ['id' => $technology->id]);
})->with(['admin', 'author']);

test('technology requires name and slug to be stored', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.technologies.store'), [
        'name' => '',
        'slug' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'slug']);
});

test('technology slug must be unique on creation', function () {
    Technology::factory()->create(['slug' => 'existing-tech']);

    $response = $this->actingAs($this->admin)->post(route('admin.technologies.store'), [
        'name' => 'Duplicate Tech',
        'slug' => 'existing-tech',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('technology update allows keeping the same slug', function () {
    $technology = Technology::factory()->create(['name' => 'Original', 'slug' => 'original-tech']);

    $response = $this->actingAs($this->admin)->put(route('admin.technologies.update', $technology), [
        'name' => 'Updated Tech',
        'slug' => 'original-tech',
    ]);

    $response->assertRedirect(route('admin.technologies.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('technologies', [
        'id' => $technology->id,
        'name' => 'Updated Tech',
        'slug' => 'original-tech',
    ]);
});

test('technology update rejects duplicate slug from another technology', function () {
    Technology::factory()->create(['slug' => 'other-tech']);
    $technology = Technology::factory()->create(['slug' => 'my-tech']);

    $response = $this->actingAs($this->admin)->put(route('admin.technologies.update', $technology), [
        'name' => 'Updated Tech',
        'slug' => 'other-tech',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('deleting a technology detaches pivot entries without deleting projects', function () {
    $technology = Technology::factory()->create();
    $project = Project::factory()->create();
    $technology->projects()->attach($project);

    $this->assertDatabaseHas('project_technology', [
        'project_id' => $project->id,
        'technology_id' => $technology->id,
    ]);

    $this->actingAs($this->admin)->delete(route('admin.technologies.destroy', $technology));

    $this->assertDatabaseMissing('technologies', ['id' => $technology->id]);
    $this->assertDatabaseMissing('project_technology', [
        'technology_id' => $technology->id,
    ]);
    $this->assertDatabaseHas('projects', ['id' => $project->id]);
});

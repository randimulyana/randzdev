<?php

use App\Models\User;

test('guest can access public about route', function () {
    $response = $this->get(route('about'));

    $response->assertOk()
        ->assertViewIs('about');
});

test('authenticated user can access public about route', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('about'));

    $response->assertOk()
        ->assertViewIs('about');
});

test('profile section renders personal background, bio, location, education, and current focus', function () {
    $response = $this->get(route('about'));

    $profile = config('portfolio.profile');

    $response->assertOk()
        ->assertSee($profile['name'])
        ->assertSee($profile['role'])
        ->assertSee($profile['bio'])
        ->assertSee($profile['location'])
        ->assertSee($profile['education'])
        ->assertSee($profile['current_focus']);
});

test('social links are rendered correctly', function () {
    $response = $this->get(route('about'));

    $social = config('portfolio.social');

    $response->assertOk()
        ->assertSee($social['github'])
        ->assertSee($social['linkedin'])
        ->assertSee('mailto:'.$social['email'])
        ->assertSee($social['website']);
});

test('developer journey renders all milestones in chronological order', function () {
    $response = $this->get(route('about'));

    $response->assertOk();

    $journey = config('portfolio.journey');

    $content = $response->getContent();
    $lastPos = 0;

    foreach ($journey as $milestone) {
        $response->assertSee($milestone['period'])
            ->assertSee($milestone['title'])
            ->assertSee($milestone['description']);

        $currentPos = strpos($content, e($milestone['title']));
        expect($currentPos)->toBeGreaterThan($lastPos);
        $lastPos = $currentPos;
    }
});

test('categorized skills are rendered without percentage bars', function () {
    $response = $this->get(route('about'));

    $response->assertOk();

    foreach (config('portfolio.skills') as $categoryName => $skills) {
        $response->assertSee($categoryName);
        foreach ($skills as $skill) {
            $response->assertSee($skill);
        }
    }

    $response->assertDontSee('style="width:')
        ->assertDontSee('%</span>', false);
});

test('seo metadata and canonical url are rendered on about page', function () {
    $response = $this->get(route('about'));

    $expectedTitle = 'About — '.config('app.name');
    $expectedDescription = 'Learn more about '.config('portfolio.profile.name').', software engineering background, developer journey, and technical skillset.';

    $response->assertOk()
        ->assertSee('<title>'.$expectedTitle.'</title>', false)
        ->assertSee('<meta name="description" content="'.$expectedDescription.'">', false)
        ->assertSee('<link rel="canonical" href="'.route('about').'">', false);
});

test('navbar displays about as active link on about page', function () {
    $response = $this->get(route('about'));

    $response->assertOk()
        ->assertSee('text-indigo-600 font-semibold', false);
});

<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'title' => ucfirst($title),
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'thumbnail' => null,
            'role' => 'Full-Stack Developer',
            'status' => 'completed',
            'repository_url' => fake()->url(),
            'live_url' => fake()->url(),
            'started_at' => now()->subMonths(3)->toDateString(),
            'completed_at' => now()->subMonth()->toDateString(),
            'featured' => false,
            'published_at' => now(),
        ];
    }

    /**
     * Indicate that the project is featured.
     */
    public function featured(): static
    {
        return $this->state(fn () => [
            'featured' => true,
        ]);
    }

    /**
     * Indicate that the project is in draft status and not published.
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the project has no category assigned.
     */
    public function withoutCategory(): static
    {
        return $this->state(fn () => [
            'category_id' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\LearningPath;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LearningPath>
 */
class LearningPathFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'created_by' => User::factory(),
            'updated_by' => null,
            'title' => ucfirst(rtrim($title, '.')),
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'thumbnail' => null,
            'difficulty' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'estimated_duration' => fake()->randomElement(['2 hours', '4 hours', '1 week']),
            'status' => 'published',
            'published_at' => now(),
        ];
    }

    /**
     * Indicate that the learning path is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the learning path is archived.
     */
    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => 'archived',
            'published_at' => now()->subDay(),
        ]);
    }
}

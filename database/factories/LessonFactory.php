<?php

namespace Database\Factories;

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
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
            'learning_path_id' => LearningPath::factory(),
            'created_by' => User::factory(),
            'updated_by' => null,
            'title' => ucfirst(rtrim($title, '.')),
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'order' => 1,
            'status' => 'published',
            'published_at' => now(),
        ];
    }

    /**
     * Indicate that the lesson is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the lesson is archived.
     */
    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => 'archived',
            'published_at' => now()->subDay(),
        ]);
    }
}

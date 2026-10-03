<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => ucfirst(rtrim($title, '.')),
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(4, true),
            'featured_image' => null,
            'type' => 'article',
            'status' => 'published',
            'reading_time' => fake()->numberBetween(2, 10),
            'meta_title' => null,
            'meta_description' => null,
            'canonical_url' => null,
            'published_at' => now(),
        ];
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the post is archived.
     */
    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indicate that the post has no category assigned.
     */
    public function withoutCategory(): static
    {
        return $this->state(fn () => [
            'category_id' => null,
        ]);
    }
}

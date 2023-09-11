<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Link;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of \App\Models\Comment
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'commentable_type' => Post::class,
            'commentable_id' => PostFactory::new(),
            'body' => $this->faker->words(3, true),
        ];
    }

    /**
     * Indicate that the model's commentable for videos.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function videos()
    {
        return $this->state(function (array $attributes) {
            return [
                'commentable_type' => Video::class,
                'commentable_id' => VideoFactory::new(),
            ];
        });
    }

    /**
     * Indicate that the model's commentable for videos.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function links()
    {
        return $this->state(function (array $attributes) {
            return [
                'commentable_type' => 'link',
                'commentable_id' => LinkFactory::new(),
            ];
        });
    }
}

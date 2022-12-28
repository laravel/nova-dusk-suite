<?php

namespace App\Nova\Dashboards\Metrics;

use App\Models\Post;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class NewPosts extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Metrics\MetricTableRow>
     */
    public function calculate(NovaRequest $request): array
    {
        return Post::latest()->take(5)->get()->transform(function ($post) {
            return MetricTableRow::make()
                ->title($post->title)
                ->subtitle(Str::limit($post->body, 10))
                ->actions(function () use ($post) {
                    return [
                        MenuItem::make('Edit', "/resources/posts/{$post->id}/edit"),
                    ];
                });
        })->all();
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }
}

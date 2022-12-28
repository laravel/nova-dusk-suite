<?php

namespace App\Nova\Dashboards\Metrics;

use App\Models\Post;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class PostCount extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Metrics\ValueResult
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, Post::class)->copyable();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int|string, string>
     */
    public function ranges(): array
    {
        return [
            30 => '30 Days',
            60 => '60 Days',
            365 => '365 Days',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'post-count';
    }
}

<?php

namespace App\Nova\Metrics;

use App\Models\Post;
use Illuminate\Http\Request;
use Laravel\Nova\Contracts\Metrics\InteractsWithFilters;
use Laravel\Nova\Metrics\Value;

class PostCount extends Value implements InteractsWithFilters
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->count(
            $request,
            Post::query()
                ->when($request->resourceId, function ($query) use ($request) {
                    return $query->where('user_id', $request->resourceId);
                })
        );
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
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
     * @return  \DateTimeInterface|\DateInterval|float|int
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
    public function uriKey()
    {
        return 'post-count';
    }
}

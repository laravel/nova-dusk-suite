<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class WithPosts extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        if ($value === 'with-post-and-comment') {
            return $query->whereHas('posts', function ($query) {
                return $query->has('comments');
            });
        } elseif ($value === 'with') {
            return $query->has('posts');
        } elseif ($value === 'without') {
            return $query->doesntHave('posts');
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function options(NovaRequest $request)
    {
        return [
            'With Commented Posts' => 'with-post-and-comment',
            'With Posts' => 'with',
            'Without Posts' => 'without',
        ];
    }
}

<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'With Commented Posts' => 'with-post-and-comment',
            'With Posts' => 'with',
            'Without Posts' => 'without',
        ];
    }
}

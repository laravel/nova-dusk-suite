<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class SelectFirst extends Filter
{
    /**
     * Key name for filter.
     *
     * @var string
     */
    public $keyName;

    /**
     * Construct a new filter.
     *
     * @param  string  $keyName
     */
    public function __construct($keyName = 'id')
    {
        $this->keyName = $keyName;
    }

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
        return $query->where($this->keyName, $value);
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
            'First User' => '1',
            'Second User' => '2',
            'Third User' => '3',
        ];
    }
}

<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where($this->keyName, $value);
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
            'First User' => '1',
            'Second User' => '2',
            'Third User' => '3',
        ];
    }
}

<?php

namespace App\Nova\Filters;

use Carbon\CarbonImmutable;
use Laravel\Nova\Filters\DateFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class Created extends DateFilter
{
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
        $value = CarbonImmutable::parse($value);

        return $query->whereBetween('created_at', [$value->startOfDay(), $value->endOfDay()]);
    }
}

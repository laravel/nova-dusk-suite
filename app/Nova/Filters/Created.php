<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use Laravel\Nova\Filters\DateFilter;

class Created extends DateFilter
{
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
        $value = CarbonImmutable::parse($value);

        return $query->whereBetween('created_at', [$value->startOfDay(), $value->endOfDay()]);
    }
}

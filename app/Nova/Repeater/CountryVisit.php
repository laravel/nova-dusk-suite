<?php

namespace App\Nova\Repeater;

use Illuminate\Support\Carbon;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Http\Requests\NovaRequest;

class CountryVisit extends Repeatable
{
    /**
     * Get the fields displayed by the repeatable.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Country::make('Country'),
            Date::make('Arrived')
                ->resolveUsing(function ($value) {
                    return ! is_null($value) ? Carbon::parse($value) : null;
                }),
            Date::make('Leave')
                ->resolveUsing(function ($value) {
                    return ! is_null($value) ? Carbon::parse($value) : null;
                }),
        ];
    }
}

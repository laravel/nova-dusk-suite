<?php

namespace App\Nova\Repeater;

use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class InvoiceItem extends Repeatable
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\InvoiceItem>
     */
    public static $model = \App\Models\InvoiceItem::class;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::hidden(),

            Number::make('Quantity')->rules('numeric'),
            Text::make('Description')->rules('string'),
            Currency::make('Price')->rules('numeric')->asMinorUnits(),
        ];
    }
}

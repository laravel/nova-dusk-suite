<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class BookPurchase
{
    /**
     * Purchase type.
     *
     * @var string|null
     */
    protected $type;

    /**
     * Construct a new object.
     *
     * @param string|null  $type
     */
    public function __construct($type = null)
    {
        $this->type = $type;
    }

    /**
     * Get the pivot fields for the relationship.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            Currency::make('Price')
                ->dependsOn(['books'], function ($field, NovaRequest $request, $fields) {
                    $bookId = (int) (
                        $request->resource === 'books' ? $request->resourceId : $fields->books
                    );

                    if ($bookId == 1) {
                        $field->rules(['required', 'numeric', 'min:10', 'max:199']);
                    }
                })->rules(['required', 'numeric', 'min:0', 'max:199']),

            Select::make('Type')
                ->options([
                    'personal' => 'Personal',
                    'gift' => 'Gift',
                ])
                ->default($this->type ?? 'personal')
                ->readonly(function () {
                    return ! is_null($this->type);
                }),

            DateTime::make('Purchased At')->rules('required'),
        ];
    }
}

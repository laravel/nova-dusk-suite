<?php

namespace App\Nova\Fields;

use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class BookPurchase
{
    use ConditionallyLoadsAttributes;

    /**
     * Purchase type.
     *
     * @var string|null
     */
    protected $type;

    /**
     * Show timestamps.
     *
     * @var bool
     */
    protected $showTimestamps;

    /**
     * Appends fields.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Construct a new object.
     *
     * @param  string|null  $type
     * @param  bool  $showTimestamps
     */
    public function __construct($type = null, $showTimestamps = false)
    {
        $this->type = $type;
        $this->showTimestamps = $showTimestamps;
    }

    /**
     * Appends with following fields.
     *
     * @param  array  $fields
     * @return $this
     */
    public function appends(array $fields)
    {
        $this->appends = $fields;

        return $this;
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
                ->dependsOn(['books'], function ($field, NovaRequest $request, $formData) {
                    $bookId = (int) $formData->resource('books', $formData->books);

                    if ($bookId == 1) {
                        $field->rules(['required', 'numeric', 'min:10', 'max:199'])
                            ->help('Price starts from $10-$199');

                        return;
                    }

                    $field->rules(['required', 'numeric', 'min:0', 'max:99'])
                        ->help('Price starts from $0-$99');
                })
                ->asMinorUnits()
                ->filterable(),

            Select::make('Type')
                ->options([
                    'personal' => 'Personal',
                    'gift' => 'Gift',
                ])
                ->default($this->type)
                ->readonly(function () {
                    return ! is_null($this->type);
                }),

            DateTime::make('Purchased At')
                ->rules('required')
                ->default(now()->second(0)),

            $this->merge($this->appends),

            $this->mergeWhen($this->showTimestamps, function () {
                return [
                    DateTime::make('Created At')->readonly(),
                    DateTime::make('Updated At')->readonly(),
                ];
            }),
        ];
    }
}

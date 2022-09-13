<?php

namespace App\Nova\Fields;

use App\Nova\Book;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Hidden;
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
                ->dependsOn(['books', 'personalBooks', 'giftBooks'], function ($field, NovaRequest $request, FormData $formData) {
                    $bookId = (int) $formData->resource(Book::uriKey());

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

            $this->mergeWhen(is_null($this->type), function () {
                return [
                    Select::make('Type')
                        ->options([
                            'personal' => 'Personal',
                            'gift' => 'Gift',
                        ])
                        ->rules('required')
                        ->dependsOn('price', function ($field, NovaRequest $request, FormData $formData) {
                            if (! is_null($formData->price) && $formData->price == 0) {
                                $field->readonly()->default('gift');
                            }
                        }),
                ];
            }),

            $this->mergeUnless(is_null($this->type), function () {
                return [
                    Select::make('Type')->options([
                        'personal' => 'Personal',
                        'gift' => 'Gift',
                    ])
                    ->readonly()
                    ->default($this->type),
                ];
            }),

            Hidden::make('Type', 'hiddenType')
                ->tap(function ($field) {
                    $field->resolveUsing(function ($value, $resource) {
                        return $resource->type;
                    })
                    ->fillUsing(function ($request, $model, $attribute, $requestAttribute) use ($field) {
                        $value = $request->input($attribute);

                        if (! $field->isValidNullValue($value)) {
                            $model->type = $value;
                        }
                    })->dependsOn(['price', 'type'], function ($field, NovaRequest $request, FormData $formData) {
                        $field->default($formData->type);
                    });
                })
                ->onlyOnForms(),

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

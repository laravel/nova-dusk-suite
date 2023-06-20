<?php

namespace App\Nova;

use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Book
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Book extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Book::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'sku';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'id', 'sku',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('ID', 'id')->sortable(),
            Text::make(__('SKU'), 'sku')
                ->rules('required')
                ->sortable()
                ->showOnPreview(),

            Text::make('Title')->readonly(function ($request) {
                return $request->isUpdateOrUpdateAttachedRequest();
            })->creationRules('required')->showOnPreview(),

            Trix::make('Description')
                ->withFiles()
                ->stacked()
                ->fullWidth()
                ->nullable()
                ->dependsOn('active', function (Trix $field, NovaRequest $request, FormData $formData) {
                    if ($formData->boolean('active') === true) {
                        $field->show();
                    } else {
                        $field->hide();
                    }
                })->dependsOnCreating('title', function (Trix $field, NovaRequest $request, FormData $formData) {
                    $field->default($formData->title);
                }),

            Boolean::make('Active')->default(function ($request) {
                return true;
            })->filterable()
                ->showOnPreview(),

            BelongsToMany::make('Purchasers', 'purchasers', User::class)
                ->fields(new Fields\BookPurchase(null, true)),

            BelongsToMany::make('Personal Purchasers', 'personalPurchasers', User::class)
                ->fields(new Fields\BookPurchase('personal')),

            BelongsToMany::make('Gift Purchasers', 'giftPurchasers', User::class)
                ->fields(new Fields\BookPurchase('gift'))
                ->allowDuplicateRelations()
                ->collapsedByDefault(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Metrics\Metric>
     */
    public function cards(NovaRequest $request): array
    {
        return [
            Metrics\BookPurchases::make()->onlyOnDetail(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [
            new Lenses\BookPurchases,
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            new Actions\MarkAsActive(),
            ExportAsCsv::make(),
        ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        if (empty($request->orderByDirection)) {
            $query->reorder()->orderBy('sku', 'asc');
        }

        return $query;
    }
}

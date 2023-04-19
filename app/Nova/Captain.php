<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\VaporImage;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Captain
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Captain extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Captain::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field|\Illuminate\Http\Resources\MergeValue>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('ID', 'id')->sortable(),

            Text::make('Name', 'name')
                ->rules('required')
                ->sortable(),

            $this->merge(function () use ($request) {
                $storage = $request->user()->settings['storage'] ?? 'local' === 'local';

                if ($storage === 'vapor') {
                    return [
                        VaporImage::make('Photo', 'photo')
                            ->prunable()
                            ->help('Using cloud storage'),
                    ];
                }

                return [
                    Image::make('Photo', 'photo')
                        ->disk($storage === 's3' ? 's3' : config('nova.storage_disk'))
                        ->prunable()
                        ->help('Using local storage'),
                ];
            }),

            BelongsToMany::make('Ships', 'ships')
                ->display('name')
                ->fields(function ($request) {
                    return [
                        Text::make('Notes', 'notes')->rules('max:20'),
                        File::make('Contract', 'contract')->prunable()->store(function ($request) {
                            if ($request->contract) {
                                return $request->contract->storeAs('/', 'Contract.pdf', 'public');
                            }
                        }),
                    ];
                })
                ->prunable()
                ->searchable(uses_searchable()),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request): array
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
        return [];
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
            Actions\FieldsAction::make()->standalone()->canSee(function ($request) {
                return ! ($request->allResourcesSelected() || (optional($request->selectedResourceIds())->isNotEmpty() ?? false));
            }),
            tap(Actions\FieldsAction::make()->fullscreen(), function ($action) {
                $action->name = 'Fields Action (fullscreen)';
            }),
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
}

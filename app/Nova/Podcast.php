<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Audio;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\VaporAudio;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Podcast
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Podcast extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Podcast::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

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
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')->rules('required'),

            $this->merge(function () use ($request) {
                $storage = $request->user()->settings['storage'] ?? 'local' === 'local';

                if ($storage === 'vapor') {
                    return [
                        VaporAudio::make('File', 'filename')->nullable(),
                    ];
                }

                return [
                    Audio::make('File', 'filename')
                        ->disk($storage === 's3' ? 's3' : config('nova.storage_disk'))
                        ->nullable(),
                ];
            }),

            MorphMany::make('Comments', 'comments'),

            MorphToMany::make('Tags', 'tags')
                ->display('name')
                ->fields(function () {
                    return [
                        Text::make('Notes', 'notes')->rules('max:20'),
                    ];
                })->searchable(uses_searchable())
                ->showCreateRelationButton(uses_inline_create()),
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
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}

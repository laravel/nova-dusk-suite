<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Post
 * @extends \App\Nova\Resource<TModel>
 */
class Post extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Post::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'User Post';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make('ID', 'id')->asBigInt()->sortable(),

            BelongsTo::make('User', 'user')
                ->display('name')
                ->sortable()
                ->default(function ($request) {
                    return $request->user()->id > 1 ? $request->user()->id : null;
                })
                ->searchable(file_exists(base_path('.searchable')))
                ->showCreateRelationButton(file_exists(base_path('.inline-create')))
                ->filterable(),

            Text::make('Title', 'title')->sortable(),
            Textarea::make('Body', 'body')->stacked(),
            File::make('Attachment')
                ->nullable()
                ->dependsOn('user', function ($field, NovaRequest $request, FormData $formData) {
                    if ($formData->user == 4) {
                        $field->hide();
                    }
                }),

            MorphMany::make('Comments', 'comments'),

            MorphToMany::make('Tags', 'tags')
                    ->display('name')
                    ->fields(function () {
                        return [
                            Text::make('Notes', 'notes')->rules('max:20'),
                        ];
                    })->searchable(file_exists(base_path('.searchable'))),

            KeyValue::make('Meta')->nullable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [
            Metrics\PostCountOverTime::make(), //->refreshWhenFilterChanged(),
            Metrics\PostCountByUser::make(), //->refreshWhenFilterChanged(),
            Metrics\PostCount::make(), //->refreshWhenFilterChanged(),

            Metrics\CommentCount::make()->onlyOnDetail(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [
            new Lenses\PostLens,
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            new Actions\MarkAsActive,
            new Actions\AddComment,
            Actions\StandaloneTask::make()->standalone(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new Filters\SelectFirst('user_id'),
            new Filters\UserPost,
        ];
    }
}

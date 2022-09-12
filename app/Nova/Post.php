<?php

namespace App\Nova;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Heading;
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
                ->dependsOn('title', function (BelongsTo $field, NovaRequest $request, FormData $formData) {
                    if (Str::startsWith($formData->title, 'Space Pilgrim:')) {
                        $field->default(1);
                    }
                })
                ->reorderAssociatables(uses_without_reordering())
                ->searchable(uses_searchable())
                ->showCreateRelationButton(uses_inline_create())
                ->filterable(),

            Text::make('Title', 'title')->rules('required')->sortable(),

            Textarea::make('Body', 'body')->rules('required')->stacked(),

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
                    })->searchable(uses_searchable())
                    ->showCreateRelationButton(uses_inline_create()),

            new Heading('Social Data'),

            KeyValue::make('Meta')
                ->dependsOn('title', function (KeyValue $field, NovaRequest $request, FormData $formData) {
                    if (Str::startsWith($formData->title, 'Space Pilgrim:')) {
                        $field->default([
                            'Series' => 'Space Pilgrim',
                        ]);
                    } elseif (Str::startsWith($formData->title, 'Nova:')) {
                        $field->default([
                            'Series' => 'Laravel Nova',
                        ]);
                    }
                })->nullable(),
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
            Metrics\PostCountOverTime::make()->refreshWhenFiltersChange(),
            Metrics\PostCountByUser::make()->refreshWhenFiltersChange(),
            Metrics\PostCount::make()->refreshWhenFiltersChange(),

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
            Actions\BatchableSleep::make(),
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

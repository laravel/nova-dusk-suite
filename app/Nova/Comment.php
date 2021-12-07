<?php

namespace App\Nova;

use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @property \App\Models\Comment|null $resource
 * @mixin \App\Models\Comment
 */
class Comment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Comment';

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
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make('ID', 'id')->sortable(),
            $this->commentable(),
            Text::make('Body', 'body'),
            File::make('Attachment')->nullable(),
        ];
    }

    /**
     * Get the commentable field definition.
     *
     * @return \Laravel\Nova\Fields\MorphTo
     */
    protected function commentable()
    {
        return MorphTo::make('Commentable', 'commentable')->display([
            Link::class => function ($resource) {
                return $resource->title;
            },
            Post::class => function ($resource) {
                return $resource->title;
            },
            Video::class => function ($resource) {
                return $resource->title;
            },
        ])->types([
            Link::class => 'Link',
            Post::class => 'Post',
            Video::class => 'Video',
        ])->showCreateRelationButton(file_exists(base_path('.inline-create')))
        ->searchable(file_exists(base_path('.searchable')));
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }
}

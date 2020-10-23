<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;

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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }
}

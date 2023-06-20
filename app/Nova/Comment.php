<?php

namespace App\Nova;

use App\Models\Video as VideoModel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

/**
 * @template TModel of \App\Models\Comment
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Comment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Comment::class;

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
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
            ID::make('ID', 'id')->sortable(),
            BelongsTo::make('User')->nullable()->peekable(),

            $commentable = $this->commentable(),

            Text::make('Body', 'body')
                ->rules('required')
                ->dependsOn($commentable, function (Text $field, NovaRequest $request, FormData $formData) {
                    $model = Nova::modelInstanceForKey($formData->commentable_type ?? $request->viaResource);

                    if ($model instanceof VideoModel) {
                        $field->rules('required', 'min:10')->help('Video requires minimum 10 characters!');
                    }

                    if (! is_null($model)) {
                        ray($model->newInstance()->find($formData->commentable ?? $request->viaResourceId));
                    }
                }),

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
        ])->showCreateRelationButton(uses_inline_create())
            ->searchable(uses_searchable());
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
            Actions\TouchCommentable::make()->standalone(),
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

<?php

namespace App\Nova\Actions;

use App\Nova\Link;
use App\Nova\Post;
use App\Nova\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class TouchCommentable extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        if ($fields->commentable instanceof Model && $fields->commentable->exists) {
            $fields->commentable->touch();
        }

        return;
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            MorphTo::make('Commentable', 'commentable')
                ->display([
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
                ]),
        ];
    }
}

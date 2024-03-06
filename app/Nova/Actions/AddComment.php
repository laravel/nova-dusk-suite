<?php

namespace App\Nova\Actions;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class AddComment extends Action
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
        $models->each(function ($model) use ($fields) {
            $comment = (new Comment)->forceFill([
                'user_id' => optional($fields->user)->getKey() ?? null,
                'body' => $fields->body,
            ]);

            $model->comments()->save($comment);
        });

        return null;
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
            Boolean::make('Anonymous Comment', 'anonymous')
                ->default(true),

            BelongsTo::make('User')
                ->hide()
                ->rules('sometimes')
                ->searchable(uses_searchable())
                ->dependsOn('anonymous', function (BelongsTo $field, NovaRequest $request, FormData $formData) {
                    if ($formData->boolean('anonymous') === false) {
                        $field->show()->rules('required');
                    } else {
                        $field->hide()->setValue(null);
                    }
                }),

            Text::make('Body')->rules('required'),
        ];
    }
}

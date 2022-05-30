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
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function ($model) use ($fields) {
            $comment = (new Comment)->forceFill([
                'user_id' => optional($fields->user)->getKey() ?? null,
                'body' => $fields->body,
            ]);

            $model->comments()->save($comment);
        });
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Boolean::make('System Notification')
                ->default(true),

            BelongsTo::make('User')
                ->hide()
                ->dependsOn('system_notification', function (BelongsTo $field, NovaRequest $request, FormData $formData) {
                    if ($FormData->system_notification === false) {
                        $field->show()->rules('required');
                    }
                }),

            Text::make('Body')->rules('required'),
        ];
    }
}

<?php

namespace App\Nova\Actions;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
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
            Boolean::make('Anonymous Comment', 'anonymous')
                ->default(true),

            BelongsTo::make('User')
                ->hide()
                ->rules([
                    Rule::requiredIf(function () use ($request) {
                        return in_array($request->anonymous, [0, 'false', false]);
                    }),
                ])
                ->dependsOn('anonymous', function (BelongsTo $field, NovaRequest $request, FormData $formData) {
                    if ($formData->anonymous === false) {
                        $field->show();
                    }
                }),

            Text::make('Body')->rules('required'),
        ];
    }
}

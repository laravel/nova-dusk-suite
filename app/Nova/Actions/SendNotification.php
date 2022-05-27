<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;

class SendNotification extends Action
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
        //
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
            Select::make('Type')
                ->options([
                    'text' => 'Simple Notification',
                    'text-url' => 'Notification with URL',
                    'textarea' => 'Long Notification',
                    'textarea-url' => 'Long Notification with URL',
                ]),

            Text::make('Content')
                ->readonly()
                ->dependsOn('type', function (Text $field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->type, ['text', 'text-url'])) {
                        $field->readonly(false)->rules('required');
                    } elseif (! empty($formData->type)) {
                        $field->hide();
                    }
                }),

            Textarea::make('Content')
                ->hide()
                ->dependsOn('type', function (Textarea $field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->type, ['textarea', 'textarea-url'])) {
                        $field->show()->rules('required');
                    }
                }),

            URL::make('Action URL')
                ->hide()
                ->dependsOn('type', function (URL $field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->type, ['text-url', 'textarea-url'])) {
                        $field->show()->rules('required');
                    }
                }),
        ];
    }
}

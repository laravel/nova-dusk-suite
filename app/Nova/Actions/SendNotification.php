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
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL as NovaURL;

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
        $notification = NovaNotification::make()->message($fields->message)->type($fields->type);

        if (! empty($fields->icon)) {
            $notification->icon($fields->icon);
        }

        if (! empty($fields->action_url) && ! empty($fields->action_text)) {
            $notification->action($fields->action_text, NovaURL::remote($fields->action_url));
        }

        $models->each->notify($notification);
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
                    'success' => 'Success',
                    'info' => 'Information',
                    'warning' => 'Warning',
                    'error' => 'Error',
                ]),

            Text::make('Message')
                ->readonly()
                ->dependsOn('type', function (Text $field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->type, ['info', 'warning'])) {
                        $field->readonly(false)->rules('required');
                    } elseif (! empty($formData->type)) {
                        $field->hide();
                    }
                }),

            Textarea::make('Message')
                ->hide()
                ->dependsOn('type', function (Textarea $field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->type, ['success', 'error'])) {
                        $field->show()->rules('required');
                    }
                }),

            URL::make('Action URL')
                ->hide()
                ->dependsOn('type', function (URL $field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->type, ['success', 'info'])) {
                        $field->show()->rules('required');
                    }
                }),

            Text::make('Action Text')
                ->hide()
                ->dependsOn(['type', 'action_url'], function (Text $field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->type, ['success', 'info'])) {
                        $field->show();
                    }

                    if (! empty($formData->action_url)) {
                        $field->rules('required')->suggestions([
                            'Download',
                            'View',
                        ])->default('Download');
                    }
                }),

            Select::make('Icon')
                ->rules(['required'])
                ->options([])
                ->dependsOn('type', function (Select $field, NovaRequest $request, FormData $formData) {
                    $options = [
                        'light-bulb',
                        'information-circle',
                        'download',
                        'duplicate',
                    ];

                    if (in_array($formData->type, ['warning', 'error'])) {
                        $options = [
                            'exclamation',
                            'exclamation-circle',
                            'trash',
                            'emoji-sad',
                        ];
                    }

                    $field->options($options);
                })
        ];
    }
}

<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Http\Requests\NovaRequest;

class FieldsAction extends Action
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
        ray($fields);
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $toggleDisplaySelectOptions = function () {
            return ['hide' => 'Hide', 'show' => 'Show'];
        };

        $toggleReadonly = function ($field) {
            $field->dependsOn('use_readonly', function ($field, NovaRequest $request, FormData $formData) {
                $field->readonly($formData->use_readonly);
            });
        };

        return [
            Fields\Boolean::make('Toggle Readonly', 'use_readonly')->default(false),
            Fields\Boolean::make('Boolean')->tap($toggleReadonly),
            Fields\Color::make('Color')->tap($toggleReadonly),
            Fields\Date::make('Date')->tap($toggleReadonly),
            Fields\DateTime::make('DateTime')->tap($toggleReadonly),
            Fields\Email::make('E-mail Address', 'email')->tap($toggleReadonly),
            Fields\File::make('File')->tap($toggleReadonly),
            Fields\Hidden::make('Hidden')->tap($toggleReadonly),
            Fields\KeyValue::make('KeyValue')->tap($toggleReadonly),
            Fields\Markdown::make('Markdown')->tap($toggleReadonly),
            Fields\Number::make('Number')->tap($toggleReadonly),
            Fields\Number::make('Range Number')->withMeta(['type' => 'range'])->tap($toggleReadonly),
            Fields\Password::make('Password')->tap($toggleReadonly),
            Fields\Trix::make('Trix'),
            Fields\Trix::make('Readonly Trix')->readonly(),
            Fields\URL::make('URL')->tap($toggleReadonly),
            Fields\Text::make('Text')->tap($toggleReadonly),
            Fields\Text::make('Stacked_Field_Text')->stacked()->tap($toggleReadonly),
            Fields\Textarea::make('Textarea')->tap($toggleReadonly),

            Fields\Select::make('Select 1')
                ->options($toggleDisplaySelectOptions),

            Fields\Select::make('Select 2')
                ->options($toggleDisplaySelectOptions)
                ->dependsOn('select_1', function (Fields\Select $field, NovaRequest $request, FormData $formData) {
                    if ($formData->select_1 != 'show') {
                        $field->hide();
                    }
                }),

            Fields\Select::make('Select 3')
                ->options($toggleDisplaySelectOptions)
                ->dependsOn(['select_1', 'select_2'], function (Fields\Select $field, NovaRequest $request, FormData $formData) {
                    if ($formData->select_1 != 'show' || $formData->select_2 != 'show') {
                        $field->hide();
                    }
                }),
        ];
    }
}

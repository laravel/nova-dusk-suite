<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
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
    public function handle(Fields\ActionFields $fields, Collection $models): mixed
    {
        ray($fields);

        return null;
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, mixed>
     */
    public function fields(NovaRequest $request): array
    {
        $toggleDisplaySelectOptions = function () {
            return ['hide' => 'Hide', 'show' => 'Show'];
        };

        $toggleReadonly = function ($field) {
            $field->dependsOn('use_readonly', function (Fields\Field $field, NovaRequest $request, Fields\FormData $formData) {
                $field->readonly($formData->use_readonly);
            });
        };

        return [
            Fields\Hidden::make('Selected Resources', 'selected_resources')->trackSelectedResources('use_readonly'),
            Fields\Boolean::make('Toggle Readonly', 'use_readonly')->default(false),
            Fields\Boolean::make('Boolean')->tap($toggleReadonly),
            Fields\BooleanGroup::make('Boolean Group')
                ->options(['selected' => 'Is Selected'])
                ->dependsOn('boolean', function (Fields\BooleanGroup $field, NovaRequest $request, Fields\FormData $formData) {
                    $field->setValue(['selected' => $formData->boolean('boolean')]);
                }),
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
                ->dependsOn('select_1', function (Fields\Select $field, NovaRequest $request, Fields\FormData $formData) {
                    if ($formData->select_1 != 'show') {
                        $field->hide();
                    }
                }),

            Fields\Select::make('Select 3')
                ->options($toggleDisplaySelectOptions)
                ->dependsOn(['select_1', 'select_2'], function (Fields\Select $field, NovaRequest $request, Fields\FormData $formData) {
                    if ($formData->select_1 != 'show' || $formData->select_2 != 'show') {
                        $field->hide();
                    }
                }),
        ];
    }
}

<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\ActionFields;
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
        return [
            Fields\Boolean::make('Boolean'),
            Fields\Color::make('Color'),
            Fields\Date::make('Date'),
            Fields\DateTime::make('DateTime'),
            Fields\Email::make('E-mail Address', 'email'),
            Fields\File::make('File'),
            Fields\Hidden::make('Hidden'),
            Fields\KeyValue::make('KeyValue'),
            Fields\Markdown::make('Markdown'),
            Fields\Number::make('Number'),
            Fields\Password::make('Password'),
            Fields\Trix::make('Trix'),
            Fields\URL::make('URL'),
            Fields\Text::make('Text'),
        ];
    }
}

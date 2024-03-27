<?php

namespace App\Nova\Actions;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Laravel\Nova\Http\Requests\NovaRequest;

use function App\Nova\uses_searchable;

class CreateUserProfile extends Action
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
        $user = $models->first();

        $profile = new Profile();
        $profile->user_id = $user->getKey();
        $profile->timezone = $fields->timezone;

        if (! is_null($fields->twitter)) {
            $profile->twitter_url = "https://twitter.com/{$fields->twitter}";
        }

        if (! is_null($fields->github)) {
            $profile->github_url = "https://github.com/{$fields->github}";
        }

        $profile->save();

        return Action::message('User Profile created');
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
            Text::make('Twitter Profile', 'twitter')->nullable(),
            Text::make('GitHub Username', 'github')->nullable(),
            Timezone::make('Timezone')
                ->searchable(uses_searchable())
                ->dependsOn(['github'], function (Timezone $field, NovaRequest $request, FormData $formData) {
                    switch ($formData->github) {
                        case 'crynobone':
                            $field->setValue('Asia/Kuala_Lumpur');
                            break;
                        default:
                            $field->setValue('UTC');
                    }
                })->default('UTC'),
        ];
    }
}

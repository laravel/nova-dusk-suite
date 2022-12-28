<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class RememberTokenCopier extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * Determine where the action redirection should be without confirmation.
     *
     * @var bool
     */
    public $withoutConfirmation = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        if ($models->count() > 1) {
            return Action::danger('Please run this on only one user resource.');
        }

        $user = $models->first();

        return Action::modal('remember-token-copier', [
            'title' => "{$user->name}'s Remember Token",
            'token' => $user->remember_token,
        ]);
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}

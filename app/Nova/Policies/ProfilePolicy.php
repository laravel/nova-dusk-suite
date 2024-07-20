<?php

namespace App\Nova\Policies;

use App\Models\User;
use App\Nova\Profile;

class ProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ! $user->isBlockedFrom('profile.viewAny')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Profile $profile): bool
    {
        return ! $user->isBlockedFrom('profile.view.'.$profile->id)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ! $user->isBlockedFrom('profile.create')
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Profile $profile): bool
    {
        return ! $user->isBlockedFrom('profile.update.'.$profile->id)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Profile $profile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can add a comment to the podcast.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function addPassport(User $user, Profile $profile)
    {
        return Str::endsWith($user->email, '@laravel.com');
    }
}

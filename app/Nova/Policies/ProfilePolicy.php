<?php

namespace App\Nova\Policies;

use App\Models\User;
use App\Nova\Profile;
use Illuminate\Auth\Access\Response;

class ProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return ! $user->isBlockedFrom('profile.viewAny')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Profile $profile): Response
    {
        return ! $user->isBlockedFrom('profile.view.'.$profile->id)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return ! $user->isBlockedFrom('profile.create')
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Profile $profile): Response
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
     * @return bool
     */
    public function addPassport(User $user, Profile $profile): bool
    {
        return str_ends_with($user->email, '@laravel.com');
    }
}

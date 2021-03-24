<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any posts.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return ! $user->isBlockedFrom('post.viewAny');
    }

    /**
     * Determine whether the user can view the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function view(User $user, Post $post)
    {
        return ! $user->isBlockedFrom('post.view.'.$post->id);
    }

    /**
     * Determine whether the user can create posts.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return ! $user->isBlockedFrom('post.create');
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function update(User $user, Post $post)
    {
        return ! $user->isBlockedFrom('post.update.'.$post->id);
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function replicate(User $user, Post $model)
    {
        return ! $user->isBlockedFrom('post.replicate.'.$model->id);
    }

    /**
     * Determine whether the user can add a comment to the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function addComment(User $user, Post $post)
    {
        return ! $user->isBlockedFrom('post.addComment.'.$post->id);
    }

    /**
     * Determine whether the user can attach any tag to the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function attachAnyTag(User $user, Post $post)
    {
        return ! $user->isBlockedFrom('post.attachAnyTag.'.$post->id);
    }

    /**
     * Determine whether the user can attach a tag to the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @param  \App\Models\Tag  $tag
     * @return mixed
     */
    public function attachTag(User $user, Post $post, Tag $tag)
    {
        return ! $user->isBlockedFrom('post.attachTag.'.$post->id);
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function delete(User $user, Post $post)
    {
        return ! $user->isBlockedFrom('post.delete.'.$post->id);
    }

    /**
     * Determine whether the user can restore the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function restore(User $user, Post $post)
    {
        return ! $user->isBlockedFrom('post.restore.'.$post->id);
    }

    /**
     * Determine whether the user can force delete the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function forceDelete(User $user, Post $post)
    {
        return ! $user->isBlockedFrom('post.forceDelete.'.$post->id);
    }
}

<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

class User extends Authenticatable
{
    use Actionable, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'blocked_from' => 'json',
        'active' => 'boolean',
    ];

    /**
     * Get all of the user's posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get all of the roles attached to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withPivot('notes');
    }

    /**
     * get user address
     */
    public function address()
    {
        return $this->hasOne(Address::class);
    }

    /**
     * Store the actions the user should be blocked from.
     */
    public function shouldBlockFrom(...$block)
    {
        $this->forceFill([
            'blocked_from' => collect($block)->mapWithKeys(function ($block) {
                return [$block => true];
            })->all(),
        ])->save();
    }

    /**
     * Determine if the user is blocked from performing the given action.
     */
    public function isBlockedFrom($action)
    {
        return ! empty($this->blocked_from) &&
               array_key_exists($action, $this->blocked_from);
    }
}

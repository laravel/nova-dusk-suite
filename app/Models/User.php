<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

/**
 * @property bool $active
 * @property bool $exists
 * @property \App\Models\Profile|null $profile
 */
class User extends Authenticatable
{
    use Actionable, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, class-string|string>
     */
    protected $casts = [
        'settings' => Casts\AsArrayObject::class,
        'blocked_from' => 'json',
        'active' => 'boolean',
    ];

    /**
     * Get the user's profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get all of the user's posts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get all of the roles attached to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withPivot('notes');
    }

    /**
     * Get all of the puchases that belong to the book.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function personalBooks()
    {
        return $this->belongsToMany(Book::class, 'book_purchases')
                    ->using(BookPurchase::class)
                    ->withPivot('id', 'price', 'type', 'purchased_at')
                    ->wherePivotIn('type', ['personal'])
                    ->withTimestamps();
    }

    /**
     * Get all of the puchases that belong to the book.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function giftBooks()
    {
        return $this->belongsToMany(Book::class, 'book_purchases')
                    ->using(BookPurchase::class)
                    ->withPivot('id', 'price', 'type', 'purchased_at')
                    ->wherePivotIn('type', ['gift']);
    }

    /**
     * Store the actions the user should be blocked from.
     *
     * @param  string[]  $block
     * @return void
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
     *
     * @param  string  $action
     * @return bool
     */
    public function isBlockedFrom($action)
    {
        return ! empty($this->blocked_from) &&
               array_key_exists($action, $this->blocked_from);
    }
}

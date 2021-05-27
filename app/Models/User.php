<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

class User extends Authenticatable
{
    use Actionable, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'settings' => Casts\AsArrayObject::class,
        'blocked_from' => 'json',
        'active' => 'boolean',
    ];

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

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
     * Get all of the puchases that belong to the book.
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
     */
    public function giftBooks()
    {
        return $this->belongsToMany(Book::class, 'book_purchases')
                    ->using(BookPurchase::class)
                    ->withPivot('id', 'price', 'type', 'purchased_at')
                    ->wherePivotIn('type', ['gift'])
                    ->withTimestamps();
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Nova\Actions\Actionable;

/**
 * @property \App\Models\User|null $user
 * @property int|null $user_id
 */
class Profile extends Model
{
    use HasFactory, Actionable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'languages' => 'array',
        'interests' => 'array',
    ];

    /**
     * Get the user the profile is belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company the profile is belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the profile's passport.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function passport(): HasOne
    {
        return $this->hasOne(Passport::class);
    }

    /**
     * Get the profile latest post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestPost(): HasOne
    {
        return $this->hasOne(Post::class, 'user_id')->latestOfMany();
    }
}

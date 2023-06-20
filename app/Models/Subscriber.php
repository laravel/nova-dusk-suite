<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Nova\Auth\Impersonatable;

class Subscriber extends Authenticatable
{
    use HasFactory, Impersonatable;

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
        'meta' => 'json',
    ];

    /**
     * Determine if the user can impersonate another user.
     *
     * @return bool
     */
    public function canImpersonate(): bool
    {
        return false;
    }
}

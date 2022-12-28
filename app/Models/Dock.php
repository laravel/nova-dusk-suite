<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Dock extends Model
{
    use Actionable, SoftDeletes;

    /**
     * Get all of the ships that belongs to the dock.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }

    /**
     * Get all of the sails that belongs to the dock.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function sails(): HasManyThrough
    {
        return $this->hasManyThrough(Sail::class, Ship::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Dock extends Model
{
    use Actionable, SoftDeletes;

    /**
     * Get all of the ships that belongs to the dock.
     */
    public function ships()
    {
        return $this->hasMany(Ship::class);
    }

    /**
     * Get all of the sails that belongs to the dock.
     */
    public function sails()
    {
        return $this->hasManyThrough(Sail::class, Ship::class);
    }
}

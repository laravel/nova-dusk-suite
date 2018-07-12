<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dock extends Model
{
    /**
     * Get all of the ships that belongs to the dock.
     */
    public function ships()
    {
        return $this->hasMany(Ship::class);
    }
}

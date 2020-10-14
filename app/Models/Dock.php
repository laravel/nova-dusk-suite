<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dock extends Model
{
    use SoftDeletes;

    /**
     * Get all of the ships that belongs to the dock.
     */
    public function ships()
    {
        return $this->hasMany(Ship::class);
    }
}

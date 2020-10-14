<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sail extends Model
{
    /**
     * Get the ship that the sail belongs to.
     */
    public function ship()
    {
        return $this->belongsTo(Ship::class);
    }
}

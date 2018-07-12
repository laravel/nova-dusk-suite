<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    /**
     * Get the dock that the ship belongs to.
     */
    public function dock()
    {
        return $this->belongsTo(Dock::class);
    }
}

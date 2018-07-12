<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ship extends Model
{
    use SoftDeletes;

    /**
     * Get the dock that the ship belongs to.
     */
    public function dock()
    {
        return $this->belongsTo(Dock::class);
    }
}

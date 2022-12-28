<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sail extends Model
{
    /**
     * Get the ship that the sail belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }
}

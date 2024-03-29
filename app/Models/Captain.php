<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Captain extends Model
{
    /**
     * Get all of the ships that belong to the captain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(Ship::class)->withPivot('notes', 'contract');
    }
}

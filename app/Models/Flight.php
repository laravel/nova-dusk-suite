<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Flight extends Model
{
    /**
     * Get all of the flights that belong to the passport.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function passports(): BelongsToMany
    {
        return $this->belongsToMany(Passport::class, 'flight_passports')
            ->withTimestamps();
    }
}

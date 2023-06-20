<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Passport extends Model
{
    use HasFactory;

    /**
     * Get the profile the passport is belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get all of the flights that belong to the passport.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function flights(): BelongsToMany
    {
        return $this->belongsToMany(Flight::class, 'flight_passports')
            ->withTimestamps();
    }
}

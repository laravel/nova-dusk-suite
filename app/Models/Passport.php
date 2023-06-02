<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passport extends Model
{
    use HasFactory;

    /**
     * Get the profile the passport is belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get all of the flights that belong to the passport.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function flights()
    {
        return $this->belongsToMany(Flight::class, 'flight_passports')
            ->withTimestamps();
    }
}

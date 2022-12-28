<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    /**
     * Get the company's photo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function photo(): MorphOne
    {
        return $this->morphOne(Photo::class, 'imageable');
    }

    /**
     * Get all of the company's profiles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}

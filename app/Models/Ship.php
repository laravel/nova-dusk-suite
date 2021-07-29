<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ship extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'departed_at' => 'datetime',
    ];

    /**
     * Get the dock that the ship belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dock()
    {
        return $this->belongsTo(Dock::class);
    }

    /**
     * Get all of the captains for the ship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function captains()
    {
        return $this->belongsToMany(Captain::class)->withPivot('notes', 'contract');
    }

    /**
     * Get all of the sails for the ship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sails()
    {
        return $this->hasMany(Sail::class);
    }
}

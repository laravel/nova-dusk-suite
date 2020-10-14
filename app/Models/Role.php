<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * Get the users the role is attached to.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('notes');
    }
}

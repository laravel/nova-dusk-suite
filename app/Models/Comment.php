<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * Get all of the commentable models.
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}

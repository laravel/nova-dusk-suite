<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Address extends Model
{
    use Searchable, SoftDeletes;

    /**
     * get the user for this address
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

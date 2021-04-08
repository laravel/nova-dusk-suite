<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BookPurchase extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'book_purchases';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'purchased_at' => 'datetime',
    ];
}

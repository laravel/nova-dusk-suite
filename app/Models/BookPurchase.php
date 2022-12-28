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
     * @var array<string, string>
     */
    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::created(function ($model) {
            ray('created', $model);
        });

        static::deleted(function ($model) {
            ray('deleted', $model);
        });
    }
}

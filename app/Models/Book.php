<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', '=', 1);
        });
    }

    /**
     * Get all of the puchases that belong to the book.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchasers()
    {
        return $this->belongsToMany(User::class, 'book_purchases')
                    ->using(BookPurchase::class)
                    ->withPivot('id', 'price', 'type', 'purchased_at')
                    ->withTimestamps();
    }

    /**
     * Get all of the personal puchases that belong to the book.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function personalPurchasers()
    {
        return $this->belongsToMany(User::class, 'book_purchases')
                    ->using(BookPurchase::class)
                    ->withPivot('id', 'price', 'type', 'purchased_at')
                    ->wherePivotIn('type', ['personal'])
                    ->withTimestamps();
    }

    /**
     * Get all of the gift puchases that belong to the book.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function giftPurchasers()
    {
        return $this->belongsToMany(User::class, 'book_purchases')
                    ->using(BookPurchase::class)
                    ->withPivot('id', 'price', 'type')
                    ->wherePivotIn('type', ['gift'])
                    ->withTimestamps();
    }
}

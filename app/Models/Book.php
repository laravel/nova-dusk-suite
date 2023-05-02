<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
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
    public function purchasers(): BelongsToMany
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
    public function personalPurchasers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'book_purchases')
            ->as('purchase')
            ->using(BookPurchase::class)
            ->withPivot('id', 'price', 'type', 'purchased_at')
            ->withPivotValue('type', 'personal')
            ->withTimestamps();
    }

    /**
     * Get all of the gift puchases that belong to the book.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function giftPurchasers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'book_purchases')
            ->as('purchase')
            ->using(BookPurchase::class)
            ->withPivot('id', 'price', 'type')
            ->withPivotValue('type', 'gift')
            ->withTimestamps();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'sku';
    }
}

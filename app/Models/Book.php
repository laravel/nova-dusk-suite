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
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'book_purchases')
                    ->using(BookPurchase::class)
                    ->withPivot('id', 'price', 'type', 'purchased_at')
                    ->withTimestamps();
    }
}

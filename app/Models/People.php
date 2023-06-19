<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property string $name
 * @property \Carbon\CarbonInterface|null $date_of_birth
 */
class People extends Model
{
    use Searchable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, class-string|string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * get employee from people.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

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
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}

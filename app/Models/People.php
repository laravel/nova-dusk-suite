<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    use HasFactory;

    /**
     * get employee from people.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}

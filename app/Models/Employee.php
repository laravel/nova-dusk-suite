<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    /*
     * get people from employee
     */
    public function people()
    {
        return $this->belongsTo(People::class);
    }
}

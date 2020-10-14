<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * Get all of the items that belongs to the dock.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}

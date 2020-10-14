<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    /**
     * Get the invoice this item belongs to.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}

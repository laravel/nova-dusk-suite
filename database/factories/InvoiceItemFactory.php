<?php

use Faker\Generator as Faker;

$factory->define(App\Models\InvoiceItem::class, function (Faker $faker) {
    return [
        'invoice_id' => factory(App\Models\Invoice::class)->create()->id,
    ];
});

<?php

use Faker\Generator as Faker;

$factory->define(App\InvoiceItem::class, function (Faker $faker) {
    return [
        'invoice_id' => factory(App\Invoice::class)->create()->id
    ];
});

<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Book::forceCreate([
            'sku' => 'codehappy',
            'title' => 'Laravel: Code Happy',
            'active' => 1,
        ]);

        Book::forceCreate([
            'sku' => 'codebright',
            'title' => 'Laravel: Code Bright',
            'active' => 1,
        ]);

        Book::forceCreate([
            'sku' => 'laravel',
            'title' => 'Laravel: From Apprentice To Artisan',
            'active' => 1,
        ]);

        Book::forceCreate([
            'sku' => 'laravel-testing-decoded',
            'title' => 'Laravel Testing Decoded',
            'active' => 1,
        ]);
    }
}

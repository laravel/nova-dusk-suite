<?php

namespace Database\Seeders;

use App\Models\Subscriber;
use Illuminate\Database\Seeder;

class SubscriberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = '$2y$10$fW9dlDjKpEbikp9CWY.mpORRqTN6.MjoBFDZpq1QvkJEYCI9Oe1Sq'; // p4ssw0rd

        Subscriber::forceCreate([
            'name' => 'Dries Vints',
            'email' => 'dries@vints.io',
            'password' => $password,
        ]);
    }
}

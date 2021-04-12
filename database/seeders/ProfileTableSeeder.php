<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Profile::forceCreate([
            'user_id' => 1,
            'github_url' => 'https://github.com/taylorotwell',
            'twitter_url' => 'https://twitter.com/taylorotwell',
        ]);

        Profile::forceCreate([
            'user_id' => 2,
            'github_url' => 'https://github.com/themsaid',
            'twitter_url' => 'https://twitter.com/themsaid',
        ]);

        Profile::forceCreate([
            'user_id' => 3,
            'github_url' => 'https://github.com/davidhemphill',
            'twitter_url' => 'https://twitter.com/davidhemphill',
        ]);
    }
}

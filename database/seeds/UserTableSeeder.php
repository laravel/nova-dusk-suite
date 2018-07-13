<?php

use App\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => '$2y$10$oGhaPFdmduG9419sPVkrROY3joLf0iNwxplM5UzcD.x7u06KcNJj6',
            'blocked_from' => [],
        ]);

        User::forceCreate([
            'name' => 'Mohamed Said',
            'email' => 'mohamed@laravel.com',
            'password' => '$2y$10$oGhaPFdmduG9419sPVkrROY3joLf0iNwxplM5UzcD.x7u06KcNJj6',
            'blocked_from' => [],
        ]);

        User::forceCreate([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'password' => '$2y$10$oGhaPFdmduG9419sPVkrROY3joLf0iNwxplM5UzcD.x7u06KcNJj6',
            'blocked_from' => [],
        ]);
    }
}

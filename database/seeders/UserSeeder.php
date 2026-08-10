<?php

namespace Database\Seeders;

use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed a known account that can be used to log in.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@projectflow.test'],
            [
                'name' => 'ProjectFlow Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}

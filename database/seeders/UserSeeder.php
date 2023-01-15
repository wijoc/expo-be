<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create(
            [
                'name' => 'User Test 1',
                'email' => 'user1@test.ty',
                'email_prefix' => 'u***1@t***t.ty',
                'verified_at' => now(),
                'phone' => null,
                'phone_prefix' => null,
                'role' => 'user',
                'password' => Hash::make('qwerty')
            ],
            [
                'name' => 'admin Test 1',
                'email' => 'admin1@test.ty',
                'email_prefix' => 'a***1@t***t.ty',
                'verified_at' => now(),
                'phone' => null,
                'phone_prefix' => null,
                'role' => 'admin',
                'password' => Hash::make('admin')
            ],
        );

        // User::factory()->count(2)->create();
    }
}

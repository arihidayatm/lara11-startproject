<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
        // superadmin
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@mahdev.com',
                'password' => Hash::make ('qwerty123'),
                'role' => 'superadmin',
                'status' => 'active',
            ],
        // admin
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@mahdev.com',
                'password' => Hash::make ('qwerty123'),
                'role' => 'admin',
                'status' => 'active',
            ],
        // user
            [
                'name' => 'User',
                'username' => 'user',
                'email' => 'user@mahdev.com',
                'password' => Hash::make ('qwerty123'),
                'role' => 'user',
                'status' => 'active',
            ]
        ]);
    }
}

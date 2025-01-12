<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Marcel Bueno',
            'email' => 'marcelhxc@gmail.com',
            'password' => Hash::make('pass@1243'),
        ]);
    }
}

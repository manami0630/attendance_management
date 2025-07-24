<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '山田太郎',
                'email' => 'yamada@example.com',
                'password' => bcrypt('password123'),
                'role' => 'user',
            ],
            [
                'name' => '山本太郎',
                'email' => 'admin@example.com',
                'password' => bcrypt('password456'),
                'role' => 'admin',
            ],
        ]);
    }
}

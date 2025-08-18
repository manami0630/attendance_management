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
        $now = new \DateTime();

        DB::table('users')->insert([
            [
                'name' => '山田太郎',
                'email' => 'yamada@example.com',
                'password' => bcrypt('password123'),
                'email_verified_at' => $now->format('Y-m-d H:i'),
                'role' => 'user',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '山本太郎',
                'email' => 'yamamoto@example.com',
                'password' => bcrypt('password456'),
                'email_verified_at' => $now->format('Y-m-d H:i'),
                'role' => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}


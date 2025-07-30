<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserBreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('user_break_times')->insert([
            [
                'user_id' => 1,
                'date' => $now->subDays(1)->format('Y-m-d'),
                'break_start_time' => $now->subDays(1)->format('H:i:s'),
                'break_end_time' => $now->subDays(1)->addHours(1)->format('H:i:s'),
            ],
            [
                'user_id' => 2,
                'date' => $now->subDays(1)->format('Y-m-d'),
                'break_start_time' => $now->subDays(1)->format('H:i:s'),
                'break_end_time' => $now->subDays(1)->addHours(1)->format('H:i:s'),
            ],
        ]);
    }
}

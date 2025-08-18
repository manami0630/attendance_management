<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserAttendanceRecordsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('user_attendance_records')->insert([
            [
                'user_id' => 1,
                'date' => $now->subDays(1)->format('Y-m-d'),
                'clock_in_time' => $now->subDays(1)->format('H:i:s'),
                'clock_out_time' => $now->subDays(1)->addHours(9)->format('H:i:s'),
                'status' => '退勤済',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'user_id' => 2,
                'date' => $now->subDays(1)->format('Y-m-d'),
                'clock_in_time' => $now->subDays(1)->format('H:i:s'),
                'clock_out_time' => $now->subDays(1)->addHours(9)->format('H:i:s'),
                'status' => '退勤済',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
        ]);
    }
}

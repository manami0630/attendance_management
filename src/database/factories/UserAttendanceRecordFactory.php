<?php

namespace Database\Factories;

use App\Models\UserAttendanceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAttendanceRecordFactory extends Factory
{
    protected $model = UserAttendanceRecord::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'date' => now()->toDateString(),
            'clock_in_time' => null,
            'clock_out_time' => null,
            'status' => '勤務外',
        ];
    }
}
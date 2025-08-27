<?php

namespace Database\Factories;

use App\Models\UserBreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserBreakTimeFactory extends Factory
{
    protected $model = UserBreakTime::class;

    public function definition()
    {
        return [
            'user_id' => 1,
            'user_attendance_record_id' => 1,
            'break_start_time' => $this->faker->time(),
            'break_end_time' => $this->faker->time(),
            'date' => $this->faker->date(),
        ];
    }
}
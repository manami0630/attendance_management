<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserBreakApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserBreakApplicationFactory extends Factory
{
    protected $model = UserBreakApplication::class;

    public function definition()
    {
        return [
            'application_id' => \App\Models\UserApplication::factory(),
            'break_start_time' => $this->faker->time(),
            'break_end_time' => $this->faker->time(),
            'user_id' => User::factory(),
        ];
    }
}
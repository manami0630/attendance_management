<?php

namespace Database\Factories;

use App\Models\UserApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserApplicationFactory extends Factory
{
    protected $model = UserApplication::class;

    public function definition()
    {
        return [
            'status' => '承認待ち',
            'user_id' => User::factory(),
            'target_date' => $this->faker->date(),
            'reason' => $this->faker->sentence(),
        ];
    }
}
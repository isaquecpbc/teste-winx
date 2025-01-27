<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ScheduleFactory extends Factory
{
    protected $model = \App\Models\Schedule::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'type' => 'meeting',
            'description' => $this->faker->paragraph,
            'status' => $this->faker->boolean,
            'start_at' => Carbon::now()->addDays(rand(1, 14))->toDateTimeString(),
            'conclusion_at' => Carbon::now()->addDays(rand(15, 30))->toDateTimeString(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}


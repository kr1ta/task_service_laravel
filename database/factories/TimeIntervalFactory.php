<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeIntervalFactory extends Factory
{
    public function definition(): array
    {
        $types = ['task', 'habit'];
        $type = $this->faker->randomElement($types);

        $id = $this->faker->numberBetween(1, 100);

        $startTime = $this->faker->dateTimeBetween('-1 month', 'today');

        $duration = $this->faker->numberBetween(10, 1000);

        $finishTime = Carbon::instance($startTime)->addMinutes($duration);

        return [
            'intervalable_id' => $id,
            'intervalable_type' => $type,
            'start_time' => $startTime,
            'finish_time' => $finishTime,
            'duration' => $duration,
            'created_at' => $this->faker->dateTimeBetween('-6 days', 'now'),
        ];
    }
}

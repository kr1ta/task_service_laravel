<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TimeIntervalFactory extends Factory
{
    public function definition(): array
    {
        $duration = $this->faker->numberBetween(10, 100);

        $task_id = $this->faker->optional(0.5)->numberBetween(1, 10);

        if (!$task_id) {
            $habit_id = $this->faker->numberBetween(1, 10);
        }

        return [
            #'task_id' => $task_id,
            #'habit_id' => $task_id ? null : $habit_id,
            'start_time' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'duration' => $duration,
            #'deleted_at' => null,
        ];
    }
}

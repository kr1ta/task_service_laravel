<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition()
    {
        $finishAt = $this->faker->optional(0.7)->dateTimeBetween('+1 day', '+1 week');
        $status = $finishAt ? 'Completed' : $this->faker->randomElement(['In Progress']);

        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            // 'finish_at' => $finishAt,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
            // 'deleted_at' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HabitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}

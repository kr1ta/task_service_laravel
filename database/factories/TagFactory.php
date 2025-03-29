<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'name' => $this->faker->unique()->word,
            'deleted_at' => null,
        ];
    }
}

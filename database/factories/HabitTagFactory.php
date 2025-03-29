<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Habit;

class HabitTagFactory extends Factory
{
    public function definition(): array
    {
        $habitIds = Habit::pluck('id')->toArray();
        $tagIds = Habit::pluck('id')->toArray();

        return [
            'habit_id' => $this->faker->randomElement($habitIds),
            'tag_id' => $this->faker->randomElement($tagIds),
        ];
    }
}

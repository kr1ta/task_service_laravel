<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskTagFactory extends Factory
{
    public function definition(): array
    {
        $taskIds = Task::pluck('id')->toArray();
        $tagIds = Tag::pluck('id')->toArray();

        return [
            'task_id' => $this->faker->randomElement($taskIds),
            'tag_id' => $this->faker->randomElement($tagIds),
        ];
    }
}

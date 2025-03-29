<?php

namespace Database\Factories;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Models\Tag;

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

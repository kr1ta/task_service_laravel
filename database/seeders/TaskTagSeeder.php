<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaskTag;

class TaskTagSeeder extends Seeder
{
    public function run(): void
    {
        TaskTag::factory()->count(10)->create();
    }
}

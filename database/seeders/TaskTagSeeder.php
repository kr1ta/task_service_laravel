<?php

namespace Database\Seeders;

use App\Models\TaskTag;
use Illuminate\Database\Seeder;

class TaskTagSeeder extends Seeder
{
    public function run(): void
    {
        TaskTag::factory()->count(10)->create();
    }
}

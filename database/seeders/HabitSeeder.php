<?php

namespace Database\Seeders;

use App\Models\Habit;
use Illuminate\Database\Seeder;

class HabitSeeder extends Seeder
{
    public function run(): void
    {
        Habit::factory()->count(10)->create();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Habit;

class HabitSeeder extends Seeder
{
    public function run(): void
    {
        Habit::factory()->count(10)->create();
    }
}

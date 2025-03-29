<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HabitTag;

class HabitTagSeeder extends Seeder
{
    public function run(): void
    {
        HabitTag::factory()->count(10)->create();
    }
}

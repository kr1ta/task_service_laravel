<?php

namespace Database\Seeders;

use App\Models\HabitTag;
use Illuminate\Database\Seeder;

class HabitTagSeeder extends Seeder
{
    public function run(): void
    {
        HabitTag::factory()->count(10)->create();
    }
}

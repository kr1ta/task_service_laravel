<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimeInterval;

class TimeIntervalSeeder extends Seeder
{
    public function run(): void
    {
        TimeInterval::factory()->count(10)->create();
    }
}

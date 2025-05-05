<?php

namespace Database\Seeders;

use App\Models\TimeInterval;
use Illuminate\Database\Seeder;

class TimeIntervalSeeder extends Seeder
{
    public function run(): void
    {
        TimeInterval::factory()->count(20)->create();
    }
}

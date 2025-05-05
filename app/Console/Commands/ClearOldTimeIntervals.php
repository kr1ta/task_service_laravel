<?php

namespace App\Console\Commands;

use App\Models\TimeInterval;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearOldTimeIntervals extends Command
{
    protected $signature = 'ti:clear {--force}';

    protected $description = 'Удаляет все временные интервалы, обновленные до сегодняшнего дня';

    public function handle()
    {
        if (! $this->option('force')) {
            $confirm = $this->confirm('Вы уверены, что хотите удалить старые записи? Это действие необратимо.');
            if (! $confirm) {
                return;
            }
        }

        $count = TimeInterval::whereDate('created_at', '<', Carbon::today())->delete();

        $this->info("Удалено {$count} записей.");
    }
}

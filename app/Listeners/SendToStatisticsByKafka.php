<?php

namespace App\Listeners;

use App\Jobs\SendToStatisticsByKafkaJob;

class SendToStatisticsByKafka
{
    public function handle(object $event): void
    {
        SendToStatisticsByKafkaJob::dispatch($event);
    }
}
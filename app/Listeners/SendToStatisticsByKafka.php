<?php

namespace App\Listeners;

use App\Jobs\SendToStatisticsByKafkaJob;

class SendToStatisticsByKafka
{
    public function handle(object $event): void
    {
        \Log::info("In the Listener, type: {$event->update_type}");

        SendToStatisticsByKafkaJob::dispatch($event->interval, $event->update_type, $event->unspent_time);
        // SendToStatisticsByKafkaJob::dispatch($interval)
        // ->delay(now()->addSeconds($interval->duration));
    }
}
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RdKafka\Producer;
use RdKafka\Conf;

class SendToStatisticsByKafkaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function handle(): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', env('KAFKA_BROKER'));

        $producer = new Producer($conf);
        $topic = $producer->newTopic('stat');

        \Log::info("in job, " . json_encode($this->message));

        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($this->message));

        while ($producer->getOutQLen() > 0) {
            $producer->poll(50);
        }
    }
}

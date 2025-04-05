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

    public $interval;
    public $update_type;
    public $unspent_time;
    public $tag_id;

    public function __construct($interval, $update_type, $unspent_time)
    {
        $this->interval = $interval;
        $this->update_type = $update_type;
        $this->unspent_time = $unspent_time;
        // $this->tag_id = $tag_id;
    }

    public function handle(): void
    {
        \Log::info("In the job, type: {$this->update_type}");
        $conf = new Conf();
        $conf->set('metadata.broker.list', 'localhost:9092');

        $producer = new Producer($conf);
        $topic = $producer->newTopic('stat');

        \Log::info("unspent time in Job: {$this->unspent_time}");

        $message = json_encode([
            'interval' => $this->interval,
            'update_type' => $this->update_type,
            // 'tag_id' => $this->tag_id,
            'unspent_time' => $this->update_type == 'stop' ? $this->unspent_time : 0,
        ]);

        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

        while ($producer->getOutQLen() > 0) {
            $producer->poll(50);
        }
    }
}

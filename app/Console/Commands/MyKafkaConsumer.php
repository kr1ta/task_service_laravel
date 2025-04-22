<?php

namespace App\Console\Commands;

use App\Models\Habit;
use App\Models\Task;
use Illuminate\Console\Command;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class MyKafkaConsumer extends Command
{
    protected $signature = 'kafka:consume';

    protected $description = 'Consume messages from Kafka';

    public function handle()
    {
        $conf = new Conf;
        $conf->set('group.id', 'task_service_group');
        $conf->set('metadata.broker.list', env('KAFKA_BROKER'));
        $conf->set('auto.offset.reset', 'earliest');

        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe(['user-created']); // Название топика

        echo "Waiting for messages...\n";

        while (true) {
            $message = $consumer->consume(120 * 1000); // Таймаут в миллисекундах

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $payload = json_decode($message->payload, true);
                    $this->processMessage($payload);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "No more messages; will wait for more\n";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Timed out\n";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
            }
        }
    }

    private function processMessage($payload)
    {
        $userId = $payload['user_id'];
        $this->info("Received user created event for user ID: $userId");

        Task::create(['user_id' => $userId, 'title' => 'Initial task', 'description' => 'Hello! Glad to see ya, it\'s ypur first task!']);
        Habit::create(['user_id' => $userId, 'title' => 'Walk', 'description' => 'It\'s your first habit!']);
    }
}

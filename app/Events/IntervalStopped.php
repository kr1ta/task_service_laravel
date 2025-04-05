<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IntervalStopped
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $interval;
    public $unspent_time = 0;
    public $update_type = 'stop';

    public function __construct($interval, $unspent_time)
    {
        \Log::info("In the IntervalStarted event");
        $this->interval = $interval;
        $this->unspent_time = $unspent_time;
    }
}

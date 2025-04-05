<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IntervalStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $interval;
    public $unspent_time = 0;
    public $update_type = 'start';

    public function __construct($interval)
    {
        \Log::info("In the IntervalStarted event");
        $this->interval = $interval;
    }
}

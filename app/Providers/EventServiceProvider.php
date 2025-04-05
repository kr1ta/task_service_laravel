<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use App\Events\IntervalStarted;
use App\Events\IntervalStopped;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\SendToStatisticsByKafka;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        IntervalStarted::class => [
            SendToStatisticsByKafka::class,
        ],

        IntervalStopped::class => [
            SendToStatisticsByKafka::class,
        ],

    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

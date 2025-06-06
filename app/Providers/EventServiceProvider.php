<?php

namespace App\Providers;

use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use App\Models\Task;
use App\Observers\PotentialTradeRouteObserver;
use App\Observers\ShipObserver;
use App\Observers\TaskObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        PotentialTradeRoute::observe(PotentialTradeRouteObserver::class);
        Ship::observe(ShipObserver::class);
        Task::observe(TaskObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

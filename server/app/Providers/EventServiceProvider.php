<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\CustodyTransferred;
use App\Listeners\HandleCustodyTransferred;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, list<class-string>>
     */
    protected $listen = [
        CustodyTransferred::class => [
            HandleCustodyTransferred::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Listeners are automatically registered by Laravel.
    }
}

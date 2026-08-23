<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\ClaimApproved;
use App\Events\ClaimRejected;
use App\Events\ClaimSubmitted;
use App\Events\CustodyTransferred;
use App\Events\ItemReported;
use App\Events\ItemReturned;
use App\Listeners\DispatchMatchSuggestion;
use App\Listeners\HandleCustodyTransferred;
use App\Listeners\NotifyClaimApproved;
use App\Listeners\NotifyClaimRejected;
use App\Listeners\NotifyClaimSubmitted;
use App\Listeners\NotifyItemReturned;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, list<class-string>>
     */
    protected $listen = [
        // Custody
        CustodyTransferred::class => [
            HandleCustodyTransferred::class,
        ],

        // Claims
        ClaimSubmitted::class => [
            NotifyClaimSubmitted::class,
        ],
        ClaimApproved::class => [
            NotifyClaimApproved::class,
        ],
        ClaimRejected::class => [
            NotifyClaimRejected::class,
        ],

        // Items
        ItemReported::class => [
            DispatchMatchSuggestion::class,
        ],
        ItemReturned::class => [
            NotifyItemReturned::class,
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

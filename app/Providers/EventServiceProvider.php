<?php

namespace App\Providers;

use App\Events\Core\CoreTransactionRecorded;
use App\Listeners\Core\HandleCoreTransactionRecorded;
use App\Listeners\Core\RecordCoreRevenueEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CoreTransactionRecorded::class => [
            HandleCoreTransactionRecorded::class,
            RecordCoreRevenueEvent::class,
        ],
    ];
}

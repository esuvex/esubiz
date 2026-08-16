<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Event;

class CoreEventDispatcher
{
    /**
     * Dispatch a Core synchronization event.
     *
     * Every Core feature, module, addon or integration
     * can publish through this service without depending
     * directly on another feature.
     */
    public function dispatch(
        string $event,
        array $payload = []
    ): void {
        Event::dispatch($event, $payload);
    }
}

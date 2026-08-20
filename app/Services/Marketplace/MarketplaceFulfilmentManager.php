<?php

namespace App\Services\Marketplace;

use RuntimeException;

class MarketplaceFulfilmentManager
{
    protected array $handlers = [];

    public function register(string $productType, object $handler): void
    {
        $this->handlers[$productType] = $handler;
    }

    public function fulfil(object $order, object $listing): array
    {
        $handler = $this->handlers[$listing->product_type] ?? null;

        if (!$handler) {
            $class = 'App\\Services\\Marketplace\\Handlers\\'
                . str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $listing->product_type)))
                . 'FulfilmentHandler';

            if (class_exists($class)) {
                $handler = app($class);
                $this->register($listing->product_type, $handler);
            }
        }

        if (!$handler || !method_exists($handler, 'fulfil')) {
            throw new RuntimeException(
                "No marketplace fulfilment handler registered for product type [{$listing->product_type}]."
            );
        }

        return $handler->fulfil($order, $listing);
    }
}

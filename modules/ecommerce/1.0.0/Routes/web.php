<?php

use Illuminate\Support\Facades\Route;
use Esubiz\Modules\Ecommerce\Http\Controllers\EcommerceController;

Route::middleware(['web', 'auth'])
    ->prefix('admin/ecommerce')
    ->name('core.ecommerce.')
    ->group(function () {
        Route::get(
            '/',
            fn (EcommerceController $controller) =>
                $controller->page('dashboard')
        )->name('dashboard');

        Route::get(
            '/products',
            [EcommerceController::class, 'products']
        )->name('products.index');

        foreach ([
            'categories',
            'inventory',
            'collections',
            'orders',
            'customers',
            'discounts',
            'checkout-links',
            'shipping',
            'returns',
            'reviews',
            'currencies',
            'payments',
            'settings',
            'reports',
        ] as $page) {
            Route::get(
                '/' . $page,
                fn (EcommerceController $controller) =>
                    $controller->page($page)
            )->name($page . '.index');
        }
    });

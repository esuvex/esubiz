<?php

use Esubiz\SsoClient\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/sso/login', [SsoController::class, 'login'])
        ->name('esubiz.sso.login');

    Route::get('/sso/callback', [SsoController::class, 'callback'])
        ->name('esubiz.sso.callback');

    Route::get('/sso/user', [SsoController::class, 'user'])
        ->name('esubiz.sso.user');

    Route::post('/sso/logout', [SsoController::class, 'logout'])
        ->name('esubiz.sso.logout');
});

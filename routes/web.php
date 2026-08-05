<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\WebsiteWizardController;
use App\Http\Controllers\WebsiteThemeController;
use App\Http\Controllers\WebsitePlanController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\SaaS\DashboardController as SaaSDashboardController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'frontend.home')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Main Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Dashboards
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/saas/dashboard', [SaaSDashboardController::class, 'index'])
        ->name('saas.dashboard');

    Route::get('/developer/dashboard', [DeveloperDashboardController::class, 'index'])
        ->name('developer.dashboard');

    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])
        ->name('user.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Website Builder Wizard
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/create', [WebsiteWizardController::class, 'create'])
        ->name('websites.create');

    Route::get('/websites/theme', [WebsiteThemeController::class, 'index'])
        ->name('websites.theme');

    Route::get('/websites/plan', [WebsitePlanController::class, 'index'])
        ->name('websites.plan');

    Route::post('/websites', [WebsiteController::class, 'store'])
        ->name('websites.store');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

require __DIR__.'/auth.php';

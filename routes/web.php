<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\WebsiteWizardController;

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
        return view('dashboard');
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

    Route::post('/websites/create', [WebsiteWizardController::class, 'store'])
        ->name('websites.store');

    /*
    |--------------------------------------------------------------------------
    | Continue Draft
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/continue', [WebsiteWizardController::class, 'continue'])
        ->name('websites.continue');

    /*
    |--------------------------------------------------------------------------
    | Wizard Steps
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/theme', [WebsiteWizardController::class, 'theme'])
        ->name('websites.theme');

    Route::get('/websites/{website}/information', [WebsiteWizardController::class, 'information'])
        ->name('websites.information');

    Route::get('/websites/{website}/plan', [WebsiteWizardController::class, 'plan'])
        ->name('websites.plan');

    /*
    |--------------------------------------------------------------------------
    | NEW STEP 5 - Website Address
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/domain', [WebsiteWizardController::class, 'domain'])
        ->name('websites.domain');

    /*
    |--------------------------------------------------------------------------
    | Step 6
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/address', [WebsiteWizardController::class, 'address'])
        ->name('websites.address');

    /*
    |--------------------------------------------------------------------------
    | Step 7
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/administrator', [WebsiteWizardController::class, 'administrator'])
        ->name('websites.administrator');

    /*
    |--------------------------------------------------------------------------
    | Step 8
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/review', [WebsiteWizardController::class, 'review'])
        ->name('websites.review');

    Route::post('/websites/{website}/deploy', [WebsiteWizardController::class, 'deploy'])
        ->name('websites.deploy');

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

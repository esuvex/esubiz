<?php

use App\Http\Controllers\Admin\FinancialReportController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\User\WebsiteManagementController;
use App\Http\Controllers\WebsiteWizardController;
use App\Http\Controllers\AccountModeController;
use App\Http\Controllers\DeveloperAccountController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\TenantWebsiteController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\WebsiteTypeController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\Developer\BuilderController as DeveloperBuilderController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\WebsiteController as UserWebsiteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'frontend.home')->name('home');

Route::domain('www.esubiz.com')
    ->get('/', fn () => view('frontend.home'))
    ->name('www.home');

/*
|--------------------------------------------------------------------------
| Public Tenant Website
|--------------------------------------------------------------------------
*/

Route::domain('{subdomain}.esubiz.com')
    ->where(['subdomain' => '(?!www$)(?!esubiz$)[a-zA-Z0-9-]+'])
    ->group(function () {
        Route::get('/', [TenantWebsiteController::class, 'home'])
            ->name('tenant.website.home');
    });



Route::get('/oauth/authorize', [SsoController::class, 'authorize'])
    ->middleware('auth')
    ->name('sso.authorize');


Route::get('/sso/callback', [SsoController::class, 'callback'])
    ->name('sso.callback');
Route::post('/oauth/token', [SsoController::class, 'token'])
    ->name('sso.token');

Route::get('/oauth/user', [SsoController::class, 'user'])
    ->name('sso.user');


Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Main Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        $mode = session('account_mode', 'user');

        return match ($mode) {
            'admin' => redirect()->route('admin.dashboard'),
            'developer' => redirect()->route('developer.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    })->name('dashboard');

    Route::post('/account/mode', [AccountModeController::class, 'switch'])
        ->name('account.mode.switch');


    Route::get('/developer-account', [DeveloperAccountController::class, 'create'])
        ->name('developer-account.create');
    Route::post('/developer-account', [DeveloperAccountController::class, 'store'])
        ->name('developer-account.store');

    /*
    |--------------------------------------------------------------------------
    | Dashboards
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/website-types', [WebsiteTypeController::class, 'index'])
    ->middleware(['permission:roles.view', 'account-mode:admin'])
    ->name('admin.website-types.index');

    Route::get('/admin/website-types/create', [WebsiteTypeController::class, 'create'])
        ->middleware(['permission:roles.view', 'account-mode:admin'])
        ->name('admin.website-types.create');

    Route::post('/admin/website-types', [WebsiteTypeController::class, 'store'])
        ->middleware(['permission:roles.view', 'account-mode:admin'])
        ->name('admin.website-types.store');

    Route::get('/admin/website-types/{websiteType}/edit', [WebsiteTypeController::class, 'edit'])
        ->middleware(['permission:roles.view', 'account-mode:admin'])
        ->name('admin.website-types.edit');

    Route::put('/admin/website-types/{websiteType}', [WebsiteTypeController::class, 'update'])
        ->middleware(['permission:roles.view', 'account-mode:admin'])
        ->name('admin.website-types.update');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware(['permission:roles.view', 'account-mode:admin'])
        ->name('admin.dashboard');

    Route::get('/developer/dashboard', [DeveloperDashboardController::class, 'index'])
        ->middleware(['permission:developer.console', 'account-mode:developer'])
        ->name('developer.dashboard');

    Route::get('/developer/builder', [DeveloperBuilderController::class, 'index'])
        ->middleware('account-mode:developer')
        ->name('developer.builder');

    Route::post('/developer/builder/create', [DeveloperBuilderController::class, 'create'])
        ->middleware('account-mode:developer')
        ->name('developer.builder.create');

    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])
        ->middleware(['permission:websites.manage', 'account-mode:user'])
        ->name('user.dashboard');

    Route::get('/websites', [UserWebsiteController::class, 'index'])
        ->name('user.websites.index');

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
    | My Website Management
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/edit', [WebsiteManagementController::class, 'edit'])
        ->name('user.websites.edit');

    Route::put('/websites/{website}/edit', [WebsiteManagementController::class, 'update'])
        ->name('user.websites.update');


    /*
    |--------------------------------------------------------------------------
    | Wizard Steps
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/theme', [WebsiteWizardController::class, 'theme'])
        ->name('websites.theme');

Route::post('/websites/{website}/theme', [WebsiteWizardController::class, 'theme'])
    ->name('websites.theme.save');

    Route::get('/websites/{website}/information', [WebsiteWizardController::class, 'information'])
        ->name('websites.information');

    Route::post('/websites/{website}/information', [WebsiteWizardController::class, 'information'])
        ->name('websites.information.save');

    Route::get('/websites/check-subdomain', [WebsiteWizardController::class, 'checkSubdomain'])
        ->name('websites.check-subdomain');

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

Route::get('/admin/financial-reports', [FinancialReportController::class, 'index'])
    ->name('admin.financial-reports');
Route::get('/admin/financial-reports/csv', [\App\Http\Controllers\Admin\FinancialReportController::class, 'csv'])
    ->name('admin.financial-reports.csv');

Route::get('/admin/financial-reports/pdf', [\App\Http\Controllers\Admin\FinancialReportController::class, 'pdf'])
    ->name('admin.financial-reports.pdf');

Route::post('/admin/financial-reports/email', [\App\Http\Controllers\Admin\FinancialReportController::class, 'email'])
    ->name('admin.financial-reports.email');



Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/credit-packages', [\App\Http\Controllers\Admin\CreditPackageController::class, 'index'])
        ->name('credit-packages.index');
    Route::post('/credit-packages', [\App\Http\Controllers\Admin\CreditPackageController::class, 'store'])
        ->name('credit-packages.store');
    Route::post('/credit-packages/{id}/toggle', [\App\Http\Controllers\Admin\CreditPackageController::class, 'toggle'])
        ->name('credit-packages.toggle');
    Route::delete('/credit-packages/{id}', [\App\Http\Controllers\Admin\CreditPackageController::class, 'destroy'])
        ->name('credit-packages.destroy');
});

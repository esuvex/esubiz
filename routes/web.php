<?php

use App\Http\Controllers\MarketplaceController;

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



Route::middleware(['auth'])->prefix('admin/core-addons')->name('admin.core-addons.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\CoreAddonController::class, 'index'])
        ->name('index');

    Route::post('/addons', [\App\Http\Controllers\Admin\CoreAddonController::class, 'storeAddon'])
        ->name('addons.store');

    Route::put('/addons/{addon}', [\App\Http\Controllers\Admin\CoreAddonController::class, 'updateAddon'])
        ->name('addons.update');

    Route::post('/addons/{addon}/toggle', [\App\Http\Controllers\Admin\CoreAddonController::class, 'toggleAddon'])
        ->name('addons.toggle');

    Route::post('/bundles', [\App\Http\Controllers\Admin\CoreAddonController::class, 'storeBundle'])
        ->name('bundles.store');

    Route::put('/bundles/{bundle}', [\App\Http\Controllers\Admin\CoreAddonController::class, 'updateBundle'])
        ->name('bundles.update');

    Route::post('/bundles/{bundle}/toggle', [\App\Http\Controllers\Admin\CoreAddonController::class, 'toggleBundle'])
        ->name('bundles.toggle');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\CoreAddonController::class, 'destroyAddon'])
        ->name('addons.destroy');

    Route::delete('/bundles/{bundle}', [\App\Http\Controllers\Admin\CoreAddonController::class, 'destroyBundle'])
        ->name('bundles.destroy');

});

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Site Settings → Payment Gateways
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/site-settings/payment-gateways', [\App\Http\Controllers\Admin\OnlinePaymentController::class, 'gateways'])
        ->name('admin.payment-gateways.index');

    Route::prefix('admin/site-settings/payment-gateways')
        ->name('admin.payment-gateways.')
        ->group(function () {

            Route::get('/online', [
                \App\Http\Controllers\Admin\OnlinePaymentController::class,
                'index'
            ])->name('online.index');

            Route::post('/online/{provider}', [
                \App\Http\Controllers\Admin\OnlinePaymentController::class,
                'update'
            ])->name('online.update');

            Route::get('/offline-payments', [
                \App\Http\Controllers\Admin\OfflinePaymentReviewController::class,
                'index'
            ])->name('offline-payments.index');

            Route::post('/offline-payments/{attempt}/mark-paid', [
                \App\Http\Controllers\Admin\OfflinePaymentReviewController::class,
                'markPaid'
            ])->name('offline-payments.mark-paid');

            Route::post('/offline-payments/{attempt}/reject', [
                \App\Http\Controllers\Admin\OfflinePaymentReviewController::class,
                'reject'
            ])->name('offline-payments.reject');



            Route::get('/offline', [
                \App\Http\Controllers\Admin\OfflinePaymentController::class,
                'index'
            ])->name('offline.index');

            Route::post('/offline', [
                \App\Http\Controllers\Admin\OfflinePaymentController::class,
                'store'
            ])->name('offline.store');

            Route::post('/offline/{method}', [
                \App\Http\Controllers\Admin\OfflinePaymentController::class,
                'update'
            ])->name('offline.update');

            Route::delete('/offline/{method}', [
                \App\Http\Controllers\Admin\OfflinePaymentController::class,
                'destroy'
            ])->name('offline.destroy');

        });

    Route::post('/marketplace/checkout', [\App\Http\Controllers\MarketplaceController::class, 'checkout'])
        ->name('marketplace.checkout.create');

    Route::get('/marketplace/checkout', [\App\Http\Controllers\MarketplaceController::class, 'pendingCheckouts'])
        ->name('marketplace.checkout.index');



    Route::get('/marketplace/checkout/{order}', [\App\Http\Controllers\MarketplaceController::class, 'checkoutPage'])
        ->name('marketplace.checkout');

    Route::get('/marketplace/orders/{order}/payment-status', [\App\Http\Controllers\MarketplaceController::class, 'paymentStatus'])
        ->name('marketplace.payment-status');

    Route::get('/marketplace', [\App\Http\Controllers\MarketplaceController::class, 'index'])
        ->name('marketplace.index');
    Route::get('/marketplace/addons', [\App\Http\Controllers\MarketplaceController::class, 'addons'])
        ->name('marketplace.addons');

});

Route::middleware(['auth', 'account-mode:developer'])->group(function () {
    Route::get('/developer/marketplace', [\App\Http\Controllers\MarketplaceController::class, 'developer'])
        ->name('developer.marketplace');
    Route::get('/developer/marketplace/addons', [\App\Http\Controllers\MarketplaceController::class, 'developerAddons'])
        ->name('developer.marketplace.addons');

});

Route::middleware(['auth'])->post(
    '/admin/core-addons/fulfil',
    [\App\Http\Controllers\Admin\CoreAddonFulfilmentController::class, 'fulfil']
)->name('admin.core-addons.fulfil');



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

    Route::get('/user/financials', [\App\Http\Controllers\User\DashboardController::class, 'financialRecords'])
    ->middleware('auth')
    ->name('user.financial.records');


Route::get('/user/financials/csv', [\App\Http\Controllers\User\DashboardController::class, 'downloadFinancialRecordsCsv'])
    ->middleware('auth')
    ->name('user.financial.records.csv');

Route::get('/user/financials/pdf', [\App\Http\Controllers\User\DashboardController::class, 'downloadFinancialRecordsPdf'])
    ->middleware('auth')
    ->name('user.financial.records.pdf');

Route::post('/user/financials/email', [\App\Http\Controllers\User\DashboardController::class, 'emailFinancialRecords'])
    ->middleware('auth')
    ->name('user.financial.records.email');

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

Route::get('/developer/financials', [\App\Http\Controllers\Developer\DashboardController::class, 'financialRecords'])
    ->middleware(['auth'])
    ->name('developer.financial.records');

Route::get('/developer/financials/csv', [\App\Http\Controllers\Developer\DashboardController::class, 'downloadFinancialRecordsCsv'])
    ->middleware(['auth'])
    ->name('developer.financial.records.csv');

Route::get('/developer/financials/pdf', [\App\Http\Controllers\Developer\DashboardController::class, 'downloadFinancialRecordsPdf'])
    ->middleware(['auth'])
    ->name('developer.financial.records.pdf');

Route::post('/developer/financials/email', [\App\Http\Controllers\Developer\DashboardController::class, 'emailFinancialRecords'])
    ->middleware(['auth'])
    ->name('developer.financial.records.email');

Route::get('/admin/financial-reports', [FinancialReportController::class, 'index'])
    ->name('admin.financial-reports');
Route::get('/admin/financial-reports/csv', [\App\Http\Controllers\Admin\FinancialReportController::class, 'csv'])
    ->name('admin.financial-reports.csv');

Route::get('/admin/financial-reports/pdf', [\App\Http\Controllers\Admin\FinancialReportController::class, 'pdf'])
    ->name('admin.financial-reports.pdf');

Route::post('/admin/financial-reports/email', [\App\Http\Controllers\Admin\FinancialReportController::class, 'email'])
    ->name('admin.financial-reports.email');



Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/core/features', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'index'])
        ->name('core-features.index');

    Route::post('/core/features', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'storeFeature'])
        ->name('core-features.store');

    Route::post('/core/features/{id}/update', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'updateFeature'])
        ->name('core-features.update');

    Route::post('/core/features/{id}/toggle', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'toggleFeature'])
        ->name('core-features.toggle');

    Route::post('/core/features/{featureId}/limits', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'storeLimit'])
        ->name('core-features.limits.store');

    Route::post('/core/features/limits/{id}/update', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'updateLimit'])
        ->name('core-features.limits.update');

    Route::post('/core/features/limits/{id}/toggle', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'toggleLimit'])
        ->name('core-features.limits.toggle');

    Route::get('/credit-packages', [\App\Http\Controllers\Admin\CreditPackageController::class, 'index'])
        ->name('credit-packages.index');

    Route::post('/credit-packages', [\App\Http\Controllers\Admin\CreditPackageController::class, 'store'])
        ->name('credit-packages.store');

    Route::post('/credit-packages/create-product', [\App\Http\Controllers\Admin\CreditPackageController::class, 'createProduct'])
        ->name('credit-packages.create-product');

    Route::get('/credit-packages/products/{id}/edit', [\App\Http\Controllers\Admin\CreditPackageController::class, 'editProduct'])
        ->name('credit-packages.edit-product');

    Route::post('/credit-packages/products/{id}', [\App\Http\Controllers\Admin\CreditPackageController::class, 'updateProduct'])
        ->name('credit-packages.update-product');

    Route::get('/credit-packages/{id}/edit', [\App\Http\Controllers\Admin\CreditPackageController::class, 'editPackage'])
        ->name('credit-packages.edit');

    Route::post('/credit-packages/{id}/update', [\App\Http\Controllers\Admin\CreditPackageController::class, 'updatePackage'])
        ->name('credit-packages.update');

    Route::post('/credit-packages/{id}/toggle', [\App\Http\Controllers\Admin\CreditPackageController::class, 'toggle'])
        ->name('credit-packages.toggle');

    Route::delete('/credit-packages/{id}', [\App\Http\Controllers\Admin\CreditPackageController::class, 'destroy'])
        ->name('credit-packages.destroy');
});;


    Route::middleware(['auth'])->prefix('crm')->name('user.crm.')->group(function () {
        Route::get('/', [\App\Http\Controllers\User\CoreCrmController::class, 'index'])
            ->name('index');

        Route::post('/contacts', [\App\Http\Controllers\User\CoreCrmController::class, 'storeContact'])
            ->name('contacts.store');

        Route::post('/leads', [\App\Http\Controllers\User\CoreCrmController::class, 'storeLead'])
            ->name('leads.store');

        Route::post('/tasks', [\App\Http\Controllers\User\CoreCrmController::class, 'storeTask'])
            ->name('tasks.store');
    });


Route::get('/admin/core-addons/{id}/edit', [
    \App\Http\Controllers\Admin\CoreAddonController::class,
    'editAddon'
])->name('admin.core-addons.edit');

Route::put('/admin/core-addons/{id}', [
    \App\Http\Controllers\Admin\CoreAddonController::class,
    'updateAddon'
])->name('admin.core-addons.update');

Route::post('/admin/core-addons/{id}/grant', [
    \App\Http\Controllers\Admin\CoreAddonController::class,
    'grantAddon'
])->name('admin.core-addons.grant');

Route::post('/marketplace/developer/checkout', [MarketplaceController::class, 'developerCheckoutSubmit'])
    ->name('marketplace.developer.checkout.submit');
Route::post('/marketplace/developer/payment', [MarketplaceController::class, 'developerPayment'])
    ->name('marketplace.developer.payment');

Route::post('/marketplace/saas/payment', [MarketplaceController::class, 'saasPayment'])
    ->name('marketplace.saas.payment');
Route::delete('/admin/marketplace/checkout-sessions/{session}', [MarketplaceController::class, 'adminDeleteCheckoutSession'])
    ->name('admin.marketplace.checkout-sessions.delete');
Route::get('/admin/marketplace/checkout-sessions', [MarketplaceController::class, 'adminCheckoutSessions'])
    ->name('admin.marketplace.checkout-sessions');
Route::get('/marketplace/developer/pending-checkouts/{order}/continue', [MarketplaceController::class, 'continueDeveloperCheckout'])
    ->name('marketplace.developer.pending-checkouts.continue');
Route::get('/marketplace/developer/library', [MarketplaceController::class, 'developerLibrary'])
    ->name('marketplace.developer.library')
    ->middleware('auth');

Route::get('/marketplace/developer/pending-checkouts', [MarketplaceController::class, 'developerPendingCheckouts'])
    ->name('marketplace.developer.pending-checkouts');
Route::get('/marketplace/developer/checkout/{productType}/{productId}', [MarketplaceController::class, 'developerCheckout'])
    ->name('marketplace.developer.checkout');
Route::get('/marketplace/developer/addons', [MarketplaceController::class, 'developerAddons'])
    ->name('marketplace.developer.addons');

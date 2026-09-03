<?php

/*
 * ESUBIZ_UNIVERSAL_TENANT_ADDON_CHECKOUT_ROUTE_V1
 *
 * Same Add-on checkout controller, exposed on every tenant/custom host.
 * No new checkout implementation.
 */
\Route::post(
    '/esubiz-addon-checkout/{website}',
    [
        \App\Http\Controllers\TenantAddonCheckoutController::class,
        'create'
    ]
)->middleware('web');



/*
|--------------------------------------------------------------------------
| ESUBIZ_UNIVERSAL_ADDON_CHECKOUT_HOST_ROUTE_V1
|--------------------------------------------------------------------------
|
| Universal Add-on checkout starter.
|
| This route is intentionally registered before domain-specific route
| groups so the same checkout endpoint works from:
|
| - SaaS tenant subdomains
| - custom SaaS domains
| - central Esubiz context
| - authenticated off-server website context
|
| Placement never controls checkout routing.
|
*/
\Route::post(
    '/websites/{website}/addon-checkout',
    [
        \App\Http\Controllers\TenantAddonCheckoutController::class,
        'create'
    ]
)->middleware('web');



use App\Http\Controllers\TenantAddonCheckoutController;




/*
|--------------------------------------------------------------------------
| Central Esubiz Login - MUST stay before tenant routes
|--------------------------------------------------------------------------
*/






use App\Http\Controllers\TenantAiProposalController;
use App\Http\Controllers\MarketplaceController;

use App\Http\Controllers\Admin\FinancialReportController;

use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Public Central Esubiz AI Avatar Media
|--------------------------------------------------------------------------
|
| Official avatars are landlord-owned media stored in Central storage.
| This route is intentionally public because tenant websites must
| display the selected official Esubiz AI avatar.
|
*/



/*
|--------------------------------------------------------------------------
| PUBLIC ESUBIZ CENTRAL MEDIA
|--------------------------------------------------------------------------
|
| Landlord-owned media only.
| Tenant media must not use this route.
|
*/

/*
 * ESUBIZ_CORE_GOOGLE_PRIORITY_V52
 *
 * Core Google authentication routes.
 */

Route::middleware('web')->group(function () {
    Route::get(
        '/auth/google',
        [
            \App\Http\Controllers\TenantSocialAuthController::class,
            'googleRedirect',
        ]
    )->name('tenant.auth.google.redirect');

    Route::get(
        '/auth/google/callback',
        [
            \App\Http\Controllers\TenantSocialAuthController::class,
            'googleCallback',
        ]
    )->name('tenant.auth.google.callback');
});

Route::get(
    '/media/central/{path}',
    [
        \App\Http\Controllers\Media\CentralMediaController::class,
        'show',
    ]
)
->where(
    'path',
    '.*'
)
->name(
    'esubiz.central-media.show'
);


Route::get(
    '/media/esubiz-ai/avatar/{persona}',
    [
        \App\Http\Controllers\Admin\AiController::class,
        'avatarImage',
    ]
)->name(
    'esubiz.ai.avatar.image'
);


Route::get('/gift-card/validate', [
    \App\Http\Controllers\GiftCardValidatorController::class,
    'index',
])->name('gift-card.validate');

Route::post('/gift-card/validate', [
    \App\Http\Controllers\GiftCardValidatorController::class,
    'validateCard',
])->name('gift-card.validate.check');

Route::post('/gift-card/validate-checkout', [
    \App\Http\Controllers\GiftCardValidatorController::class,
    'validateCheckout'
])->name('gift-card.validate.checkout');


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
use App\Http\Controllers\Admin\WebsiteController as AdminWebsiteController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\Developer\BuilderController as DeveloperBuilderController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\WebsiteController as UserWebsiteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| ESUBIZ-VITE-ASSET-FALLBACK
|--------------------------------------------------------------------------
|
| Serve compiled Vite assets directly through Laravel when the web server
| rewrites /build/assets requests into index.php.
|
*/

Route::get('/build/assets/{file}', function ($file) {
    abort_if(
        str_contains($file, '..')
        || str_contains($file, '/')
        || str_contains($file, '\\'),
        404
    );

    $path = public_path('build/assets/' . $file);

    abort_unless(is_file($path), 404);

    return response()->file($path);
})
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('vite.asset.fallback');



/*
|--------------------------------------------------------------------------
| ESUBIZ-CENTRAL-PUBLIC-ASSET-FALLBACK
|--------------------------------------------------------------------------
|
| Serve central Esubiz public assets directly from Laravel's public folder
| when Apache rewrites the request into index.php.
|
*/







/*
|--------------------------------------------------------------------------
| CENTRAL-STATIC-FILE-FALLBACK
|--------------------------------------------------------------------------
|
| Central Esubiz only.
| Serve any genuine static file from Laravel public/ when Apache rewrites
| the request to index.php.
|
*/

$serveCentralStaticFile = function ($file) {

    abort_if(
        str_contains($file, '..')
        || str_starts_with($file, '/')
        || str_contains($file, "\0"),
        404
    );

    $path = public_path($file);

    abort_unless(
        is_file($path),
        404
    );

    return response()->file($path);
};


/* esubiz.com */



/* www.esubiz.com */




/*
|--------------------------------------------------------------------------
| CENTRAL-VITE-EXPLICIT-MIME
|--------------------------------------------------------------------------
|
| Central Esubiz only.
| Serve Vite assets through Laravel with browser-correct MIME headers.
|
*/

$serveCentralViteAsset = function ($file) {

    abort_if(
        str_contains($file, '..')
        || str_contains($file, '/')
        || str_contains($file, '\\'),
        404
    );

    $path = public_path('build/assets/' . $file);

    abort_unless(is_file($path), 404);

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    $mime = match ($extension) {
        'css' => 'text/css; charset=UTF-8',
        'js', 'mjs' => 'application/javascript; charset=UTF-8',
        'map', 'json' => 'application/json; charset=UTF-8',
        default => 'application/octet-stream',
    };

    return response()->file(
        $path,
        [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]
    );
};


/* Non-WWW central application */
Route::domain('esubiz.com')
    ->get(
        '/build/assets/{file}',
        $serveCentralViteAsset
    )
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('central.vite.asset');


/* WWW central homepage */
Route::domain('www.esubiz.com')
    ->get(
        '/build/assets/{file}',
        $serveCentralViteAsset
    )
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('central.vite.asset.www');


Route::view('/', 'frontend.home')->name('home');


/*
|--------------------------------------------------------------------------
| Central WWW Compatibility
|--------------------------------------------------------------------------
|
| Historical Esubiz behavior:
|   www.esubiz.com          = public homepage
|   esubiz.com/*            = application/accounts
|
*/

Route::domain('www.esubiz.com')
    ->get('/', fn () => view('frontend.home'))
    ->name('www.home');

Route::domain('www.esubiz.com')
    ->any('/{path}', function (
        \Illuminate\Http\Request $request,
        $path
    ) {
        return redirect()->to(
            'https://esubiz.com/' . ltrim($path, '/')
            . (
                $request->getQueryString()
                    ? '?' . $request->getQueryString()
                    : ''
            ),
            302
        );
    })
    ->where('path', '.+')
    ->name('www.central.redirect');








Route::domain('esubiz.com')
    ->get('/', fn () => view('frontend.home'))
    ->name('www.home');

/*
|--------------------------------------------------------------------------
| Public Tenant Website
|--------------------------------------------------------------------------
*/

Route::domain('{subdomain}.esubiz.com')
    ->where([
        'subdomain' =>
            '(?!www$)(?!esubiz$)[a-zA-Z0-9-]+'
    ])
    ->middleware([
        'website-tenant',
        /*
         * ESUBIZ_UNIVERSAL_BANDWIDTH_ENFORCEMENT_ROUTE_V1
         *
         * Bandwidth enforcement now uses the universal entitlement engine.
         * Metering remains handled by TrackTenantAdminBandwidth.
         */
        'core.entitlement:bandwidth',
        \App\Http\Middleware\TrackTenantAdminBandwidth::class,
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Public Tenant Website
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/',
            [TenantWebsiteController::class, 'home']
        )->name('tenant.website.home');


        /*
        |--------------------------------------------------------------------------
        | Tenant Core CMS Entry
        |--------------------------------------------------------------------------
        |
        | Every current and future tenant uses:
        |
        | tenant.esubiz.com/admin
        |
        */
/*
 * ESUBIZ_TENANT_AUTH_ROUTES_V1
 *
 * These routes live inside the existing tenant-domain route group.
 * They therefore provide /login and /register only for tenant websites
 * and do not replace esubiz.com's landlord authentication routes.
 */

/*
|--------------------------------------------------------------------------
| ESUBIZ_TENANT_AUTH_SETTINGS_ROUTES_V1
|--------------------------------------------------------------------------
|
| Plug-and-play authentication configuration for the active Core website.
| Works from the website's own tenant database and is therefore suitable
| for SaaS and off-server Core installations.
|
*/

/*
|--------------------------------------------------------------------------
| ESUBIZ_UNIFIED_SITE_SETTINGS_ROUTE_V1
|--------------------------------------------------------------------------
|
| Main website Settings page.
| Site Settings is the first tab.
|
*/

Route::get(
    '/admin/settings',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'siteSettings',
    ]
)->name('tenant.cms.settings.site');

/*
 * ESUBIZ_CORE_SITE_SETTINGS_UPDATE_ROUTE_V1
 */
Route::post(
    '/admin/settings',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'updateSiteSettings',
    ]
)->name('tenant.cms.settings.site.update');


/*
 * ESUBIZ_CORE_PROFILE_SETTINGS_ROUTES_V1
 */
Route::get(
    '/admin/settings/profile',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'profileSettings',
    ]
)->name('tenant.cms.settings.profile');

Route::post(
    '/admin/settings/profile',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'updateProfileSettings',
    ]
)->name('tenant.cms.settings.profile.update');

Route::post(
    '/admin/settings/profile/password',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'updateProfilePassword',
    ]
)->name('tenant.cms.settings.profile.password.update');

/*
 * ESUBIZ_CORE_PROFILE_AVATAR_ROUTES_V1
 *
 * Core serves its own avatar through Laravel.
 * This avoids dependency on public/storage web-server mapping.
 */
Route::get(
    '/admin/settings/profile/avatar',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'profileAvatar',
    ]
)->name('tenant.cms.settings.profile.avatar');

Route::delete(
    '/admin/settings/profile/avatar',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'deleteProfileAvatar',
    ]
)->name('tenant.cms.settings.profile.avatar.delete');

/*
|--------------------------------------------------------------------------
| ESUBIZ_CORE_USER_PROFILE_SETTINGS_ROUTES_V1
|--------------------------------------------------------------------------
|
| User-facing aliases for the SAME Core Profile Settings handlers.
| No duplicate profile system is introduced.
|
| Pure Core users remain outside the technical /admin surface.
|
*/
Route::get(
    '/user/profile',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'profileSettings',
    ]
)->name('tenant.core.user.profile');

Route::post(
    '/user/profile',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'updateProfileSettings',
    ]
)->name('tenant.core.user.profile.update');

Route::post(
    '/user/profile/password',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'updateProfilePassword',
    ]
)->name('tenant.core.user.profile.password.update');

Route::post(
    '/user/profile/avatar',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'updateProfileAvatar',
    ]
)->name('tenant.core.user.profile.avatar');

Route::delete(
    '/user/profile/avatar',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'deleteProfileAvatar',
    ]
)->name('tenant.core.user.profile.avatar.delete');



Route::get(
    '/admin/settings/authentication',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'authenticationSettings',
    ]
)->name('tenant.cms.settings.authentication');

Route::post(
    '/admin/settings/authentication',
    [
        \App\Http\Controllers\TenantCmsController::class,
        'updateAuthenticationSettings',
    ]
)->name('tenant.cms.settings.authentication.update');


Route::get(
    '/login',
    [\App\Http\Controllers\TenantCmsController::class, 'showLogin']
)->name('tenant.auth.login');

/*
 * ESUBIZ_TENANT_NATIVE_LOGIN_ROUTE_V1
 */
Route::post(
    '/login',
    [\App\Http\Controllers\TenantCmsController::class, 'login']
)->name('tenant.auth.login.submit');

Route::get(
    '/register',
    [\App\Http\Controllers\TenantCmsController::class, 'showRegister']
)->name('tenant.auth.register');

/*
 * ESUBIZ_COMPLETE_TENANT_AUTH_ROUTES_V1
 */

Route::post(
    '/register',
    [\App\Http\Controllers\TenantCmsController::class, 'register']
)->name('tenant.auth.register.submit');

Route::get(
    '/forgot-password',
    [\App\Http\Controllers\TenantCmsController::class, 'showForgotPassword']
)->name('tenant.auth.password.request');

Route::post(
    '/forgot-password',
    [\App\Http\Controllers\TenantCmsController::class, 'sendPasswordReset']
)->name('tenant.auth.password.email');

Route::get(
    '/reset-password/{token}',
    [\App\Http\Controllers\TenantCmsController::class, 'showResetPassword']
)->name('tenant.auth.password.reset');

Route::post(
    '/reset-password',
    [\App\Http\Controllers\TenantCmsController::class, 'resetPassword']
)->name('tenant.auth.password.update');

        Route::get(
            '/admin',
            [
                \App\Http\Controllers\TenantCmsController::class,
                'admin',
            ]
        )->name('tenant.cms.admin');


        /*
        |--------------------------------------------------------------------------
        | Tenant Core CMS Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/admin/dashboard',
            [
                \App\Http\Controllers\TenantCmsController::class,
                'dashboard',
            ]
        )->name('tenant.cms.dashboard');


        /*
        |--------------------------------------------------------------------------
        | ESUBIZ_CORE_USER_DASHBOARD_ROUTE_V1
        |--------------------------------------------------------------------------
        |
        | Core website User Dashboard.
        |
        | This route exists inside the tenant/Core route context.
        | It is separate from Central Esubiz /user/dashboard.
        |
        */
        Route::get(
            '/user/dashboard',
            [
                \App\Http\Controllers\TenantCmsController::class,
                'userDashboard',
            ]
        )->name('tenant.core.user.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Homepage Update
        |--------------------------------------------------------------------------
        */



        /*
        |--------------------------------------------------------------------------
        | Core CMS - Tenant Media Upload
        |--------------------------------------------------------------------------
        */
        Route::post(
            '/admin/media/upload-image',
            [
                \App\Http\Controllers\TenantMediaController::class,
                'uploadImage',
            ]
        )->middleware('core.entitlement:storage')
        ->name('tenant.cms.media.image.upload');

        /*
         * ESUBIZ_TENANT_VIDEO_UPLOAD_ROUTE_V1
         */
        Route::post(
            '/admin/media/upload-video',
            [
                \App\Http\Controllers\TenantMediaController::class,
                'uploadVideo',
            ]
        )->middleware('core.entitlement:storage')
        ->name('tenant.cms.media.video.upload');




        /*
        |--------------------------------------------------------------------------
        | Core CMS - Themes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/admin/themes',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'index',
            ]
        )->name('tenant.cms.themes.index');


        /*
        |--------------------------------------------------------------------------
        | Core CMS - Central Site AI
        |--------------------------------------------------------------------------
        |
        | One AI gateway for tenant website features.
        |
        | Individual features register capabilities with the
        | Esubiz Central AI Function Library instead of creating
        | their own AI implementations.
        |
        */
        Route::post(
            '/admin/site-ai/generate',
            [
                \App\Http\Controllers\TenantSiteAiController::class,
                'generate',
            ]
        )->name(
            'tenant.cms.site-ai.generate'
        );

        /*
         * ESUBIZ_TENANT_AI_USAGE_PRICING_ROUTE_V1
         *
         * Separate customer-facing AI economics page.
         * AI Settings remains dedicated to configuration.
         */
        Route::get(
            '/admin/ai/usage',
            [
                \App\Http\Controllers\TenantSiteAiController::class,
                'usagePricing',
            ]
        )->name(
            'tenant.cms.ai.usage'
        );


        Route::get(
            '/admin/esubiz-ai/settings',
            [
                \App\Http\Controllers\TenantSiteAiController::class,
                'settings',
            ]
        )->name(
            'tenant.cms.site-ai.settings'
        );


        Route::post(
            '/admin/esubiz-ai/settings',
            [
                \App\Http\Controllers\TenantSiteAiController::class,
                'updateSettings',
            ]
        )->name(
            'tenant.cms.site-ai.settings.update'
        );

        /*
         * Autosave individual website AI service switches.
         */
        Route::post(
            '/admin/esubiz-ai/service',
            [
                \App\Http\Controllers\TenantSiteAiController::class,
                'updateServiceSetting',
            ]
        )->name(
            'tenant.cms.site-ai.service.update'
        );

        Route::post(
            '/admin/esubiz-ai/avatar',
            [
                \App\Http\Controllers\TenantSiteAiController::class,
                'updateAvatarSetting',
            ]
        )->name(
            'tenant.cms.site-ai.avatar.update'
        );

        Route::post(
            '/admin/esubiz-ai/autosave',
            [
                \App\Http\Controllers\TenantSiteAiController::class,
                'autosaveSetting',
            ]
        )->name(
            'tenant.cms.site-ai.autosave'
        );







        /*
        |--------------------------------------------------------------------------
        | ESUBIZ_AI_COMPATIBILITY_ROUTES
        |--------------------------------------------------------------------------
        |
        | Preserve the existing Esubiz AI sidebar URLs while the
        | new global AI platform is being built.
        |
        | /admin/ai          -> AI dashboard/settings entry
        | /admin/ai/settings -> persona/settings page
        |
        */

        Route::get(
            '/admin/ai',
            function (
                string $subdomain
            ) {
                return redirect()->route(
                    'tenant.cms.site-ai.settings',
                    [
                        'subdomain' =>
                            $subdomain,
                    ]
                );
            }
        )->name(
            'tenant.cms.ai.dashboard'
        );


        Route::get(
            '/admin/ai/settings',
            function (
                string $subdomain
            ) {
                return redirect()->route(
                    'tenant.cms.site-ai.settings',
                    [
                        'subdomain' =>
                            $subdomain,
                    ]
                );
            }
        )->name(
            'tenant.cms.ai.settings.compat'
        );






        /*
         * Exact built-in Business Theme routes.
         * These stay before the generic {theme} routes.
         */

        Route::get(
            '/admin/themes/business/configure',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'configureBusiness',
            ]
        )->name('tenant.cms.themes.business.configure');

        Route::post(
            '/admin/themes/business/configure',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'updateBusiness',
            ]
        )->name('tenant.cms.themes.business.update');


        Route::post(
            '/admin/pages/business-home/content',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'updateBusinessHomepageContent',
            ]
        )->name(
            'tenant.cms.themes.business.home-content.update'
        );




        Route::get(
            '/admin/themes/business/preview-image',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'businessPreview',
            ]
        )->name('tenant.cms.themes.business.preview');


        /*
         * AJAX Theme Enable / Disable
         *
         * Fixed endpoint avoids redirect and dynamic-action URL
         * issues. Theme slug and requested action are POST data.
         */
        Route::post(
            '/admin/themes/toggle',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'toggle',
            ]
        )->name('tenant.cms.themes.toggle');


        Route::get(
            '/admin/themes/{theme}/configure',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'configure',
            ]
        )->name('tenant.cms.themes.configure');

        Route::post(
            '/admin/themes/{theme}/enable',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'enable',
            ]
        )->name('tenant.cms.themes.enable');

        Route::post(
            '/admin/themes/{theme}/disable',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'disable',
            ]
        )
            ->where('theme', 'business|corporate-default')
            ->name('tenant.cms.themes.disable');


        Route::post(
            '/admin/themes/{theme}/configure',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'update',
            ]
        )->name('tenant.cms.themes.update');


        /*
        |--------------------------------------------------------------------------
        | Core CMS - Forms
        |--------------------------------------------------------------------------
        |
        | Universal Core Forms manager.
        */

        Route::get(
            '/admin/forms',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'index',
            ]
        )->name('tenant.cms.forms.index');

        Route::get(
            '/admin/forms/create',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'create',
            ]
        )->name('tenant.cms.forms.create');

        Route::post(
            '/admin/forms',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'store',
            ]
        )->name('tenant.cms.forms.store');

        Route::get(
            '/admin/forms/{form}/edit',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'edit',
            ]
        )->name('tenant.cms.forms.edit');

        Route::put(
            '/admin/forms/{form}',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'update',
            ]
        )->name('tenant.cms.forms.update');

        Route::delete(
            '/admin/forms/{form}',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'destroy',
            ]
        )->name('tenant.cms.forms.destroy');


        /*
        |--------------------------------------------------------------------------
        | ESUBIZ_CORE_FORM_SUBMISSION_ROUTES_V1
        | Core CMS - Form Submissions
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/admin/forms/{form}/submissions',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'submissions',
            ]
        )
            ->whereNumber('form')
            ->name(
                'tenant.cms.forms.submissions.index'
            );

        Route::get(
            '/admin/forms/{form}/submissions/{submission}',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'showSubmission',
            ]
        )
            ->whereNumber('form')
            ->whereNumber('submission')
            ->name(
                'tenant.cms.forms.submissions.show'
            );

        Route::patch(
            '/admin/forms/{form}/submissions/{submission}/status',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'updateSubmissionStatus',
            ]
        )
            ->whereNumber('form')
            ->whereNumber('submission')
            ->name(
                'tenant.cms.forms.submissions.status'
            );

        Route::delete(
            '/admin/forms/{form}/submissions/{submission}',
            [
                \App\Http\Controllers\TenantFormsController::class,
                'destroySubmission',
            ]
        )
            ->whereNumber('form')
            ->whereNumber('submission')
            ->name(
                'tenant.cms.forms.submissions.destroy'
            );


        /*
        |--------------------------------------------------------------------------
        | ESUBIZ_CORE_USERS_ROUTES_V1
        | Core CMS - Users
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/admin/users',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'index',
            ]
        )->name('tenant.cms.users.index');

        Route::get(
            '/admin/users/create',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'create',
            ]
        )->name('tenant.cms.users.create');

        Route::post(
            '/admin/users',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'store',
            ]
        )->name('tenant.cms.users.store');

        Route::get(
            '/admin/users/{user}/edit',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'edit',
            ]
        )
            ->whereNumber('user')
            ->name('tenant.cms.users.edit');

        Route::put(
            '/admin/users/{user}',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'update',
            ]
        )
            ->whereNumber('user')
            ->name('tenant.cms.users.update');

        Route::delete(
            '/admin/users/{user}',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'destroy',
            ]
        )
            ->whereNumber('user')
            ->name('tenant.cms.users.destroy');

    /*
     * ESUBIZ_CORE_ROLES_PERMISSIONS_ROUTES_V1
     */
    
        /*
         * ESUBIZ_CORE_PARTNER_ADMIN_CONFIG_ROUTES_V2
         *
         * Dedicated Partner / Investor configuration.
         * Static routes intentionally declared before
         * dynamic /admin/users/{user} routes.
         */
        Route::get(
            '/admin/users/partners',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'partners',
            ]
        )->name('tenant.cms.users.partners');

        Route::put(
            '/admin/users/partners/{user}',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'updatePartner',
            ]
        )
            ->whereNumber('user')
            ->name('tenant.cms.users.partners.update');


Route::get(
        '/admin/users/roles',
        [\App\Http\Controllers\TenantRolesController::class, 'index']
    )->name('tenant.cms.roles.index');

    Route::get(
        '/admin/users/roles/create',
        [\App\Http\Controllers\TenantRolesController::class, 'create']
    )->name('tenant.cms.roles.create');

    Route::post(
        '/admin/users/roles',
        [\App\Http\Controllers\TenantRolesController::class, 'store']
    )->name('tenant.cms.roles.store');

    Route::get(
        '/admin/users/roles/{role}/edit',
        [\App\Http\Controllers\TenantRolesController::class, 'edit']
    )->whereNumber('role')->name('tenant.cms.roles.edit');

    Route::put(
        '/admin/users/roles/{role}',
        [\App\Http\Controllers\TenantRolesController::class, 'update']
    )->whereNumber('role')->name('tenant.cms.roles.update');

    Route::delete(
        '/admin/users/roles/{role}',
        [\App\Http\Controllers\TenantRolesController::class, 'destroy']
    )->whereNumber('role')->name('tenant.cms.roles.destroy');



        /*
        |--------------------------------------------------------------------------
        | Core CMS - Pages
        |--------------------------------------------------------------------------
        |
        | Core pages are unlimited.
        */
        Route::get(
            '/admin/pages',
            [
                \App\Http\Controllers\TenantPagesController::class,
                'index',
            ]
        )->name('tenant.cms.pages.index');

        Route::get(
            '/admin/pages/create',
            [
                \App\Http\Controllers\TenantPagesController::class,
                'create',
            ]
        )->name('tenant.cms.pages.create');

        Route::post(
            '/admin/pages',
            [
                \App\Http\Controllers\TenantPagesController::class,
                'store',
            ]
        )->name('tenant.cms.pages.store');

        
    /*
     * ESUBIZ_TENANT_ADMIN_ADDON_CHECKOUT_ROUTE_V1
     *
     * Universal Add-on checkout endpoint inside the SAME routing
     * context as tenant Admin/Page Builder routes.
     */
    Route::post(
        'admin/addon-checkout/{website}',
        [
            \App\Http\Controllers\TenantAddonCheckoutController::class,
            'create'
        ]
    )->name('tenant.admin.addons.checkout');

Route::get(
            '/admin/pages/{page}/edit',
            [
                \App\Http\Controllers\TenantPagesController::class,
                'edit',
            ]
        )->name('tenant.cms.pages.edit');

        Route::put(
            '/admin/pages/{page}',
            [
                \App\Http\Controllers\TenantPagesController::class,
                'update',
            ]
        )->name('tenant.cms.pages.update');

        Route::delete(
            '/admin/pages/{page}',
            [
                \App\Http\Controllers\TenantPagesController::class,
                'destroy',
            ]
        )->name('tenant.cms.pages.destroy');


        Route::post(
            '/admin/logout',
            [
                \App\Http\Controllers\TenantCmsController::class,
                'logout',
            ]
        )->name('tenant.cms.logout');


        Route::put(
            '/admin/homepage',
            [
                \App\Http\Controllers\TenantCmsController::class,
                'updateHomepage',
            ]
        )->name('tenant.cms.homepage.update');


        /*
        |--------------------------------------------------------------------------
        | Public Tenant CMS Pages
        |--------------------------------------------------------------------------
        |
        | Keep this wildcard route LAST inside the tenant domain group so
        | /admin and every other explicit tenant route always take priority.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Public Tenant Media
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/media/{path}',
            [
                \App\Http\Controllers\TenantMediaController::class,
                'show',
            ]
        )
            ->where('path', '.*')
            ->name('tenant.website.media');



        Route::get(
            '/theme-assets/{path}',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'asset',
            ]
        )
            ->where('path', '.*')
            ->name('tenant.theme.asset');


        



Route::get(
            '/{slug}',
            [TenantWebsiteController::class, 'page']
        )
            ->where('slug', '^(?!auth(?:/|$))(?:^(?!(?:admin|sso)(?:/|$)).+)$')
    /* ESUBIZ_CORE_RESERVED_AUTH_NAMESPACE_V54 */
            ->name('tenant.website.page');

    });



Route::domain('esubiz.com')
    ->middleware([
        'auth',
        'account-mode:user',
    ])
    ->get(
        '/websites/{website}/dashboard',
        [
            \App\Http\Controllers\User\WebsiteController::class,
            'openDashboard',
        ]
    )
    ->name('user.websites.dashboard');



/*
|--------------------------------------------------------------------------
| Esubiz Managed Authentication Broker
|--------------------------------------------------------------------------
|
| ESUBIZ_MANAGED_AUTH_BROKER_V1
|
| Central broker for external authentication providers used by Core.
| Provider-specific OAuth transport is configured separately.
|
*/

Route::get(
    '/oauth/provider/{provider}',
    [
        \App\Http\Controllers\ManagedAuthController::class,
        'start',
    ]
)
    ->where(
        'provider',
        'google|facebook|instagram|tiktok|x'
    )
    ->name('managed-auth.start');

Route::get(
    '/oauth/provider/{provider}/callback',
    [
        \App\Http\Controllers\ManagedAuthController::class,
        'callback',
    ]
)
    ->where(
        'provider',
        'google|facebook|instagram|tiktok|x'
    )
    ->name('managed-auth.callback');

Route::get('/oauth/authorize', [SsoController::class, 'authorize'])
    ->middleware('auth')
    ->name('sso.authorize');





/*
|--------------------------------------------------------------------------
| SSO Callbacks
|--------------------------------------------------------------------------
|
| Central Esubiz authentication can return centrally, while tenant website
| SSO may return directly to the originating tenant subdomain.
|
*/

/*
|--------------------------------------------------------------------------
| ESUBIZ_TENANT_SSO_START_ROUTE_V1
|--------------------------------------------------------------------------
|
| Public SaaS tenant entry point for central Esubiz authentication.
|
*/

Route::domain('{subdomain}.esubiz.com')
    ->get('/sso/start', [SsoController::class, 'start'])
    ->name('tenant.sso.start');


Route::domain('esubiz.com')
    ->get('/sso/callback', [SsoController::class, 'callback'])
    ->name('sso.callback.central');

Route::domain('{subdomain}.esubiz.com')
    ->where([
        'subdomain' => '(?!www$)(?!esubiz$)[a-zA-Z0-9-]+',
    ])
    ->get('/sso/callback', [SsoController::class, 'callback'])
    ->name('sso.callback');


Route::post('/oauth/token', [SsoController::class, 'token'])
    ->name('sso.token');

Route::get('/oauth/user', [SsoController::class, 'user'])
    ->name('sso.user');




/*
|--------------------------------------------------------------------------
| Admin Marketplace - Themes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/admin/marketplace/themes',
        [
            \App\Http\Controllers\Admin\ThemeController::class,
            'index',
        ]
    )->name('admin.themes.index');

    /*
    |--------------------------------------------------------------------------
    | Central Admin - Esubiz AI
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/admin/ai',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'index',
        ]
    )->name(
        'admin.ai.index'


    );

    /*
    |--------------------------------------------------------------------------
    | CENTRAL AI COMMERCIAL CONTROLS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/admin/ai/commercial/{setting}',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'updateCommercialSetting',
        ]
    )->name(
        'admin.ai.commercial.update'
    );


    Route::post(
        '/admin/ai/models/{model}/pricing',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'updateModelPricing',
        ]
    )->name(
        'admin.ai.models.pricing.update'
    );



    Route::post(
        '/admin/ai/autosave',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'autosaveSetting',
        ]
    )->name(
        'admin.ai.autosave'
    );



    /*
    |--------------------------------------------------------------------------
    | Central Admin - Esubiz AI Management
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/admin/ai/avatars',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'storeAvatar',
        ]
    )->name(
        'admin.ai.avatars.store'
    );


    Route::post(
        '/admin/ai/avatars/{persona}',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'updateAvatar',
        ]
    )->name(
        'admin.ai.avatars.update'
    );


    Route::delete(
        '/admin/ai/avatars/{persona}',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'destroyAvatar',
        ]
    )->name(
        'admin.ai.avatars.destroy'
    );


    Route::post(
        '/admin/ai/providers',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'storeProvider',
        ]
    )->name(
        'admin.ai.providers.store'
    );


    Route::post(
        '/admin/ai/providers/{provider}',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'updateProvider',
        ]
    )->name(
        'admin.ai.providers.update'
    );


    Route::delete(
        '/admin/ai/providers/{provider}',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'destroyProvider',
        ]
    )->name(
        'admin.ai.providers.destroy'
    );


    Route::post(
        '/admin/ai/models',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'storeModel',
        ]
    )->name(
        'admin.ai.models.store'
    );


    Route::post(
        '/admin/ai/routing',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'storeRoutingRule',
        ]
    )->name(
        'admin.ai.routing.store'
    );


    Route::post(
        '/admin/ai/credits',
        [
            \App\Http\Controllers\Admin\AiController::class,
            'storeCreditRule',
        ]
    )->name(
        'admin.ai.credits.store'
    );





    Route::put(
        '/admin/marketplace/themes/{theme}',
        [
            \App\Http\Controllers\Admin\ThemeController::class,
            'update',
        ]
    )->name('admin.themes.update');

    Route::post(
        '/admin/marketplace/themes/{theme}/versions',
        [
            \App\Http\Controllers\Admin\ThemeController::class,
            'createVersion',
        ]
    )->name('admin.themes.versions.store');

    Route::post(
        '/admin/marketplace/themes/{theme}/publish',
        [
            \App\Http\Controllers\Admin\ThemeController::class,
            'publish',
        ]
    )->name('admin.themes.publish');

});


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



/*
 * ESUBIZ_UNIVERSAL_ADDON_CHECKOUT_ROUTE_V1
 *
 * Authenticated SaaS tenant Add-on checkout handoff.
 * Existing Marketplace checkout remains authoritative.
 */
Route::post(
    '/websites/{website}/addon-checkout',
    [
        TenantAddonCheckoutController::class,
        'create',
    ]
)
    ->middleware('auth')
    ->name('tenant.addons.checkout');

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

            Route::get('/offline-payments/{attempt}/receipt', [
                \App\Http\Controllers\Admin\OfflinePaymentReviewController::class,
                'receipt'
            ])->name('offline-payments.receipt');

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

    /*
     * ESUBIZ_SAAS_CHECKOUT_HANDOFF_ROUTE
     *
     * Signed GET bridge from SaaS tenant websites into the
     * existing Central Marketplace checkout.
     */
    Route::get(
        '/marketplace/saas-checkout',
        [
            \App\Http\Controllers\MarketplaceController::class,
            'saasCheckoutHandoff'
        ]
    )
        // ESUBIZ_SAAS_CHECKOUT_SIGNED_HANDOFF_AUTH_BYPASS_V1
        // The controller validates the signed handoff first and then
        // establishes/verifies the authoritative Central buyer session.
        ->withoutMiddleware(\Illuminate\Auth\Middleware\Authenticate::class)
        ->name('marketplace.saas-checkout');



/*
 * ESUBIZ_CENTRAL_UNIVERSAL_ADDON_CHECKOUT_V1
 *
 * One central Add-on order starter for every placement.
 * Dashboard, Settings, Page Builder and Widgets all create the
 * Marketplace order first and receive /marketplace/checkout/{order}.
 */


    Route::post('/marketplace/checkout', [\App\Http\Controllers\MarketplaceController::class, 'checkout'])
        ->name('marketplace.checkout.create');

    Route::get('/marketplace/checkout', [\App\Http\Controllers\MarketplaceController::class, 'pendingCheckouts'])
        ->name('marketplace.checkout.index');

    Route::get('/billing/orders', [\App\Http\Controllers\MarketplaceController::class, 'userOrders'])
        ->name('marketplace.user-orders');



    

    /*
     * Signed browser entry for registered off-server websites.
     *
     * This route creates a normal Central Marketplace checkout
     * session and then redirects into marketplace/checkout/{order}.
     */
    Route::get(
        '/marketplace/external-checkout',
        [
            \App\Http\Controllers\MarketplaceController::class,
            'externalCheckout',
        ]
    )
        ->middleware('signed')
        ->name(
            'marketplace.external.checkout'
        );

Route::get('/marketplace/checkout/{order}', [\App\Http\Controllers\MarketplaceController::class, 'checkoutPage'])
        ->name('marketplace.checkout');

    Route::get('/marketplace/checkout-product/{productType}/{productId}', [\App\Http\Controllers\MarketplaceController::class, 'developerCheckout'])
        ->name('marketplace.checkout.product');

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


    /*
    |--------------------------------------------------------------------------
    | Central Admin Website Registry
    |--------------------------------------------------------------------------
    |
    | Admin-only registry for all canonical Esubiz websites:
    |
    | - SaaS websites
    | - off-server websites
    |
    | This is intentionally separate from /websites, which remains the
    | User Mode website-management area.
    |
    */

    Route::get(
        '/admin/websites',
        [AdminWebsiteController::class, 'index']
    )
        ->middleware([
            'permission:roles.view',
            'account-mode:admin',
        ])
        ->name('admin.websites.index');

    Route::get(
        '/admin/websites/{website}',
        [AdminWebsiteController::class, 'show']
    )
        ->whereNumber('website')
        ->middleware([
            'permission:roles.view',
            'account-mode:admin',
        ])
        ->name('admin.websites.show');


    /*
     * ESUBIZ_SHARED_WEBSITE_MANAGEMENT_V1
     */
    Route::get(
        '/admin/websites/{website}/edit',
        [AdminWebsiteController::class, 'edit']
    )
        ->whereNumber('website')
        ->middleware([
            'permission:roles.view',
            'account-mode:admin',
        ])
        ->name('admin.websites.edit');

    Route::patch(
        '/admin/websites/{website}',
        [AdminWebsiteController::class, 'update']
    )
        ->whereNumber('website')
        ->middleware([
            'permission:roles.view',
            'account-mode:admin',
        ])
        ->name('admin.websites.update');


    /*
     * ESUBIZ_CENTRAL_ADMIN_TENANT_SUPPORT_LOGIN_V1
     */
    Route::get(
        '/admin/websites/{website}/login',
        [AdminWebsiteController::class, 'loginToWebsite']
    )
        ->whereNumber('website')
        ->middleware([
            'permission:roles.view',
            'account-mode:admin',
        ])
        ->name('admin.websites.login');


    Route::patch(
        '/admin/websites/{website}/toggle',
        [AdminWebsiteController::class, 'toggle']
    )
        ->whereNumber('website')
        ->middleware([
            'permission:roles.view',
            'account-mode:admin',
        ])
        ->name(
            'admin.websites.toggle'
        );

    Route::delete(
        '/admin/websites/{website}',
        [AdminWebsiteController::class, 'destroy']
    )
        ->whereNumber('website')
        ->middleware([
            'permission:roles.view',
            'account-mode:admin',
        ])
        ->name(
            'admin.websites.destroy'
        );



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

Route::domain('esubiz.com')
    ->get(
        '/admin/dashboard',
        [AdminDashboardController::class, 'index']
    )
    ->middleware([
        'auth',
        'permission:roles.view',
        'account-mode:admin',
    ])
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


Route::get(
    '/websites/{website}/info',
    [
        \App\Http\Controllers\User\WebsiteController::class,
        'info',
    ]
)
    ->whereNumber('website')
    ->middleware([
        'auth',
        'account-mode:user',
    ])
    ->name(
        'user.websites.info'
    );

Route::patch(
    '/websites/{website}/toggle',
    [
        \App\Http\Controllers\User\WebsiteController::class,
        'toggle',
    ]
)
    ->whereNumber('website')
    ->middleware([
        'auth',
        'account-mode:user',
    ])
    ->name(
        'user.websites.toggle'
    );

Route::delete(
    '/websites/{website}',
    [
        \App\Http\Controllers\User\WebsiteController::class,
        'destroy',
    ]
)
    ->whereNumber('website')
    ->middleware([
        'auth',
        'account-mode:user',
    ])
    ->name(
        'user.websites.destroy'
    );


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

/*
|--------------------------------------------------------------------------
| Central Esubiz Account Wallet
|--------------------------------------------------------------------------
| One wallet is shared by User Mode and Developer Mode.
*/
Route::get('/wallet', [
    \App\Http\Controllers\WalletController::class,
    'index'
])->middleware(['auth'])->name('account.wallet');

Route::post('/wallet/fund', [
    \App\Http\Controllers\WalletController::class,
    'fund'
])->middleware(['auth'])->name('account.wallet.fund');

Route::post('/wallet/gift-card/validate', [
    \App\Http\Controllers\WalletController::class,
    'validateGiftCardFunding'
])->middleware(['auth'])->name('account.wallet.gift-card.validate');

Route::get('/wallet/funding/{funding}/status', [
    \App\Http\Controllers\WalletController::class,
    'fundingStatus'
])->middleware(['auth'])->name('account.wallet.funding.status');


Route::prefix('admin/payment-gateways')->middleware(['auth'])->group(function () {

    Route::get('/wallet', [
        \App\Http\Controllers\Admin\WalletController::class,
        'index'
    ])->name('admin.payment-gateways.wallet');

    Route::patch('/wallet', [
        \App\Http\Controllers\Admin\WalletController::class,
        'update'
    ])->name('admin.payment-gateways.wallet.update');


    Route::get('/gift-card', [
        \App\Http\Controllers\Admin\GiftCardController::class,
        'index'
    ])->name('admin.payment-gateways.gift-card');

    Route::get('/gift-card/create', [
        \App\Http\Controllers\Admin\GiftCardController::class,
        'create'
    ])->name('admin.payment-gateways.gift-card.create');

    Route::post('/gift-card', [
        \App\Http\Controllers\Admin\GiftCardController::class,
        'store'
    ])->name('admin.payment-gateways.gift-card.store');
    
    Route::post('/gift-card/generate',
        [\App\Http\Controllers\Admin\GiftCardController::class, 'generate']
    )->name('admin.payment-gateways.gift-card.generate');
    
    Route::get('/gift-card/export/csv',
        [\App\Http\Controllers\Admin\GiftCardController::class, 'csv']
    )->name('admin.payment-gateways.gift-card.csv');

    Route::get('/gift-card/export/pdf',
        [\App\Http\Controllers\Admin\GiftCardController::class, 'pdf']
    )->name('admin.payment-gateways.gift-card.pdf');

    Route::get('/gift-card/{id}', [
        \App\Http\Controllers\Admin\GiftCardController::class,
        'show'
    ])->name('admin.payment-gateways.gift-card.show');

    Route::patch('/gift-card/{id}', [
        \App\Http\Controllers\Admin\GiftCardController::class,
        'update'
    ])->name('admin.payment-gateways.gift-card.update');

    Route::post('/gift-card/{id}/enable', [
        \App\Http\Controllers\Admin\GiftCardController::class,
        'enable'
    ])->name('admin.payment-gateways.gift-card.enable');

    Route::post('/gift-card/{id}/disable', [
        \App\Http\Controllers\Admin\GiftCardController::class,
        'disable'
    ])->name('admin.payment-gateways.gift-card.disable');

    Route::delete('/gift-card/{id}',
        [\App\Http\Controllers\Admin\GiftCardController::class, 'destroy']
    )->name('admin.payment-gateways.gift-card.destroy');

});

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

Route::post('/marketplace/payment', [MarketplaceController::class, 'marketplacePayment'])
    ->name('marketplace.payment');

Route::post('/marketplace/developer/checkout', [MarketplaceController::class, 'developerCheckoutSubmit'])
    ->name('marketplace.developer.checkout.submit');
Route::get('/marketplace/developer/offline-payment/{attempt}', [MarketplaceController::class, 'developerOfflinePayment'])
    ->name('marketplace.developer.offline-payment');

Route::post('/marketplace/developer/offline-payment/{attempt}/receipt', [MarketplaceController::class, 'submitOfflinePaymentReceipt'])
    ->name('marketplace.developer.offline-payment.receipt');

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


Route::middleware(['auth'])->group(function () {
    Route::get(
        '/admin/site-settings/payout',
        [\App\Http\Controllers\Admin\PayoutController::class, 'index']
    )->name('admin.site-settings.payout.index');

    Route::get(
        '/admin/site-settings/payout/automatic',
        [\App\Http\Controllers\Admin\PayoutController::class, 'automatic']
    )->name('admin.site-settings.payout.automatic.index');

    Route::get(
        '/admin/site-settings/payout/manual',
        [\App\Http\Controllers\Admin\PayoutController::class, 'manual']
    )->name('admin.site-settings.payout.manual.index');

    Route::post(
        '/admin/site-settings/payout/automatic/{method}',
        [\App\Http\Controllers\Admin\PayoutController::class, 'updateAutomatic']
    )->name('admin.site-settings.payout.automatic.update');

    Route::post(
        '/admin/site-settings/payout/manual',
        [\App\Http\Controllers\Admin\PayoutController::class, 'storeManual']
    )->name('admin.site-settings.payout.manual.store');

    Route::put(
        '/admin/site-settings/payout/manual/{method}',
        [\App\Http\Controllers\Admin\PayoutController::class, 'updateManual']
    )->name('admin.site-settings.payout.manual.update');

    Route::delete(
        '/admin/site-settings/payout/manual/{method}',
        [\App\Http\Controllers\Admin\PayoutController::class, 'destroyManual']
    )->name('admin.site-settings.payout.manual.destroy');

    Route::post(
    '/admin/site-settings/payout/requests/{requestId}/complete',
    [\App\Http\Controllers\Admin\PayoutController::class, 'completeRequest']
)->name('admin.site-settings.payout.request.complete');

Route::post(
        '/admin/site-settings/payout/requests/{requestId}/reject',
        [\App\Http\Controllers\Admin\PayoutController::class, 'rejectRequest']
    )->name('admin.site-settings.payout.request.reject');
});


Route::middleware(['auth'])->group(function () {
    Route::post(
        'wallet/payout',
        [\App\Http\Controllers\WalletController::class, 'requestPayout']
    )->name('account.wallet.payout.request');

    Route::post(
        'wallet/payout/preview-conversion',
        [\App\Http\Controllers\WalletController::class, 'previewPayoutConversion']
    )->name('account.wallet.payout.preview-conversion');
});



/*
|--------------------------------------------------------------------------
| ESUBIZ_OFF_SERVER_LICENSE_VALIDATE_ROUTE
|--------------------------------------------------------------------------
|
| PRE-INSTALL validation.
|
| This route confirms that the manually entered licence is genuine
| and eligible for the requested domain.
|
| It does NOT activate or domain-lock the licence.
|
*/

Route::post(
    '/api/v1/core/license/validate',
    [
        \App\Http\Controllers\Api\OffServerLicenseController::class,
        'validate',
    ]
)
    ->withoutMiddleware(
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class
    )
    ->middleware('throttle:30,1')
    ->name('api.core.license.validate');


/*
|--------------------------------------------------------------------------
| ESUBIZ_OFF_SERVER_CORE_LICENSE_ACTIVATION
|--------------------------------------------------------------------------
|
| Initial activation endpoint for licensed off-server Esubiz Core.
|
| This endpoint does not grant normal Central API access.
| It establishes the permanent:
|
| licence -> domain -> installation -> Central website identity
|
*/

Route::post(
    '/api/v1/core/license/activate',
    [
        \App\Http\Controllers\Api\OffServerLicenseController::class,
        'activate',
    ]
)
    /*
     * Server-to-server activation endpoint.
     *
     * Off-server Core cannot possess an Esubiz browser CSRF token,
     * so CSRF is disabled ONLY for this exact route.
     *
     * All Central Esubiz and SaaS browser routes remain protected.
     */
    ->withoutMiddleware(
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class
    )
    ->middleware('throttle:20,1')
    ->name('api.core.license.activate');


/*
|--------------------------------------------------------------------------
| ESUBIZ_CHECKPOINT5_OFF_SERVER_MARKETPLACE
|--------------------------------------------------------------------------
|
| Authenticated off-server Core -> Central Marketplace checkout.
|
| API entry uses the installation bearer token.
| Browser handoff uses a short-lived signed Central URL.
|
*/

Route::post(
    '/api/v1/core/marketplace/checkout-link',
    [
        \App\Http\Controllers\Api\OffServerMarketplaceController::class,
        'checkoutLink',
    ]
)
    ->middleware('throttle:30,1')
    ->name(
        'api.core.marketplace.checkout-link'
    );


Route::get(
    '/marketplace/off-server/checkout/{order}',
    [
        \App\Http\Controllers\Api\OffServerMarketplaceController::class,
        'handoff',
    ]
)
    ->name(
        'marketplace.off-server.checkout.handoff'
    );


/*
 * ================================================================
 * CHECKPOINT_7_ACTIVE_GIFTCARD_API_ROUTES
 * ================================================================
 *
 * This application currently exposes its Core Central API endpoints
 * through the active web route bootstrap.
 *
 * Keep Gift Card authorization inside the controller/service layer:
 *
 * bearer
 * -> installation
 * -> website
 * -> active licence
 * -> giftcard scope
 * -> Central GiftCardService
 */
Route::middleware([
    'throttle:20,1',
])->group(function () {

    Route::post(
        '/api/v1/core/giftcard/validate',
        [
            \App\Http\Controllers\Api\OffServerGiftCardController::class,
            'validateCard',
        ]
    )->name(
        'api.core.giftcard.validate'
    );


    Route::post(
        '/api/v1/core/giftcard/redeem',
        [
            \App\Http\Controllers\Api\OffServerGiftCardController::class,
            'redeem',
        ]
    )->name(
        'api.core.giftcard.redeem'
    );

});


/*
 * ================================================================
 * CHECKPOINT_8_OFF_SERVER_COMMUNICATION_ROUTES
 * ================================================================
 *
 * Off-server Core authenticated Central communication services.
 *
 * Authentication and scope checks occur through the installation
 * bearer-token authorization gateway.
 */
Route::middleware([
    'throttle:30,1',
])->group(function () {

    Route::post(
        '/api/v1/core/sms/send',
        [
            \App\Http\Controllers\Api\OffServerCommunicationController::class,
            'sms',
        ]
    )->name(
        'api.core.sms.send'
    );


    Route::post(
        '/api/v1/core/email/send',
        [
            \App\Http\Controllers\Api\OffServerCommunicationController::class,
            'email',
        ]
    )->name(
        'api.core.email.send'
    );


    Route::post(
        '/api/v1/core/whatsapp/send',
        [
            \App\Http\Controllers\Api\OffServerCommunicationController::class,
            'whatsapp',
        ]
    )->name(
        'api.core.whatsapp.send'
    );

});


/*
 * ================================================================
 * CHECKPOINT_10_CENTRAL_WEBSITE_STATE_ROUTES
 * ================================================================
 *
 * Canonical Central website product-state reads.
 */
Route::get(
    '/api/v1/core/website/state',
    [
        \App\Http\Controllers\Api\CentralWebsiteStateController::class,
        'offServer',
    ]
)
    ->middleware([
        'throttle:60,1',
    ])
    ->name(
        'api.core.website.state'
    );


Route::get(
    '/api/v1/saas/websites/{website}/state',
    [
        \App\Http\Controllers\Api\CentralWebsiteStateController::class,
        'saas',
    ]
)
    ->whereNumber('website')
    ->middleware([
        'auth',
        'throttle:60,1',
    ])
    ->name(
        'api.saas.website.state'
    );


/*
 * ================================================================
 * CENTRAL_GLOBAL_AI_SETTINGS
 * ================================================================
 *
 * One Central chat-style configuration applies across Esubiz.
 */
Route::get(
    '/admin/ai/settings',
    [
        \App\Http\Controllers\Admin\AiSettingsController::class,
        'index',
    ]
)
    ->middleware([
        'auth',
        'permission:roles.view',
        'account-mode:admin',
    ])
    ->name(
        'admin.ai.settings'
    );


Route::patch(
    '/admin/ai/settings/chat',
    [
        \App\Http\Controllers\Admin\AiSettingsController::class,
        'updateChat',
    ]
)
    ->middleware([
        'auth',
        'permission:roles.view',
        'account-mode:admin',
    ])
    ->name(
        'admin.ai.settings.chat.update'
    );



/*
|--------------------------------------------------------------------------
| ESUBIZ_TENANT_AI_PROPOSAL_ROUTES_V1
|--------------------------------------------------------------------------
|
| Universal AI proposal preview / approval endpoints.
|
| The routes never write website content directly.
| Approval delegates to the registered capability applier,
| which persists into the tenant/off-server website's normal
| editable destination.
|
*/

/*
 * ESUBIZ_TENANT_AI_PROPOSAL_DOMAIN_FIX_V1
 *
 * app.root_domain may be unset on installations that predate the
 * central root-domain configuration. Never register tenant proposal
 * routes against "{subdomain}.".
 */
Route::domain(
    '{subdomain}.'
    . (
        config('app.root_domain')
        ?: 'esubiz.com'
    )
)->middleware([
    'web',
    'auth',
])->group(function () {

    Route::get(
        '/admin/ai/proposals/{uuid}',
        [
            TenantAiProposalController::class,
            'show',
        ]
    )->name(
        'tenant.ai.proposals.show'
    );

    Route::post(
        '/admin/ai/proposals/{uuid}/approve',
        [
            TenantAiProposalController::class,
            'approve',
        ]
    )->name(
        'tenant.ai.proposals.approve'
    );

    Route::post(
        '/admin/ai/proposals/{uuid}/reject',
        [
            TenantAiProposalController::class,
            'reject',
        ]
    )->name(
        'tenant.ai.proposals.reject'
    );
});


/*
|--------------------------------------------------------------------------
| Core Public Form Submission
|--------------------------------------------------------------------------
|
| ESUBIZ_CORE_PUBLIC_FORM_SUBMISSION_ROUTE_V1
|
*/
Route::post(
    '/forms/{form}/submit',
    [
        \App\Http\Controllers\TenantPublicFormController::class,
        'submit',
    ]
)
    ->whereNumber('form')
    ->name('core.forms.submit');

/*
|--------------------------------------------------------------------------
| ESUBIZ_CORE_USERS_ROLES_ROUTE_RBAC_V1
|--------------------------------------------------------------------------
|
| Route-level authorization for established Core Users and Roles actions.
|
| Controller authorization remains in place as defense-in-depth.
|
| Permission denial is handled by CorePermissionMiddleware /
| CorePermissionService, which uses the established redirect behavior
| rather than exposing a 403 page.
|
| Future Core/add-on/module routes should use the same
| core.permission:<feature>.<action> contract.
|
*/

\Illuminate\Support\Facades\Route::matched(
    function (
        \Illuminate\Routing\Events\RouteMatched $event
    ): void {

    /*
     * ESUBIZ_CORE_ROLE_SURFACE_BOUNDARY_V1
     *
     * Hard separation of Core role surfaces:
     *
     * Pure User:
     *   allowed /user/*
     *   blocked from /admin/* except /admin/logout
     *
     * Internal roles:
     *   Administrator
     *   Partners / Investors
     *   Staff
     *   custom internal roles
     *
     *   allowed /admin/*
     *   blocked from /user/*
     *
     * This is authorization, not merely menu visibility.
     */
    try {
        $coreSurfacePermissions = app(
            \App\Services\Core\CorePermissionService::class
        );

        $coreSurfaceUserId =
            $coreSurfacePermissions->userId();

        if ($coreSurfaceUserId) {
            $coreSurfaceRoles =
                $coreSurfacePermissions->roles();

            $coreSurfacePureUser =
                count($coreSurfaceRoles) === 1
                && in_array(
                    'user',
                    $coreSurfaceRoles,
                    true
                );

            $coreSurfacePath =
                '/'
                . ltrim(
                    request()->path(),
                    '/'
                );

            if (
                $coreSurfacePureUser
                && str_starts_with(
                    $coreSurfacePath,
                    '/admin/'
                )
                && $coreSurfacePath !== '/admin/logout'
            ) {
                throw new
                    \Illuminate\Http\Exceptions\HttpResponseException(
                        redirect('/user/dashboard')
                    );
            }

            if (
                !$coreSurfacePureUser
                && str_starts_with(
                    $coreSurfacePath,
                    '/user/'
                )
            ) {
                throw new
                    \Illuminate\Http\Exceptions\HttpResponseException(
                        redirect('/admin/dashboard')
                    );
            }
        }
    } catch (
        \Illuminate\Http\Exceptions\HttpResponseException $e
    ) {
        throw $e;
    } catch (\Throwable $e) {
        /*
         * Do not break public routes if Core RBAC has not
         * been installed for an older Core database yet.
         */
    }


        $permissionMap = [
// Users
            'tenant.cms.users.index' =>
                'users.view',

            'tenant.cms.users.create' =>
                'users.create',

            'tenant.cms.users.store' =>
                'users.create',

            'tenant.cms.users.edit' =>
                'users.edit',

            'tenant.cms.users.update' =>
                'users.edit',

            'tenant.cms.users.destroy' =>
                'users.delete',

            // Roles & Permissions
            'tenant.cms.roles.index' =>
                'roles.view',

            'tenant.cms.roles.create' =>
                'roles.create',

            'tenant.cms.roles.store' =>
                'roles.create',

            'tenant.cms.roles.edit' =>
                'roles.edit',

            'tenant.cms.roles.update' =>
                'roles.edit',

            'tenant.cms.roles.destroy' =>
                'roles.delete',
        ];

        $routeName = $event->route->getName();

        if (
            !$routeName
            || !isset($permissionMap[$routeName])
        ) {
            return;
        }

        $event->route->middleware(
            \App\Http\Middleware\CorePermissionMiddleware::class
            . ':'
            . $permissionMap[$routeName]
        );
    }
);


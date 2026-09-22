<?php

/*
 * ESUBIZ_UNIVERSAL_TENANT_ADDON_CHECKOUT_ROUTE_V1
 *
 * Same Add-on checkout controller, exposed on every tenant/custom host.
 * No new checkout implementation.
 */
use App\Http\Controllers\Admin\CentralSiteSettingsController;
use App\Http\Controllers\Admin\DeveloperProductTypePolicyController;

Route::post(
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
 *
 * ESUBIZ_CORE_UNIVERSAL_ESUBIZ_PROVIDER_ROUTES_V1
 * Esubiz uses the same Core provider surface regardless of deployment.
 */

Route::middleware('web')->group(function () {

    Route::get(
        '/auth/esubiz',
        [
            \App\Http\Controllers\TenantSocialAuthController::class,
            'esubizRedirect',
        ]
    )->name('tenant.auth.esubiz.redirect');

    Route::get(
        '/auth/esubiz/callback',
        [
            \App\Http\Controllers\TenantSocialAuthController::class,
            'esubizCallback',
        ]
    )->name('tenant.auth.esubiz.callback');

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
    ->name('central.home');

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

/*
 * ESUBIZ_CORE_GENERAL_EMAIL_WORKSPACE_ROUTES_V1
 *
 * Email is the canonical Core mail engine.
 * CRM, HR, Live Chat, Tickets, Contact Form, Marketing,
 * modules, Core System and Esubiz are proxy/source systems.
 */
Route::get(
    '/admin/communication/email',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'index',
    ]
)->name('tenant.cms.email.index');

/*
 * ESUBIZ_CORE_EMAIL_SENDER_ROUTES_V3
 */
Route::get(
    '/admin/communication/email/premium-data',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'premiumData',
    ]
)->name('tenant.cms.email.premium-data');

/*
 * ESUBIZ_CORE_EXTERNAL_MAILBOX_ROUTE_V1
 */
Route::post(
    '/admin/communication/email/mailboxes/external',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'connectExternalMailbox',
    ]
)->name('tenant.cms.email.mailboxes.external.store');


Route::post(
    '/admin/communication/email/sender-mode',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'updateSenderMode',
    ]
)->name('tenant.cms.email.sender-mode');



/*
 * ESUBIZ_CORE_EMAIL_FOLDER_REFRESH_ROUTE_V1
 */
Route::post(
    '/admin/communication/email/refresh',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'refreshFolder',
    ]
)->name('tenant.cms.email.refresh');

Route::get(
    '/admin/communication/email/messages',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'messages',
    ]
)->name('tenant.cms.email.messages');

/*
 * ESUBIZ_CORE_EMAIL_SAVE_DRAFT_ROUTE_V1
 */
/*
 * ESUBIZ_CORE_EMAIL_DRAFT_ATTACHMENT_REMOVE_ROUTE_V1
 */
Route::delete(
    '/admin/communication/email/messages/{message}/attachments/{attachment}',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'removeDraftAttachment',
    ]
)
    ->whereNumber('message')
    ->whereNumber('attachment')
    ->name(
        'tenant.cms.email.drafts.attachments.destroy'
    );

/*
 * ESUBIZ_CORE_EMAIL_REAL_SEND_ROUTE_V1
 */
Route::post(
    '/admin/communication/email/send',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'sendEmail',
    ]
)->name('tenant.cms.email.send');


Route::post(
    '/admin/communication/email/drafts',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'saveDraft',
    ]
)->name('tenant.cms.email.drafts.store');


/*
 * ESUBIZ_CORE_EMAIL_MESSAGE_VIEW_ROUTE_V1
 */
Route::get(
    '/admin/communication/email/messages/{message}',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'message',
    ]
)->whereNumber('message')
    ->name('tenant.cms.email.message');

// ESUBIZ_CORE_EMAIL_MAILBOX_ACTIONS_V16
Route::post(
    '/admin/email/messages/{message}/action',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'messageAction',
    ]
)->name('core.email.messages.action');

/*
 * ESUBIZ_CORE_EMAIL_ATTACHMENT_DOWNLOAD_ROUTE_V1
 */
Route::get(
    '/admin/communication/email/attachments/{attachment}/download',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'downloadAttachment',
    ]
)
    ->whereNumber('attachment')
    ->name('core.email.attachments.download');

/*
 * ESUBIZ_CORE_EMAIL_ATTACHMENT_PREVIEW_ROUTE_V1
 */
Route::get(
    '/admin/communication/email/attachments/{attachment}/preview',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'previewAttachment',
    ]
)
    ->whereNumber('attachment')
    ->name('core.email.attachments.preview');




/*
 * ESUBIZ_CORE_EMAIL_MAILBOX_SETTINGS_ROUTES_V1
 */
Route::get(
    '/admin/communication/email/settings',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'mailboxSettings',
    ]
)->name('core.email.settings.show');

Route::post(
    '/admin/communication/email/settings',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'saveMailboxSettings',
    ]
)->name('core.email.settings.save');


/*
 * ESUBIZ_CORE_EMAIL_BRANDING_ROUTES_V1
 *
 * Website-wide Core email branding. Saved independently from
 * mailbox settings and applied to outgoing Core emails.
 */
Route::post(
    '/admin/communication/email/branding',
    [
        \App\Http\Controllers\Core\CoreEmailController::class,
        'saveEmailBranding',
    ]
)->name('core.email.branding.save');


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
     * ESUBIZ_CORE_THEME_MARKETPLACE_PAGE_ROUTE_V1
     */
    Route::get(
        '/admin/themes/marketplace',
        [
            \App\Http\Controllers\TenantThemeController::class,
            'marketplace'
        ]
    )->name(
        'tenant.cms.themes.marketplace'
    );


        /*
        |--------------------------------------------------------------------------
        | Core CMS - Modules
        |--------------------------------------------------------------------------
        |
        | Installed Modules and Module Marketplace.
        |
        | Website Type / Wizard provisioning remains authoritative for
        | modules assigned during initial website composition.
        |
        */

        Route::get(
            '/admin/modules',
            [
                \App\Http\Controllers\TenantModuleController::class,
                'index',
            ]
        )->name('tenant.cms.modules.index');

        Route::get(
            '/admin/modules/marketplace',
            [
                \App\Http\Controllers\TenantModuleController::class,
                'marketplace',
            ]
        )->name('tenant.cms.modules.marketplace');

        Route::post(
            '/admin/modules/{module}/enable',
            [
                \App\Http\Controllers\TenantModuleController::class,
                'enable',
            ]
        )->name('tenant.cms.modules.enable');

        Route::post(
            '/admin/modules/{module}/disable',
            [
                \App\Http\Controllers\TenantModuleController::class,
                'disable',
            ]
        )->name('tenant.cms.modules.disable');



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




        /*
         * ESUBIZ_GENERIC_INSTALLED_THEME_PREVIEW_V6C
         *
         * Universal installed-theme preview endpoint.
         */
        Route::get(
            '/admin/themes/{theme}/preview-image',
            [
                \App\Http\Controllers\TenantThemeController::class,
                'themePreview',
            ]
        )->name('tenant.cms.themes.preview');


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

        /*
         * ESUBIZ_TENANT_USER_SHOW_ROUTE_V27
         *
         * Direct user URLs use the existing authoritative edit screen.
         */
        Route::get(
            '/admin/users/{user}',
            [
                \App\Http\Controllers\TenantUsersController::class,
                'edit',
            ]
        )
            ->whereNumber('user')
            ->name('tenant.cms.users.show');

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
            ->where('slug', '(?!(?:auth|admin|sso)$)[^/]+')
    /* ESUBIZ_CORE_PUBLIC_PAGE_ROUTE_REGEX_V55 */
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
            'dashboard',
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
| Admin Marketplace - Settings
|--------------------------------------------------------------------------
|
| Universal Central Marketplace configuration.
| Categories configured here are shared by Themes, Modules, Add-ons,
| Bundles and Website Types.
|
*/

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/admin/marketplace/settings',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'index',
        ]
    )->name('admin.marketplace.settings.index');

    Route::post(
        '/admin/marketplace/settings/general',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'updateGeneral',
        ]
    )->name('admin.marketplace.settings.general.update');

    Route::post(
        '/admin/marketplace/settings/product-availability',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'updateProductAvailability',
        ]
    )->name('admin.marketplace.settings.availability.update');

    Route::post(
        '/admin/marketplace/settings/financial-rules',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'updateFinancialRule',
        ]
    )->name('admin.marketplace.settings.financial.update');

    Route::post(
        '/admin/marketplace/settings/product-taxes',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'updateProductTax',
        ]
    )->name('admin.marketplace.settings.product-taxes.update');

    Route::delete(
        '/admin/marketplace/settings/product-taxes/{productTax}',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'destroyProductTax',
        ]
    )->name('admin.marketplace.settings.product-taxes.destroy');

    Route::post(
        '/admin/marketplace/settings/expense-rules',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'storeExpenseRule',
        ]
    )->name('admin.marketplace.settings.expenses.store');

    Route::put(
        '/admin/marketplace/settings/expense-rules/{marketplaceExpenseRule}',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'updateExpenseRule',
        ]
    )->name('admin.marketplace.settings.expenses.update');

    Route::delete(
        '/admin/marketplace/settings/expense-rules/{marketplaceExpenseRule}',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'destroyExpenseRule',
        ]
    )->name('admin.marketplace.settings.expenses.destroy');

    Route::post(
        '/admin/marketplace/settings/referral-rules',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'storeReferralRule',
        ]
    )->name('admin.marketplace.settings.referrals.store');

    Route::put(
        '/admin/marketplace/settings/referral-rules/{marketplaceReferralRule}',
        [\App\Http\Controllers\Admin\MarketplaceSettingsController::class, 'updateReferralRule']
    )->name('admin.marketplace.settings.referrals.update');

    Route::delete(
        '/admin/marketplace/settings/referral-rules/{marketplaceReferralRule}',
        [
            \App\Http\Controllers\Admin\MarketplaceSettingsController::class,
            'destroyReferralRule',
        ]
    )->name('admin.marketplace.settings.referrals.destroy');

    Route::post(
        '/admin/marketplace/settings/categories',
        [
            \App\Http\Controllers\Admin\MarketplaceCategoryController::class,
            'store',
        ]
    )->name('admin.marketplace.categories.store');

    Route::put(
        '/admin/marketplace/settings/categories/{marketplaceCategory}',
        [
            \App\Http\Controllers\Admin\MarketplaceCategoryController::class,
            'update',
        ]
    )->name('admin.marketplace.categories.update');

    Route::delete(
        '/admin/marketplace/settings/categories/{marketplaceCategory}',
        [
            \App\Http\Controllers\Admin\MarketplaceCategoryController::class,
            'destroy',
        ]
    )->name('admin.marketplace.categories.destroy');
});


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

        /* ESUBIZ_THEME_MANAGEMENT_ROUTES_V2 */

        Route::get(
            '/admin/marketplace/themes/create',
            [
                \App\Http\Controllers\Admin\ThemeController::class,
                'create'
            ]
        )->name('admin.themes.create');


        Route::get(
            '/admin/marketplace/themes/{theme}',
            [
                \App\Http\Controllers\Admin\ThemeController::class,
                'show'
            ]
        )->whereNumber('theme')
         ->name('admin.themes.show');


        Route::get(
            '/admin/marketplace/themes/{theme}/edit',
            [
                \App\Http\Controllers\Admin\ThemeController::class,
                'edit'
            ]
        )->whereNumber('theme')
         ->name('admin.themes.edit');


        Route::delete(
            '/admin/marketplace/themes/{theme}',
            [
                \App\Http\Controllers\Admin\ThemeController::class,
                'destroy'
            ]
        )->whereNumber('theme')
         ->name('admin.themes.destroy');



        /* ESUBIZ_ADMIN_THEME_PACKAGE_INGESTION_ROUTE_V1 */
        Route::post(
            '/admin/themes/packages',
            [
                \App\Http\Controllers\Admin\ThemeController::class,
                'storePackage'
            ]
        )->name('admin.themes.packages.store');


    /*
    |--------------------------------------------------------------------------
    | Admin Marketplace - Modules
    |--------------------------------------------------------------------------
    |
    | Central owns Module catalogue, package, pricing, compatibility,
    | Wizard visibility and Core integration configuration.
    |
    */

    Route::get(
        '/admin/marketplace/modules',
        [
            \App\Http\Controllers\Admin\ModuleController::class,
            'index',
        ]
    )->name('admin.modules.index');

    Route::get(
        '/admin/marketplace/modules/create',
        [
            \App\Http\Controllers\Admin\ModuleController::class,
            'create',
        ]
    )->name('admin.modules.create');

    Route::post(
        '/admin/marketplace/modules',
        [
            \App\Http\Controllers\Admin\ModuleController::class,
            'store',
        ]
    )->name('admin.modules.store');

    Route::get(
        '/admin/marketplace/modules/{module}',
        [
            \App\Http\Controllers\Admin\ModuleController::class,
            'show',
        ]
    )->whereNumber('module')
     ->name('admin.modules.show');

    Route::get(
        '/admin/marketplace/modules/{module}/edit',
        [
            \App\Http\Controllers\Admin\ModuleController::class,
            'edit',
        ]
    )->whereNumber('module')
     ->name('admin.modules.edit');

    Route::put(
        '/admin/marketplace/modules/{module}',
        [
            \App\Http\Controllers\Admin\ModuleController::class,
            'update',
        ]
    )->whereNumber('module')
     ->name('admin.modules.update');

    Route::delete(
        '/admin/marketplace/modules/{module}',
        [
            \App\Http\Controllers\Admin\ModuleController::class,
            'destroy',
        ]
    )->whereNumber('module')
     ->name('admin.modules.destroy');

    Route::post(
        '/admin/modules/packages',
        [
            \App\Http\Controllers\Admin\ModuleController::class,
            'storePackage',
        ]
    )->name('admin.modules.packages.store');


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


/*
|--------------------------------------------------------------------------
| ESUBIZ_CENTRAL_SITE_SETTINGS_SHELL_V1
|--------------------------------------------------------------------------
| Central configuration hub. Existing feature controllers remain
| authoritative; this page only provides the unified settings navigation.
*/
Route::get('/admin/site-settings', function () {
    return view('admin.site-settings.index');
})->middleware(['auth'])->name('admin.site-settings.index');

/*
|--------------------------------------------------------------------------
| Central Platform Migration & Updates
|--------------------------------------------------------------------------
|
| Existing Core tenant updates are controlled from:
| Admin > Site Settings > Migration & Updates.
|
| These routes do not expose tenant installer/seeder operations.
|
*/

Route::post(
    '/admin/site-settings/updates',
    [\App\Http\Controllers\Admin\PlatformUpdateController::class, 'store']
)->middleware(['auth'])
    ->name('admin.site-settings.updates.store');

Route::patch(
    '/admin/site-settings/updates/{updateId}/status',
    [\App\Http\Controllers\Admin\PlatformUpdateController::class, 'status']
)->middleware(['auth'])
    ->whereNumber('updateId')
    ->name('admin.site-settings.updates.status');

Route::post(
    '/admin/site-settings/updates/{updateId}/dry-run',
    [\App\Http\Controllers\Admin\PlatformUpdateController::class, 'dryRun']
)->middleware(['auth'])
    ->whereNumber('updateId')
    ->name('admin.site-settings.updates.dry-run');

Route::post(
    '/admin/site-settings/updates/{updateId}/execute',
    [\App\Http\Controllers\Admin\PlatformUpdateController::class, 'execute']
)->middleware(['auth'])
    ->whereNumber('updateId')
    ->name('admin.site-settings.updates.execute');


/*
|--------------------------------------------------------------------------
| ESUBIZ_PLATFORM_UPDATE_SETTINGS_ROUTE_V1
|--------------------------------------------------------------------------
|
| Central lifecycle configuration for Platform Updates.
| Manual/Automatic mode, timing, backup, restore, health checks,
| cleanup and notification preferences are persisted here.
|
| Automatic scheduler execution remains protected by the controller's
| automatic_ready configuration guard.
|
*/
Route::patch(
    '/admin/site-settings/updates/settings',
    [
        \App\Http\Controllers\Admin\PlatformUpdateSettingController::class,
        'update'
    ]
)
    ->middleware(['auth'])
    ->name('admin.site-settings.updates.settings.update');


/*
|--------------------------------------------------------------------------
| ESUBIZ_DASHBOARD_NOTICE_ROUTES_V1
|--------------------------------------------------------------------------
|
| Central Admin-controlled tenant dashboard notices.
| Independent from Platform Update Manual/Automatic settings.
|
*/
Route::post(
    '/admin/site-settings/dashboard-notices',
    [
        \App\Http\Controllers\Admin\DashboardNoticeController::class,
        'store'
    ]
)
    ->middleware(['auth'])
    ->name('admin.site-settings.dashboard-notices.store');

Route::patch(
    '/admin/site-settings/dashboard-notices/{noticeId}',
    [
        \App\Http\Controllers\Admin\DashboardNoticeController::class,
        'update',
    ]
)
    ->whereNumber('noticeId')
    ->middleware(['auth'])
    ->name('admin.site-settings.dashboard-notices.update');


Route::patch(
    '/admin/site-settings/dashboard-notices/{noticeId}/status',
    [
        \App\Http\Controllers\Admin\DashboardNoticeController::class,
        'status'
    ]
)
    ->middleware(['auth'])
    ->whereNumber('noticeId')
    ->name('admin.site-settings.dashboard-notices.status');

Route::delete(
    '/admin/site-settings/dashboard-notices/{noticeId}',
    [
        \App\Http\Controllers\Admin\DashboardNoticeController::class,
        'destroy'
    ]
)
    ->middleware(['auth'])
    ->whereNumber('noticeId')
    ->name('admin.site-settings.dashboard-notices.destroy');


/*
|--------------------------------------------------------------------------
| ESUBIZ_CENTRAL_SITE_PAGES_V1
|--------------------------------------------------------------------------
| Central Esubiz public-site page management only.
| This does not use or modify tenant/Core page routes.
*/
Route::get(
    '/admin/site-pages',
    [\App\Http\Controllers\Admin\SitePageController::class, 'index']
)->middleware(['auth'])->name('admin.site-pages.index');


/*
 * ESUBIZ_WEBSITE_WIZARD_SETTINGS_ROUTE_V1
 */
Route::post(
    '/admin/site-settings/website-wizard',
    [
        \App\Http\Controllers\Admin\WebsiteWizardSettingsController::class,
        'update'
    ]
)
    ->middleware(['auth'])
    ->name('admin.site-settings.website-wizard.update');



/*
 * ESUBIZ_CENTRAL_SSO_SETTINGS_ROUTE_V1
 */
Route::post(
    '/admin/site-settings/auth/esubiz-sso',
    [\App\Http\Controllers\Admin\EsubizSsoSettingsController::class, 'update']
)->middleware(['auth'])->name('admin.site-settings.auth.esubiz-sso.update');

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


/* ESUBIZ_ADDON_PREVIEW_ROUTE_V534 */
Route::get('/marketplace/addons/{type}/{id}/preview-image', function (string $type, int $id) {
    abort_unless(in_array($type, ['addon', 'bundle'], true), 404);

    $table = $type === 'bundle'
        ? 'core_addon_bundles'
        : 'core_addons';

    $product = \Illuminate\Support\Facades\DB::table($table)
        ->where('id', $id)
        ->first();

    abort_unless($product && !empty($product->preview_image), 404);

    $relativePath = ltrim((string) $product->preview_image, '/');
    $path = storage_path('app/public/' . $relativePath);

    abort_unless(
        is_file($path)
        && str_starts_with(
            realpath($path) ?: '',
            realpath(storage_path('app/public')) ?: '__invalid__'
        ),
        404
    );

    return response()->file($path);
})->name('marketplace.addons.preview');

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

    Route::get('/developer/marketplace/addons/off-server', [\App\Http\Controllers\MarketplaceController::class, 'developerAddonsOffServer'])
        ->name('developer.marketplace.addons.off-server');

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

    /*
     * ESUBIZ_WEBSITE_TYPE_MANAGEMENT_ROUTES_V9
     */
    Route::get('/admin/website-types/{websiteType}', [WebsiteTypeController::class, 'show'])
        ->middleware(['permission:roles.view', 'account-mode:admin'])
        ->name('admin.website-types.show');

    Route::patch('/admin/website-types/{websiteType}/status', [WebsiteTypeController::class, 'toggleStatus'])
        ->middleware(['permission:roles.view', 'account-mode:admin'])
        ->name('admin.website-types.status');

    Route::delete('/admin/website-types/{websiteType}', [WebsiteTypeController::class, 'destroy'])
        ->middleware(['permission:roles.view', 'account-mode:admin'])
        ->name('admin.website-types.destroy');

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

    /*
     * ESUBIZ_DEVELOPER_WEBSITE_BUILDER_HUB_V1
     *
     * Developer Website Builder entry point.
     * SaaS uses the canonical User-mode wizard.
     * Off-server uses the existing Developer compiler/builder.
     */
    Route::view(
        '/developer/website-builder',
        'developer.website-builder'
    )->name('developer.website-builder');

    Route::get('/developer/builder', [DeveloperBuilderController::class, 'index'])
        ->middleware('account-mode:developer')
        ->name('developer.builder');

    Route::post('/developer/builder/create', [DeveloperBuilderController::class, 'create'])
        ->middleware('account-mode:developer')
        ->name('developer.builder.create');

// ESUBIZ_DEVELOPER_BUILD_ACTION_ROUTES_V1

// ESUBIZ_DEVELOPER_LIVE_COMPILATION_V1
Route::post(
    '/developer/builder/builds/{build}/compile',
    [DeveloperBuilderController::class, 'compile']
)->name('developer.builder.compile');

// ESUBIZ_DEVELOPER_BUILD_STATUS_V1
Route::get(
    '/developer/builder/builds/{build}/status',
    [DeveloperBuilderController::class, 'status']
)->name('developer.builder.status');

Route::post(
    '/developer/builder/builds/{build}/recompile',
    [DeveloperBuilderController::class, 'recompile']
)->name('developer.builder.recompile');

Route::post(
    '/developer/builder/builds/{build}/payment',
    [DeveloperBuilderController::class, 'proceedToPayment']
)->name('developer.builder.payment');


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

    Route::post('/websites/{website}/administrator', [WebsiteWizardController::class, 'administrator'])
        ->name('websites.administrator.save');

    /*
    |--------------------------------------------------------------------------
    | Step 8
    |--------------------------------------------------------------------------
    */

    Route::get('/websites/{website}/review', [WebsiteWizardController::class, 'review'])
        ->name('websites.review');

    Route::post('/websites/{website}/deploy', [WebsiteWizardController::class, 'deploy'])
        ->name('websites.deploy');

    Route::get(
        '/websites/{website}/deployment-progress',
        [WebsiteWizardController::class, 'deploymentProgress']
    )->name('websites.deployment-progress');

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

    Route::get('/core/features/list', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'features'])
        ->name('core-features.list');

    Route::get('/core/features/tenant-tables', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'tenantTables'])
        ->name('core-features.tenant-tables');

    Route::get('/core/features/{id}/management', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'management'])
        ->name('core-features.management');

    Route::post('/core/features', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'storeFeature'])
        ->name('core-features.store');

    Route::post('/core/features/{id}/update', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'updateFeature'])
        ->name('core-features.update');

    Route::post('/core/features/{id}/toggle', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'toggleFeature'])
        ->name('core-features.toggle');

    Route::post('/core/features/{id}/delete', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'destroyFeature'])
        ->name('core-features.destroy');

    Route::get('/core/features/{featureId}/limits', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'limits'])
        ->name('core-features.limits.index');

    Route::post('/core/features/{featureId}/limits', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'storeLimit'])
        ->name('core-features.limits.store');

    Route::post('/core/features/limits/{id}/update', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'updateLimit'])
        ->name('core-features.limits.update');

    Route::post('/core/features/limits/{id}/toggle', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'toggleLimit'])
        ->name('core-features.limits.toggle');

    Route::post('/core/features/limits/{id}/delete', [\App\Http\Controllers\Admin\CoreFeatureController::class, 'destroyLimit'])
        ->name('core-features.limits.destroy');

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

// ESUBIZ_DEVELOPER_BUILD_PROTECTED_DOWNLOAD_ROUTE_V1
Route::get(
    '/marketplace/developer/library/builds/{build}/download',
    [MarketplaceController::class, 'developerBuildDownload']
)
    ->name('marketplace.developer.library.build.download')
    ->middleware('auth');

Route::get('/marketplace/developer/pending-checkouts', [MarketplaceController::class, 'developerPendingCheckouts'])
    ->name('marketplace.developer.pending-checkouts');
Route::get('/marketplace/developer/checkout/{productType}/{productId}', [MarketplaceController::class, 'developerCheckout'])
    ->name('marketplace.developer.checkout');
Route::get('/marketplace/developer/addons', [MarketplaceController::class, 'developerAddons'])
    ->name('marketplace.developer.addons');

Route::get('/marketplace/developer/addons/off-server', [MarketplaceController::class, 'developerAddonsOffServer'])
    ->name('marketplace.developer.addons.off-server');


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
| ESUBIZ_OFF_SERVER_CORE_DASHBOARD_NOTICES_API_V1
|--------------------------------------------------------------------------
|
| Authenticated off-server Core -> Central Dashboard Notices.
|
| Authentication and website authority are resolved exclusively from
| the current installation bearer token. The caller never chooses the
| authoritative Central website identity.
|
*/

Route::get(
    '/api/v1/core/dashboard-notices',
    [
        \App\Http\Controllers\Api\OffServerDashboardNoticeController::class,
        'index',
    ]
)
    ->middleware('throttle:60,1')
    ->name(
        'api.core.dashboard-notices.index'
    );



/*
 * ESUBIZ_PUBLIC_OFF_SERVER_MARKETPLACE_CATALOG_V1
 *
 * Read-only Central product feed for marketplace.esubiz.com.
 */
Route::get(
    '/api/marketplace/off-server/catalog',
    [
        \App\Http\Controllers\Api\OffServerMarketplaceController::class,
        'catalog',
    ]
)->name('api.marketplace.off-server.catalog');

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



/*
|--------------------------------------------------------------------------
| ESUBIZ_DEVELOPER_THEME_MARKETPLACE_ROUTES_V1
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth',
    'account-mode:developer',
])->group(function () {
    Route::get(
        '/developer/marketplace/themes',
        [\App\Http\Controllers\MarketplaceController::class, 'developerThemes']
    )->name('developer.marketplace.themes');

    Route::get(
        '/developer/marketplace/themes/off-server',
        [\App\Http\Controllers\MarketplaceController::class, 'developerThemesOffServer']
    )->name('developer.marketplace.themes.off-server');
});


/*
|--------------------------------------------------------------------------
| ESUBIZ_USER_THEME_MARKETPLACE_ROUTE_V1
|--------------------------------------------------------------------------
|
| Canonical SaaS Theme Marketplace.
| User, Developer and Platform Admin accounts share this exact URL.
|
*/
Route::get(
    '/marketplace/themes',
    [\App\Http\Controllers\MarketplaceController::class, 'themes']
)->middleware([
    'auth',
    'account-mode:user',
])->name('marketplace.themes');


/*
|--------------------------------------------------------------------------
| ESUBIZ_THEME_MARKETPLACE_CATALOG_ROUTE_V1
|--------------------------------------------------------------------------
|
| Universal Theme Marketplace catalog.
| Central commerce remains authoritative for SaaS and off-server Core.
|
*/
Route::get(
    '/marketplace/themes/catalog',
    [\App\Http\Controllers\MarketplaceController::class, 'themeCatalog']
)->name('marketplace.themes.catalog');


/*
|--------------------------------------------------------------------------
| ESUBIZ_MODULE_MARKETPLACE_PAGES_V1
|--------------------------------------------------------------------------
*/
Route::get(
    '/marketplace/modules',
    [\App\Http\Controllers\MarketplaceController::class, 'modules']
)->middleware([
    'auth',
    'account-mode:user',
])->name('marketplace.modules');


Route::middleware([
    'auth',
    'account-mode:developer',
])->group(function () {
    Route::get(
        '/developer/marketplace/modules',
        [\App\Http\Controllers\MarketplaceController::class, 'developerModules']
    )->name('developer.marketplace.modules');

    Route::get(
        '/developer/marketplace/modules/off-server',
        [\App\Http\Controllers\MarketplaceController::class, 'developerModulesOffServer']
    )->name('developer.marketplace.modules.off-server');
});


/*
|--------------------------------------------------------------------------
| ESUBIZ_MODULE_MARKETPLACE_CATALOG_ROUTE_V1
|--------------------------------------------------------------------------
|
| Universal Module Marketplace catalog.
| Central commerce remains authoritative for SaaS and off-server Core.
|
*/
Route::get(
    '/marketplace/modules/catalog',
    [\App\Http\Controllers\MarketplaceController::class, 'moduleCatalog']
)->name('marketplace.modules.catalog');

/*
 * ESUBIZ_THEME_MARKETPLACE_PREVIEW_ROUTE_V1
 */
Route::get(
    '/marketplace/themes/{themePackageId}/preview',
    [
        \App\Http\Controllers\MarketplaceController::class,
        'themePreview'
    ]
)->whereNumber(
    'themePackageId'
)->name(
    'marketplace.themes.preview'
);




/*
|--------------------------------------------------------------------------
| ESUBIZ_CENTRAL_MEDIA_ROUTE_V1
|--------------------------------------------------------------------------
|
| Stable public delivery for Central Esubiz media stored under:
| storage/app/public/media
|
| Admin-managed media can later replace files without changing URLs.
| Path traversal is blocked and only real files inside the media root
| are served.
|
*/

Route::get('/media/{path}', function (string $path) {

    /*
     * ESUBIZ_CENTRAL_CANONICAL_BRANDING_MEDIA_V4
     *
     * Stable public branding URLs.
     *
     * Existing Theme Hub / public frontend consumers keep using
     * the same public paths. Main Settings only replaces the
     * backing file referenced by logo_path / favicon_path.
     */
    $centralBrandingAliasesV4 = [
        'branding/esubiz-logo.png' => 'logo_path',

        /*
         * Support the canonical favicon path plus historical
         * favicon-style requests without creating another source.
         */
        'branding/favicon.png' => 'favicon_path',
        'branding/esubiz-favicon.png' => 'favicon_path',
        'branding/favicon.ico' => 'favicon_path',
    ];

    if (
        array_key_exists(
            $path,
            $centralBrandingAliasesV4
        )
    ) {
        $settingKeyV4 =
            $centralBrandingAliasesV4[$path];

        $storedPathV4 =
            \Illuminate\Support\Facades\DB::table(
                'site_settings'
            )
                ->whereNull('workspace_id')
                ->where('key', $settingKeyV4)
                ->value('value');

        if (
            $storedPathV4
            && \Illuminate\Support\Facades\Storage::disk(
                'public'
            )->exists($storedPathV4)
        ) {
            $absolutePathV4 =
                \Illuminate\Support\Facades\Storage::disk(
                    'public'
                )->path($storedPathV4);

            $mimeV4 =
                mime_content_type(
                    $absolutePathV4
                )
                ?: 'application/octet-stream';

            /*
             * ESUBIZ_BRANDING_HTTP_CACHE_V12
             *
             * Branding images are already optimized by CentralMediaService.
             * This layer does not re-encode, resize or flatten the image,
             * so PNG/WebP transparency remains completely intact.
             *
             * Browser caching:
             * - 7 day fresh cache
             * - stale-while-revalidate for another 30 days
             * - ETag
             * - Last-Modified
             * - conditional 304 responses
             */
            $esBrandingMtimeV12 = filemtime($absolutePathV4) ?: time();
            $esBrandingSizeV12 = filesize($absolutePathV4) ?: 0;

            $esBrandingEtagV12 = '"' . sha1(
                $absolutePathV4
                . '|'
                . $esBrandingMtimeV12
                . '|'
                . $esBrandingSizeV12
            ) . '"';

            $esBrandingLastModifiedV12 =
                gmdate(
                    'D, d M Y H:i:s',
                    $esBrandingMtimeV12
                )
                . ' GMT';

            $esIfNoneMatchV12 =
                request()->header('If-None-Match');

            $esIfModifiedSinceV12 =
                request()->header('If-Modified-Since');

            $esBrandingCacheHeadersV12 = [
                'Cache-Control' =>
                    'public, max-age=604800, stale-while-revalidate=2592000',

                'ETag' =>
                    $esBrandingEtagV12,

                'Last-Modified' =>
                    $esBrandingLastModifiedV12,

                'X-Content-Type-Options' =>
                    'nosniff',
            ];

            if (
                $esIfNoneMatchV12 === $esBrandingEtagV12
                || (
                    !$esIfNoneMatchV12
                    && $esIfModifiedSinceV12
                    && strtotime(
                        $esIfModifiedSinceV12
                    ) >= $esBrandingMtimeV12
                )
            ) {
                return response(
                    '',
                    304,
                    $esBrandingCacheHeadersV12
                );
            }

            return response()->file(
                $absolutePathV4,
                array_merge(
                    [
                        'Content-Type' =>
                            mime_content_type($absolutePathV4)
                            ?: 'application/octet-stream',
                    ],
                    $esBrandingCacheHeadersV12
                )
            );
        }
    }

    $mediaRoot = realpath(storage_path('app/public/media'));

    if ($mediaRoot === false) {
        abort(404);
    }

    $requested = realpath(
        storage_path('app/public/media/' . ltrim($path, '/'))
    );

    if (
        $requested === false
        || !is_file($requested)
        || !str_starts_with(
            $requested,
            $mediaRoot . DIRECTORY_SEPARATOR
        )
    ) {
        abort(404);
    }

    $mime = mime_content_type($requested)
        ?: 'application/octet-stream';

    return response()->file(
        $requested,
        [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]
    );
})
    ->where('path', '.*')
    ->name('central.media');



/*
|--------------------------------------------------------------------------
| ESUBIZ_MARKETPLACE_LICENSE_VALIDATION_ROUTE_V1
|--------------------------------------------------------------------------
|
| Machine-to-machine validation endpoint for standalone/off-server Core.
|
| CSRF is intentionally excluded because this is not a browser form
| endpoint. The supplied Marketplace license + Core installation identity
| are validated by the Central licensing authority.
|
*/

Route::post(
    '/marketplace/licenses/validate',
    [
        \App\Http\Controllers\MarketplaceLicenseController::class,
        'validateLicense',
    ]
)
    ->withoutMiddleware(
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class
    )
    ->middleware('throttle:30,1')
    ->name('marketplace.licenses.validate');




/*
|--------------------------------------------------------------------------
| ESUBIZ_MARKETPLACE_DEPLOYMENT_API_ROUTE_V1
|--------------------------------------------------------------------------
|
| Machine-to-machine deployment metadata endpoint for authenticated
| Esubiz Core instances.
|
| UUID identifies the deployment, while the dedicated per-deployment
| bearer token authorizes retrieval.
|
*/

Route::get(
    '/marketplace/deployments/{uuid}',
    [
        \App\Http\Controllers\Api\MarketplaceDeploymentController::class,
        'show',
    ]
)
    ->middleware('throttle:60,1')
    ->whereUuid('uuid')
    ->name('marketplace.deployments.show');


/*
|--------------------------------------------------------------------------
| ESUBIZ_CORE_FIRST_RUN_SETUP_ROUTES_V1
|--------------------------------------------------------------------------
|
| Internal first-run setup endpoints for OFF-SERVER Core.
|
| Customer entry is still the root domain:
|
|     https://example.com/
|
| The CoreFirstRunGate redirects an uninstalled off-server Core here.
| These routes return 404 on SaaS and after installation completes.
|
*/

Route::get(
    '/_core/setup',
    [
        \App\Http\Controllers\Core\CoreSetupController::class,
        'start',
    ]
)->name('core.setup.start');

Route::post(
    '/_core/setup',
    [
        \App\Http\Controllers\Core\CoreSetupController::class,
        'store',
    ]
)->name('core.setup.store');



/*
|--------------------------------------------------------------------------
| ESUBIZ_CORE_FIRST_RUN_REAL_STEP_ROUTES_V1
|--------------------------------------------------------------------------
|
| Server-side actions used by the root-domain first-run wizard.
|
*/

Route::post(
    '/_core/setup/validate-license',
    [
        \App\Http\Controllers\Core\CoreSetupController::class,
        'validateLicense',
    ]
)->name('core.setup.validate-license');

Route::post(
    '/_core/setup/system-check',
    [
        \App\Http\Controllers\Core\CoreSetupController::class,
        'systemCheck',
    ]
)->name('core.setup.system-check');



/*
|--------------------------------------------------------------------------
| ESUBIZ_CORE_FIRST_RUN_DATABASE_TEST_ROUTE_V1
|--------------------------------------------------------------------------
|
| Real isolated database connection test used by installer Step 3.
|
*/

Route::post(
    '/_core/setup/database-test',
    [
        \App\Http\Controllers\Core\CoreSetupController::class,
        'testDatabase',
    ]
)->name('core.setup.database-test');



/*
|--------------------------------------------------------------------------
| ESUBIZ_CORE_FIRST_RUN_ADMINISTRATOR_ROUTE_V1
|--------------------------------------------------------------------------
|
| Validates/stages the first local administrator before final install.
|
*/

Route::post(
    '/_core/setup/administrator',
    [
        \App\Http\Controllers\Core\CoreSetupController::class,
        'validateAdministrator',
    ]
)->name('core.setup.administrator');


/*
|--------------------------------------------------------------------------
| ESUBIZ_CENTRAL_MAIN_SITE_SETTINGS_ROUTE_V1
|--------------------------------------------------------------------------
|
| Central is the authoritative source for the shared Esubiz platform
| settings used by Central, SaaS and off-server integrations.
|
*/
Route::post(
    '/admin/marketplace/settings/developer-product-policy',
    [DeveloperProductTypePolicyController::class, 'update']
)->name('admin.marketplace.settings.developer-product-policy.update');

Route::middleware('auth')
    ->patch(
        '/admin/site-settings/main',
        [CentralSiteSettingsController::class, 'update']
    )
    ->name('admin.site-settings.main.update');


/*
|--------------------------------------------------------------------------
| ESUBIZ_PUBLIC_CENTRAL_MEDIA_V18
|--------------------------------------------------------------------------
|
| Public delivery for Central platform media stored under:
|
| storage/app/public/central-media/
|
| This avoids dependence on the web server's blocked /storage
| symbolic-link path.
|
*/
\Illuminate\Support\Facades\Route::get(
    '/central-media/{path}',
    function (string $path) {
        $path = trim(
            str_replace(
                '\\',
                '/',
                $path
            ),
            '/'
        );

        if (
            $path === ''
            || str_contains($path, '..')
            || str_starts_with($path, '.')
        ) {
            abort(404);
        }

        $base = storage_path(
            'app/public/central-media'
        );

        $candidate = $base
            . DIRECTORY_SEPARATOR
            . str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $path
            );

        $realBase = realpath($base);
        $realFile = realpath($candidate);

        if (
            $realBase === false
            || $realFile === false
            || !is_file($realFile)
            || !str_starts_with(
                $realFile,
                $realBase . DIRECTORY_SEPARATOR
            )
        ) {
            abort(404);
        }

        return response()->file(
            $realFile,
            [
                'Cache-Control' =>
                    'public, max-age=86400',
            ]
        );
    }
)->where(
    'path',
    '.*'
)->name(
    'central.media.public'
);

/*
|--------------------------------------------------------------------------
| Central Profile Settings
|--------------------------------------------------------------------------
| ESUBIZ_CENTRAL_PROFILE_ROUTES_V1
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\CentralProfileController::class, 'edit'])
        ->name('central.profile.edit');

    Route::get(
    '/profile/photo',
    [\App\Http\Controllers\CentralProfileController::class, 'photo']
)
    ->middleware('auth')
    ->name('central.profile.photo');

    // ESUBIZ_CENTRAL_PROFILE_PHOTO_DELETE_V35
    Route::delete(
        'profile/photo',
        [\App\Http\Controllers\CentralProfileController::class, 'destroyPhoto']
    )->name('central.profile.photo.destroy');


Route::put('/profile', [\App\Http\Controllers\CentralProfileController::class, 'update'])
        ->name('central.profile.update');

    Route::put('/profile/password', [\App\Http\Controllers\CentralProfileController::class, 'password'])
        ->name('central.profile.password');
});

<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\Core\CoreCentralConnectionService;
use App\Services\Marketplace\CreditVolumeCheckoutService;
use App\Services\Marketplace\CreditVolumePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TenantCreditVolumeController extends Controller
{
    private function website(Request $request): Website
    {
        $tenant = \App\Models\WebsiteTenant::current();
        abort_unless($tenant, 404, 'Website tenant not found.');

        $website = Website::query()
            ->where('id', $tenant->website_id)
            ->where('status', 'active')
            ->where('user_enabled', true)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $key = 'tenant_cms_sites.' . $website->id;
        $session = $request->session();

        if ($session->get($key . '.authenticated') !== true) {
            $remember = $request->cookie('core_remember_' . $website->id);

            \Illuminate\Support\Facades\Log::notice('CORE_CREDIT_REMEMBER_FORMAT', [
                'website_id' => (int) $website->id,
                'cookie_is_string' => is_string($remember),
                'cookie_has_separator' => is_string($remember) && str_contains($remember, ':'),
                'request_site_authenticated' => (bool) $session->get($key . '.authenticated'),
                'global_site_authenticated' => (bool) session($key . '.authenticated'),
            ]);

            if (is_string($remember) && str_contains($remember, ':')) {
                [$selector, $plainToken] = explode(':', $remember, 2);

                if ($selector !== '' && $plainToken !== '') {
                    $service = app(
                        \App\Services\Website\WebsiteTenantDatabaseService::class
                    );
                    $service->connect($website);

                    $user = $service->connection()
                        ->table('site_users')
                        ->where('is_active', true)
                        ->where('remember_token', 'like', $selector . ':%')
                        ->first();

                    \Illuminate\Support\Facades\Log::notice('CORE_CREDIT_REMEMBER_LOOKUP', [
                        'website_id' => (int) $website->id,
                        'user_found' => (bool) $user,
                        'selector_format_valid' => (bool) preg_match('/^[a-f0-9]{24}$/D', $selector),
                        'token_format_valid' => (bool) preg_match('/^[a-f0-9]{64}$/D', $plainToken),
                        'stored_token_is_string' => $user && is_string($user->remember_token),
                        'token_matches' => $user && is_string($user->remember_token)
                            && hash_equals(
                                $user->remember_token,
                                $selector . ':' . hash('sha256', $plainToken)
                            ),
                    ]);

                    if ($user && is_string($user->remember_token)) {
                        $stored = explode(':', $user->remember_token, 2);

                        if (count($stored) === 2
                            && hash_equals($stored[0], $selector)
                            && $stored[1] !== ''
                            && hash_equals(
                                $stored[1],
                                hash('sha256', $plainToken)
                            )
                        ) {
                            $session->put([
                                $key . '.authenticated' => true,
                                $key . '.user_id' => (int) $user->id,
                                $key . '.auth_method' => 'local_remember',
                                $key . '.support_access' => false,
                            ]);
                            $session->regenerate();
                        }
                    }
                }
            }
        }

        return $website;
    }

    private function data(Request $request): array
    {
        $raw = trim((string) $request->input('quantity', ''));
        if (preg_match('/^[0-9]+(?:\.0+)?$/', $raw)) {
            $number = (float) $raw;
            if (is_finite($number) && $number >= 1 && $number <= 100000000) {
                $request->merge(['quantity' => (int) $number]);
            }
        }

        return $request->validate([
            'credit_type' => ['required', 'in:ai_credits,email_credits'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000000'],
        ]);
    }

    public function quote(Request $request, CoreCentralConnectionService $central, CreditVolumePricingService $pricing)
    {
        $data = $this->data($request);
        $website = $this->website($request);
        $deployment = strtolower((string) $website->deployment_type);

        abort_unless(in_array($deployment, ['saas', 'off_server'], true), 422);

        try {
            if ($deployment === 'saas') {
                return response()->json($pricing->quote(
                    $data['credit_type'],
                    (int) $data['quantity'],
                    'saas'
                ));
            }

            $response = Http::acceptJson()->timeout(8)->get(
                $central->centralUrl() . '/api/marketplace/credit-volume/quote',
                [
                    'credit_type' => $data['credit_type'],
                    'quantity' => $data['quantity'],
                    'deployment_type' => 'off_server',
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'message' => $response->json('message') === 'No active price tier covers this credit quantity.'
                        ? 'Credit prices are not available for this quantity yet.'
                        : ($response->json('message') ?: 'Credit prices are temporarily unavailable.'),
                ], 422);
            }

            return response()->json($response->json());
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception instanceof RuntimeException
                    ? (
                        $exception->getMessage() === 'No active price tier covers this credit quantity.'
                            ? 'Credit prices are not available for this quantity yet.'
                            : $exception->getMessage()
                    )
                    : 'Credit prices are temporarily unavailable.',
            ], 422);
        }
    }

    public function history(
        Request $request,
        CoreCentralConnectionService $central,
        \App\Services\Marketplace\CreditTransactionHistoryService $history
    ) {
        $data = $request->validate([
            'credit_type' => ['required', 'in:ai_credits,sms_credits,email_credits,whatsapp_credits,kyc_credits'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $website = $this->website($request);
        $siteSession = 'tenant_cms_sites.' . $website->id;
        abort_unless(
            (
                (bool) session($siteSession . '.authenticated')
                && (int) session($siteSession . '.user_id') > 0
            ) || (
                (bool) session('tenant_cms_authenticated')
                && (int) session('tenant_cms_website_id') === (int) $website->id
                && (int) session('tenant_cms_user_id') > 0
            ),
            403
        );

        if ($website->deployment_type === 'saas') {
            return response()->json($history->page(
                (int) $website->id,
                $data['credit_type'],
                (int) ($data['page'] ?? 1)
            ));
        }

        abort_unless($website->deployment_type === 'off_server', 422);

        try {
            $response = $central->request()->get(
                $central->centralUrl() . '/api/v1/core/credits/history',
                [
                    'credit_type' => $data['credit_type'],
                    'page' => (int) ($data['page'] ?? 1),
                ]
            );

            return response()->json(
                $response->successful()
                    ? $response->json()
                    : ['message' => 'Credit history is temporarily unavailable.'],
                $response->successful() ? 200 : 422
            );
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'Credit history is temporarily unavailable.',
            ], 422);
        }
    }

    public function tiers(
        Request $request,
        CoreCentralConnectionService $central,
        \App\Services\Marketplace\CreditVolumeTierCatalogService $catalog
    ) {
        $data = $request->validate([
            'credit_type' => ['required', 'in:ai_credits,sms_credits,email_credits,whatsapp_credits,kyc_credits'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $deployment = strtolower((string) $this->website($request)->deployment_type);
        abort_unless(in_array($deployment, ['saas', 'off_server'], true), 422);

        try {
            if ($deployment === 'saas') {
                return response()->json($catalog->page(
                    $data['credit_type'],
                    'saas',
                    (int) ($data['page'] ?? 1)
                ));
            }

            $response = \Illuminate\Support\Facades\Http::acceptJson()
                ->timeout(8)
                ->get(
                    $central->centralUrl() . '/api/marketplace/credit-volume/tiers',
                    [
                        'credit_type' => $data['credit_type'],
                        'deployment_type' => 'off_server',
                        'page' => (int) ($data['page'] ?? 1),
                    ]
                );

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Credit prices are temporarily unavailable.',
                ], 422);
            }

            return response()->json($response->json());
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'Credit prices are temporarily unavailable.',
            ], 422);
        }
    }

    public function checkout(
        Request $request,
        CoreCentralConnectionService $central,
        CreditVolumeCheckoutService $checkouts
    ) {
        $data = $this->data($request);
        $website = $this->website($request);
        $deployment = strtolower((string) $website->deployment_type);

        abort_unless(in_array($deployment, ['saas', 'off_server'], true), 422);

        try {
            if ($deployment === 'saas') {
                $siteSession = 'tenant_cms_sites.' . $website->id;

                $locallyAuthenticated =
                    (bool) session($siteSession . '.authenticated')
                    && (int) session($siteSession . '.user_id') > 0;

                $ssoAuthenticated =
                    (bool) session('tenant_cms_authenticated')
                    && (int) session('tenant_cms_website_id') === (int) $website->id
                    && (int) session('tenant_cms_user_id') > 0;

                if (!$locallyAuthenticated && !$ssoAuthenticated) {
                    \Illuminate\Support\Facades\Log::notice('CORE_CREDIT_SESSION_CHECK', [
                        'host' => $request->getHost(),
                        'source_host' => parse_url(
                            (string) $request->headers->get('referer', ''),
                            PHP_URL_HOST
                        ),
                        'website_id' => (int) $website->id,
                        'site_authenticated' => (bool) session($siteSession . '.authenticated'),
                        'site_user_present' => (int) session($siteSession . '.user_id') > 0,
                        'legacy_authenticated' => (bool) session('tenant_cms_authenticated'),
                        'legacy_website_matches' => (int) session('tenant_cms_website_id') === (int) $website->id,
                        'legacy_user_present' => (int) session('tenant_cms_user_id') > 0,
                        'remember_cookie_present' => $request->hasCookie('core_remember_' . $website->id),
                    ]);
                }

                if (!$locallyAuthenticated && !$ssoAuthenticated) {
                    $purchasePage = $request->headers->get('referer', '');
                    $origin = $request->getSchemeAndHttpHost();

                    if (!str_starts_with($purchasePage, $origin . '/')) {
                        $purchasePage = $origin . '/admin';
                    }

                    $request->session()->put('url.intended', $purchasePage);

                    return redirect()->to($origin . '/login')
                        ->withInput($request->only('credit_type', 'quantity'))
                        ->with('info', 'Sign in to this website to continue your purchase.');
                }

                $path = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'marketplace.credits.saas-handoff',
                    now()->addMinutes(15),
                    [
                        'website_id' => (int) $website->id,
                        'credit_type' => $data['credit_type'],
                        'quantity' => (int) $data['quantity'],
                    ],
                    false
                );

                return redirect()->away(
                    rtrim((string) config('app.url'), '/') . $path
                );
            }

            $returnUrl = $request->headers->get('referer', '');
            $origin = $request->getSchemeAndHttpHost();
            if (!str_starts_with($returnUrl, $origin . '/')) {
                $returnUrl = $origin . '/admin';
            }

            $response = $central->request(15)->post(
                $central->centralUrl()
                . '/api/v1/core/marketplace/credit-volume/checkout-link',
                [
                    'credit_type' => $data['credit_type'],
                    'quantity' => (int) $data['quantity'],
                    'return_url' => $returnUrl,
                ]
            );

            if (!$response->successful() || !$response->json('checkout_url')) {
                throw new RuntimeException(
                    $response->json('message') ?: 'Central could not start credit checkout.'
                );
            }

            return redirect()->away($response->json('checkout_url'));
        } catch (\InvalidArgumentException|RuntimeException $exception) {
            report($exception);
            $message = trim($exception->getMessage());
            throw ValidationException::withMessages([
                'quantity' => $message !== ''
                    ? $message
                    : 'Credit checkout failed (' . class_basename($exception) . ').',
            ]);
        }
    }
}

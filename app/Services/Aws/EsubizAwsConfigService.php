<?php

namespace App\Services\Aws;

use App\Models\Aws\AwsServiceSetting;
use App\Services\Platform\CentralSiteSettingsService;
use Throwable;

class EsubizAwsConfigService
{
    public function __construct(
        protected CentralSiteSettingsService $settings
    ) {
    }

    /**
     * Central database configuration is authoritative.
     *
     * If the AWS settings table has not yet been migrated, or no Central
     * AWS record exists, Laravel configuration remains the compatibility
     * fallback.
     */
    public function centralSetting(): ?AwsServiceSetting
    {
        try {
            return AwsServiceSetting::central();
        } catch (Throwable) {
            return null;
        }
    }

    public function sesRegion(): string
    {
        $setting = $this->centralSetting();

        $region = trim(
            (string) ($setting?->ses_region ?? '')
        );

        if ($region !== '') {
            return $region;
        }

        /*
         * Compatibility with any pre-existing Central setting.
         */
        $region = trim(
            (string) $this->settings->get(
                'email.aws.ses_region',
                ''
            )
        );

        if ($region !== '') {
            return $region;
        }

        $region = trim(
            (string) config(
                'services.ses.region',
                ''
            )
        );

        return $region !== ''
            ? $region
            : 'us-east-2';
    }

    /**
     * Expected SNS topic for SES outbound feedback.
     *
     * Central AWS metadata is authoritative. The existing Central settings
     * namespace remains a compatibility fallback while the Email settings UI
     * is being completed.
     *
     * No topic is guessed or synthesized from account/region information.
     */
    public function feedbackTopicArn(): ?string
    {
        $setting = $this->centralSetting();

        $metadata = is_array($setting?->metadata)
            ? $setting->metadata
            : [];

        $value = trim(
            (string) (
                $metadata['ses_feedback_topic_arn']
                ?? ''
            )
        );

        if ($value !== '') {
            return $value;
        }

        $value = trim(
            (string) $this->settings->get(
                'email.aws.ses_feedback_topic_arn',
                ''
            )
        );

        return $value !== ''
            ? $value
            : null;
    }

    /**
     * AWS Price List API endpoint region is intentionally independent
     * from the SES operational region.
     */
    public function pricingApiRegion(): string
    {
        $setting = $this->centralSetting();

        $region = trim(
            (string) (
                $setting?->pricing_api_region
                ?? ''
            )
        );

        if ($region !== '') {
            return $region;
        }

        $region = trim(
            (string) $this->settings->get(
                'email.aws.pricing_api_region',
                ''
            )
        );

        return $region !== ''
            ? $region
            : 'us-east-1';
    }

    public function senderAddress(): ?string
    {
        $setting = $this->centralSetting();

        $value = trim(
            (string) (
                $setting?->sender_address
                ?? ''
            )
        );

        if ($value !== '') {
            return $value;
        }

        $value = trim(
            (string) $this->settings->get(
                'email.aws.sender_address',
                ''
            )
        );

        if ($value !== '') {
            return $value;
        }

        $fallback = trim(
            (string) config(
                'mail.from.address',
                ''
            )
        );

        return $fallback !== ''
            ? $fallback
            : null;
    }

    public function senderName(): ?string
    {
        $setting = $this->centralSetting();

        $value = trim(
            (string) (
                $setting?->sender_name
                ?? ''
            )
        );

        if ($value !== '') {
            return $value;
        }

        $value = trim(
            (string) $this->settings->get(
                'email.aws.sender_name',
                ''
            )
        );

        if ($value !== '') {
            return $value;
        }

        $fallback = trim(
            (string) config(
                'mail.from.name',
                ''
            )
        );

        return $fallback !== ''
            ? $fallback
            : null;
    }

    /**
     * Credential resolution order:
     *
     * 1. Central encrypted AWS configuration.
     * 2. Existing Laravel/AWS configuration.
     * 3. Unavailable.
     *
     * Saving valid credentials through Central Settings therefore makes
     * them available immediately to every AWS service using this resolver.
     */
    public function credentials(): ?array
    {
        $setting = $this->centralSetting();

        if (
            $setting !== null
            && $setting->is_enabled
        ) {
            $secrets = is_array(
                $setting->secret_payload
            )
                ? $setting->secret_payload
                : [];

            $key = trim(
                (string) (
                    $secrets['access_key_id']
                    ?? ''
                )
            );

            $secret = trim(
                (string) (
                    $secrets['secret_access_key']
                    ?? ''
                )
            );

            if (
                $key !== ''
                && $secret !== ''
            ) {
                return [
                    'key' => $key,
                    'secret' => $secret,
                    'source' =>
                        'central_encrypted',
                ];
            }
        }

        $key = trim(
            (string) config(
                'services.ses.key',
                ''
            )
        );

        $secret = trim(
            (string) config(
                'services.ses.secret',
                ''
            )
        );

        if (
            $key === ''
            || $secret === ''
        ) {
            return null;
        }

        return [
            'key' => $key,
            'secret' => $secret,
            'source' =>
                'laravel_config',
        ];
    }

    public function enabled(): bool
    {
        $setting = $this->centralSetting();

        /*
         * Before a Central AWS record exists, preserve Laravel-config
         * compatibility rather than disabling AWS implicitly.
         */
        return $setting === null
            ? true
            : (bool) $setting->is_enabled;
    }

    public function configured(): bool
    {
        return $this->enabled()
            && $this->credentials() !== null
            && $this->sesRegion() !== ''
            && $this->senderAddress() !== null;
    }

    public function status(): array
    {
        $credentials = $this->credentials();

        return [
            'configured' =>
                $this->configured(),

            'enabled' =>
                $this->enabled(),

            'credentials_available' =>
                $credentials !== null,

            'credential_source' =>
                $credentials['source']
                ?? null,

            'ses_region' =>
                $this->sesRegion(),

            'pricing_api_region' =>
                $this->pricingApiRegion(),

            'ses_feedback_topic_arn' =>
                $this->feedbackTopicArn(),

            'sender_address' =>
                $this->senderAddress(),

            'sender_name' =>
                $this->senderName(),

            /*
             * Never expose the secret access key.
             */
            'access_key_masked' =>
                $credentials !== null
                    ? $this->maskAccessKey(
                        $credentials['key']
                    )
                    : null,
        ];
    }

    protected function maskAccessKey(
        string $key
    ): string {
        $length = strlen($key);

        if ($length <= 8) {
            return str_repeat(
                '*',
                $length
            );
        }

        return substr($key, 0, 4)
            . str_repeat(
                '*',
                $length - 8
            )
            . substr($key, -4);
    }
}

<?php

namespace App\Services\Aws;

use Aws\CostExplorer\CostExplorerClient;
use Aws\Pricing\PricingClient;
use Aws\SesV2\SesV2Client;
use RuntimeException;

class EsubizAwsClientFactory
{
    public function __construct(
        protected EsubizAwsConfigService $config
    ) {
    }

    /**
     * Central SES client used for Esubiz operational email,
     * SaaS Core Default email and Premium email routing.
     */
    public function ses(): SesV2Client
    {
        return new SesV2Client([
            'version' => 'latest',
            'region' => $this->config->sesRegion(),
            'credentials' => $this->sdkCredentials(),
        ]);
    }

    /**
     * AWS Price List API client.
     *
     * Pricing endpoint region is deliberately independent from
     * the region whose SES price is being resolved.
     */
    public function pricing(): PricingClient
    {
        return new PricingClient([
            'version' => 'latest',
            'region' => $this->config->pricingApiRegion(),
            'credentials' => $this->sdkCredentials(),
        ]);
    }

    /**
     * Cost Explorer is kept behind the same credential authority.
     *
     * We will use this later for AWS cost/status reporting where
     * the configured IAM identity has the required permission.
     */
    public function costExplorer(): CostExplorerClient
    {
        return new CostExplorerClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => $this->sdkCredentials(),
        ]);
    }

    /**
     * Returns credentials in the exact structure expected by
     * the AWS SDK.
     *
     * Never expose the returned array to controllers, views,
     * logs or API responses.
     */
    protected function sdkCredentials(): array
    {
        $credentials = $this->config->credentials();

        if ($credentials === null) {
            throw new RuntimeException(
                'Esubiz AWS credentials are not configured.'
            );
        }

        return [
            'key' => $credentials['key'],
            'secret' => $credentials['secret'],
        ];
    }

    public function available(): bool
    {
        return $this->config->configured();
    }
}

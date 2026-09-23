<?php

namespace App\Services\Aws;

use App\Models\Aws\AwsServicePrice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AwsSesPricingService
{
    protected const PROVIDER = 'aws';
    protected const SERVICE = 'ses';

    /*
     * Fast application cache.
     *
     * The database remains the durable last-successful provider
     * price snapshot.
     */
    protected const FRESH_CACHE_HOURS = 12;

    /*
     * We deliberately discover the AWS Price List service code
     * instead of permanently assuming a catalog identifier.
     */
    protected const SERVICE_CODE_CACHE_DAYS = 30;

    public function __construct(
        protected EsubizAwsClientFactory $clients,
        protected EsubizAwsConfigService $config
    ) {
    }

    /**
     * Returns the last verified outbound-email provider price.
     *
     * Resolution:
     * 1. fresh application cache
     * 2. durable database snapshot
     * 3. optional AWS synchronization when credentials exist
     *
     * Provider failure never invents a price.
     */
    public function outboundEmailPrice(
        bool $syncIfMissing = true
    ): ?array {
        $region = $this->config->sesRegion();

        $cacheKey = $this->priceCacheKey(
            'outbound_email',
            $region
        );

        $cached = Cache::get($cacheKey);

        if (
            is_array($cached)
            && isset(
                $cached['normalized_unit_amount']
            )
        ) {
            return $cached;
        }

        $stored = $this->storedPrice(
            'outbound_email',
            $region
        );

        if ($stored !== null) {
            $snapshot = $this->serializePrice(
                $stored
            );

            Cache::put(
                $cacheKey,
                $snapshot,
                now()->addHours(
                    self::FRESH_CACHE_HOURS
                )
            );

            /*
             * A stored provider snapshot remains usable even when
             * AWS is temporarily unavailable.
             *
             * A later scheduler/manual refresh can update it.
             */
            return $snapshot;
        }

        if (
            !$syncIfMissing
            || !$this->clients->available()
        ) {
            return null;
        }

        $result = $this->syncOutboundEmailPrice();

        return $result['price'] ?? null;
    }

    /**
     * Synchronize the applicable SES outbound-email provider price.
     *
     * This method is intentionally safe before AWS credentials exist.
     */
    public function syncOutboundEmailPrice(): array
    {
        if (!$this->clients->available()) {
            return [
                'ok' => false,
                'status' => 'not_configured',
                'message' =>
                    'AWS credentials are not configured.',
                'price' => null,
            ];
        }

        $region = $this->config->sesRegion();

        try {
            $serviceCode =
                $this->discoverSesServiceCode();

            if ($serviceCode === null) {
                throw new RuntimeException(
                    'Amazon SES was not found in the AWS Price List catalog.'
                );
            }

            $products = $this->discoverProducts(
                $serviceCode
            );

            $candidate =
                $this->selectOutboundEmailCandidate(
                    $products,
                    $region
                );

            if ($candidate === null) {
                throw new RuntimeException(
                    'No verified SES outbound-email price dimension was found for region '
                    . $region
                    . '.'
                );
            }

            $price = $this->persistCandidate(
                $serviceCode,
                $region,
                $candidate
            );

            $snapshot =
                $this->serializePrice($price);

            Cache::put(
                $this->priceCacheKey(
                    'outbound_email',
                    $region
                ),
                $snapshot,
                now()->addHours(
                    self::FRESH_CACHE_HOURS
                )
            );

            return [
                'ok' => true,
                'status' => 'synced',
                'message' =>
                    'AWS SES provider price synchronized.',
                'price' => $snapshot,
            ];
        } catch (Throwable $exception) {
            $this->recordSyncFailure(
                'outbound_email',
                $region,
                $exception
            );

            Log::warning(
                'AWS SES pricing synchronization failed.',
                [
                    'region' => $region,
                    'exception' =>
                        get_class($exception),
                    'message' =>
                        $exception->getMessage(),
                ]
            );

            return [
                'ok' => false,
                'status' => 'provider_error',
                'message' =>
                    $exception->getMessage(),
                'price' =>
                    $this->storedPriceSnapshot(
                        'outbound_email',
                        $region
                    ),
            ];
        }
    }

    /**
     * Discover the SES Price List service code dynamically.
     */
    public function discoverSesServiceCode(): ?string
    {
        $cacheKey =
            'esubiz:aws:pricing:ses-service-code';

        $cached = Cache::get($cacheKey);

        if (
            is_string($cached)
            && trim($cached) !== ''
        ) {
            return trim($cached);
        }

        $client = $this->clients->pricing();

        $nextToken = null;

        do {
            $arguments = [
                'MaxResults' => 100,
            ];

            if ($nextToken !== null) {
                $arguments['NextToken'] =
                    $nextToken;
            }

            $result =
                $client->describeServices(
                    $arguments
                );

            foreach (
                $result['Services'] ?? []
                as $service
            ) {
                $serviceCode = trim(
                    (string) (
                        $service['ServiceCode']
                        ?? ''
                    )
                );

                if ($serviceCode === '') {
                    continue;
                }

                if (
                    $this->looksLikeSesServiceCode(
                        $serviceCode
                    )
                ) {
                    Cache::put(
                        $cacheKey,
                        $serviceCode,
                        now()->addDays(
                            self::SERVICE_CODE_CACHE_DAYS
                        )
                    );

                    return $serviceCode;
                }
            }

            $nextToken =
                isset($result['NextToken'])
                && trim(
                    (string) $result['NextToken']
                ) !== ''
                    ? (string) $result['NextToken']
                    : null;
        } while ($nextToken !== null);

        /*
         * If the service code itself is not semantically named,
         * try known candidates through DescribeServices.
         *
         * These are discovery candidates only; they are not used
         * as authoritative pricing identifiers unless AWS accepts
         * them.
         */
        foreach (
            [
                'AmazonSES',
                'AmazonSimpleEmailService',
                'AWSSES',
            ] as $candidate
        ) {
            try {
                $result =
                    $client->describeServices([
                        'ServiceCode' =>
                            $candidate,
                        'MaxResults' => 1,
                    ]);

                $service =
                    ($result['Services'] ?? [])[0]
                    ?? null;

                $serviceCode = trim(
                    (string) (
                        $service['ServiceCode']
                        ?? ''
                    )
                );

                if ($serviceCode !== '') {
                    Cache::put(
                        $cacheKey,
                        $serviceCode,
                        now()->addDays(
                            self::SERVICE_CODE_CACHE_DAYS
                        )
                    );

                    return $serviceCode;
                }
            } catch (Throwable) {
                /*
                 * Candidate rejected by AWS.
                 * Continue discovery.
                 */
            }
        }

        return null;
    }

    /**
     * Retrieve the provider products for the discovered SES catalog.
     *
     * We initially retrieve the catalog broadly because we have not
     * yet observed AWS's live SES attributes for this account.
     * Selection is conservative and refuses ambiguous pricing.
     */
    protected function discoverProducts(
        string $serviceCode
    ): array {
        $client = $this->clients->pricing();

        $products = [];
        $nextToken = null;

        do {
            $arguments = [
                'ServiceCode' => $serviceCode,
                'FormatVersion' => 'aws_v1',
                'MaxResults' => 100,
            ];

            if ($nextToken !== null) {
                $arguments['NextToken'] =
                    $nextToken;
            }

            $result = $client->getProducts(
                $arguments
            );

            foreach (
                $result['PriceList'] ?? []
                as $rawProduct
            ) {
                $decoded = json_decode(
                    (string) $rawProduct,
                    true
                );

                if (is_array($decoded)) {
                    $products[] = $decoded;
                }
            }

            $nextToken =
                isset($result['NextToken'])
                && trim(
                    (string) $result['NextToken']
                ) !== ''
                    ? (string) $result['NextToken']
                    : null;
        } while ($nextToken !== null);

        return $products;
    }

    /**
     * Conservative outbound-email candidate selection.
     *
     * We do not silently choose the cheapest or first catalog row.
     * A candidate must look like an outbound email/message charge
     * and must contain a valid OnDemand USD price dimension.
     */
    protected function selectOutboundEmailCandidate(
        array $products,
        string $region
    ): ?array {
        $candidates = [];

        foreach ($products as $product) {
            $attributes =
                $product['product']['attributes']
                ?? [];

            if (!is_array($attributes)) {
                continue;
            }

            $searchable = strtolower(
                implode(
                    ' ',
                    array_map(
                        static fn ($value) =>
                            is_scalar($value)
                                ? (string) $value
                                : '',
                        array_values($attributes)
                    )
                )
            );

            /*
             * Must represent email/message sending rather than
             * unrelated SES services such as dedicated IP charges.
             */
            $emailLike =
                str_contains(
                    $searchable,
                    'email'
                )
                || str_contains(
                    $searchable,
                    'message'
                );

            $outboundLike =
                str_contains(
                    $searchable,
                    'outbound'
                )
                || str_contains(
                    $searchable,
                    'send'
                )
                || str_contains(
                    $searchable,
                    'sending'
                );

            $excluded =
                str_contains(
                    $searchable,
                    'dedicated ip'
                )
                || str_contains(
                    $searchable,
                    'virtual deliverability'
                )
                || str_contains(
                    $searchable,
                    'mail manager'
                );

            if (
                !$emailLike
                || !$outboundLike
                || $excluded
            ) {
                continue;
            }

            $dimensions =
                $this->extractUsdPriceDimensions(
                    $product
                );

            foreach ($dimensions as $dimension) {
                $candidates[] = [
                    'product' => $product,
                    'attributes' => $attributes,
                    'dimension' => $dimension,
                    'region_match' =>
                        $this->matchesRegion(
                            $attributes,
                            $region
                        ),
                ];
            }
        }

        if ($candidates === []) {
            return null;
        }

        $regional = array_values(
            array_filter(
                $candidates,
                static fn (array $candidate) =>
                    $candidate['region_match']
            )
        );

        if (count($regional) === 1) {
            return $regional[0];
        }

        /*
         * Do not invent a provider price if AWS returns several
         * plausible dimensions. We will inspect the live catalog
         * and tighten selection once credentials are supplied.
         */
        if (count($regional) > 1) {
            throw new RuntimeException(
                'AWS returned multiple SES outbound-email pricing candidates for '
                . $region
                . '; automatic selection was refused.'
            );
        }

        if (count($candidates) === 1) {
            return $candidates[0];
        }

        throw new RuntimeException(
            'AWS returned multiple SES outbound-email pricing candidates; automatic selection was refused.'
        );
    }

    protected function extractUsdPriceDimensions(
        array $product
    ): array {
        $dimensions = [];

        $onDemandTerms =
            $product['terms']['OnDemand']
            ?? [];

        if (!is_array($onDemandTerms)) {
            return [];
        }

        foreach (
            $onDemandTerms as $term
        ) {
            foreach (
                $term['priceDimensions'] ?? []
                as $rateCode => $dimension
            ) {
                $usd =
                    $dimension['pricePerUnit']['USD']
                    ?? null;

                if (
                    !is_numeric($usd)
                    || (float) $usd < 0
                ) {
                    continue;
                }

                $beginRange =
                    $dimension['beginRange']
                    ?? null;

                /*
                 * Use the base tier only.
                 */
                if (
                    $beginRange !== null
                    && is_numeric($beginRange)
                    && (float) $beginRange > 0
                ) {
                    continue;
                }

                $dimensions[] = [
                    'rate_code' =>
                        (string) $rateCode,
                    'amount' => (float) $usd,
                    'currency' => 'USD',
                    'unit' => trim(
                        (string) (
                            $dimension['unit']
                            ?? 'unit'
                        )
                    ),
                    'description' => trim(
                        (string) (
                            $dimension['description']
                            ?? ''
                        )
                    ),
                    'begin_range' =>
                        $beginRange,
                    'end_range' =>
                        $dimension['endRange']
                        ?? null,
                    'effective_at' =>
                        $term['effectiveDate']
                        ?? null,
                ];
            }
        }

        return $dimensions;
    }

    protected function matchesRegion(
        array $attributes,
        string $region
    ): bool {
        $region = strtolower(
            trim($region)
        );

        foreach (
            [
                'regionCode',
                'region',
                'location',
            ] as $key
        ) {
            $value = strtolower(
                trim(
                    (string) (
                        $attributes[$key]
                        ?? ''
                    )
                )
            );

            if (
                $value !== ''
                && $value === $region
            ) {
                return true;
            }
        }

        /*
         * AWS catalog location may be a human-readable name rather
         * than us-east-2. We deliberately do not guess that mapping.
         */
        return false;
    }

    protected function persistCandidate(
        string $serviceCode,
        string $region,
        array $candidate
    ): AwsServicePrice {
        $product =
            $candidate['product'];

        $attributes =
            $candidate['attributes'];

        $dimension =
            $candidate['dimension'];

        $amount =
            (float) $dimension['amount'];

        /*
         * AWS Price List pricePerUnit is already the price of one
         * measured unit identified by this dimension's unit.
         *
         * Preserve that provider contract exactly. Do not derive a
         * divisor from marketing wording such as "$X per 1,000".
         *
         * Once live SES catalog data is available, Esubiz verifies
         * that this measured unit represents the outbound billing
         * dimension required by Premium Email before enabling charge.
         */
        $quantity = 1.0;
        $normalized = $amount;

        return AwsServicePrice::query()
            ->updateOrCreate(
                [
                    'provider' =>
                        self::PROVIDER,
                    'service' =>
                        self::SERVICE,
                    'price_key' =>
                        'outbound_email',
                    'service_region' =>
                        $region,
                ],
                [
                    'amount' =>
                        $amount,
                    'currency' =>
                        $dimension['currency'],
                    'unit' =>
                        $dimension['unit'],
                    'quantity' =>
                        $quantity,
                    'normalized_unit_amount' =>
                        $normalized,
                    'service_code' =>
                        $serviceCode,
                    'sku' =>
                        $product['product']['sku']
                        ?? null,
                    'rate_code' =>
                        $dimension['rate_code'],
                    'usage_type' =>
                        $attributes['usagetype']
                        ?? $attributes['usageType']
                        ?? null,
                    'operation' =>
                        $attributes['operation']
                        ?? null,
                    'location' =>
                        $attributes['location']
                        ?? null,
                    'description' =>
                        $dimension['description']
                        ?: null,
                    'provider_payload' =>
                        $product,
                    'provider_effective_at' =>
                        $dimension['effective_at'],
                    'synced_at' =>
                        now(),
                    'last_success_at' =>
                        now(),
                    'last_error' =>
                        null,
                    'is_active' =>
                        true,
                ]
            );
    }

    protected function recordSyncFailure(
        string $priceKey,
        string $region,
        Throwable $exception
    ): void {
        try {
            $price = $this->storedPrice(
                $priceKey,
                $region
            );

            if ($price === null) {
                return;
            }

            $price->forceFill([
                'synced_at' => now(),
                'last_error' =>
                    mb_substr(
                        $exception->getMessage(),
                        0,
                        65000
                    ),
            ])->save();
        } catch (Throwable) {
            /*
             * Pricing failure reporting must never create a second
             * application failure.
             */
        }
    }

    protected function storedPrice(
        string $priceKey,
        string $region
    ): ?AwsServicePrice {
        try {
            return AwsServicePrice::query()
                ->where(
                    'provider',
                    self::PROVIDER
                )
                ->where(
                    'service',
                    self::SERVICE
                )
                ->where(
                    'price_key',
                    $priceKey
                )
                ->where(
                    'service_region',
                    $region
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();
        } catch (Throwable) {
            /*
             * Allows the service to exist safely before its pending
             * migration has been executed.
             */
            return null;
        }
    }

    protected function storedPriceSnapshot(
        string $priceKey,
        string $region
    ): ?array {
        $price = $this->storedPrice(
            $priceKey,
            $region
        );

        return $price !== null
            ? $this->serializePrice($price)
            : null;
    }

    protected function serializePrice(
        AwsServicePrice $price
    ): array {
        return [
            'provider' =>
                $price->provider,
            'service' =>
                $price->service,
            'price_key' =>
                $price->price_key,
            'service_region' =>
                $price->service_region,
            'amount' =>
                (float) $price->amount,
            'currency' =>
                $price->currency,
            'unit' =>
                $price->unit,
            'quantity' =>
                (float) $price->quantity,
            'normalized_unit_amount' =>
                (float) $price
                    ->normalized_unit_amount,
            'service_code' =>
                $price->service_code,
            'sku' =>
                $price->sku,
            'rate_code' =>
                $price->rate_code,
            'usage_type' =>
                $price->usage_type,
            'operation' =>
                $price->operation,
            'location' =>
                $price->location,
            'description' =>
                $price->description,
            'provider_effective_at' =>
                $price->provider_effective_at
                    ?->toIso8601String(),
            'synced_at' =>
                $price->synced_at
                    ?->toIso8601String(),
            'last_success_at' =>
                $price->last_success_at
                    ?->toIso8601String(),
            'last_error' =>
                $price->last_error,
        ];
    }

    protected function looksLikeSesServiceCode(
        string $serviceCode
    ): bool {
        $normalized = strtolower(
            preg_replace(
                '/[^a-z0-9]/i',
                '',
                $serviceCode
            ) ?? ''
        );

        return str_contains(
            $normalized,
            'simpleemail'
        )
            || $normalized === 'amazonses'
            || $normalized === 'awses'
            || str_ends_with(
                $normalized,
                'ses'
            );
    }

    protected function priceCacheKey(
        string $priceKey,
        string $region
    ): string {
        return 'esubiz:aws:pricing:'
            . self::SERVICE
            . ':'
            . strtolower($region)
            . ':'
            . strtolower($priceKey);
    }
}

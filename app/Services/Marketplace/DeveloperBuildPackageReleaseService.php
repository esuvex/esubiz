<?php

namespace App\Services\Marketplace;

use App\Models\DeveloperBuild;
use App\Models\WebsiteType;
use App\Services\Marketplace\Licensing\MarketplaceLicenseService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class DeveloperBuildPackageReleaseService
{
    /**
     * ESUBIZ_DEVELOPER_BUILD_PACKAGE_RELEASE_V1
     *
     * Compilation happens before payment.
     *
     * Successful payment does NOT rebuild the website.
     * It releases the already-compiled Website Type package by injecting:
     *
     * - license/license.txt
     * - license/license.pdf
     * - license information into index.html documentation
     * - released licensing metadata into esubiz-package.json
     *
     * The Marketplace entitlement/license remains the central authority.
     */
    public function __construct(
        protected MarketplaceLicenseService $licenses
    ) {
    }

    public function release(
        DeveloperBuild $developerBuild,
        int $entitlementId,
        int $licenseId
    ): array {
        $package = (string) $developerBuild->package_reference;

        if ($package === '' || !is_file($package)) {
            throw new RuntimeException(
                'Compiled Developer Build package could not be found.'
            );
        }

        $entitlement = DB::table('marketplace_entitlements')
            ->where('id', $entitlementId)
            ->where('product_type', 'website_type')
            ->where('deployment', 'off_server')
            ->where('status', 'active')
            ->first();

        if (!$entitlement) {
            throw new RuntimeException(
                'A valid Website Type entitlement is required to release this package.'
            );
        }

        $license = DB::table('marketplace_licenses')
            ->where('id', $licenseId)
            ->where('entitlement_id', $entitlementId)
            ->where('status', 'active')
            ->first();

        if (!$license) {
            throw new RuntimeException(
                'A valid Website Type license is required to release this package.'
            );
        }

        $websiteType = WebsiteType::query()
            ->where('slug', $developerBuild->website_type)
            ->first();

        if (!$websiteType) {
            throw new RuntimeException(
                'Website Type could not be resolved for package release.'
            );
        }

        if (
            !empty($entitlement->product_slug) &&
            (string) $entitlement->product_slug !== (string) $websiteType->slug
        ) {
            throw new RuntimeException(
                'Website Type entitlement does not match this Developer Build.'
            );
        }

        $licenseKey = $this->licenses->revealLicenseKey($licenseId);

        if (!is_string($licenseKey) || trim($licenseKey) === '') {
            throw new RuntimeException(
                'Website Type license key could not be revealed for package release.'
            );
        }

        $licenseKey = trim($licenseKey);

        $version = (string) (
            $entitlement->product_version
            ?: $websiteType->package_version
            ?: '1.0.0'
        );

        $releasedAt = now()->toIso8601String();

        $licenseText = $this->licenseText(
            $websiteType->name,
            $websiteType->slug,
            $version,
            $licenseKey,
            $entitlementId,
            $licenseId,
            $releasedAt
        );

        $licensePdf = $this->licensePdf(
            $websiteType->name,
            $websiteType->slug,
            $version,
            $licenseKey,
            $entitlementId,
            $licenseId,
            $releasedAt
        );

        $tempPackage = $package . '.release-' . bin2hex(random_bytes(6));

        if (!copy($package, $tempPackage)) {
            throw new RuntimeException(
                'Could not prepare temporary package for license release.'
            );
        }

        $zip = new ZipArchive();

        try {
            $opened = $zip->open($tempPackage);

            if ($opened !== true) {
                throw new RuntimeException(
                    'Compiled Developer Build package could not be opened.'
                );
            }

            $this->replaceZipEntry(
                $zip,
                'license/license.txt',
                $licenseText
            );

            $this->replaceZipEntry(
                $zip,
                'license/license.pdf',
                $licensePdf
            );

            $existingIndex = $zip->getFromName('index.html');

            $indexHtml = $this->documentationHtml(
                is_string($existingIndex) ? $existingIndex : null,
                $websiteType->name,
                $websiteType->slug,
                $version,
                $licenseKey,
                $releasedAt
            );

            $this->replaceZipEntry(
                $zip,
                'index.html',
                $indexHtml
            );

            $packageMetadata = [];

            $existingMetadata = $zip->getFromName('esubiz-package.json');

            if (is_string($existingMetadata) && trim($existingMetadata) !== '') {
                $decoded = json_decode($existingMetadata, true);

                if (is_array($decoded)) {
                    $packageMetadata = $decoded;
                }
            }

            $packageMetadata['licensing'] = array_merge(
                is_array($packageMetadata['licensing'] ?? null)
                    ? $packageMetadata['licensing']
                    : [],
                [
                    'authority' => 'Esubiz Central',
                    'mode' => 'post_payment',
                    'status' => 'issued',
                    'product_type' => 'website_type',
                    'product_slug' => (string) $websiteType->slug,
                    'product_version' => $version,
                    'entitlement_id' => $entitlementId,
                    'license_id' => $licenseId,
                    'license_embedded' => true,
                    'license_documents' => [
                        'license/license.txt',
                        'license/license.pdf',
                    ],
                    'released_at' => $releasedAt,
                ]
            );

            /*
             * Do not put the plaintext key inside package JSON metadata.
             * It belongs only in the human-readable license documents.
             */
            unset($packageMetadata['licensing']['license_key']);

            $this->replaceZipEntry(
                $zip,
                'esubiz-package.json',
                json_encode(
                    $packageMetadata,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                )
            );

            if (!$zip->close()) {
                throw new RuntimeException(
                    'Released Developer Build package could not be finalized.'
                );
            }

            if (!rename($tempPackage, $package)) {
                throw new RuntimeException(
                    'Released Developer Build package could not replace the private compiled package.'
                );
            }
        } catch (\Throwable $e) {
            try {
                $zip->close();
            } catch (\Throwable) {
                //
            }

            if (is_file($tempPackage)) {
                @unlink($tempPackage);
            }

            throw $e;
        }

        return [
            'package' => $package,
            'entitlement_id' => $entitlementId,
            'license_id' => $licenseId,
            'product_type' => 'website_type',
            'product_slug' => (string) $websiteType->slug,
            'product_version' => $version,
            'released_at' => $releasedAt,
        ];
    }

    protected function replaceZipEntry(
        ZipArchive $zip,
        string $name,
        string $contents
    ): void {
        if ($zip->locateName($name) !== false) {
            $zip->deleteName($name);
        }

        if (!$zip->addFromString($name, $contents)) {
            throw new RuntimeException(
                "Could not write {$name} into released package."
            );
        }
    }

    protected function licenseText(
        string $productName,
        string $productSlug,
        string $version,
        string $licenseKey,
        int $entitlementId,
        int $licenseId,
        string $releasedAt
    ): string {
        return implode(PHP_EOL, [
            'ESUBIZ PRODUCT LICENSE',
            '======================',
            '',
            'Product: ' . $productName,
            'Product Type: Website Type',
            'Product Slug: ' . $productSlug,
            'Version: ' . $version,
            '',
            'License Key: ' . $licenseKey,
            '',
            'Entitlement ID: ' . $entitlementId,
            'License ID: ' . $licenseId,
            'Issued By: Esubiz Central',
            'Released At: ' . $releasedAt,
            '',
            'MANUAL INSTALLATION',
            'Upload and extract this genuine Esubiz package on the target server.',
            'Open the website root in your browser and complete first-run setup.',
            'When requested, enter the License Key shown above.',
            'Esubiz Central will validate and bind the license to the installation.',
            '',
            'AUTOMATIC INSTALLATION / UPDATE',
            'No manual validation box is required.',
            'Esubiz performs entitlement and license validation automatically.',
            '',
            'This license remains subject to the applicable Esubiz product terms.',
            '',
        ]);
    }

    protected function documentationHtml(
        ?string $existingHtml,
        string $productName,
        string $productSlug,
        string $version,
        string $licenseKey,
        string $releasedAt
    ): string {
        $section = '
<section id="esubiz-license" style="margin:32px 0;padding:24px;border:1px solid #d9d9d9;border-radius:12px;">
    <h2>Esubiz Product License</h2>
    <p><strong>Product:</strong> ' . e($productName) . '</p>
    <p><strong>Type:</strong> Website Type</p>
    <p><strong>Slug:</strong> ' . e($productSlug) . '</p>
    <p><strong>Version:</strong> ' . e($version) . '</p>
    <p><strong>License Key:</strong></p>
    <p style="font-family:monospace;font-size:18px;font-weight:700;word-break:break-all;">'
        . e($licenseKey) .
    '</p>
    <p><strong>Released:</strong> ' . e($releasedAt) . '</p>
    <h3>Manual installation</h3>
    <p>Upload and extract this package, open the website root and complete the Esubiz first-run setup. Enter the license above when requested.</p>
    <h3>Automatic installation and updates</h3>
    <p>No manual license validation box is required. Esubiz validates the entitlement and license automatically.</p>
    <p>The same license is also included in <code>license/license.txt</code> and <code>license/license.pdf</code>.</p>
</section>';

        if ($existingHtml && trim($existingHtml) !== '') {
            $withoutOldSection = preg_replace(
                '/<section id="esubiz-license".*?<\/section>/is',
                '',
                $existingHtml
            );

            if (stripos($withoutOldSection, '</body>') !== false) {
                return preg_replace(
                    '/<\/body>/i',
                    $section . PHP_EOL . '</body>',
                    $withoutOldSection,
                    1
                );
            }

            return $withoutOldSection . PHP_EOL . $section;
        }

        return '<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . e($productName) . ' - Esubiz Documentation</title>
</head>
<body style="font-family:Arial,sans-serif;max-width:900px;margin:40px auto;padding:0 20px;line-height:1.6;">
    <h1>' . e($productName) . '</h1>
    <p>Esubiz Website Type package documentation.</p>
    ' . $section . '
</body>
</html>';
    }

    /**
     * Create a small dependency-free PDF containing the issued license.
     */
    protected function licensePdf(
        string $productName,
        string $productSlug,
        string $version,
        string $licenseKey,
        int $entitlementId,
        int $licenseId,
        string $releasedAt
    ): string {
        $lines = [
            'ESUBIZ PRODUCT LICENSE',
            '',
            'Product: ' . $productName,
            'Product Type: Website Type',
            'Product Slug: ' . $productSlug,
            'Version: ' . $version,
            '',
            'License Key:',
            $licenseKey,
            '',
            'Entitlement ID: ' . $entitlementId,
            'License ID: ' . $licenseId,
            'Issued By: Esubiz Central',
            'Released At: ' . $releasedAt,
            '',
            'Manual install: enter this license when Esubiz Core requests validation.',
            'Automatic install/update: Esubiz validates the license automatically.',
        ];

        $escape = static function (string $value): string {
            $value = preg_replace('/[^\x20-\x7E]/', '?', $value);

            return str_replace(
                ['\\', '(', ')'],
                ['\\\\', '\(', '\)'],
                $value
            );
        };

        $stream = "BT\n/F1 12 Tf\n50 790 Td\n";

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $stream .= "0 -22 Td\n";
            }

            $stream .= '(' . $escape($line) . ") Tj\n";
        }

        $stream .= "ET";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $objectNumber = $number + 1;
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= "{$objectNumber} 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);

        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size "
            . (count($objects) + 1)
            . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}

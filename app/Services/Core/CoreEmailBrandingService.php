<?php

namespace App\Services\Core;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CoreEmailBrandingService
{
    /**
     * ESUBIZ_CORE_EMAIL_BRANDING_RENDERER_V1
     *
     * Website-wide renderer for outgoing Core HTML email.
     *
     * The original email body remains the message content.
     * This service adds the website's saved email branding around it.
     */
    public function render(
        Website $website,
        string $bodyHtml
    ): string {
        if (
            !Schema::connection('website_tenant')
                ->hasTable('site_settings')
        ) {
            return $bodyHtml;
        }

        try {
            $settings =
                DB::connection('website_tenant')
                    ->table('site_settings')
                    ->pluck('value', 'key')
                    ->all();
        } catch (\Throwable $e) {
            return $bodyHtml;
        }

        $header = $this->text(
            $settings['email_branding_header']
                ?? ''
        );

        $footer = $this->text(
            $settings['email_branding_footer']
                ?? ''
        );

        $signature = $this->text(
            $settings['email_branding_signature']
                ?? ''
        );

        $background = $this->color(
            $settings['email_branding_background']
                ?? '#f8fafc',
            '#f8fafc'
        );

        $brandColor = $this->color(
            $settings['email_branding_accent']
                ?? '#0f172a',
            '#0f172a'
        );

        $textColor = $this->color(
            $settings['email_branding_text_color']
                ?? '#334155',
            '#334155'
        );

        $width = in_array(
            (string) (
                $settings['email_branding_width']
                    ?? '640'
            ),
            ['560', '640', '720'],
            true
        )
            ? (string) $settings['email_branding_width']
            : '640';

        $font = match (
            (string) (
                $settings['email_branding_font']
                    ?? 'system'
            )
        ) {
            'serif' =>
                "Georgia, 'Times New Roman', serif",

            'clean' =>
                "Helvetica, Arial, sans-serif",

            default =>
                "Arial, Helvetica, sans-serif",
        };

        $logoUrl = $this->logoUrl(
            $website,
            $settings
        );

        $contactHtml =
            $this->contactHtml(
                $settings,
                $textColor
            );

        $socialHtml =
            $this->socialHtml(
                $settings
            );

        $unsubscribeHtml =
            $this->unsubscribeHtml(
                $settings,
                $brandColor
            );

        $logoHtml = '';

        if ($logoUrl !== '') {
            $logoHtml = '
                <tr>
                    <td style="
                        padding:32px 32px 18px;
                        text-align:center;
                    ">
                        <img
                            src="' . $this->escape($logoUrl) . '"
                            alt="' . $this->escape(
                                (string) (
                                    $website->name
                                        ?? 'Website'
                                )
                            ) . '"
                            style="
                                display:inline-block;
                                max-width:180px;
                                max-height:70px;
                                width:auto;
                                height:auto;
                                border:0;
                            "
                        >
                    </td>
                </tr>
            ';
        }

        $headerHtml = '';

        if ($header !== '') {
            $headerHtml = '
                <tr>
                    <td style="
                        padding:18px 32px 8px;
                        color:' . $brandColor . ';
                        font-size:18px;
                        line-height:1.6;
                        font-weight:600;
                        text-align:left;
                    ">
                        ' . $this->multiline($header) . '
                    </td>
                </tr>
            ';
        }

        $signatureHtml = '';

        if ($signature !== '') {
            $signatureHtml = '
                <tr>
                    <td style="
                        padding:10px 32px 28px;
                        color:' . $textColor . ';
                        font-size:14px;
                        line-height:1.7;
                    ">
                        ' . $this->multiline($signature) . '
                    </td>
                </tr>
            ';
        }

        $footerHtml = '';

        if (
            $footer !== ''
            || $contactHtml !== ''
            || $socialHtml !== ''
            || $unsubscribeHtml !== ''
        ) {
            $footerHtml = '
                <tr>
                    <td style="
                        padding:24px 32px 28px;
                        border-top:1px solid #e5e7eb;
                        color:' . $textColor . ';
                        font-size:12px;
                        line-height:1.7;
                        text-align:center;
                    ">
                        '
                        . (
                            $footer !== ''
                                ? '<div style="margin-bottom:12px;">'
                                    . $this->multiline($footer)
                                    . '</div>'
                                : ''
                        )
                        . $contactHtml
                        . $socialHtml
                        . $unsubscribeHtml
                        . '
                    </td>
                </tr>
            ';
        }

        return '<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="
    margin:0;
    padding:0;
    background:' . $background . ';
    color:' . $textColor . ';
    font-family:' . $font . ';
">
    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
            width:100%;
            background:' . $background . ';
            margin:0;
            padding:24px 12px;
        "
    >
        <tr>
            <td align="center">
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="
                        width:100%;
                        max-width:' . $width . 'px;
                        background:#ffffff;
                        border-collapse:separate;
                        border-spacing:0;
                    "
                >
                    ' . $logoHtml . '
                    ' . $headerHtml . '

                    <tr>
                        <td style="
                            padding:24px 32px;
                            color:' . $textColor . ';
                            font-size:15px;
                            line-height:1.7;
                            text-align:left;
                        ">
                            ' . $bodyHtml . '
                        </td>
                    </tr>

                    ' . $signatureHtml . '
                    ' . $footerHtml . '
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    private function contactHtml(
        array $settings,
        string $textColor
    ): string {
        if (
            ($settings[
                'email_branding_contact_enabled'
            ] ?? '0') !== '1'
        ) {
            return '';
        }

        $items = [];

        $phone = $this->text(
            $settings[
                'email_branding_contact_phone'
            ] ?? ''
        );

        $email = $this->text(
            $settings[
                'email_branding_contact_email'
            ] ?? ''
        );

        $website = $this->text(
            $settings[
                'email_branding_contact_website'
            ] ?? ''
        );

        $address = $this->text(
            $settings[
                'email_branding_contact_address'
            ] ?? ''
        );

        if ($phone !== '') {
            $items[] = $this->escape($phone);
        }

        if ($email !== '') {
            $items[] =
                '<a href="mailto:'
                . $this->escape($email)
                . '" style="color:'
                . $textColor
                . ';text-decoration:none;">'
                . $this->escape($email)
                . '</a>';
        }

        if ($website !== '') {
            $items[] =
                '<a href="'
                . $this->escape($website)
                . '" style="color:'
                . $textColor
                . ';text-decoration:none;">'
                . $this->escape(
                    preg_replace(
                        '#^https?://#i',
                        '',
                        $website
                    )
                )
                . '</a>';
        }

        if ($address !== '') {
            $items[] = $this->escape($address);
        }

        if (!$items) {
            return '';
        }

        return '
            <div style="
                margin-top:12px;
                color:' . $textColor . ';
            ">
                ' . implode(' &nbsp; • &nbsp; ', $items) . '
            </div>
        ';
    }

    private function socialHtml(
        array $settings
    ): string {
        if (
            ($settings[
                'email_branding_social_enabled'
            ] ?? '0') !== '1'
        ) {
            return '';
        }

        /*
         * Email clients have inconsistent inline-SVG support.
         * These compact branded buttons are intentionally
         * HTML/CSS based so they render reliably in sent email.
         */
        $profiles = [
            'facebook' => [
                'label' => 'f',
                'color' => '#1877F2',
            ],
            'instagram' => [
                'label' => '◎',
                'color' => '#E4405F',
            ],
            'linkedin' => [
                'label' => 'in',
                'color' => '#0A66C2',
            ],
            'x' => [
                'label' => 'X',
                'color' => '#000000',
            ],
            'youtube' => [
                'label' => '▶',
                'color' => '#FF0000',
            ],
            'tiktok' => [
                'label' => '♪',
                'color' => '#000000',
            ],
        ];

        $links = [];

        foreach ($profiles as $key => $profile) {
            $url = trim(
                (string) (
                    $settings[
                        'email_branding_social_' . $key
                    ] ?? ''
                )
            );

            if ($url === '') {
                continue;
            }

            $links[] =
                '<a href="'
                . $this->escape($url)
                . '" style="
                    display:inline-block;
                    width:32px;
                    height:32px;
                    margin:4px;
                    border-radius:50%;
                    background:'
                . $profile['color']
                . ';
                    color:#ffffff;
                    font-family:Arial,sans-serif;
                    font-size:12px;
                    font-weight:bold;
                    line-height:32px;
                    text-align:center;
                    text-decoration:none;
                ">'
                . $profile['label']
                . '</a>';
        }

        if (!$links) {
            return '';
        }

        return '
            <div style="margin-top:14px;">
                ' . implode('', $links) . '
            </div>
        ';
    }

    private function unsubscribeHtml(
        array $settings,
        string $brandColor
    ): string {
        if (
            ($settings[
                'email_branding_unsubscribe_enabled'
            ] ?? '0') !== '1'
        ) {
            return '';
        }

        $text = $this->text(
            $settings[
                'email_branding_unsubscribe_text'
            ] ?? ''
        );

        if ($text === '') {
            return '';
        }

        /*
         * No recipient-specific unsubscribe endpoint exists
         * in this renderer yet, so never fabricate a URL.
         */
        return '
            <div style="
                margin-top:16px;
                padding-top:14px;
                border-top:1px solid #e5e7eb;
                color:' . $brandColor . ';
            ">
                ' . $this->escape($text) . '
            </div>
        ';
    }

    private function logoUrl(
        Website $website,
        array $settings
    ): string {
        $path = trim(
            (string) (
                $settings['email_branding_logo_path']
                    ?? $settings['site_logo_path']
                    ?? ''
            )
        );

        if ($path === '') {
            return '';
        }

        /*
         * ESUBIZ_CORE_EMAIL_LOGO_PUBLIC_URL_V2
         *
         * Core branding assets are delivered through the canonical
         * tenant /media route. Never expose storage/filesystem paths
         * directly in outgoing email.
         */
        return (string) (
            app(CoreWebsiteLogoService::class)
                ->publicUrl($path)
            ?? ''
        );
    }

    private function color(
        mixed $value,
        string $fallback
    ): string {
        $value = trim((string) $value);

        return preg_match(
            '/^#[0-9A-Fa-f]{6}$/',
            $value
        )
            ? strtolower($value)
            : $fallback;
    }

    private function text(
        mixed $value
    ): string {
        return trim((string) $value);
    }

    private function multiline(
        string $value
    ): string {
        return nl2br(
            $this->escape($value),
            false
        );
    }

    private function escape(
        mixed $value
    ): string {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

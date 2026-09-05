<?php

namespace App\Services\PageBuilderPro;

/**
 * ESUBIZ_PAGE_BUILDER_PRO_SECTION_FACTORY_V1
 *
 * Sections own layout.
 * Widgets remain presentation-neutral.
 */
class SectionFactory
{
    public function make(array $ratios = [1]): array
    {
        $ratios = array_values(
            array_filter(
                array_map('floatval', $ratios),
                fn ($ratio) => $ratio > 0
            )
        );

        if (!$ratios) {
            $ratios = [1];
        }

        return [
            'id' => $this->id('section'),
            'type' => 'section',
            'ratios' => $ratios,
            'settings' => [
                'background' => '',
                'padding_top' => 48,
                'padding_bottom' => 48,
                'gap' => 24,
                'width' => 'container',
            ],
            'columns' => array_map(
                fn () => [
                    'id' => $this->id('column'),
                    'widgets' => [],
                ],
                $ratios
            ),
        ];
    }

    protected function id(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }
}

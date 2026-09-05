<?php

namespace App\Services\PageBuilderPro;

/**
 * ESUBIZ_PAGE_BUILDER_PRO_WIDGET_FACTORY_V1
 */
class WidgetFactory
{
    public function __construct(
        protected BuilderWidgetRegistry $registry,
        protected ProWidgetRegistry $pro
    ) {
    }

    public function make(string $type, array $data = []): array
    {
        if (!$this->registry->has($type)) {
            throw new \InvalidArgumentException(
                "Unknown Page Builder Pro widget [{$type}]."
            );
        }

        $defaults = $this->registry->source($type) === 'pro'
            ? $this->pro->defaults($type)
            : [];

        return [
            'id' => $this->id('widget'),
            'type' => $type,
            'data' => array_replace_recursive(
                $defaults,
                $data
            ),
            'settings' => [],
            'pro' => BuilderSettings::widget(),
        ];
    }

    protected function id(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }
}

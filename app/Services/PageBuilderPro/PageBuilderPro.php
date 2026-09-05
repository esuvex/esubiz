<?php

namespace App\Services\PageBuilderPro;

/**
 * ESUBIZ_PAGE_BUILDER_PRO_ENGINE_V1
 *
 * Portable Page Builder Pro document engine.
 *
 * IMPORTANT:
 * - No Central Esubiz model dependency.
 * - No tenant/Core model dependency.
 * - No route dependency.
 * - No domain dependency.
 * - No entitlement dependency.
 *
 * Host applications provide storage, authorization and rendering context.
 */
class PageBuilderPro
{
    public const SCHEMA_VERSION = 1;

    public function __construct(
        protected BuilderWidgetRegistry $widgets
    ) {
    }

    public function widgets(): BuilderWidgetRegistry
    {
        return $this->widgets;
    }

    public function emptyDocument(): array
    {
        return [
            'schema' => self::SCHEMA_VERSION,
            'sections' => [],
        ];
    }

    public function normalize(?array $document): array
    {
        if (!$document) {
            return $this->emptyDocument();
        }

        $sections = $document['sections'] ?? [];

        if (!is_array($sections)) {
            $sections = [];
        }

        return [
            'schema' => (int) ($document['schema'] ?? self::SCHEMA_VERSION),
            'sections' => array_values($sections),
        ];
    }

    public function validate(?array $document): array
    {
        $document = $this->normalize($document);
        $errors = [];

        foreach ($document['sections'] as $sectionIndex => $section) {
            if (!is_array($section)) {
                $errors[] = "Section {$sectionIndex} is invalid.";
                continue;
            }

            $columns = $section['columns'] ?? [];

            if (!is_array($columns)) {
                $errors[] = "Section {$sectionIndex} columns are invalid.";
                continue;
            }

            foreach ($columns as $columnIndex => $column) {
                $widgets = is_array($column)
                    ? ($column['widgets'] ?? [])
                    : [];

                if (!is_array($widgets)) {
                    $errors[] =
                        "Section {$sectionIndex}, column {$columnIndex} widgets are invalid.";
                    continue;
                }

                foreach ($widgets as $widgetIndex => $widget) {
                    $type = is_array($widget)
                        ? (string) ($widget['type'] ?? '')
                        : '';

                    if ($type === '' || !$this->widgets->has($type)) {
                        $errors[] =
                            "Unknown widget at section {$sectionIndex}, "
                            . "column {$columnIndex}, widget {$widgetIndex}.";
                    }
                }
            }
        }

        return $errors;
    }
}

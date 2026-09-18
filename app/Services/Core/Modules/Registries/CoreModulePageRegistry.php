<?php

namespace App\Services\Core\Modules\Registries;

use InvalidArgumentException;

/**
 * ESUBIZ_CORE_MODULE_PAGE_REGISTRY_V1
 *
 * Runtime registry for pages/submenus supplied by enabled modules.
 *
 * The module supplies:
 * - page key
 * - submenu label
 * - URL
 * - permission
 * - icon
 * - order
 *
 * Central module configuration supplies the parent sidebar menu label
 * such as "Store Management" or "Hotel Management".
 *
 * Core navigation remains the final renderer/authority.
 */
class CoreModulePageRegistry
{
    protected array $pages = [];

    public function register(
        string $moduleSlug,
        string $pageKey,
        array $definition
    ): void {
        $moduleSlug = trim($moduleSlug);
        $pageKey = trim($pageKey);

        if ($moduleSlug === '' || $pageKey === '') {
            throw new InvalidArgumentException(
                'Module slug and page key are required.'
            );
        }

        $label = trim((string) ($definition['label'] ?? ''));
        $url = trim((string) ($definition['url'] ?? ''));

        if ($label === '' || $url === '') {
            throw new InvalidArgumentException(
                "Module page [{$moduleSlug}:{$pageKey}] requires a label and URL."
            );
        }

        $key = $moduleSlug . ':' . $pageKey;

        $this->pages[$key] = array_merge(
            $definition,
            [
                'key' => $key,
                'page_key' => $pageKey,
                'source_type' => 'module',
                'source_key' => $moduleSlug,
                'module_slug' => $moduleSlug,
                'label' => $label,
                'url' => $url,
                'permission' => trim(
                    (string) ($definition['permission'] ?? '')
                ),
                'order' => (int) ($definition['order'] ?? 500),
            ]
        );
    }

    public function all(): array
    {
        $pages = array_values($this->pages);

        usort(
            $pages,
            static fn (array $a, array $b): int =>
                ($a['order'] <=> $b['order'])
                ?: strcmp($a['label'], $b['label'])
        );

        return $pages;
    }

    public function forModule(string $moduleSlug): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (array $page): bool =>
                ($page['module_slug'] ?? null) === $moduleSlug
        ));
    }

    public function get(string $moduleSlug, string $pageKey): ?array
    {
        return $this->pages[$moduleSlug . ':' . $pageKey] ?? null;
    }

    public function has(string $moduleSlug, string $pageKey): bool
    {
        return $this->get($moduleSlug, $pageKey) !== null;
    }
}

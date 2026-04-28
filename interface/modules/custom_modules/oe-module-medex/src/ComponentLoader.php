<?php

declare(strict_types=1);

namespace OpenEMR\Modules\MedEx;

final class ComponentLoader
{
    private const COMPONENTS_DIR = __DIR__ . '/../components';

    /**
     * @return array<string,array<string,mixed>>
     */
    public static function manifests(): array
    {
        static $cache = null;
        if (is_array($cache)) {
            return $cache;
        }

        $cache = [];
        $componentsDir = self::COMPONENTS_DIR;
        if (!is_dir($componentsDir)) {
            return $cache;
        }

        $dirs = glob($componentsDir . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($dirs as $dir) {
            $manifestFile = $dir . '/manifest.php';
            if (!is_file($manifestFile)) {
                continue;
            }

            $manifest = require $manifestFile;
            if (!is_array($manifest)) {
                continue;
            }

            $key = trim((string)($manifest['key'] ?? basename($dir)));
            if ($key === '') {
                continue;
            }

            $manifest['key'] = $key;
            $manifest['component_dir'] = $dir;
            $manifest['aliases'] = array_values(array_filter(array_map(static function ($alias): string {
                return trim((string)$alias);
            }, (array)($manifest['aliases'] ?? []))));
            $cache[$key] = $manifest;
        }

        return $cache;
    }

    public static function includeBootstrapFiles(): void
    {
        foreach (self::manifests() as $manifest) {
            $bootstrapFiles = (array)($manifest['bootstrap'] ?? []);
            foreach ($bootstrapFiles as $relativePath) {
                $path = self::componentPath($manifest, (string)$relativePath);
                if ($path !== null && is_file($path)) {
                    require_once $path;
                }
            }
        }
    }

    /**
     * @param array<string,mixed> $manifest
     */
    private static function componentPath(array $manifest, string $relativePath): ?string
    {
        $baseDir = (string)($manifest['component_dir'] ?? '');
        if ($baseDir === '' || $relativePath === '') {
            return null;
        }

        $clean = ltrim(str_replace('..', '', $relativePath), '/');
        if ($clean === '') {
            return null;
        }

        return $baseDir . '/' . $clean;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function manifestForService(string $serviceKey): ?array
    {
        $needle = trim($serviceKey);
        if ($needle === '') {
            return null;
        }

        foreach (self::manifests() as $manifest) {
            if ($needle === (string)$manifest['key']) {
                return $manifest;
            }
            foreach ((array)($manifest['aliases'] ?? []) as $alias) {
                if ($needle === (string)$alias) {
                    return $manifest;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string,mixed> $serviceData
     * @return array<string,mixed>
     */
    public static function normalizePricingService(string $serviceKey, array $serviceData, string $helpBaseUrl = ''): array
    {
        $manifest = self::manifestForService($serviceKey) ?? [];
        $resolvedKey = trim((string)($serviceData['service_key'] ?? $serviceData['service'] ?? $manifest['key'] ?? $serviceKey));
        if ($resolvedKey === '') {
            $resolvedKey = trim($serviceKey);
        }

        $title = trim((string)($serviceData['display_name'] ?? $serviceData['name'] ?? $manifest['title'] ?? $resolvedKey));
        if ($title === '') {
            $title = $resolvedKey;
        }

        $slug = strtolower((string)(preg_replace('/[^a-z0-9]+/i', '_', $resolvedKey) ?? $resolvedKey));
        $slug = trim($slug, '_');
        if ($slug === '') {
            $slug = 'service';
        }

        $selectors = is_array($manifest['selectors'] ?? null) ? $manifest['selectors'] : [];
        $providerBased = !empty($serviceData['provider_based']) || !empty($selectors['providers']);
        $facilityBased = !empty($serviceData['facility_based']) || !empty($selectors['facilities']);
        $available = !array_key_exists('available', $serviceData)
            || $serviceData['available'] === true
            || $serviceData['available'] === 1
            || $serviceData['available'] === '1';

        $description = trim((string)($serviceData['description'] ?? $manifest['description'] ?? ''));
        if ($description === '') {
            if ($providerBased && $facilityBased) {
                $description = 'This service is billed by the providers you select and also requires facility selection.';
            } elseif ($providerBased) {
                $description = 'This service is billed by the number of providers you include.';
            } else {
                $description = 'This service is billed at the practice level.';
            }
        }

        $help = is_array($manifest['help'] ?? null) ? $manifest['help'] : [];
        $helpTopic = trim((string)($manifest['help_topic'] ?? $resolvedKey));
        $helpUrl = trim((string)($help['url'] ?? ''));
        if ($helpUrl === '' && $helpBaseUrl !== '') {
            $helpUrl = $helpBaseUrl . '&topic=' . rawurlencode($helpTopic);
        }

        return [
            'key' => $resolvedKey,
            'slug' => $slug,
            'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'description' => html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'price' => isset($serviceData['price']) ? (float)$serviceData['price'] : 0.0,
            'unit' => (string)($serviceData['unit'] ?? ''),
            'available' => $available,
            'provider_based' => $providerBased,
            'facility_based' => $facilityBased,
            'selectors' => [
                'providers' => $providerBased,
                'facilities' => $facilityBased,
            ],
            'help_url' => $helpUrl,
            'help_topic' => $helpTopic,
            'raw' => $serviceData,
            'manifest' => $manifest,
        ];
    }

    /**
     * @param array<string,mixed> $pricingServices
     * @return array<string,array<string,mixed>>
     */
    public static function buildServiceCatalog(array $pricingServices, string $helpBaseUrl = ''): array
    {
        $catalog = [];
        foreach ($pricingServices as $serviceKey => $serviceData) {
            if (!is_array($serviceData)) {
                continue;
            }

            $normalized = self::normalizePricingService((string)$serviceKey, $serviceData, $helpBaseUrl);
            if (empty($normalized['available'])) {
                continue;
            }

            $catalog[(string)$normalized['key']] = $normalized;
        }

        return $catalog;
    }

    /**
     * @param array<int|string,mixed> $enabledServices
     * @return array<int,array<string,mixed>>
     */
    public static function menusForEnabledServices(array $enabledServices): array
    {
        $enabledKeys = [];
        foreach ($enabledServices as $key => $value) {
            if (is_int($key)) {
                $serviceKey = trim((string)$value);
                if ($serviceKey !== '') {
                    $enabledKeys[$serviceKey] = true;
                }
                continue;
            }
            if ($value === true || $value === 1 || $value === '1') {
                $enabledKeys[trim((string)$key)] = true;
            }
        }

        $menus = [];
        $seenManifestKeys = [];
        foreach ($enabledKeys as $serviceKey => $_true) {
            $manifest = self::manifestForService($serviceKey);
            if (!$manifest) {
                continue;
            }
            $manifestKey = trim((string)($manifest['key'] ?? ''));
            if ($manifestKey !== '' && isset($seenManifestKeys[$manifestKey])) {
                continue;
            }
            if ($manifestKey !== '') {
                $seenManifestKeys[$manifestKey] = true;
            }
            foreach ((array)($manifest['menus'] ?? []) as $menu) {
                if (is_array($menu)) {
                    $menus[] = $menu;
                }
            }
        }

        return $menus;
    }

    /**
     * @param array<string,array<string,mixed>> $serviceCatalog
     * @return array<string,mixed>|null
     */
    public static function helpTopic(string $topic, array $serviceCatalog = []): ?array
    {
        $manifest = self::manifestForService($topic);
        if ($manifest && is_array($manifest['help'] ?? null)) {
            $help = $manifest['help'];
            $title = trim((string)($help['title'] ?? $manifest['title'] ?? 'Service Help'));
            $summary = trim((string)($help['summary'] ?? $manifest['description'] ?? ''));
            $points = array_values(array_filter(array_map(static function ($point): string {
                return trim((string)$point);
            }, (array)($help['points'] ?? []))));
            $commands = array_values(array_filter(array_map(static function ($command): string {
                return trim((string)$command);
            }, (array)($help['commands'] ?? []))));

            return [
                'title' => $title,
                'summary' => $summary,
                'points' => $points,
                'commands' => $commands,
            ];
        }

        $service = $serviceCatalog[$topic] ?? null;
        if (!is_array($service)) {
            return null;
        }

        $points = [];
        if (!empty($service['provider_based'])) {
            $points[] = 'Provider selection controls how many users are billed for this service.';
        }
        if (!empty($service['facility_based'])) {
            $points[] = 'Facility selection is required before this service can be activated.';
        }
        $unit = trim((string)($service['unit'] ?? ''));
        if ($unit !== '') {
            $points[] = 'Billing unit: ' . ltrim($unit, '/');
        }
        $points[] = 'Availability and pricing come directly from MedEx SaaS for this practice.';

        return [
            'title' => (string)($service['title'] ?? $topic),
            'summary' => (string)($service['description'] ?? ''),
            'points' => $points,
            'commands' => [],
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function userSettings(): array
    {
        $settings = [];
        foreach (self::manifests() as $manifest) {
            foreach ((array)($manifest['user_settings'] ?? []) as $setting) {
                if (is_array($setting)) {
                    $settings[] = $setting;
                }
            }
        }

        return $settings;
    }
}

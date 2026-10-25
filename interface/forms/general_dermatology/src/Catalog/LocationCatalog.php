<?php

declare(strict_types=1);

namespace GeneralDermatology\Catalog;

/**
 * Stable location vocabulary derived from map region names.
 *
 * Phase 4 map UI will consume these ids; Phase 3 exposes the catalog API.
 */
final class LocationCatalog
{
    /** @var array<string, array{id: string, label: string, laterality: string, view: string}>|null */
    private static ?array $byId = null;

    /**
     * @return array<string, array{id: string, label: string, laterality: string, view: string}>
     */
    public static function allById(): array
    {
        if (self::$byId !== null) {
            return self::$byId;
        }

        $path = dirname(__DIR__, 2) . '/resources/locations.php';
        if (is_file($path)) {
            /** @var array<int, array{id?: string, label?: string, laterality?: string, view?: string}> $rows */
            $rows = require $path;
            $byId = [];
            foreach ($rows as $row) {
                $id = (string) ($row['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $byId[$id] = [
                    'id' => $id,
                    'label' => (string) ($row['label'] ?? $id),
                    'laterality' => (string) ($row['laterality'] ?? ''),
                    'view' => (string) ($row['view'] ?? ''),
                ];
            }
            self::$byId = $byId;
            return self::$byId;
        }

        self::$byId = [];
        return self::$byId;
    }

    /**
     * @return array{id: string, label: string, laterality: string, view: string}|null
     */
    public static function findById(string $id): ?array
    {
        $all = self::allById();
        return $all[$id] ?? null;
    }

    /**
     * @return array{id: string, label: string, laterality: string, view: string}|null
     */
    public static function findByLabel(string $label): ?array
    {
        $needle = strtolower(trim($label));
        if ($needle === '') {
            return null;
        }
        foreach (self::allById() as $row) {
            if (strtolower($row['label']) === $needle) {
                return $row;
            }
        }

        return null;
    }

    public static function idFromLabel(string $label): string
    {
        $known = self::findByLabel($label);
        if ($known !== null) {
            return $known['id'];
        }

        $slug = strtolower($label);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug) ?? '';
        $slug = trim($slug, '_');

        return $slug !== '' ? $slug : 'location';
    }

    /**
     * Payload for map JS: lookup by stable id and by case-insensitive label
     * (ImageMapster region name matches the catalog label).
     *
     * @return array{
     *   byId: array<string, array{id: string, label: string, laterality: string, view: string}>,
     *   byLabel: array<string, array{id: string, label: string, laterality: string, view: string}>
     * }
     */
    public static function bootstrapForMapUi(): array
    {
        $byId = self::allById();
        $byLabel = [];
        foreach ($byId as $row) {
            $byLabel[strtolower(trim($row['label']))] = $row;
        }

        return [
            'byId' => $byId,
            'byLabel' => $byLabel,
        ];
    }

    /**
     * Resolve a map region key (ImageMapster name) into a location object.
     *
     * @return array{id: string, label: string, laterality: string, view: string, markers: list<array{x: float, y: float}>}
     */
    public static function resolveFromMapKey(string $mapKey, string $fallbackView = ''): array
    {
        $label = trim(str_replace('_', ' ', $mapKey));
        $known = self::findByLabel($label);
        if ($known !== null) {
            return [
                'id' => $known['id'],
                'label' => $known['label'],
                'laterality' => $known['laterality'],
                'view' => $known['view'] !== '' ? $known['view'] : $fallbackView,
                'markers' => [],
            ];
        }

        return [
            'id' => self::idFromLabel($label),
            'label' => $label,
            'laterality' => self::inferLaterality($label),
            'view' => $fallbackView !== '' ? $fallbackView : self::inferView($label),
            'markers' => [],
        ];
    }

    /**
     * Infer laterality from common derm location wording.
     */
    public static function inferLaterality(string $label): string
    {
        $l = strtolower($label);
        if (str_contains($l, 'left')) {
            return 'L';
        }
        if (str_contains($l, 'right')) {
            return 'R';
        }
        if (str_contains($l, 'mid ') || str_starts_with($l, 'mid') || str_contains($l, ' midline')) {
            return 'M';
        }

        return '';
    }

    /**
     * Infer view bucket for map UI.
     */
    public static function inferView(string $label): string
    {
        $l = strtolower($label);
        $faceHints = [
            'face', 'forehead', 'cheek', 'nose', 'nasal', 'ala', 'lip', 'chin', 'ear',
            'eyelid', 'temple', 'scalp', 'brow', 'infraorbital', 'orbital',
        ];
        foreach ($faceHints as $hint) {
            if (str_contains($l, $hint)) {
                return 'face';
            }
        }
        if (str_contains($l, 'posterior') || str_contains($l, 'back ')) {
            return 'body_back';
        }

        return 'body_front';
    }
}

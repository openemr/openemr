<?php

declare(strict_types=1);

namespace GeneralDermatology\Domain;

use GeneralDermatology\Catalog\LocationCatalog;
use GeneralDermatology\Exception\GeneralDermatologyException;

/**
 * Versioned treatment payload stored in general_dermatology.treatment.
 *
 * Storage shape (schema_version 2):
 * {
 *   "schema_version": 2,
 *   "findings": [ { finding_location, location{...}, dx_s, ...procedure fields } ]
 * }
 *
 * Legacy v1 storage was a bare JSON array of finding objects.
 * UI continues to consume a flat list of findings via findingsForUi().
 */
final class TreatmentDocument
{
    public const SCHEMA_VERSION = 2;

    /** @var list<string> */
    private const CORE_FINDING_KEYS = [
        'finding_location',
        'finding_description',
        'dx_s',
        'dx_desc',
        'dx_alt',
        'icd_alt',
        'severity_drop',
        'plan',
        'plan_desc',
        'procedure_plans',
        'minutes',
        'location',
    ];

    /**
     * Decode DB JSON (v1 array or v2 document) into a v2 document.
     *
     * @return array{schema_version: int, findings: list<array<string, mixed>>}
     */
    public static function fromStorage(null|string|array $raw): array
    {
        if ($raw === null || $raw === '') {
            return self::emptyDocument();
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return self::emptyDocument();
            }
            $raw = $decoded;
        }

        // v2 document
        if (array_key_exists('schema_version', $raw) || array_key_exists('findings', $raw)) {
            $findings = $raw['findings'] ?? [];
            if (!is_array($findings)) {
                $findings = [];
            }

            return [
                'schema_version' => self::SCHEMA_VERSION,
                'findings' => self::normalizeFindingsList($findings),
            ];
        }

        // v1 bare list
        if (array_is_list($raw) || self::looksLikeFindingsList($raw)) {
            return [
                'schema_version' => self::SCHEMA_VERSION,
                'findings' => self::normalizeFindingsList($raw),
            ];
        }

        return self::emptyDocument();
    }

    /**
     * Build a v2 document from POSTed treatment[n][...] arrays.
     *
     * @param array<int|string, mixed> $postFindings
     * @return array{schema_version: int, findings: list<array<string, mixed>>}
     */
    public static function fromPostFindings(array $postFindings): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'findings' => self::normalizeFindingsList($postFindings),
        ];
    }

    /**
     * Flatten document to the UI/legacy list shape (no schema wrapper).
     *
     * @param array{schema_version?: int, findings?: list<array<string, mixed>>} $document
     * @return list<array<string, mixed>>
     */
    public static function findingsForUi(array $document): array
    {
        $findings = $document['findings'] ?? [];
        if (!is_array($findings)) {
            return [];
        }

        $out = [];
        foreach ($findings as $finding) {
            if (is_array($finding)) {
                $out[] = $finding;
            }
        }

        return $out;
    }

    /**
     * @param array{schema_version?: int, findings?: list<array<string, mixed>>} $document
     */
    public static function encodeForStorage(array $document): string
    {
        $normalized = [
            'schema_version' => self::SCHEMA_VERSION,
            'findings' => self::normalizeFindingsList($document['findings'] ?? []),
        ];

        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new GeneralDermatologyException('Unable to encode treatment document.');
        }

        return $json;
    }

    /**
     * Validate a v2 document. Returns list of error strings (empty = ok).
     *
     * @param array{schema_version?: int, findings?: mixed} $document
     * @return list<string>
     */
    public static function validate(array $document): array
    {
        $errors = [];
        if (!array_key_exists('findings', $document) || !is_array($document['findings'])) {
            $errors[] = 'Treatment document must contain a findings array.';
            return $errors;
        }

        foreach ($document['findings'] as $index => $finding) {
            if (!is_array($finding)) {
                $errors[] = "Finding #{$index} must be an object.";
                continue;
            }
            $location = trim((string) ($finding['finding_location'] ?? ''));
            if ($location === '') {
                $errors[] = "Finding #{$index} is missing finding_location.";
            }
            if (isset($finding['location']) && !is_array($finding['location'])) {
                $errors[] = "Finding #{$index} location must be an object when present.";
            }
        }

        return $errors;
    }

    /**
     * @return array{schema_version: int, findings: list<array<string, mixed>>}
     */
    public static function emptyDocument(): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'findings' => [],
        ];
    }

    /**
     * @param array<int|string, mixed> $findings
     * @return list<array<string, mixed>>
     */
    private static function normalizeFindingsList(array $findings): array
    {
        $normalized = [];
        foreach ($findings as $item) {
            if (!is_array($item)) {
                continue;
            }
            $finding = self::normalizeFinding($item);
            if ($finding === null) {
                continue;
            }
            $normalized[] = $finding;
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>|null
     */
private static function normalizeFinding(array $item): ?array
    {
        // UI may post a single JSON blob for structured location + markers.
        if (isset($item['location_json']) && is_string($item['location_json']) && $item['location_json'] !== '') {
            $decodedLocation = json_decode($item['location_json'], true);
            if (is_array($decodedLocation)) {
                $existingLocation = is_array($item['location'] ?? null) ? $item['location'] : [];
                $item['location'] = array_merge($existingLocation, $decodedLocation);
                if (
                    trim((string) ($item['finding_location'] ?? '')) === ''
                    && trim((string) ($decodedLocation['label'] ?? '')) !== ''
                ) {
                    $item['finding_location'] = (string) $decodedLocation['label'];
                }
            }
        }

        $locationLabel = trim((string) ($item['finding_location'] ?? ''));
        if ($locationLabel === '') {
            // Skip empty finding cards (same behavior as legacy save filter).
            return null;
        }

        $out = [];
        foreach ($item as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }
            // Drop pure control noise and transport-only keys.
            if (in_array($key, ['csrf_token_form', 'mode', 'id', 'location_json'], true)) {
                continue;
            }
            $out[$key] = self::normalizeValue($value);
        }

        $out['finding_location'] = $locationLabel;
        $out['location'] = self::normalizeLocation(
            is_array($item['location'] ?? null) ? $item['location'] : [],
            $locationLabel
        );

        // Ensure core keys exist as stable strings when absent.
        foreach (['dx_s', 'plan', 'plan_desc', 'procedure_plans', 'severity_drop', 'icd_alt', 'dx_alt'] as $core) {
            if (!array_key_exists($core, $out)) {
                $out[$core] = '';
            } elseif (is_array($out[$core])) {
                // select2 multi sometimes posts arrays for dx_desc
                if ($core === 'dx_desc') {
                    $out[$core] = array_values(array_map('strval', $out[$core]));
                } else {
                    $out[$core] = implode(', ', array_map('strval', $out[$core]));
                }
            } else {
                $out[$core] = is_scalar($out[$core]) ? (string) $out[$core] : '';
            }
        }

        if (!array_key_exists('dx_desc', $out)) {
            $out['dx_desc'] = '';
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $location
     * @return array{id: string, label: string, laterality: string, view: string, markers: list<array{x: float, y: float}>}
     */
    private static function normalizeLocation(array $location, string $fallbackLabel): array
    {
        $label = trim((string) ($location['label'] ?? $fallbackLabel));
        $id = trim((string) ($location['id'] ?? ''));
        if ($id === '' && $label !== '') {
            $id = LocationCatalog::idFromLabel($label);
        }
        $laterality = trim((string) ($location['laterality'] ?? ''));
        if ($laterality === '' && $label !== '') {
            $laterality = LocationCatalog::inferLaterality($label);
        }
        $view = trim((string) ($location['view'] ?? ''));
        if ($view === '' && $label !== '') {
            $view = LocationCatalog::inferView($label);
        }

        $markers = [];
        $rawMarkers = $location['markers'] ?? [];
        if (is_string($rawMarkers) && $rawMarkers !== '') {
            $decodedMarkers = json_decode($rawMarkers, true);
            $rawMarkers = is_array($decodedMarkers) ? $decodedMarkers : [];
        }
        if (is_array($rawMarkers)) {
            foreach ($rawMarkers as $marker) {
                if (!is_array($marker)) {
                    continue;
                }
                if (!isset($marker['x'], $marker['y'])) {
                    continue;
                }
                $x = (float) $marker['x'];
                $y = (float) $marker['y'];
                if ($x < 0.0 || $x > 1.0 || $y < 0.0 || $y > 1.0) {
                    continue;
                }
                $markers[] = ['x' => round($x, 6), 'y' => round($y, 6)];
            }
        }

        return [
            'id' => $id,
            'label' => $label,
            'laterality' => $laterality,
            'view' => $view,
            'markers' => $markers,
        ];
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[is_string($k) || is_int($k) ? $k : (string) $k] = self::normalizeValue($v);
            }
            return $out;
        }

        return '';
    }

    /**
     * @param array<int|string, mixed> $raw
     */
    private static function looksLikeFindingsList(array $raw): bool
    {
        foreach ($raw as $item) {
            if (is_array($item) && array_key_exists('finding_location', $item)) {
                return true;
            }
        }

        return false;
    }
}

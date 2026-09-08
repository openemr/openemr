<?php

/**
 * Compare a consumer repo's vendored copies of cross-repo contracts against
 * the canonical sources in this repo. Used by CI in consumer repos
 * (openemr/openemr-devops, openemr/website-openemr) to catch silent contract
 * drift.
 *
 * Layout assumption: the consumer mirrors the canonical relative paths under
 * its vendored dir. A consumer that vendors to `vendored/openemr/` therefore
 * has `vendored/openemr/contracts/dispatch.schema.json` and
 * `vendored/openemr/src/TagVerifier.php`. A consumer that vendored into a
 * different layout (e.g. `src/Release/TagVerifier.php`) can pass a
 * `$pathOverrides` map keyed by canonical path → consumer-relative path.
 *
 * Equivalence is per-file-type, per the openemr-devops#664 spec (issue lives
 * on openemr-devops for historical reasons; the mechanism now lives here):
 *
 *   - JSON: parse + canonical re-serialize (recursively sort object keys,
 *     preserve list order, normalize whitespace), then string-compare.
 *     A consumer can reformat or reorder object keys without tripping drift.
 *   - PHP: strip the `namespace` declaration line, then string-compare.
 *     A consumer can vendor under its own namespace (e.g.
 *     `OpenEMR\ReleaseDocs\Release`) so long as everything else — class
 *     structure, method signatures, behavior — is identical.
 *   - Other: byte-for-byte sha256. Default for any file type added later.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class VendoredFileChecker
{
    /**
     * Files consumers must vendor. Keep this list tight — every entry is an
     * obligation on every consumer repo's CI.
     */
    public const VENDORED_PATHS = [
        'contracts/dispatch.schema.json',
        'src/TagVerifier.php',
        'src/TagVerificationResult.php',
    ];

    /** @var array<string, string> */
    private array $pathOverrides;

    /**
     * @param array<array-key, mixed> $pathOverrides Map of canonical relative
     *     path → consumer relative path. Validated at runtime since CLI/CI
     *     callers can produce arbitrary input: keys and values must both be
     *     strings; unmapped entries default to the canonical path; unknown
     *     keys (not in VENDORED_PATHS) throw, as do values that are absolute
     *     or contain `..` segments — overrides must stay inside the
     *     consumer dir.
     */
    public function __construct(
        private string $canonicalRoot,
        private string $consumerDir,
        array $pathOverrides = [],
    ) {
        $validated = [];
        foreach ($pathOverrides as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'Override entries must be string=>string, got %s=>%s',
                    get_debug_type($key),
                    get_debug_type($value),
                ));
            }
            if (!in_array($key, self::VENDORED_PATHS, true)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown override key %s; must be one of: %s',
                    $key,
                    implode(', ', self::VENDORED_PATHS),
                ));
            }
            if ($value === '' || $value[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $value) === 1) {
                throw new \InvalidArgumentException(sprintf(
                    'Override value for %s must be a relative path inside the consumer dir, got: %s',
                    $key,
                    $value,
                ));
            }
            $validated[$key] = $value;
        }
        $this->pathOverrides = $validated;
    }

    /**
     * @return list<VendoredDriftIssue>
     */
    public function check(): array
    {
        $issues = [];
        foreach (self::VENDORED_PATHS as $rel) {
            $canonicalAbs = $this->canonicalRoot . '/' . $rel;
            $consumerRel = $this->pathOverrides[$rel] ?? $rel;
            $consumerAbs = $this->consumerDir . '/' . $consumerRel;

            if (!is_file($canonicalAbs)) {
                $issues[] = new VendoredDriftIssue(
                    $rel,
                    'missing_canonical',
                    'Canonical file not found: ' . $canonicalAbs,
                );
                continue;
            }
            if (!is_file($consumerAbs)) {
                $issues[] = new VendoredDriftIssue(
                    $consumerRel,
                    'missing_consumer',
                    'Consumer copy missing — vendor it from canonical at ' . $canonicalAbs,
                );
                continue;
            }
            if (!$this->equivalent($rel, $canonicalAbs, $consumerAbs)) {
                $issues[] = new VendoredDriftIssue(
                    $consumerRel,
                    'drift',
                    'Consumer copy differs from canonical — re-vendor from ' . $canonicalAbs,
                );
            }
        }
        return $issues;
    }

    private function equivalent(string $relativePath, string $canonicalAbs, string $consumerAbs): bool
    {
        $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
        return match ($extension) {
            'json' => $this->canonicalJson($canonicalAbs) === $this->canonicalJson($consumerAbs),
            'php' => $this->stripNamespace($this->readFile($canonicalAbs))
                === $this->stripNamespace($this->readFile($consumerAbs)),
            default => hash_file('sha256', $canonicalAbs) === hash_file('sha256', $consumerAbs),
        };
    }

    private function canonicalJson(string $path): string
    {
        $contents = $this->readFile($path);
        try {
            $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Invalid JSON in ' . $path . ': ' . $e->getMessage(), 0, $e);
        }
        $this->sortObjectKeys($decoded);
        return json_encode(
            $decoded,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * Recursively sort object (associative array) keys to make JSON
     * comparison insensitive to key order. List order stays intact —
     * arrays in JSON Schema (`enum`, `oneOf`, `required`) carry semantic
     * meaning that reordering would silently change.
     */
    private function sortObjectKeys(mixed &$value): void
    {
        if (!is_array($value)) {
            return;
        }
        if (!array_is_list($value)) {
            ksort($value);
        }
        foreach ($value as &$child) {
            $this->sortObjectKeys($child);
        }
    }

    /**
     * Strip the single `namespace …;` declaration line from a PHP source
     * string. Removes the line itself but leaves surrounding whitespace
     * intact — both canonical and consumer files have the same
     * surrounding whitespace, so byte-comparing the post-strip strings
     * is sufficient.
     */
    private function stripNamespace(string $contents): string
    {
        $stripped = preg_replace('/^namespace\s+[\w\\\\]+;\s*$/m', '', $contents);
        return $stripped ?? $contents;
    }

    private function readFile(string $path): string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }
        return $contents;
    }
}

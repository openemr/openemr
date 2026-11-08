<?php

/**
 * Derive the minimum tested runtime version per component from the
 * openemr/openemr CI test matrix.
 *
 * The matrix is the source of truth for what a release was actually exercised
 * against: CI globs `ci/!(compose-shared-*)/docker-compose.yml` and runs one
 * job per directory. This class reads the same directories, decodes the PHP
 * version from each directory name and the database type/version from each
 * compose file's `services.mysql.image`, and reports the minimum tested version
 * per component. The full tested matrix is not reproduced here; the release
 * notes link to the release branch's `ci/` directory so the exact set of tested
 * combinations lives in the repo.
 *
 * Decode rules mirror ci/parse_docker_dir.sh in openemr/openemr (the canonical
 * decoder) so the result can never disagree with what CI ran:
 *   - PHP from the dir name's 2nd `_`-delimited field: `82` -> `8.2`
 *     (first char major, remainder minor).
 *   - DB type + version from `services.mysql.image`: strip the `@sha256:` digest,
 *     split on `:`, e.g. `mariadb:11.8.6` -> `mariadb` / `11.8.6`.
 *
 * Versions are keyed to MAJOR.MINOR (the image patch is dropped) so the result
 * stays stable across patch-image bumps, and the minimum is computed with
 * version_compare (version-aware, not lexical: `8.10` > `8.9`).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type Minimums array<string, string>
 */
final readonly class CompatibilityDeriver
{
    public function __construct(
        private string $ciDir,
    ) {
    }

    /**
     * Minimum tested version per component, keyed `php` first then db types
     * in alphabetical order (e.g. `mariadb` before `mysql`).
     *
     * @return Minimums
     */
    public function derive(): array
    {
        if (!is_dir($this->ciDir)) {
            throw new \RuntimeException("CI directory not found: {$this->ciDir}");
        }

        $phpVersions = [];
        /** @var array<string, list<string>> $dbVersions keyed by db type (mariadb, mysql) */
        $dbVersions = [];

        foreach (new \DirectoryIterator($this->ciDir) as $entry) {
            if ($entry->isDot() || !$entry->isDir()) {
                continue;
            }
            $dirName = $entry->getFilename();
            // Mirror CI's `!(compose-shared-*)` glob exclusion.
            if (str_starts_with($dirName, 'compose-shared-')) {
                continue;
            }
            $composePath = $entry->getPathname() . '/docker-compose.yml';
            // CI's glob only matches directories containing docker-compose.yml,
            // so directories without one (e.g. inferno's compose.yml, the bare
            // nginx config dir) are not matrix jobs and are skipped.
            if (!is_file($composePath)) {
                continue;
            }

            $phpVersions[] = $this->decodePhpVersion($dirName);

            [$dbType, $dbVersion] = $this->decodeDatabase($composePath);
            $dbVersions[$dbType][] = $dbVersion;
        }

        if ($phpVersions === []) {
            throw new \RuntimeException("No CI matrix directories found under: {$this->ciDir}");
        }

        $minimums = ['php' => $this->minimum($phpVersions)];
        // Sort db types for deterministic output (mariadb before mysql).
        ksort($dbVersions);
        foreach ($dbVersions as $dbType => $versions) {
            $minimums[$dbType] = $this->minimum($versions);
        }

        return $minimums;
    }

    /**
     * Decode the PHP minor version from a matrix directory name.
     *
     * Mirrors `IFS=_ read -r webserver php _` then `printf '%d.%d' "${php::1}"
     * "${php:1}"` in ci/parse_docker_dir.sh: the 2nd `_`-field is the packed PHP
     * version, first char major and remainder minor (`82` -> `8.2`, `810` ->
     * `8.10`).
     */
    private function decodePhpVersion(string $dirName): string
    {
        $fields = explode('_', $dirName);
        $packed = $fields[1] ?? '';
        if (!ctype_digit($packed) || strlen($packed) < 2) {
            throw new \RuntimeException(
                "Cannot decode PHP version from CI directory name: {$dirName}",
            );
        }
        return $packed[0] . '.' . substr($packed, 1);
    }

    /**
     * Decode the database type and MAJOR.MINOR version from a compose file's
     * `services.mysql.image`.
     *
     * Mirrors ci/parse_docker_dir.sh: strip the `@sha256:` digest (`%%@*`), then
     * split on `:` (`IFS=:`). The image patch is dropped so the manifest is
     * stable across patch bumps (`mariadb:11.8.6` -> `mariadb` / `11.8`).
     *
     * @return array{string, string} [type, MAJOR.MINOR version]
     */
    private function decodeDatabase(string $composePath): array
    {
        $parsed = Yaml::parseFile($composePath);
        $services = is_array($parsed) ? ($parsed['services'] ?? null) : null;
        $mysql = is_array($services) ? ($services['mysql'] ?? null) : null;
        $image = is_array($mysql) ? ($mysql['image'] ?? null) : null;
        if (!is_string($image) || $image === '') {
            throw new \RuntimeException(
                "Missing services.mysql.image in compose file: {$composePath}",
            );
        }

        // Strip the @sha256:... digest suffix before splitting on ':'.
        $digestPos = strpos($image, '@');
        if ($digestPos !== false) {
            $image = substr($image, 0, $digestPos);
        }

        $colonPos = strpos($image, ':');
        if ($colonPos === false) {
            throw new \RuntimeException(
                "Cannot decode database type:version from image '{$image}' in {$composePath}",
            );
        }
        $type = substr($image, 0, $colonPos);
        $fullVersion = substr($image, $colonPos + 1);

        return [$type, $this->majorMinor($fullVersion)];
    }

    /**
     * Reduce a full version to MAJOR.MINOR, dropping any patch and beyond.
     */
    private function majorMinor(string $version): string
    {
        $parts = explode('.', $version);
        $major = $parts[0];
        $minor = $parts[1] ?? '0';
        return $major . '.' . $minor;
    }

    /**
     * Fold a list of versions into the minimum using version-aware ordering.
     *
     * @param list<string> $versions
     */
    private function minimum(array $versions): string
    {
        $min = $versions[0];
        foreach ($versions as $version) {
            if (version_compare($version, $min, '<')) {
                $min = $version;
            }
        }
        return $min;
    }
}

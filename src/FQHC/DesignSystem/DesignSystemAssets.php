<?php

/**
 * FQHC design-system asset bundle resolver.
 *
 * Resolves the design-system asset bundle (design tokens + Web Component
 * library) to browser URLs and reports any missing files. Kept free of
 * superglobals and framework state so it is unit-testable in isolation and
 * reusable by both the module's host page and a runtime health check.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\DesignSystem;

final readonly class DesignSystemAssets
{
    /**
     * Stylesheets in load order, relative to the module's `public/` directory.
     * `tokens.css` MUST load first — it defines the custom properties every
     * other stylesheet and component depends on.
     *
     * @var list<string>
     */
    public const STYLES = [
        'assets/css/tokens.css',
        'assets/css/fqhc.css',
    ];

    /**
     * Scripts (ES modules) relative to the module's `public/` directory.
     *
     * @var list<string>
     */
    public const SCRIPTS = [
        'assets/js/fqhc-components.js',
    ];

    /**
     * @param string $publicRoot    Absolute filesystem path to the module's `public/` directory.
     * @param string $publicBaseUrl Browser-facing base URL for that same directory.
     */
    public function __construct(
        private string $publicRoot,
        private string $publicBaseUrl,
    ) {
    }

    /**
     * Stylesheet URLs in load order, cache-busted by file mtime.
     *
     * @return list<string>
     */
    public function styleUrls(): array
    {
        return $this->urls(self::STYLES);
    }

    /**
     * Script URLs, cache-busted by file mtime.
     *
     * @return list<string>
     */
    public function scriptUrls(): array
    {
        return $this->urls(self::SCRIPTS);
    }

    /**
     * Absolute filesystem paths of bundle files that are missing. An empty list
     * means the bundle is intact; a non-empty list is a deployment problem the
     * caller (smoke test or health check) should surface.
     *
     * @return list<string>
     */
    public function missingFiles(): array
    {
        $missing = [];
        foreach ([...self::STYLES, ...self::SCRIPTS] as $relativePath) {
            $path = $this->path($relativePath);
            if (!is_file($path)) {
                $missing[] = $path;
            }
        }

        return $missing;
    }

    /**
     * @param list<string> $relativePaths
     * @return list<string>
     */
    private function urls(array $relativePaths): array
    {
        $base = rtrim($this->publicBaseUrl, '/');

        return array_map(function (string $relativePath) use ($base): string {
            $fsPath = $this->path($relativePath);
            $version = is_file($fsPath) ? (string) filemtime($fsPath) : '0';

            return $base . '/' . $relativePath . '?v=' . $version;
        }, $relativePaths);
    }

    private function path(string $relativePath): string
    {
        return rtrim($this->publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
}

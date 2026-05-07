<?php

/**
 * Validate and normalize default-open-tab entries.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tabs;

use OpenEMR\Common\Filesystem\SafeIncludeResolver;

/**
 * Filter the `default_open_tabs` session value before rendering it
 * into the main tab shell.
 *
 * Each entry must:
 *  - be an array with a string `notes` value (path under the project
 *    root, optionally with a query string)
 *  - have a string identifier — `option_id` (modern, from
 *    `list_options.default_open_tabs`) or `id` (legacy, used by the
 *    password-expiration / open-patient / load-calendar entries that
 *    `interface/main/main_screen.php` prepends or appends)
 *  - have a string display label — `title` (modern) or `label` (legacy)
 *  - resolve to a real file under the project root via
 *    `SafeIncludeResolver` so a rogue `notes` value cannot point at
 *    arbitrary files
 *
 * Invalid or out-of-tree entries are dropped. Surviving entries are
 * normalized to the modern key shape (`option_id` / `title`) so the
 * rendering loop only has to deal with one variant.
 */
final class DefaultTabsFilter
{
    /**
     * @param mixed $tabs Raw value pulled from the session
     * @param string $projectDir Absolute filesystem path to the project root
     * @return list<array{notes: string, option_id: string, title: string}>
     */
    public function filter(mixed $tabs, string $projectDir): array
    {
        if (!is_array($tabs)) {
            return [];
        }
        $valid = [];
        foreach ($tabs as $tab) {
            $normalized = self::normalize($tab);
            if ($normalized === null) {
                continue;
            }
            if (!self::resolvesUnderRoot($normalized['notes'], $projectDir)) {
                continue;
            }
            $valid[] = $normalized;
        }
        return $valid;
    }

    /**
     * @return array{notes: string, option_id: string, title: string}|null
     */
    private static function normalize(mixed $tab): ?array
    {
        if (!is_array($tab)) {
            return null;
        }
        $notes = $tab['notes'] ?? null;
        $option_id = $tab['option_id'] ?? $tab['id'] ?? null;
        $title = $tab['title'] ?? $tab['label'] ?? null;
        if (!is_string($notes) || !is_string($option_id) || !is_string($title)) {
            return null;
        }
        return ['notes' => $notes, 'option_id' => $option_id, 'title' => $title];
    }

    private static function resolvesUnderRoot(string $notes, string $projectDir): bool
    {
        $relative = preg_replace('/\?.*$/', '', $notes) ?? '';
        if ($relative === '') {
            return false;
        }
        return SafeIncludeResolver::resolve($projectDir, $relative) !== false;
    }
}

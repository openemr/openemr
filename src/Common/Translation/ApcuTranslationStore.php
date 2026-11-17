<?php

/**
 * SharedTranslationStore backed by APCu.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * APCu is optional: it is missing from many PHP builds (for example XAMPP on
 * Windows), shared hosting often disables it, and it is off on the command
 * line by default (apc.enable_cli). Check isAvailable() first and fall back
 * to the database when it returns false.
 */
final class ApcuTranslationStore implements SharedTranslationStore
{
    public static function isAvailable(): bool
    {
        return function_exists('apcu_enabled') && apcu_enabled();
    }

    public function fetch(string $key): ?array
    {
        $value = apcu_fetch($key, $found);
        if (!$found || !is_array($value)) {
            return null;
        }
        $definitions = [];
        foreach ($value as $constant => $definition) {
            if (!is_string($definition)) {
                return null;
            }
            $definitions[$constant] = $definition;
        }
        return $definitions;
    }

    public function store(string $key, array $definitions, int $ttlSeconds): void
    {
        // A failed store (for example a full cache) only means the next request
        // reads the database again.
        apcu_store($key, $definitions, $ttlSeconds);
    }
}

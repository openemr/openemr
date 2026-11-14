<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Translation;

use OpenEMR\Common\Database\QueryUtils;

/**
 * In-memory cache for translations to avoid repeated database queries.
 */
class TranslationCache
{
    /** @var array<int, array<string, string>> Cache indexed by language ID */
    private static array $cache = [];

    /** @var array<int, true> Languages whose definitions have all been loaded */
    private static array $warmed = [];

    /**
     * Warm the cache by loading all translations for the given language.
     */
    public static function warm(int $langId): void
    {
        if (isset(self::$warmed[$langId])) {
            return;
        }

        // STRAIGHT_JOIN keeps MariaDB reading only this language's rows through
        // the lang_cons index. Without it, a large language sometimes got a plan
        // that scanned every constant and probed definitions in all languages
        // (about 340 ms instead of about 40 ms for a full language).
        $sql = <<<'SQL'
            SELECT lang_constants.constant_name,
                   lang_definitions.definition
              FROM lang_definitions
              STRAIGHT_JOIN lang_constants
                ON lang_constants.cons_id = lang_definitions.cons_id
             WHERE lang_definitions.lang_id = ?
            SQL;
        $rows = QueryUtils::fetchRecordsNoLog($sql, [$langId]);
        self::$cache[$langId] = array_column($rows, 'definition', 'constant_name');
        self::$warmed[$langId] = true;
    }

    /**
     * Number of definitions a language has (0 for English).
     */
    public static function countDefinitions(int $langId): int
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT COUNT(*) AS cnt FROM lang_definitions WHERE lang_id = ?',
            [$langId]
        );
        $count = $rows[0]['cnt'] ?? 0;
        return is_numeric($count) ? (int) $count : 0;
    }

    /**
     * Check if a translation exists in cache.
     */
    public static function has(int $langId, string $constant): bool
    {
        return isset(self::$cache[$langId][$constant]);
    }

    /**
     * Get a translation from cache.
     */
    public static function get(int $langId, string $constant): ?string
    {
        return self::$cache[$langId][$constant] ?? null;
    }

    /**
     * Store a translation in cache.
     */
    public static function set(int $langId, string $constant, string $definition): void
    {
        self::$cache[$langId] ??= [];
        self::$cache[$langId][$constant] = $definition;
    }

    /**
     * Check if all of a language's definitions have been loaded, so a
     * constant missing from the cache has no translation in that language.
     * Per language: a request can warm one language and then translate in
     * another (login.php sets the language after globals.php has run).
     */
    public static function isWarmed(int $langId): bool
    {
        return isset(self::$warmed[$langId]);
    }

    /**
     * Reset the cache (useful for testing).
     */
    public static function reset(): void
    {
        self::$cache = [];
        self::$warmed = [];
    }
}

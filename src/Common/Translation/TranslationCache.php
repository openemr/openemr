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
    /**
     * Name of the hidden global (not listed in globals.inc.php) whose value is
     * part of every shared-cache key. Bumping it makes every server and pod
     * ignore the languages it has cached.
     */
    public const VERSION_GLOBAL = 'translation_cache_version';

    /** @var array<int, array<array-key, string>> Cache indexed by language ID */
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
        self::warmWith($langId, self::loadDefinitions($langId));
    }

    /**
     * Mark a language as fully loaded with the given definitions (for example
     * from a shared cache).
     *
     * @param array<array-key, string> $definitions Keyed by constant name
     */
    public static function warmWith(int $langId, array $definitions): void
    {
        self::$cache[$langId] = $definitions;
        self::$warmed[$langId] = true;
    }

    /**
     * Read all of a language's definitions from the database.
     *
     * @return array<array-key, string> Keyed by constant name
     */
    public static function loadDefinitions(int $langId): array
    {
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
        $definitions = [];
        foreach (QueryUtils::fetchRecordsNoLog($sql, [$langId]) as $row) {
            $constant = $row['constant_name'] ?? null;
            $definition = $row['definition'] ?? null;
            if ((is_string($constant) || is_int($constant)) && is_string($definition)) {
                $definitions[$constant] = $definition;
            }
        }
        return $definitions;
    }

    /**
     * Invalidate every server's shared cache of translations. Call after
     * changing lang_definitions.
     */
    public static function bumpSharedVersion(): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)'
            . ' ON DUPLICATE KEY UPDATE `gl_value` = VALUES(`gl_value`)',
            [self::VERSION_GLOBAL, bin2hex(random_bytes(8))]
        );
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

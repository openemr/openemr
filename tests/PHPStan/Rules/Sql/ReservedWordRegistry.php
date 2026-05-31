<?php

/**
 * Union of MySQL and MariaDB reserved-word sets, sourced from
 * phpmyadmin/sql-parser's keyword tables plus a small supplement for
 * MySQL 8.0+ window-function reserveds that the parser library has not
 * yet picked up.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules\Sql;

use PhpMyAdmin\SqlParser\Context;
use PhpMyAdmin\SqlParser\Token;
use ReflectionClass;
use RuntimeException;

/**
 * The bulk of the list refreshes automatically when phpmyadmin/sql-parser
 * is updated. Rather than hard-coding a specific ContextMySql<NNNNNN> /
 * ContextMariaDb<NNNNNN> class name (which would require a manual import
 * bump every time upstream ships a new engine version), the registry
 * discovers the highest-numbered Context class for each engine at
 * construction time. Cost is one glob + one ReflectionClass per engine,
 * once per PHPStan worker boot.
 *
 * The supplement below covers reserved words the upstream library does
 * not yet ship -- historically MySQL 8.0 window-function additions
 * (RANK, DENSE_RANK, ROW_NUMBER, etc.) plus a handful of MariaDB-specific
 * entries (RETURNING, SLOW, PAGE_CHECKSUM, ROWS_EXAMINED). The list is
 * auto-refreshed monthly by .github/workflows/refresh-reserved-word-supplement.yml
 * which queries live MySQL + MariaDB engines and opens a PR when drift
 * is detected; reserved-words/{mysql,mariadb}.tsv is the audit trail.
 *
 * See https://dev.mysql.com/doc/refman/8.4/en/keywords.html for MySQL
 * and https://mariadb.com/kb/en/reserved-words/ for MariaDB if hand
 * verification is ever needed.
 */
final readonly class ReservedWordRegistry
{
    /**
     * Reserved words that phpmyadmin/sql-parser's keyword tables omit
     * for the latest MySQL and MariaDB Context classes shipped by the
     * library. All entries are single-word identifiers; composed tokens
     * (e.g. "ORDER BY") can never be bare column names and are
     * excluded by the refresh script.
     *
     * @var list<string>
     */
    private const RESERVED_WORD_SUPPLEMENT = [
        'cume_dist',
        'delete_domain_id',
        'dense_rank',
        'do_domain_ids',
        'empty',
        'first_value',
        'function',
        'grouping',
        'groups',
        'ignore_domain_ids',
        'json_table',
        'lag',
        'last_value',
        'lateral',
        'lead',
        'library',
        'master_demote_to_replica',
        'master_demote_to_slave',
        'nth_value',
        'ntile',
        'of',
        'page_checksum',
        'parse_vcol_expr',
        'percent_rank',
        'portion',
        'rank',
        'ref_system_id',
        'returning',
        'row',
        'sql_after_gtids',
        'sql_before_gtids',
        'sql_buffer_result',
        'sql_cache',
        'sql_no_cache',
        'stats_auto_recalc',
        'stats_persistent',
        'stats_sample_pages',
        'system',
        'window',
    ];

    /**
     * Lowercase reserved words, stored as a flipped map for O(1) lookup.
     *
     * @var array<string, true>
     */
    private array $words;

    public function __construct()
    {
        $set = [];

        foreach (self::collectReserved('MySql') as $word) {
            $set[$word] = true;
        }
        foreach (self::collectReserved('MariaDb') as $word) {
            $set[$word] = true;
        }
        foreach (self::RESERVED_WORD_SUPPLEMENT as $word) {
            $set[$word] = true;
        }

        $this->words = $set;
    }

    public function isReserved(string $word): bool
    {
        return isset($this->words[strtolower($word)]);
    }

    /**
     * @return iterable<string>
     */
    private static function collectReserved(string $engine): iterable
    {
        $contextClass = self::resolveLatestContextClass($engine);

        /** @var array<string, int> $keywords */
        $keywords = $contextClass::KEYWORDS;
        foreach ($keywords as $word => $flags) {
            if (($flags & Token::FLAG_KEYWORD_RESERVED) !== 0) {
                yield strtolower($word);
            }
        }
    }

    /**
     * Locates the highest-numbered ContextMySql* / ContextMariaDb* class
     * shipped by the currently-installed phpmyadmin/sql-parser, so the
     * registry self-updates on `composer update` without manual import
     * bumps.
     *
     * @return class-string
     */
    private static function resolveLatestContextClass(string $engine): string
    {
        $contextsDir = dirname(
            (string) (new ReflectionClass(Context::class))->getFileName()
        ) . '/Contexts';

        $files = glob($contextsDir . '/Context' . $engine . '*.php');
        if (!is_array($files) || $files === []) {
            throw new RuntimeException(sprintf(
                'No phpmyadmin/sql-parser context files found for engine "%s" in %s',
                $engine,
                $contextsDir,
            ));
        }

        $latestClass = null;
        $latestVersion = -1;
        $pattern = '/Context' . preg_quote($engine, '/') . '(\d+)\.php$/';
        foreach ($files as $file) {
            if (preg_match($pattern, $file, $matches) !== 1) {
                continue;
            }
            $version = (int) $matches[1];
            if ($version > $latestVersion) {
                $latestVersion = $version;
                $latestClass = 'PhpMyAdmin\\SqlParser\\Contexts\\Context' . $engine . $matches[1];
            }
        }

        if ($latestClass === null || !class_exists($latestClass)) {
            throw new RuntimeException(sprintf(
                'Could not resolve a Context class for engine "%s"',
                $engine,
            ));
        }

        return $latestClass;
    }
}

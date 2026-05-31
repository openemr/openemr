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
 * The supplement below covers the ~13 single-word reserveds that landed
 * with MySQL 8.0 (window functions, CTE keywords, JSON_TABLE) that the
 * upstream library's MySQL 9.x context still omits. Historically this
 * list has been touched exactly once -- the 5.7 → 8.0 transition. When
 * the upstream library eventually fills the gap, individual entries can
 * be removed; the registry will still produce the same union.
 *
 * See https://dev.mysql.com/doc/refman/8.4/en/keywords.html for the
 * authoritative MySQL reserved-word table.
 */
final readonly class ReservedWordRegistry
{
    /**
     * MySQL 8.0+ reserveds that phpmyadmin/sql-parser's keyword tables
     * still omit. All are reserved single-word identifiers.
     *
     * @var list<string>
     */
    private const MYSQL_EIGHT_SUPPLEMENT = [
        'cume_dist',
        'dense_rank',
        'first_value',
        'groups',
        'lag',
        'last_value',
        'lead',
        'nth_value',
        'ntile',
        'percent_rank',
        'rank',
        'row_number',
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
        foreach (self::MYSQL_EIGHT_SUPPLEMENT as $word) {
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

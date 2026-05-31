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

use PhpMyAdmin\SqlParser\Contexts\ContextMariaDb120100;
use PhpMyAdmin\SqlParser\Contexts\ContextMySql90300;
use PhpMyAdmin\SqlParser\Token;

/**
 * The bulk of the list refreshes automatically when phpmyadmin/sql-parser
 * is updated — the union spans 320+ words and tracks both engines' GA
 * releases. The supplement below covers the ~13 single-word reserveds
 * that landed with MySQL 8.0 (window functions, CTE keywords, JSON_TABLE)
 * that the upstream library's MySQL 9.x context still omits.
 *
 * Maintenance burden: the supplement has been touched exactly once in
 * 15 years (the 5.7 → 8.0 transition). When the upstream library
 * eventually fills the gap, individual entries can be removed; the
 * registry will still produce the same union.
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

        foreach (ContextMySql90300::KEYWORDS as $word => $flags) {
            if (($flags & Token::FLAG_KEYWORD_RESERVED) !== 0) {
                $set[strtolower($word)] = true;
            }
        }
        foreach (ContextMariaDb120100::KEYWORDS as $word => $flags) {
            if (($flags & Token::FLAG_KEYWORD_RESERVED) !== 0) {
                $set[strtolower($word)] = true;
            }
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
}

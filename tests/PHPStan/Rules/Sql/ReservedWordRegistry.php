<?php

/**
 * Canonical reserved-word set for MySQL 8+ and MariaDB.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules\Sql;

/**
 * Union of words reserved by any MySQL 8.0+ or MariaDB 10.6+ release.
 *
 * An identifier that matches any entry in this set will fail to parse on at
 * least one of OpenEMR's supported database engines unless backticked. The
 * SqlReservedWordRule uses this set, intersected with known OpenEMR column
 * names, to flag latent reserved-word bugs.
 *
 * Sources:
 *   - https://dev.mysql.com/doc/refman/8.4/en/keywords.html (R column)
 *   - https://mariadb.com/kb/en/reserved-words/
 *
 * Refresh policy: when a new MySQL or MariaDB major release drops, re-pull
 * both lists and union into this constant. The phpmyadmin/sql-parser
 * Context* classes are NOT authoritative — their MySQL 8+ tables are missing
 * window-function reserveds (RANK, DENSE_RANK, ROW_NUMBER, etc.). Do not
 * delegate to them.
 */
final readonly class ReservedWordRegistry
{
    /**
     * Lowercase reserved words. Stored as a flipped map (name => true) for
     * O(1) lookup.
     *
     * @var array<string, true>
     */
    private array $words;

    public function __construct()
    {
        // Curated below. When updating, keep entries sorted within each
        // group and preserve the source attribution comments.
        $words = [
            // --- Always-reserved (present in MySQL 5.7 and earlier; also reserved in MariaDB) ---
            'accessible', 'add', 'all', 'alter', 'analyze', 'and', 'as', 'asc',
            'asensitive', 'before', 'between', 'bigint', 'binary', 'blob',
            'both', 'by', 'call', 'cascade', 'case', 'change', 'char',
            'character', 'check', 'collate', 'column', 'condition', 'constraint',
            'continue', 'convert', 'create', 'cross', 'cube', 'current_date',
            'current_time', 'current_timestamp', 'current_user', 'cursor',
            'database', 'databases', 'day_hour', 'day_microsecond', 'day_minute',
            'day_second', 'dec', 'decimal', 'declare', 'default', 'delayed',
            'delete', 'desc', 'describe', 'deterministic', 'distinct',
            'distinctrow', 'div', 'double', 'drop', 'dual', 'each', 'else',
            'elseif', 'enclosed', 'escaped', 'exists', 'exit', 'explain',
            'false', 'fetch', 'float', 'float4', 'float8', 'for', 'force',
            'foreign', 'from', 'fulltext', 'generated', 'get', 'grant', 'group',
            'having', 'high_priority', 'hour_microsecond', 'hour_minute',
            'hour_second', 'if', 'ignore', 'in', 'index', 'infile', 'inner',
            'inout', 'insensitive', 'insert', 'int', 'int1', 'int2', 'int3',
            'int4', 'int8', 'integer', 'interval', 'into', 'io_after_gtids',
            'io_before_gtids', 'is', 'iterate', 'join', 'key', 'keys', 'kill',
            'leading', 'leave', 'left', 'like', 'limit', 'linear', 'lines',
            'load', 'localtime', 'localtimestamp', 'lock', 'long', 'longblob',
            'longtext', 'loop', 'low_priority', 'master_bind',
            'master_ssl_verify_server_cert', 'match', 'maxvalue', 'mediumblob',
            'mediumint', 'mediumtext', 'middleint', 'minute_microsecond',
            'minute_second', 'mod', 'modifies', 'natural', 'no_write_to_binlog',
            'not', 'null', 'numeric', 'on', 'optimize', 'option', 'optionally',
            'or', 'order', 'out', 'outer', 'outfile', 'partition', 'precision',
            'primary', 'procedure', 'purge', 'range', 'read', 'reads',
            'read_write', 'real', 'references', 'regexp', 'release', 'rename',
            'repeat', 'replace', 'require', 'resignal', 'restrict', 'return',
            'revoke', 'right', 'rlike', 'schema', 'schemas', 'second_microsecond',
            'select', 'sensitive', 'separator', 'set', 'show', 'signal',
            'smallint', 'spatial', 'specific', 'sql', 'sqlexception', 'sqlstate',
            'sqlwarning', 'sql_big_result', 'sql_calc_found_rows',
            'sql_small_result', 'ssl', 'starting', 'stored', 'straight_join',
            'table', 'terminated', 'then', 'tinyblob', 'tinyint', 'tinytext',
            'to', 'trailing', 'trigger', 'true', 'undo', 'union', 'unique',
            'unlock', 'unsigned', 'update', 'usage', 'use', 'using', 'utc_date',
            'utc_time', 'utc_timestamp', 'values', 'varbinary', 'varchar',
            'varcharacter', 'varying', 'virtual', 'when', 'where', 'while',
            'with', 'write', 'xor', 'year_month', 'zerofill',

            // --- Added as reserved in MySQL 8.0 (window functions, json table, CTEs) ---
            // https://dev.mysql.com/doc/refman/8.0/en/keywords.html
            // Not all of these are reserved in MariaDB — that's why this set
            // is a union: an identifier should be backticked if reserved in
            // EITHER engine, since OpenEMR's CI matrix exercises both.
            'array', 'cume_dist', 'dense_rank', 'empty', 'first_value',
            'grouping', 'groups', 'json_table', 'lag', 'last_value', 'lateral',
            'lead', 'nth_value', 'ntile', 'of', 'over', 'percent_rank', 'rank',
            'recursive', 'row', 'row_number', 'rows', 'system', 'window',

            // --- Added as reserved in MySQL 8.0.31 (set operations) ---
            'except', 'intersect',

            // --- MariaDB-specific additions not already covered above ---
            // https://mariadb.com/kb/en/reserved-words/
            'rows_examined', 'slow', 'page_checksum', 'returning',
        ];

        $flipped = [];
        foreach ($words as $word) {
            $flipped[$word] = true;
        }
        $this->words = $flipped;
    }

    /**
     * Case-insensitive reserved-word check.
     */
    public function isReserved(string $word): bool
    {
        return isset($this->words[strtolower($word)]);
    }
}

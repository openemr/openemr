<?php

/**
 * Tests for SqlReservedWordRule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\Sql;

use OpenEMR\PHPStan\Rules\Sql\ReservedWordRegistry;
use OpenEMR\PHPStan\Rules\Sql\SchemaColumnRegistry;
use OpenEMR\PHPStan\Rules\Sql\SqlReservedWordRule;
use OpenEMR\PHPStan\Rules\Sql\SqlSinkResolver;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SqlReservedWordRule>
 */
final class SqlReservedWordRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SqlReservedWordRule(
            new ReservedWordRegistry(),
            // Empty string for cachePath disables caching so tests stay
            // hermetic across runs.
            new SchemaColumnRegistry(__DIR__ . '/fixtures/test-schema.sql', ''),
            new SqlSinkResolver(),
        );
    }

    public function testFlagsRankInOrderBy(): void
    {
        $this->analyse(
            [__DIR__ . '/data/unbacktick_rank_in_order_by.php'],
            [[$this->expectedMessage('rank'), 4]],
        );
    }

    public function testIgnoresBacktickedRank(): void
    {
        $this->analyse(
            [__DIR__ . '/data/backtick_rank_in_order_by.php'],
            [],
        );
    }

    public function testSetPositionNotYetCaught(): void
    {
        // v1 of the rule only inspects ORDER BY / GROUP BY / PARTITION BY
        // positions. UPDATE ... SET col = ... is a known gap, captured here
        // to make the limit explicit.
        $this->analyse(
            [__DIR__ . '/data/set_position_not_yet_caught.php'],
            [],
        );
    }

    public function testFlagsReservedWordInGroupBy(): void
    {
        $this->analyse(
            [__DIR__ . '/data/group_by_reserved.php'],
            [[$this->expectedMessage('groups'), 4]],
        );
    }

    public function testIgnoresReservedWordsInKeywordPositions(): void
    {
        // INTERVAL inside DATE_ADD(...) and TABLE in CREATE TABLE are SQL
        // keywords, not identifier references. The position-aware scope
        // (only ORDER BY / GROUP BY / PARTITION BY) keeps these silent.
        $this->analyse(
            [__DIR__ . '/data/non_identifier_keyword_positions.php'],
            [],
        );
    }

    public function testIgnoresNonSinkFunctions(): void
    {
        $this->analyse(
            [__DIR__ . '/data/non_sink_function.php'],
            [],
        );
    }

    public function testIgnoresDynamicSqlSilently(): void
    {
        $this->analyse(
            [__DIR__ . '/data/dynamic_sql.php'],
            [],
        );
    }

    public function testIgnoresReservedWordsThatAreNotSchemaIdentifiers(): void
    {
        $this->analyse(
            [__DIR__ . '/data/reserved_not_column.php'],
            [],
        );
    }

    public function testIgnoresReservedWordsInsideStringLiterals(): void
    {
        $this->analyse(
            [__DIR__ . '/data/string_literal_value.php'],
            [],
        );
    }

    public function testFlagsConcatenatedConstantSql(): void
    {
        $this->analyse(
            [__DIR__ . '/data/concat_chain.php'],
            [[$this->expectedMessage('rank'), 8]],
        );
    }

    public function testFlagsOnlyTheOrderByOccurrence(): void
    {
        // `rank` appears twice (SELECT + ORDER BY) and `groups` once (FROM).
        // v1 only inspects ORDER BY position, so we expect exactly one
        // flag for the trailing rank.
        $this->analyse(
            [__DIR__ . '/data/multiple_offenders.php'],
            [[$this->expectedMessage('rank'), 6]],
        );
    }

    public function testFlagsQueryUtilsStaticSink(): void
    {
        $this->analyse(
            [__DIR__ . '/data/queryutils_static_sink.php'],
            [[$this->expectedMessage('rank'), 22]],
        );
    }

    private function expectedMessage(string $identifier): string
    {
        return sprintf(
            'SQL identifier `%s` is reserved in MySQL 8+ or MariaDB. '
            . 'Backtick it (`%s`) or rename the column — bare use fails to parse on those engines.',
            $identifier,
            $identifier,
        );
    }
}

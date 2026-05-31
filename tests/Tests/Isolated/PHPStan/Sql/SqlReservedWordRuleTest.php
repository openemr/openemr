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

    public function testFlagsRankInUpdateSet(): void
    {
        $this->analyse(
            [__DIR__ . '/data/update_set_position.php'],
            [[$this->expectedMessage('rank'), 4]],
        );
    }

    public function testFlagsRankInWhere(): void
    {
        $this->analyse(
            [__DIR__ . '/data/where_position.php'],
            [[$this->expectedMessage('rank'), 4]],
        );
    }

    public function testIgnoresQualifiedReferences(): void
    {
        // MySQL accepts reserved words as identifiers after a `.` without
        // quoting. Qualified references in every position (SELECT, WHERE,
        // ORDER BY, UPDATE SET, JOIN ON) must be silent.
        $this->analyse(
            [__DIR__ . '/data/qualified_references.php'],
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
        // keywords, not identifier references. Parser-driven detection
        // distinguishes them positionally and keeps the rule silent.
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

    public function testReportsEachUniqueOffenderOncePerCall(): void
    {
        // `rank` is referenced twice and `groups` once. Each unique name
        // should be reported exactly once.
        $this->analyse(
            [__DIR__ . '/data/multiple_offenders.php'],
            [
                [$this->expectedMessage('groups'), 6],
                [$this->expectedMessage('rank'), 6],
            ],
        );
    }

    public function testFlagsQueryUtilsStaticSink(): void
    {
        $this->analyse(
            [__DIR__ . '/data/queryutils_static_sink.php'],
            [[$this->expectedMessage('rank'), 22]],
        );
    }

    public function testInsertColumnsNotYetCaught(): void
    {
        // phpmyadmin/sql-parser strips backticks from INSERT column lists
        // before exposing them on IntoKeyword. Until the rule learns to
        // re-derive backtick state from the token stream for that case,
        // INSERT column lists are silently uncovered.
        $this->analyse(
            [__DIR__ . '/data/insert_columns_not_yet_caught.php'],
            [],
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

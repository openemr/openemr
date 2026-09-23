<?php

/**
 * Runs the MISSING modifier's SQL against the database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Services\Search;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Search\SearchFieldStatementResolver;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The isolated tests pin the SQL text of the MISSING modifier; this one runs it. The difference
 * matters on MySQL 8, where `CAST(col AS CHAR)` of a binary uuid that is not valid UTF-8 is cut
 * at the first invalid byte (to '' when that is the first byte) under the sql_mode OpenEMR sets,
 * or NULL in strict mode, so the uuid looked missing. MariaDB returns the bytes, so the same SQL
 * worked there.
 *
 * One patient row is seeded with a binary uuid that is never valid UTF-8 (first byte 0xFF), a
 * `date`, no `deceased_date`, and a `mname` made only of spaces.
 */
class MissingModifierDatabaseTest extends TestCase
{
    private ?int $pid = null;

    protected function setUp(): void
    {
        $nextPid = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($nextPid);
        $this->pid = (int) $nextPid;
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, mname, `date`, deceased_date) VALUES (?, ?, ?, ?, '   ', ?, NULL)",
            [$this->pid, "\xFF" . random_bytes(15), 'test-fixture-missing', 'test-fixture-missing', '2024-01-02 03:04:05']
        );
    }

    /**
     * Remove the seeded patient.
     */
    protected function tearDown(): void
    {
        if ($this->pid !== null) {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$this->pid]);
            $this->pid = null;
        }
    }

    /**
     * The MISSING fragment selects the seeded row exactly when the column is (not) missing.
     *
     * @param string $column patient_data column
     * @param bool $missing true for `:missing=true`, false for `:missing=false`
     * @param int $expected rows the filter should keep
     */
    #[DataProvider('missingProvider')]
    public function testMissingModifierMatchesTheSeededRow(string $column, bool $missing, int $expected): void
    {
        $field = new TokenSearchField($column, [new TokenSearchValue($missing)]);
        $field->setModifier(SearchModifier::MISSING);
        $fragment = SearchFieldStatementResolver::resolveTokenField($field);

        $count = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM patient_data WHERE pid = ? AND " . $fragment->getFragment(),
            'n',
            array_merge([$this->pid], $fragment->getBoundValues())
        );
        $this->assertIsNumeric($count);
        $this->assertSame($expected, (int) $count);
    }

    /**
     * On every column the CHAR cast can read, the filter keeps exactly the rows the plain CHAR
     * comparison keeps: whatever the collation does with a value made only of spaces, the
     * result is the one it was before the null-safe change.
     *
     * @param string $column patient_data column
     * @param bool $missing true for `:missing=true`, false for `:missing=false`
     */
    #[DataProvider('textColumnProvider')]
    public function testReadableColumnsMatchTheCharComparison(string $column, bool $missing): void
    {
        $field = new TokenSearchField($column, [new TokenSearchValue($missing)]);
        $field->setModifier(SearchModifier::MISSING);
        $fragment = SearchFieldStatementResolver::resolveTokenField($field);
        $charComparison = $missing
            ? "($column IS NULL OR CAST($column AS CHAR) = '')"
            : "($column IS NOT NULL AND CAST($column AS CHAR) != '')";

        $expected = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM patient_data WHERE pid = ? AND " . $charComparison,
            'n',
            [$this->pid]
        );
        $actual = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM patient_data WHERE pid = ? AND " . $fragment->getFragment(),
            'n',
            array_merge([$this->pid], $fragment->getBoundValues())
        );
        $this->assertIsNumeric($expected);
        $this->assertIsNumeric($actual);
        $this->assertSame((int) $expected, (int) $actual);
    }

    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function textColumnProvider(): array
    {
        return [
            'space-only text, missing=false' => ['mname', false],
            'space-only text, missing=true' => ['mname', true],
            'text, missing=false' => ['fname', false],
            'text, missing=true' => ['fname', true],
            'datetime, missing=false' => ['date', false],
            'null datetime, missing=true' => ['deceased_date', true],
        ];
    }

    /**
     * @return array<string, array{string, bool, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function missingProvider(): array
    {
        return [
            'binary uuid present, missing=false' => ['uuid', false, 1],
            'binary uuid present, missing=true' => ['uuid', true, 0],
            'datetime present, missing=false' => ['date', false, 1],
            'datetime present, missing=true' => ['date', true, 0],
            'datetime null, missing=false' => ['deceased_date', false, 0],
            'datetime null, missing=true' => ['deceased_date', true, 1],
        ];
    }
}

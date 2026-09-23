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
 * matters on MySQL 8, where `CAST(col AS CHAR)` of a binary uuid that is not valid UTF-8 is NULL,
 * so a "not missing" filter on a uuid column matched no row at all. MariaDB returns the bytes,
 * so the same SQL worked there.
 *
 * One patient row is seeded with a binary uuid that is never valid UTF-8 (first byte 0xFF), a
 * `date` and no `deceased_date`.
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
            "INSERT INTO patient_data (pid, uuid, fname, lname, `date`, deceased_date) VALUES (?, ?, ?, ?, ?, NULL)",
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

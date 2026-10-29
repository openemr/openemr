<?php

/**
 * Runs the EXACT modifier's SQL against the database: same rows as BINARY alone, and an index.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Services\Search;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Search\SearchFieldStatementResolver;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * EXACT was `BINARY col = ?`, which keeps the match exact but stops the database from using the
 * column's index. It is now `(col = ? AND BINARY col = ?)`. The plain comparison is looser (the
 * collation folds case, pads trailing spaces and folds accents) and BINARY then keeps only the exact
 * match, so the result should be the rows BINARY alone keeps. These tests check that over the values
 * where a collation and a byte comparison disagree, on a text and an integer column, and that the
 * new form lets the index answer.
 *
 * Four patients are seeded whose last names differ only in case, a trailing space and an accent.
 */
class ExactModifierDatabaseTest extends TestCase
{
    private const LNAMES = ['Exactcase-fixture', 'exactcase-fixture', 'Exactcase-fixture ', 'Éxactcase-fixture'];

    /** @var list<int> */
    private array $pids = [];

    protected function setUp(): void
    {
        $next = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($next);
        foreach (self::LNAMES as $offset => $lname) {
            $pid = (int) $next + $offset;
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'test-fixture-exact', ?, NOW())",
                [$pid, random_bytes(16), $lname]
            );
            $this->pids[] = $pid;
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->pids as $pid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$pid]);
        }
        $this->pids = [];
    }

    /**
     * @return array<string, array{string, int}> a last name searched for, and the seeded rows BINARY matches
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function lnameProvider(): array
    {
        return [
            'as written' => ['Exactcase-fixture', 1],
            'lower case' => ['exactcase-fixture', 1],
            'upper case' => ['EXACTCASE-FIXTURE', 0],
            'one trailing space' => ['Exactcase-fixture ', 1],
            'two trailing spaces' => ['Exactcase-fixture  ', 0],
            'accented' => ['Éxactcase-fixture', 1],
            'case differs inside the name' => ['Exactcase-Fixture', 0],
        ];
    }

    #[DataProvider('lnameProvider')]
    public function testTextColumnMatchesTheRowsBinaryMatches(string $lname, int $binaryMatches): void
    {
        $this->assertSame($binaryMatches, $this->seededRows('BINARY lname = ?', [$lname]), 'BINARY alone, the control');
        $this->assertSame($binaryMatches, $this->seededRows(...$this->exactFragment('lname', $lname)));
    }

    /**
     * An integer column compared with text the way a search field binds it.
     */
    public function testIntegerColumnMatchesTheRowsBinaryMatches(): void
    {
        $pid = (string) $this->pids[0];
        foreach ([$pid => 1, '0' . $pid => 0, $pid . ' ' => 0, $pid . '.0' => 0, $pid . 'abc' => 0] as $probe => $binaryMatches) {
            $probe = (string) $probe;
            $this->assertSame($binaryMatches, $this->seededRows('BINARY pid = ?', [$probe]), "BINARY alone for '$probe'");
            $this->assertSame($binaryMatches, $this->seededRows(...$this->exactFragment('pid', $probe)), "EXACT for '$probe'");
        }
    }

    /**
     * The point of the change: the plan looks the row up through the pid key, where BINARY alone
     * read the whole table or the whole of an index.
     */
    public function testTheIndexAnswersTheSearch(): void
    {
        [$fragment, $binds] = $this->exactFragment('pid', (string) $this->pids[0]);
        [$type, $key] = $this->plan($fragment, $binds);
        $this->assertContains($type, ['const', 'eq_ref', 'ref'], 'EXACT looks the row up');
        $this->assertSame('pid', $key);
        [$type] = $this->plan('BINARY pid = ?', [(string) $this->pids[0]]);
        $this->assertContains($type, ['ALL', 'index'], 'BINARY alone reads every row');
    }

    /**
     * @return array{string, list<mixed>}
     */
    private function exactFragment(string $column, string $value): array
    {
        $fragment = SearchFieldStatementResolver::resolveStringSearchField(
            new StringSearchField($column, [$value], SearchModifier::EXACT)
        );
        return [$fragment->getFragment(), array_values($fragment->getBoundValues())];
    }

    /**
     * @param list<mixed> $binds
     */
    private function seededRows(string $where, array $binds): int
    {
        $placeholders = implode(',', array_fill(0, count($this->pids), '?'));
        $count = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM patient_data WHERE pid IN ($placeholders) AND $where",
            'n',
            array_merge($this->pids, $binds)
        );
        $this->assertIsNumeric($count);
        return (int) $count;
    }

    /**
     * The plan's access type and key for a filter on patient_data.
     *
     * @param list<mixed> $binds
     * @return array{string, ?string}
     */
    private function plan(string $where, array $binds): array
    {
        // TRADITIONAL, the table form: MySQL 9 defaults to a tree, which has no access type column.
        $plan = QueryUtils::fetchRecords("EXPLAIN FORMAT=TRADITIONAL SELECT pid FROM patient_data WHERE $where", $binds);
        $this->assertCount(1, $plan);
        $type = $plan[0]['type'] ?? null;
        $key = $plan[0]['key'] ?? null;
        $this->assertIsString($type);
        return [$type, is_string($key) ? $key : null];
    }
}

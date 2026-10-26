<?php

/**
 * E2e test: picking statements to send on EOB Posting - Search.
 *
 * A Due Pt search lists every invoice the patient owes, whether or not a
 * statement went out last week. "No Statement Since" leaves out invoices
 * sent a statement on or after the date given (invoices never sent one
 * stay), and the column headers sort the list without losing which rows
 * are ticked. Four invoices: one never sent, one sent 2026-09-01, one sent
 * on the cutoff date used (2026-10-01) and one sent 2026-10-20 (two
 * statements). Later patients get lower encounter numbers, so sorting the
 * Invoice column by patient then encounter differs from encounter alone.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class StatementListSortTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-statement-list';
    private const LAST_NAME = 'Stmtlist';
    private const LAST_STMT_COLUMN = 4;
    private const PRV_COLUMN = 9;
    private const INVOICE_COLUMN = 2;

    private int $neverSent = 0;
    private int $sentInSeptember = 0;
    private int $sentOnCutoff = 0;
    private int $sentInOctober = 0;

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testNoStatementSinceLeavesOutRecentlySentInvoices(): void
    {
        $this->insertFixtureData();
        try {
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->searchDuePt('');
            $this->assertEqualsCanonicalizing(
                [$this->neverSent, $this->sentInSeptember, $this->sentOnCutoff, $this->sentInOctober],
                $this->listedEncounters(),
                'Without the filter every invoice due from the patient is listed'
            );

            $this->searchDuePt('2026-10-01');
            $this->assertEqualsCanonicalizing(
                [$this->neverSent, $this->sentInSeptember],
                $this->listedEncounters(),
                'Invoices sent a statement on or after the date (the cutoff day included) are left out; never sent ones stay'
            );
            $this->assertSame(
                '2026-10-01',
                $this->client->executeScript('return document.getElementById("form_no_stmt_since").value;'),
                'The date stays in the search form'
            );
        } finally {
            try {
                if (isset($this->client)) {
                    $this->client->quit();
                }
            } finally {
                $this->cleanUpFixtureData();
            }
        }
    }

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testHeadersSortTheRowsAndKeepTheirTicks(): void
    {
        $this->insertFixtureData();
        try {
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->searchDuePt('');

            $this->client->executeScript(
                'document.querySelector("#invoice-list td[data-encounter=\'" + arguments[0] + "\']")'
                . '.closest("tr").querySelector("input[type=checkbox]").checked = false;',
                [$this->sentInSeptember]
            );

            $this->clickHeader(self::LAST_STMT_COLUMN);
            $this->assertSame(
                [$this->neverSent, $this->sentInSeptember, $this->sentOnCutoff, $this->sentInOctober],
                $this->listedEncounters(),
                'Last Stmt ascending: never sent first, then oldest statement'
            );
            $this->assertSame('ascending', $this->ariaSort(self::LAST_STMT_COLUMN));

            $this->clickHeader(self::LAST_STMT_COLUMN);
            $this->assertSame(
                [$this->sentInOctober, $this->sentOnCutoff, $this->sentInSeptember, $this->neverSent],
                $this->listedEncounters(),
                'A second click sorts descending'
            );
            $this->assertSame('descending', $this->ariaSort(self::LAST_STMT_COLUMN));

            $this->clickHeader(self::PRV_COLUMN);
            $this->assertSame(
                [$this->neverSent, $this->sentOnCutoff, $this->sentInSeptember, $this->sentInOctober],
                $this->listedEncounters(),
                'Prv sorts by the number of statements sent; ties keep the order they had (stable sort)'
            );
            $this->assertNull($this->ariaSort(self::LAST_STMT_COLUMN), 'Only the sorted column is marked');

            $ticks = $this->client->executeScript(
                'return Array.from(document.querySelectorAll("#invoice-list tbody tr"))'
                . '.map(r => r.querySelector("input[type=checkbox]").checked);'
            );
            $this->assertSame([true, true, false, true], $ticks, 'Sorting keeps which rows are ticked');

            // Patients were added in this order, with encounter numbers in the reverse order.
            $this->clickHeader(self::INVOICE_COLUMN);
            $this->assertSame(
                [$this->neverSent, $this->sentInSeptember, $this->sentOnCutoff, $this->sentInOctober],
                $this->listedEncounters(),
                'Invoice sorts by patient, then encounter, as it is shown'
            );
        } finally {
            try {
                if (isset($this->client)) {
                    $this->client->quit();
                }
            } finally {
                $this->cleanUpFixtureData();
            }
        }
    }

    private function searchDuePt(string $noStatementSince): void
    {
        $this->client->request('GET', '/interface/billing/sl_eob_search.php');
        $this->client->waitFor('#formSearch', 30);
        $this->client->executeScript(
            'document.getElementById("form_category").value = "Due Pt";'
            . ' document.getElementById("form_name").value = arguments[0];'
            . ' document.getElementById("form_no_stmt_since").value = arguments[1];'
            . ' document.getElementById("formSearch").requestSubmit(document.getElementById("btn-inv-search"));',
            [self::LAST_NAME, $noStatementSince]
        );
        $this->client->waitFor('#invoice-list', 30);
    }

    /**
     * @return list<int>
     */
    private function listedEncounters(): array
    {
        $values = $this->client->executeScript(
            'return Array.from(document.querySelectorAll("#invoice-list tbody tr")).map(r => r.cells[2].dataset.encounter);'
        );
        $this->assertIsArray($values);
        return array_values(array_map($this->toInt(...), $values));
    }

    private function clickHeader(int $column): void
    {
        $this->client->executeScript(
            'document.querySelectorAll("#invoice-list thead th")[arguments[0]].querySelector("button").click();',
            [$column]
        );
    }

    private function ariaSort(int $column): ?string
    {
        $value = $this->client->executeScript(
            'return document.querySelectorAll("#invoice-list thead th")[arguments[0]].getAttribute("aria-sort");',
            [$column]
        );
        return is_string($value) ? $value : null;
    }

    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        $pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        $invoices = [
            ['Never', 0, null],
            ['September', 1, '2026-09-01'],
            ['Cutoff', 1, '2026-10-01'],
            ['October', 2, '2026-10-20'],
        ];
        // Encounter numbers in the reverse of the patients' order.
        $encounterIds = [];
        foreach ($invoices as $ignored) {
            $encounterIds[] = $this->toInt(QueryUtils::generateId());
        }
        $encounterIds = array_reverse($encounterIds);
        $encounters = [];
        foreach ($invoices as $i => [$first, $statements, $lastStatement]) {
            QueryUtils::sqlInsert(
                'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
                [$pid, self::FIXTURE_MARKER, $first, self::LAST_NAME, '1970-01-01', 'Female']
            );
            $encounter = $encounterIds[$i];
            QueryUtils::sqlInsert(
                'INSERT INTO form_encounter (date, reason, pid, encounter, provider_id, stmt_count, last_stmt_date)'
                . ' VALUES (NOW(), ?, ?, ?, 1, ?, ?)',
                [self::FIXTURE_MARKER, $pid, $encounter, $statements, $lastStatement]
            );
            QueryUtils::sqlInsert(
                "INSERT INTO billing (date, code_type, code, pid, provider_id, encounter, code_text, billed, activity, units, fee)"
                . " VALUES (NOW(), 'CPT4', '99213', ?, 1, ?, ?, 1, 1, 1, '75.00')",
                [$pid, $encounter, self::FIXTURE_MARKER]
            );
            $encounters[] = $encounter;
            ++$pid;
        }
        [$this->neverSent, $this->sentInSeptember, $this->sentOnCutoff, $this->sentInOctober] = $encounters;
    }

    private function cleanUpFixtureData(): void
    {
        $pids = QueryUtils::fetchTableColumn('SELECT pid FROM patient_data WHERE pubpid = ?', 'pid', [self::FIXTURE_MARKER]);
        foreach ($pids as $pid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [$pid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE reason = ?', [self::FIXTURE_MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pubpid = ?', [self::FIXTURE_MARKER]);
    }

    private function toInt(mixed $value): int
    {
        if (!is_numeric($value)) {
            $this->fail('Expected a number, got ' . get_debug_type($value));
        }
        return (int) $value;
    }
}

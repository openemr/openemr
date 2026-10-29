<?php

/**
 * E2e regression test for the Patient Finder.
 *
 * Drives the real (MVC) finder end-to-end over HTTP and asserts that:
 *   - the finder page renders its table with column headers;
 *   - the AJAX endpoint returns well-formed DataTables JSON;
 *   - a global search narrows the result set to a seeded patient, whose row
 *     carries the expected row id and name.
 *
 * This covers the finder itself. The module extension points (column
 * registration, row enrichment, and the SQL exclusion filter) are a PHP
 * contract and are exercised in-process by the isolated unit tests
 * (tests/Tests/Isolated/PatientFinder) — they cannot be registered from a
 * browser-driven test, which runs in a separate process from the web request.
 *
 * Self-seeding: a patient is inserted (tagged with a pubpid marker) and removed
 * in teardown, so the test leaves no state behind.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
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

class PatientFinderTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-finder';
    private const SEEDED_LASTNAME = 'Zzfinderseed';

    private int $seededPid = 0;

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testFinderSearchesAndReturnsSeededPatient(): void
    {
        $this->seedPatient();
        try {
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);

            // (1) The finder page renders its table with column headers.
            $this->client->request('GET', '/interface/main/finder/dynamic_finder.php');
            $this->client->waitFor('#pt_table', 20);
            $headers = (string) $this->client->executeScript(
                'return Array.from(document.querySelectorAll("#pt_table thead th"))'
                . '.map(function (th) { return th.textContent.trim(); }).join("|");'
            );
            $this->assertNotSame('', $headers, 'the finder should render at least one column header');

            // (2) The AJAX endpoint returns well-formed DataTables JSON.
            $all = $this->fetchAjax('sEcho=1&iDisplayStart=0&iDisplayLength=50');
            $this->assertArrayHasKey('sEcho', $all, 'AJAX payload should echo sEcho');
            $this->assertArrayHasKey('iTotalRecords', $all, 'AJAX payload should carry iTotalRecords');
            $this->assertArrayHasKey('aaData', $all, 'AJAX payload should carry aaData');

            // (3) A global search narrows to the seeded patient, whose row carries
            //     the expected DT_RowId and name.
            $hit = $this->fetchAjax('sEcho=2&iDisplayStart=0&iDisplayLength=50&sSearch=' . self::SEEDED_LASTNAME);
            $this->assertGreaterThanOrEqual(1, (int) $hit['iTotalDisplayRecords'], 'search should find the seeded patient');
            $cells = $this->cellsForPid($hit, $this->seededPid);
            $this->assertNotEmpty($cells, 'the seeded patient row should be returned');
            $this->assertTrue(
                $this->anyCellContains($cells, self::SEEDED_LASTNAME),
                'the seeded patient name should appear in the row'
            );
        } finally {
            $this->cleanUpPatients();
            if (isset($this->client)) {
                $this->client->quit();
            }
        }
    }

    /**
     * Fetch the finder AJAX endpoint in the logged-in browser and decode the JSON.
     *
     * @return array<string, mixed>
     */
    private function fetchAjax(string $query): array
    {
        $this->client->request('GET', '/interface/main/finder/dynamic_finder_ajax.php?' . $query);
        $text = (string) $this->client->executeScript('return document.body.innerText;');
        $decoded = json_decode(trim($text), true);
        $this->assertIsArray($decoded, 'finder AJAX did not return JSON: ' . substr($text, 0, 300));
        return $decoded;
    }

    /**
     * The string cell values (excluding DT_RowId) of the row for the given pid.
     *
     * @param array<string, mixed> $payload
     * @return array<int, string>
     */
    private function cellsForPid(array $payload, int $pid): array
    {
        foreach (($payload['aaData'] ?? []) as $row) {
            if (($row['DT_RowId'] ?? '') === 'pid_' . $pid) {
                $cells = [];
                foreach ($row as $key => $value) {
                    if ($key !== 'DT_RowId') {
                        $cells[] = (string) $value;
                    }
                }
                return $cells;
            }
        }
        return [];
    }

    /**
     * @param array<int, string> $cells
     */
    private function anyCellContains(array $cells, string $needle): bool
    {
        foreach ($cells as $cell) {
            if (str_contains($cell, $needle)) {
                return true;
            }
        }
        return false;
    }

    private function seedPatient(): void
    {
        $this->cleanUpPatients();
        $pid = (int) QueryUtils::fetchSingleValue(
            'SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data',
            'pid'
        );
        $this->seededPid = $pid;
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date)'
            . ' VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$pid, self::FIXTURE_MARKER, 'Zzfinderseedf', self::SEEDED_LASTNAME, '1980-01-01', 'Male']
        );
    }

    private function cleanUpPatients(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM patient_data WHERE pubpid = ?',
            [self::FIXTURE_MARKER]
        );
    }
}

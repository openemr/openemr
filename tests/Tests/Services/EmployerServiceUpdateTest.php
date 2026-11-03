<?php

/**
 * EmployerService::updateEmployerData() for a patient with no employer_data row.
 *
 * Editing Demographics merges the submitted employer fields into the patient's
 * most recent employer_data row. A patient can have no such row yet (imported,
 * or created outside Add New Patient), and the merge then iterated over `false`,
 * kept nothing, and inserted nothing: every value the user entered was lost.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\EmployerService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmployerServiceUpdateTest extends TestCase
{
    private const NAME_PREFIX = 'test-fixture-EmployerUpdate ';

    private EmployerService $service;
    private FixtureManager $fixtureManager;
    private int $pid;

    protected function setUp(): void
    {
        $this->service = new EmployerService();
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();

        $pid = QueryUtils::fetchSingleValue(
            "SELECT `pid` FROM `patient_data` WHERE `pubpid` LIKE ? ORDER BY `pid` LIMIT 1",
            'pid',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . '%']
        );
        if (!is_numeric($pid)) {
            self::fail('patient fixture not found');
        }
        $this->pid = (int) $pid;
        // The fixture patient stands in for an imported one: no employer history.
        QueryUtils::sqlStatementThrowException("DELETE FROM `employer_data` WHERE `pid` = ?", [$this->pid]);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `employer_data` WHERE `pid` = ?", [$this->pid]);
        $this->fixtureManager->removePatientFixtures();
    }

    /** @return list<string> employer names for the patient, oldest first */
    private function names(): array
    {
        $names = [];
        foreach (QueryUtils::fetchRecords("SELECT `name` FROM `employer_data` WHERE `pid` = ? ORDER BY `id`", [$this->pid]) as $row) {
            $names[] = is_string($row['name'] ?? null) ? $row['name'] : '';
        }
        return $names;
    }

    #[Test]
    public function savesTheFirstRowWhenThePatientHasNone(): void
    {
        self::assertSame([], $this->names(), 'precondition: no employer row');

        $this->service->updateEmployerData($this->pid, ['name' => self::NAME_PREFIX . 'Acme', 'city' => 'Testville']);

        self::assertSame([self::NAME_PREFIX . 'Acme'], $this->names());
        $row = QueryUtils::querySingleRow("SELECT `city` FROM `employer_data` WHERE `pid` = ?", [$this->pid]);
        self::assertIsArray($row);
        self::assertSame('Testville', $row['city']);
    }

    #[Test]
    public function addsAHistoryRowWhenOneExists(): void
    {
        $this->service->updateEmployerData($this->pid, ['name' => self::NAME_PREFIX . 'First'], true);
        $this->service->updateEmployerData($this->pid, ['name' => self::NAME_PREFIX . 'Second']);

        self::assertSame([self::NAME_PREFIX . 'First', self::NAME_PREFIX . 'Second'], $this->names());
    }
}

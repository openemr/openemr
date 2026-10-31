<?php

/**
 * InsuranceService::doesInsuranceTypeHaveEntry().
 *
 * Without a type it is meant to say whether the patient has any coverage row.
 * It used to pass the pid to getOne(), which takes a coverage uuid and always
 * returns a ProcessingResult, and compare that with false, so it answered yes
 * for every patient.
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
use OpenEMR\Services\InsuranceService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InsuranceServiceTypeEntryTest extends TestCase
{
    private InsuranceService $service;
    private FixtureManager $fixtureManager;
    private int $pid;

    protected function setUp(): void
    {
        $this->service = new InsuranceService();
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
        QueryUtils::sqlStatementThrowException("DELETE FROM `insurance_data` WHERE `pid` = ?", [$this->pid]);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `insurance_data` WHERE `pid` = ?", [$this->pid]);
        $this->fixtureManager->removePatientFixtures();
    }

    private function addCoverage(string $type): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `insurance_data` (`pid`, `type`, `date`, `plan_name`) VALUES (?, ?, '2026-01-01', 'test-fixture')",
            [$this->pid, $type]
        );
    }

    #[Test]
    public function aPatientWithNoCoverageHasNoEntry(): void
    {
        self::assertFalse($this->service->doesInsuranceTypeHaveEntry($this->pid));
        self::assertNull($this->service->doesInsuranceTypeHaveEntry($this->pid, 'primary'));
    }

    #[Test]
    public function anyCoverageRowCountsWhenNoTypeIsGiven(): void
    {
        $this->addCoverage('secondary');

        self::assertTrue($this->service->doesInsuranceTypeHaveEntry($this->pid));
        self::assertNull($this->service->doesInsuranceTypeHaveEntry($this->pid, 'primary'));
        self::assertNotNull($this->service->doesInsuranceTypeHaveEntry($this->pid, 'secondary'));
    }
}

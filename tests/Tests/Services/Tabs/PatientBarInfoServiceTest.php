<?php

/**
 * Database test: the patient bar's info line reads the external ID, Sex, date of birth
 * and age (or age at death) of the right patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Tabs;

use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PatientService;
use OpenEMR\Tabs\PatientBarInfoService;
use PHPUnit\Framework\TestCase;

class PatientBarInfoServiceTest extends TestCase
{
    /** Synthetic ids, clear of the demo data. */
    private const ALIVE_PID = 99990061;
    private const DECEASED_PID = 99990062;
    private const FUTURE_DEATH_PID = 99990063;
    private const BARE_PID = 99990064;
    private const MISSING_PID = 99990069;

    private const GLOBALS = ['disable_translation' => true, 'date_display_format' => '0', 'age_display_format' => '0'];

    /** @var array<string, mixed> */
    private array $savedGlobals = [];

    private PatientBarInfoService $service;

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        foreach (self::GLOBALS as $name => $value) {
            $this->savedGlobals[$name] = $globals->get($name);
            $globals->set($name, $value);
        }
        $this->cleanUp();
        $this->insertPatient(self::ALIVE_PID, 'B-61', '1950-01-01', 'Female', null);
        $this->insertPatient(self::DECEASED_PID, 'B-62', '1940-03-04', 'Male', '2021-06-01 10:00:00');
        $this->insertPatient(self::FUTURE_DEATH_PID, 'B-63', '1940-03-04', 'Male', '2030-01-01 00:00:00');
        $this->insertPatient(self::BARE_PID, '', null, '', null);
        $this->service = new PatientBarInfoService(
            new PatientService(),
            new FrozenClock(new \DateTimeImmutable('2026-11-23 12:00:00')),
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        foreach ($this->savedGlobals as $name => $value) {
            OEGlobalsBag::getInstance()->set($name, $value);
        }
    }

    public function testALivingPatientShowsTheirAge(): void
    {
        $info = $this->service->forPid(self::ALIVE_PID);

        $this->assertNotNull($info);
        $age = (new PatientService())->getPatientAgeDisplay('1950-01-01');
        $this->assertSame(['PID ' . self::ALIVE_PID, 'Female', 'DOB 1950-01-01', $age . ' years'], $info->parts());
        $this->assertFalse($info->deceased);
    }

    public function testADeceasedPatientShowsTheirAgeAtDeath(): void
    {
        $info = $this->service->forPid(self::DECEASED_PID);

        $this->assertNotNull($info);
        $this->assertSame(['PID ' . self::DECEASED_PID, 'Male', 'DOB 1940-03-04', 'Age at death 81 years'], $info->parts());
    }

    public function testADeathDateStillToComeIsNotTreatedAsDeceased(): void
    {
        $info = $this->service->forPid(self::FUTURE_DEATH_PID);

        $this->assertNotNull($info);
        $this->assertFalse($info->deceased);
    }

    public function testEmptyFieldsAreLeftOut(): void
    {
        $info = $this->service->forPid(self::BARE_PID);

        $this->assertNotNull($info);
        $this->assertSame(['PID ' . self::BARE_PID], $info->parts());
    }

    public function testAnUnknownPatientHasNoInfo(): void
    {
        $this->assertNull($this->service->forPid(self::MISSING_PID));
    }

    private function insertPatient(int $pid, string $pubpid, ?string $dob, string $sex, ?string $deceasedDate): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, deceased_date, date) VALUES (?, ?, 'PAT', 'BARTEST', ?, ?, ?, NOW())",
            [$pid, $pubpid, $dob, $sex, $deceasedDate]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM patient_data WHERE pid IN (?, ?, ?, ?)',
            [self::ALIVE_PID, self::DECEASED_PID, self::FUTURE_DEATH_PID, self::BARE_PID]
        );
    }
}

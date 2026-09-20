<?php

/**
 * PatientValidationServiceTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientValidationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PatientValidationServiceTest extends TestCase
{
    private const MODULE_NAME = 'Patientvalidation';

    // Fixture rows are tagged through mod_directory so tearDown() finds exactly them again.
    private const FIXTURE_DIRECTORY_PREFIX = 'patientvalidation-test-';

    /**
     * mod_active of the Patientvalidation rows that existed before the test, keyed by mod_id.
     *
     * @var array<int, int>
     */
    private array $originalActive = [];

    /**
     * Starts every test from a known state: no active Patientvalidation row. Rows that already
     * exist are deactivated, not deleted, and restored in tearDown().
     */
    protected function setUp(): void
    {
        $this->deleteFixtureRows();
        $this->originalActive = [];
        $rows = QueryUtils::fetchRecords(
            "SELECT `mod_id`, `mod_active` FROM `modules` WHERE `mod_name` = ?",
            [self::MODULE_NAME]
        );
        foreach ($rows as $row) {
            $modId = $row['mod_id'] ?? null;
            $active = $row['mod_active'] ?? null;
            $this->assertIsNumeric($modId);
            $this->assertIsNumeric($active);
            $this->originalActive[(int) $modId] = (int) $active;
        }
        QueryUtils::sqlStatementThrowException(
            "UPDATE `modules` SET `mod_active` = 0 WHERE `mod_name` = ?",
            [self::MODULE_NAME]
        );
    }

    /**
     * Removes the fixture rows and gives the pre-existing rows their original mod_active back.
     */
    protected function tearDown(): void
    {
        $this->deleteFixtureRows();
        foreach ($this->originalActive as $modId => $active) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE `modules` SET `mod_active` = ? WHERE `mod_id` = ? AND `mod_name` = ?",
                [$active, $modId, self::MODULE_NAME]
            );
        }
    }

    /**
     * Deletes every modules row written by this test.
     */
    private function deleteFixtureRows(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `modules` WHERE `mod_directory` LIKE ?",
            [self::FIXTURE_DIRECTORY_PREFIX . '%']
        );
    }

    /**
     * Writes a modules row directly, bypassing the code under test.
     */
    private function insertModuleRow(string $modName, int $modActive, string $tag): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `modules` (`mod_name`, `mod_directory`, `mod_active`, `directory`, `date`, `sql_version`, `acl_version`)"
            . " VALUES (?, ?, ?, '', NOW(), '', '')",
            [$modName, self::FIXTURE_DIRECTORY_PREFIX . $tag, $modActive]
        );
    }

    /**
     * Reads the number of active Patientvalidation rows directly, bypassing the code under test.
     */
    private function countActiveRows(): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT COUNT(*) AS c FROM `modules` WHERE `mod_name` = ? AND `mod_active` = 1",
            [self::MODULE_NAME]
        );
        $this->assertIsArray($row);
        $count = $row['c'] ?? null;
        $this->assertIsNumeric($count);
        return (int) $count;
    }

    /**
     * Characterization of library/patientvalidation.inc.php: the hook is reported inactive when
     * no active Patientvalidation row exists.
     */
    #[Test]
    public function testHookIsInactiveWhenNoActiveRowExists(): void
    {
        $this->assertSame(0, $this->countActiveRows());
        $this->assertFalse(checkIfPatientValidationHookIsActive());
    }

    /**
     * Characterization of library/patientvalidation.inc.php: an active Patientvalidation row
     * makes the hook active.
     */
    #[Test]
    public function testHookIsActiveWhenAnActiveRowExists(): void
    {
        $this->insertModuleRow(self::MODULE_NAME, 1, 'active');

        $this->assertTrue(checkIfPatientValidationHookIsActive());
    }

    /**
     * Characterization of library/patientvalidation.inc.php: a registered but inactive
     * Patientvalidation row does not make the hook active.
     */
    #[Test]
    public function testHookIsInactiveWhenTheOnlyRowIsInactive(): void
    {
        $this->insertModuleRow(self::MODULE_NAME, 0, 'inactive');

        $this->assertFalse(checkIfPatientValidationHookIsActive());
    }

    /**
     * Characterization of library/patientvalidation.inc.php: an active module with another
     * name, even one that starts with "Patientvalidation", does not make the hook active.
     */
    #[Test]
    public function testHookIsInactiveWhenOnlyAnotherModuleIsActive(): void
    {
        $this->insertModuleRow(self::MODULE_NAME . 'Other', 1, 'other');

        $this->assertFalse(checkIfPatientValidationHookIsActive());
    }

    /**
     * Characterization of library/patientvalidation.inc.php: more than one active row still
     * reads as active (the legacy check was "any row", not "exactly one row").
     */
    #[Test]
    public function testHookIsActiveWhenSeveralActiveRowsExist(): void
    {
        $this->insertModuleRow(self::MODULE_NAME, 1, 'active-a');
        $this->insertModuleRow(self::MODULE_NAME, 1, 'active-b');

        $this->assertTrue(checkIfPatientValidationHookIsActive());
    }

    /**
     * The service method answers the same as the legacy function it replaces.
     */
    #[Test]
    public function testServiceReportsTheActiveState(): void
    {
        $this->assertFalse(PatientValidationService::checkIfPatientValidationHookIsActive());

        $this->insertModuleRow(self::MODULE_NAME, 1, 'service');

        $this->assertTrue(PatientValidationService::checkIfPatientValidationHookIsActive());
    }
}

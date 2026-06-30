<?php

/**
 * Isolated tests for the UDS cross-table reconciliation across the four patient
 * tables (3A, 3B, ZIP, 4).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Payer\UdsPayerCategory;
use OpenEMR\FQHC\Reporting\CrossTableReconciliation;
use OpenEMR\FQHC\Reporting\Table3aAgeBand;
use OpenEMR\FQHC\Reporting\Table3aPatientRecord;
use OpenEMR\FQHC\Reporting\Table3aReportBuilder;
use OpenEMR\FQHC\Reporting\Table3bPatientRecord;
use OpenEMR\FQHC\Reporting\Table3bReportBuilder;
use OpenEMR\FQHC\Reporting\Table4PatientRecord;
use OpenEMR\FQHC\Reporting\Table4ReportBuilder;
use OpenEMR\FQHC\Reporting\UdsAgeGroup;
use OpenEMR\FQHC\Reporting\UdsEthnicityCategory;
use OpenEMR\FQHC\Reporting\UdsRaceCategory;
use OpenEMR\FQHC\Reporting\UdsSex;
use OpenEMR\FQHC\Reporting\ZipCodeTablePatientRecord;
use OpenEMR\FQHC\Reporting\ZipCodeTableReportBuilder;
use OpenEMR\FQHC\Reporting\ZipResidence;
use PHPUnit\Framework\TestCase;

final class CrossTableReconciliationTest extends TestCase
{
    public function testAllTablesAgreeForTheSameThreePatients(): void
    {
        $result = (new CrossTableReconciliation())->reconcile(
            $this->table3a(3),
            $this->table3b(3),
            $this->zip([UdsPayerCategory::Medicaid, UdsPayerCategory::Medicare, UdsPayerCategory::Private]),
            $this->table4([UdsPayerCategory::Medicaid, UdsPayerCategory::Medicare, UdsPayerCategory::Private]),
        );

        self::assertTrue($result->patientTotalsAgree());
        self::assertTrue($result->zipColumnsAgreeWithTable4());
        self::assertTrue($result->isConsistent());
    }

    public function testZipMedicaidColumnFoldsMedicaidAndOtherPublicFromTable4(): void
    {
        $payers = [UdsPayerCategory::Medicaid, UdsPayerCategory::OtherPublic];
        $result = (new CrossTableReconciliation())->reconcile(
            $this->table3a(2),
            $this->table3b(2),
            $this->zip($payers),
            $this->table4($payers),
        );

        self::assertSame(2, $result->zipMedicaidChipOtherPublic);
        self::assertSame(2, $result->table4MedicaidPlusOtherPublic);
        self::assertTrue($result->isConsistent());
    }

    public function testInconsistentWhenPatientCountsDisagree(): void
    {
        $result = (new CrossTableReconciliation())->reconcile(
            $this->table3a(1),
            $this->table3b(2),
            $this->zip([UdsPayerCategory::Private, UdsPayerCategory::Private]),
            $this->table4([UdsPayerCategory::Private, UdsPayerCategory::Private]),
        );

        self::assertFalse($result->patientTotalsAgree());
        self::assertFalse($result->isConsistent());
    }

    public function testInconsistentWhenZipColumnsDisagreeWithTable4(): void
    {
        // Same patient totals, but the insurance mix differs between the tables.
        $result = (new CrossTableReconciliation())->reconcile(
            $this->table3a(2),
            $this->table3b(2),
            $this->zip([UdsPayerCategory::Private, UdsPayerCategory::Private]),
            $this->table4([UdsPayerCategory::Medicare, UdsPayerCategory::Medicare]),
        );

        self::assertTrue($result->patientTotalsAgree());
        self::assertFalse($result->zipColumnsAgreeWithTable4());
        self::assertFalse($result->isConsistent());
    }

    private function table3a(int $patients): \OpenEMR\FQHC\Reporting\Table3aReport
    {
        $records = [];
        for ($i = 0; $i < $patients; $i++) {
            $records[] = new Table3aPatientRecord(Table3aAgeBand::fromAge(30), UdsSex::Female);
        }

        return (new Table3aReportBuilder())->build($records);
    }

    private function table3b(int $patients): \OpenEMR\FQHC\Reporting\Table3bReport
    {
        $records = [];
        for ($i = 0; $i < $patients; $i++) {
            $records[] = new Table3bPatientRecord(UdsRaceCategory::White, UdsEthnicityCategory::NotHispanic, false);
        }

        return (new Table3bReportBuilder())->build($records);
    }

    /**
     * @param list<UdsPayerCategory> $payers
     */
    private function zip(array $payers): \OpenEMR\FQHC\Reporting\ZipCodeTableReport
    {
        $records = [];
        foreach ($payers as $payer) {
            $records[] = new ZipCodeTablePatientRecord(ZipResidence::ofZip('02118'), $payer);
        }

        return (new ZipCodeTableReportBuilder())->build($records);
    }

    /**
     * @param list<UdsPayerCategory> $payers
     */
    private function table4(array $payers): \OpenEMR\FQHC\Reporting\Table4Report
    {
        $records = [];
        foreach ($payers as $payer) {
            $records[] = new Table4PatientRecord(FplBand::AtOrBelow100, $payer, UdsAgeGroup::EighteenAndOver);
        }

        return (new Table4ReportBuilder())->build($records);
    }
}

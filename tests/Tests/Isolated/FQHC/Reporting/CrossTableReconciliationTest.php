<?php

/**
 * Isolated tests for the UDS cross-table patient-total reconciliation.
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
use OpenEMR\FQHC\Reporting\Table4PatientRecord;
use OpenEMR\FQHC\Reporting\Table4ReportBuilder;
use OpenEMR\FQHC\Reporting\UdsAgeGroup;
use OpenEMR\FQHC\Reporting\UdsSex;
use PHPUnit\Framework\TestCase;

final class CrossTableReconciliationTest extends TestCase
{
    public function testTotalsAgreeWhenBothTablesCoverTheSamePatients(): void
    {
        $table3a = (new Table3aReportBuilder())->build([
            new Table3aPatientRecord(Table3aAgeBand::fromAge(10), UdsSex::Male),
            new Table3aPatientRecord(Table3aAgeBand::fromAge(40), UdsSex::Female),
            new Table3aPatientRecord(Table3aAgeBand::fromAge(70), UdsSex::Male),
        ]);
        $table4 = (new Table4ReportBuilder())->build([
            new Table4PatientRecord(FplBand::AtOrBelow100, UdsPayerCategory::Medicaid, UdsAgeGroup::Under18),
            new Table4PatientRecord(FplBand::Unknown, null, UdsAgeGroup::EighteenAndOver),
            new Table4PatientRecord(FplBand::Above200, UdsPayerCategory::Medicare, UdsAgeGroup::EighteenAndOver),
        ]);

        $result = (new CrossTableReconciliation())->reconcile($table3a, $table4);

        self::assertSame(3, $result->table3aTotalPatients);
        self::assertSame(3, $result->table4IncomeTotal);
        self::assertSame(3, $result->table4InsuranceTotal);
        self::assertTrue($result->isConsistent());
    }

    public function testInconsistentWhenTablesDisagreeOnPatientCount(): void
    {
        $table3a = (new Table3aReportBuilder())->build([
            new Table3aPatientRecord(Table3aAgeBand::fromAge(10), UdsSex::Male),
        ]);
        $table4 = (new Table4ReportBuilder())->build([
            new Table4PatientRecord(FplBand::AtOrBelow100, UdsPayerCategory::Medicaid, UdsAgeGroup::Under18),
            new Table4PatientRecord(FplBand::Above200, UdsPayerCategory::Private, UdsAgeGroup::EighteenAndOver),
        ]);

        $result = (new CrossTableReconciliation())->reconcile($table3a, $table4);

        self::assertSame(1, $result->table3aTotalPatients);
        self::assertSame(2, $result->table4IncomeTotal);
        self::assertFalse($result->isConsistent());
    }
}

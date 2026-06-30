<?php

/**
 * Isolated tests for the UDS Patients by ZIP Code Table aggregator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Payer\UdsPayerCategory;
use OpenEMR\FQHC\Reporting\UdsZipInsuranceColumn;
use OpenEMR\FQHC\Reporting\ZipCodeTablePatientRecord;
use OpenEMR\FQHC\Reporting\ZipCodeTableReportBuilder;
use OpenEMR\FQHC\Reporting\ZipResidence;
use PHPUnit\Framework\TestCase;

final class ZipCodeTableReportBuilderTest extends TestCase
{
    public function testEmptyInputProducesEmptyReport(): void
    {
        $report = (new ZipCodeTableReportBuilder())->build([]);

        self::assertSame(0, $report->total());
        self::assertSame([], $report->residences());
    }

    public function testGroupsByResidenceAndInsuranceColumn(): void
    {
        $bostonA = ZipResidence::ofZip('02118');
        $bostonB = ZipResidence::ofZip('02118');
        $report = (new ZipCodeTableReportBuilder())->build([
            new ZipCodeTablePatientRecord($bostonA, UdsPayerCategory::Medicaid),
            new ZipCodeTablePatientRecord($bostonB, UdsPayerCategory::Private),
            new ZipCodeTablePatientRecord(ZipResidence::ofZip('02119'), UdsPayerCategory::Medicare),
        ]);

        self::assertSame(1, $report->count($bostonA, UdsZipInsuranceColumn::MedicaidChipOtherPublic));
        self::assertSame(1, $report->count($bostonA, UdsZipInsuranceColumn::Private));
        self::assertSame(2, $report->residenceTotal($bostonA));
        self::assertCount(2, $report->residences());
        self::assertSame(3, $report->total());
    }

    public function testMedicaidAndOtherPublicFoldIntoOneColumn(): void
    {
        $zip = ZipResidence::ofZip('10001');
        $report = (new ZipCodeTableReportBuilder())->build([
            new ZipCodeTablePatientRecord($zip, UdsPayerCategory::Medicaid),
            new ZipCodeTablePatientRecord($zip, UdsPayerCategory::OtherPublic),
        ]);

        self::assertSame(2, $report->columnTotal(UdsZipInsuranceColumn::MedicaidChipOtherPublic));
    }

    public function testUnclassifiedPayerCountsAsUninsuredColumn(): void
    {
        $zip = ZipResidence::ofZip('33101');
        $report = (new ZipCodeTableReportBuilder())->build([
            new ZipCodeTablePatientRecord($zip, null),
            new ZipCodeTablePatientRecord($zip, UdsPayerCategory::None),
        ]);

        self::assertSame(2, $report->columnTotal(UdsZipInsuranceColumn::Uninsured));
        self::assertSame(2, $report->total());
    }

    public function testUnknownResidenceIsItsOwnRow(): void
    {
        $report = (new ZipCodeTableReportBuilder())->build([
            new ZipCodeTablePatientRecord(ZipResidence::unknown(), UdsPayerCategory::Private),
            new ZipCodeTablePatientRecord(ZipResidence::ofZip('60601'), UdsPayerCategory::Private),
        ]);

        self::assertSame(1, $report->residenceTotal(ZipResidence::unknown()));
        self::assertCount(2, $report->residences());
        self::assertSame(2, $report->columnTotal(UdsZipInsuranceColumn::Private));
    }
}

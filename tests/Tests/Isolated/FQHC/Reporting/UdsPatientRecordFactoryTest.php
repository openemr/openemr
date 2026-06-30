<?php

/**
 * Isolated tests for the UDS per-patient record factory.
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
use OpenEMR\FQHC\Reporting\ReportingPatient;
use OpenEMR\FQHC\Reporting\Table3aAgeBand;
use OpenEMR\FQHC\Reporting\UdsAgeGroup;
use OpenEMR\FQHC\Reporting\UdsEthnicityCategory;
use OpenEMR\FQHC\Reporting\UdsPatientRecordFactory;
use OpenEMR\FQHC\Reporting\UdsRaceCategory;
use OpenEMR\FQHC\Reporting\UdsSex;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulation;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulationStatus;
use PHPUnit\Framework\TestCase;

final class UdsPatientRecordFactoryTest extends TestCase
{
    public function testTable3aRecordClassifiesAgeBandAndSex(): void
    {
        $record = (new UdsPatientRecordFactory())->table3a($this->patient(ageYears: 40, sexCode: 'Female'));

        self::assertNotNull($record);
        self::assertEquals(Table3aAgeBand::fromAge(40), $record->ageBand);
        self::assertSame(UdsSex::Female, $record->sex);
    }

    public function testTable3aRecordIsNullWhenAgeOrSexUnresolved(): void
    {
        $factory = new UdsPatientRecordFactory();

        self::assertNull($factory->table3a($this->patient(ageYears: null, sexCode: 'Male')));
        self::assertNull($factory->table3a($this->patient(ageYears: 30, sexCode: 'UNK')));
    }

    public function testTable3bRecordRunsAllDemographicClassifiers(): void
    {
        $record = (new UdsPatientRecordFactory())->table3b($this->patient(
            raceCode: 'chinese',
            ethnicityCode: 'hisp_or_latin',
            languageCode: 'spanish',
            interpreterNeeded: null,
        ));

        self::assertSame(UdsRaceCategory::Chinese, $record->race);
        self::assertSame(UdsEthnicityCategory::Combined, $record->ethnicity);
        self::assertTrue($record->bestServedInNonEnglishLanguage);
    }

    public function testTable4RecordClassifiesPayerAndAgeGroup(): void
    {
        $status = new SpecialPopulationStatus(SpecialPopulation::Veteran);
        $record = (new UdsPatientRecordFactory())->table4($this->patient(
            ageYears: 10,
            incomeBand: FplBand::From101To150,
            insuranceTypeCode: 3,
            specialPopulations: [$status],
        ));

        self::assertNotNull($record);
        self::assertSame(FplBand::From101To150, $record->incomeBand);
        self::assertSame(UdsPayerCategory::Medicaid, $record->payerCategory);
        self::assertSame(UdsAgeGroup::Under18, $record->ageGroup);
        self::assertSame([$status], $record->specialPopulationStatuses);
    }

    public function testTable4RecordIsNullWhenAgeUnresolved(): void
    {
        self::assertNull((new UdsPatientRecordFactory())->table4($this->patient(ageYears: null)));
    }

    public function testTable4RecordLeavesUnknownPayerNull(): void
    {
        $record = (new UdsPatientRecordFactory())->table4($this->patient(ageYears: 50, insuranceTypeCode: null));

        self::assertNotNull($record);
        self::assertNull($record->payerCategory, 'the Table 4 builder coerces null to None at counting time');
    }

    public function testZipRecordParsesResidenceAndPayer(): void
    {
        $record = (new UdsPatientRecordFactory())->zip($this->patient(zip: '02118-4321', insuranceTypeCode: 2));

        self::assertSame('02118', $record->residence->zip);
        self::assertSame(UdsPayerCategory::Medicare, $record->payerCategory);
    }

    /**
     * @param list<SpecialPopulationStatus> $specialPopulations
     */
    private function patient(
        ?int $ageYears = 30,
        ?string $sexCode = 'Male',
        ?string $raceCode = null,
        ?string $ethnicityCode = null,
        ?string $languageCode = null,
        ?string $interpreterNeeded = null,
        ?string $zip = null,
        FplBand $incomeBand = FplBand::Unknown,
        ?int $insuranceTypeCode = null,
        array $specialPopulations = [],
    ): ReportingPatient {
        return new ReportingPatient(
            pid: 1,
            ageYears: $ageYears,
            sexCode: $sexCode,
            raceCode: $raceCode,
            ethnicityCode: $ethnicityCode,
            languageCode: $languageCode,
            interpreterNeeded: $interpreterNeeded,
            zip: $zip,
            incomeBand: $incomeBand,
            insuranceTypeCode: $insuranceTypeCode,
            specialPopulations: $specialPopulations,
        );
    }
}

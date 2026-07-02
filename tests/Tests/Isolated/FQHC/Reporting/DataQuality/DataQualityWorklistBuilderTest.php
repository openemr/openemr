<?php

/**
 * Isolated tests for UDS data-quality gap detection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting\DataQuality;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Reporting\DataQuality\DataQualityWorklistBuilder;
use OpenEMR\FQHC\Reporting\DataQuality\UdsDataQualityGap;
use OpenEMR\FQHC\Reporting\ReportingPatient;
use PHPUnit\Framework\TestCase;

final class DataQualityWorklistBuilderTest extends TestCase
{
    public function testACleanPatientHasNoGapsAndIsOmitted(): void
    {
        $worklist = (new DataQualityWorklistBuilder())->build([$this->patient()]);

        self::assertSame([], $worklist);
    }

    public function testFlagsMissingAge(): void
    {
        $worklist = (new DataQualityWorklistBuilder())->build([$this->patient(ageYears: null)]);

        self::assertSame([UdsDataQualityGap::MissingAge], $worklist[0]->gaps);
    }

    public function testFlagsMissingOrUnrecognizedSex(): void
    {
        $worklist = (new DataQualityWorklistBuilder())->build([$this->patient(sexCode: 'UNK')]);

        self::assertSame([UdsDataQualityGap::MissingSex], $worklist[0]->gaps);
    }

    public function testFlagsUnknownFplBand(): void
    {
        $worklist = (new DataQualityWorklistBuilder())->build([$this->patient(incomeBand: FplBand::Unknown)]);

        self::assertSame([UdsDataQualityGap::UnknownFplBand], $worklist[0]->gaps);
    }

    public function testFlagsAnInsuranceCodeThatDoesNotMapToAUdsPayerCategory(): void
    {
        // 100 is not a recognized insurance_type_code in UdsPayerClassifier.
        $worklist = (new DataQualityWorklistBuilder())->build([$this->patient(insuranceTypeCode: 100)]);

        self::assertSame([UdsDataQualityGap::UnclassifiedInsurance], $worklist[0]->gaps);
    }

    public function testNoInsuranceOnFileIsNotAGap(): void
    {
        // A null insurance code is a legitimate "self-pay/uninsured" state,
        // not an unclassified one.
        $worklist = (new DataQualityWorklistBuilder())->build([$this->patient(insuranceTypeCode: null)]);

        self::assertSame([], $worklist);
    }

    public function testAPatientCanHaveMultipleGaps(): void
    {
        $worklist = (new DataQualityWorklistBuilder())->build([
            $this->patient(ageYears: null, sexCode: 'UNK'),
        ]);

        self::assertSame([UdsDataQualityGap::MissingAge, UdsDataQualityGap::MissingSex], $worklist[0]->gaps);
    }

    public function testOnlyPatientsWithGapsAppearInTheWorklist(): void
    {
        $worklist = (new DataQualityWorklistBuilder())->build([
            $this->patient(pid: 1),
            $this->patient(pid: 2, ageYears: null),
        ]);

        self::assertCount(1, $worklist);
        self::assertSame(2, $worklist[0]->pid);
    }

    private function patient(
        int $pid = 1,
        ?int $ageYears = 40,
        ?string $sexCode = 'Female',
        FplBand $incomeBand = FplBand::From101To150,
        ?int $insuranceTypeCode = 3,
    ): ReportingPatient {
        return new ReportingPatient(
            pid: $pid,
            ageYears: $ageYears,
            sexCode: $sexCode,
            raceCode: 'white',
            ethnicityCode: 'not_hisp_or_latin',
            languageCode: 'english',
            interpreterNeeded: 'no',
            zip: '02118',
            incomeBand: $incomeBand,
            insuranceTypeCode: $insuranceTypeCode,
            specialPopulations: [],
        );
    }
}

<?php

/**
 * Isolated tests for the UDS Snapshot assembler and field model.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Snapshot;

use OpenEMR\FQHC\Snapshot\PatientDemographics;
use OpenEMR\FQHC\Snapshot\UdsField;
use OpenEMR\FQHC\Snapshot\UdsSnapshotAssembler;
use PHPUnit\Framework\TestCase;

final class UdsSnapshotAssemblerTest extends TestCase
{
    private UdsSnapshotAssembler $assembler;

    protected function setUp(): void
    {
        $this->assembler = new UdsSnapshotAssembler();
    }

    public function testDemographicsFieldsHaveExpectedLabelsInOrder(): void
    {
        $fields = $this->assembler->demographicsFields($this->fullDemographics());
        $labels = array_map(static fn(UdsField $f): string => $f->label, $fields);

        self::assertSame(
            ['Age / sex', 'Race', 'Ethnicity', 'Preferred language', 'ZIP code'],
            $labels,
        );
    }

    public function testAgeAndSexAreJoined(): void
    {
        $fields = $this->assembler->demographicsFields($this->fullDemographics());

        self::assertSame('47 · Female', $fields[0]->value);
        self::assertTrue($fields[0]->isRecorded());
    }

    public function testAgeOnlyWhenSexMissing(): void
    {
        $demographics = new PatientDemographics(null, '47', null, null, null, null, null);

        self::assertSame('47', $this->assembler->demographicsFields($demographics)[0]->value);
    }

    public function testAgeSexEmptyWhenBothMissing(): void
    {
        $demographics = new PatientDemographics(null, null, null, null, null, null, null);
        $field = $this->assembler->demographicsFields($demographics)[0];

        self::assertNull($field->value);
        self::assertFalse($field->isRecorded());
    }

    public function testPendingSectionsCoverTheRemainingNewAreas(): void
    {
        $titles = array_map(
            static fn($section): string => $section->title,
            $this->assembler->pendingSections(),
        );

        // Income & FPL is now its own interactive card, not a generic placeholder.
        self::assertSame(
            ['Special populations', 'Insurance (UDS payer category)'],
            $titles,
        );
    }

    public function testAssembleCarriesPatientNameAndBothSections(): void
    {
        $snapshot = $this->assembler->assemble($this->fullDemographics());

        self::assertSame('Jane Doe', $snapshot->patientName);
        self::assertCount(5, $snapshot->demographics);
        self::assertCount(2, $snapshot->pending);
    }

    private function fullDemographics(): PatientDemographics
    {
        return new PatientDemographics(
            fullName: 'Jane Doe',
            ageDisplay: '47',
            sex: 'Female',
            race: 'Black or African American',
            ethnicity: 'Not Hispanic or Latino',
            language: 'Spanish',
            zip: '78207',
        );
    }
}

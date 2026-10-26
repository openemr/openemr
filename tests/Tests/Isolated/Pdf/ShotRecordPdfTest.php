<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Pdf;

use OpenEMR\Pdf\ShotRecordPdf;
use PHPUnit\Framework\TestCase;

final class ShotRecordPdfTest extends TestCase
{
    private const FACILITY = ['facility_address' => "Riverside Clinic\n1 Main St"];

    private const PATIENT = [
        'patient_name' => 'Doe, Jane Q',
        'patient_DOB' => '3/4/1990',
        'patient_address' => "2 Oak Rd\nSpringfield, IL 62701",
    ];

    private const LABELS = [
        'date_of_birth' => 'Date of Birth',
        'none_on_file' => 'No immunizations on file',
        'signature' => 'Signature',
    ];

    /** The document uncompressed, so its text can be read. */
    private static function document(\Cezpdf $pdf): string
    {
        return $pdf->ezOutput(true);
    }

    public function testAPatientWithNoImmunizationsGetsARecordSayingSo(): void
    {
        // Cezpdf::ezTable() throws a TypeError on an empty list, which made the page a 500.
        $out = self::document(ShotRecordPdf::build(self::FACILITY, self::PATIENT, [], 'Shot Record as of: 10/26/2026', self::LABELS));

        $this->assertStringStartsWith('%PDF', $out);
        $this->assertStringContainsString('No immunizations on file', $out);
        $this->assertStringContainsString('Shot Record as of: 10/26/2026', $out);
        $this->assertStringContainsString('Doe, Jane Q', $out);
        $this->assertStringContainsString('Date of Birth: 3/4/1990', $out);
        $this->assertStringContainsString('Signature:', $out);
    }

    public function testImmunizationsAreATableUnderTheTitle(): void
    {
        $rows = [
            ['Vaccine' => 'Tdap', 'Lot' => 'AB123'],
            ['Vaccine' => 'Influenza', 'Lot' => 'CD456'],
        ];
        $out = self::document(ShotRecordPdf::build(self::FACILITY, self::PATIENT, $rows, 'Shot Record as of: 10/26/2026', self::LABELS));

        $this->assertStringStartsWith('%PDF', $out);
        foreach (['Vaccine', 'Lot', 'Tdap', 'AB123', 'Influenza', 'CD456', 'Shot Record as of: 10/26/2026'] as $text) {
            $this->assertStringContainsString($text, $out);
        }
        $this->assertStringNotContainsString('No immunizations on file', $out);
    }

    public function testNoPatientRowLeavesThePatientLinesBlank(): void
    {
        $out = self::document(ShotRecordPdf::build([], false, [], 'Shot Record', self::LABELS));

        $this->assertStringStartsWith('%PDF', $out);
        $this->assertStringContainsString('Date of Birth: ', $out);
    }
}

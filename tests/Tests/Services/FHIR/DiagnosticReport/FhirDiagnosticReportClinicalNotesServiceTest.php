<?php

/**
 * FhirDiagnosticReportClinicalNotesServiceTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR\DiagnosticReport;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDiagnosticReport;
use OpenEMR\Services\FHIR\DiagnosticReport\FhirDiagnosticReportClinicalNotesService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FhirDiagnosticReportClinicalNotesServiceTest extends TestCase
{
    private const PATIENT_UUID = '96a0b5a3-7a6c-4d5e-9f10-3c2b1a0d9e83';

    /**
     * A clinical note without a date (C-CDA import stores NULL when the entry has none)
     * still becomes a report, without effective[x].
     */
    #[Test]
    public function noteWithoutDateBecomesAReportWithoutEffectiveDate(): void
    {
        $json = $this->parseToJson(null);

        $this->assertArrayNotHasKey('effectiveDateTime', $json);
        $this->assertArrayNotHasKey('issued', $json);
        $subject = $json['subject'] ?? null;
        $this->assertIsArray($subject);
        $this->assertSame('Patient/' . self::PATIENT_UUID, $subject['reference'] ?? null);
    }

    /**
     * A dated note keeps its date as effective[x].
     */
    #[Test]
    public function datedNoteKeepsItsEffectiveDate(): void
    {
        $effective = $this->parseToJson('2026-03-02')['effectiveDateTime'] ?? null;

        $this->assertIsString($effective);
        $this->assertStringStartsWith('2026-03-0', $effective);
    }

    /**
     * Parses a clinical-note row and returns the report as the API serializes it.
     *
     * @return array<mixed>
     */
    private function parseToJson(?string $date): array
    {
        $report = (new FhirDiagnosticReportClinicalNotesService())->parseOpenEMRRecord($this->record($date));
        $this->assertInstanceOf(FHIRDiagnosticReport::class, $report);
        $json = json_decode(json_encode($report, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json);
        return $json;
    }

    /**
     * A clinical-note row as ClinicalNotesService::search() returns it.
     *
     * @return array<string, mixed>
     */
    private function record(?string $date): array
    {
        return [
            'uuid' => '96a0b5a3-7a6c-4d5e-9f10-3c2b1a0d9e81',
            'date' => $date,
            'last_updated' => '2026-03-02 10:00:00',
            'euuid' => null,
            'user_uuid' => '96a0b5a3-7a6c-4d5e-9f10-3c2b1a0d9e82',
            'npi' => '1234567893',
            'description' => 'Follow-up note',
            'puuid' => self::PATIENT_UUID,
            'category_code' => 'LOINC:LP29708-2',
            'category_title' => 'Cardiology',
            // search() turns the stored code into codings keyed by code.
            'code' => ['11488-4' => ['code' => '11488-4', 'description' => 'Consult note', 'system' => 'http://loinc.org']],
            'codetext' => 'Consult note',
        ];
    }
}

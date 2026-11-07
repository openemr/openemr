<?php

/**
 * Isolated tests for the parse half of the FHIR laboratory Observation write (#14343):
 * which payloads are refused, and why, before anything touches the database, plus the
 * value, range and status mappings the write and the read share.
 *
 * The database half (orders, reports, the round trip) is FhirObservationLaboratoryServiceCrudTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Observation;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\Services\FHIR\Observation\FhirObservationLaboratoryService;
use OpenEMR\Services\ProcedureResultStatus;
use OpenEMR\Validators\ProcedureResultValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirObservationLaboratoryWriteTest extends TestCase
{
    private const PATIENT_UUID = '9c1a2b3d-4e5f-4a6b-8c7d-9e0f1a2b3c4d';
    private const ORDER_UUID = '2b3c4d5e-6f7a-4b8c-9d0e-1f2a3b4c5d6e';
    private const OTHER_ORDER_UUID = '3c4d5e6f-7a8b-4c9d-8e1f-2a3b4c5d6e7f';

    private FhirObservationLaboratoryService $service;

    protected function setUp(): void
    {
        // parseFhirResource() reads only the payload; the constructor's collaborators are for
        // the write and the read, so the object is built without them.
        $this->service = (new \ReflectionClass(FhirObservationLaboratoryService::class))
            ->newInstanceWithoutConstructor();
    }

    /**
     * @param array<string, mixed> $overrides A null value removes the element.
     * @return array<string, mixed>
     */
    private static function observation(array $overrides = []): array
    {
        $base = [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => 'laboratory',
                ]],
            ]],
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '2345-7', 'display' => 'Glucose']]],
            'subject' => ['reference' => 'Patient/' . self::PATIENT_UUID],
            'basedOn' => [['reference' => 'ServiceRequest/' . self::ORDER_UUID]],
            'effectiveDateTime' => '2026-03-04T09:30:00-05:00',
            'valueQuantity' => ['value' => 5.2, 'unit' => 'mmol/L', 'system' => 'http://unitsofmeasure.org', 'code' => 'mmol/L'],
        ];

        return array_filter(array_merge($base, $overrides), fn($value): bool => $value !== null);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function parse(array $payload): array
    {
        return $this->service->parseFhirResource(new FHIRObservation($payload));
    }

    public function testParsesAQuantityResult(): void
    {
        $parsed = $this->parse(self::observation([
            'referenceRange' => [['low' => ['value' => 3.9], 'high' => ['value' => 5.5]]],
            'interpretation' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'N']]]],
            'note' => [['text' => 'fasting'], ['text' => 'Multiple specimens collected: S1, S2']],
            'issued' => '2026-03-04T12:00:00-05:00',
        ]));

        $this->assertArrayNotHasKey('__validation_error__', $parsed);
        $this->assertSame(['2345-7'], $parsed['codes']);
        $this->assertSame('Glucose', $parsed['text']);
        $this->assertSame(self::PATIENT_UUID, $parsed['puuid']);
        $this->assertSame(self::ORDER_UUID, $parsed['order_uuid']);
        $this->assertSame('final', $parsed['status']);
        $this->assertSame(['type' => 'Quantity', 'result' => '5.2', 'units' => 'mmol/L'], $parsed['value']);
        $this->assertSame('3.9-5.5', $parsed['range']);
        // The read's own note about several specimens is not the result's comment.
        $this->assertSame('fasting', $parsed['comments']);
        $this->assertIsString($parsed['effective']);
        $this->assertIsString($parsed['issued']);
    }

    public function testFindsTheLoincCodingAfterAnotherSystem(): void
    {
        $parsed = $this->parse(self::observation([
            'code' => ['coding' => [
                ['system' => 'http://example.org/lab-codes', 'code' => 'GLU'],
                ['system' => 'http://loinc.org', 'code' => '2345-7', 'display' => 'Glucose'],
            ]],
        ]));

        $this->assertSame(['2345-7'], $parsed['codes']);
    }

    public function testStatusIsStoredAsTheResultStatusListId(): void
    {
        $this->assertSame('prelim', $this->parse(self::observation(['status' => 'preliminary']))['status']);
        $this->assertSame('correct', $this->parse(self::observation(['status' => 'corrected']))['status']);
        $this->assertSame('error', $this->parse(self::observation(['status' => 'entered-in-error']))['status']);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    #[DataProvider('refusedProvider')]
    public function testRefusesWithTheElementNamed(array $overrides, string $element): void
    {
        $parsed = $this->parse(self::observation($overrides));

        $this->assertSame($element, $parsed['__validation_field__'] ?? null, (string) json_encode($parsed));
        $this->assertIsString($parsed['__validation_error__'] ?? null);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     * @codeCoverageIgnore
     */
    public static function refusedProvider(): iterable
    {
        yield 'no LOINC coding' => [['code' => ['coding' => [['system' => 'http://example.org/lab-codes', 'code' => 'GLU']]]], 'code'];
        yield 'not category laboratory' => [['category' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/observation-category', 'code' => 'vital-signs']]]]], 'category'];
        yield 'no subject' => [['subject' => null], 'subject'];
        yield 'no basedOn' => [['basedOn' => null], 'basedOn'];
        yield 'basedOn a CarePlan' => [['basedOn' => [['reference' => 'CarePlan/' . self::ORDER_UUID]]], 'basedOn'];
        yield 'two orders' => [['basedOn' => [
            ['reference' => 'ServiceRequest/' . self::ORDER_UUID],
            ['reference' => 'ServiceRequest/' . self::OTHER_ORDER_UUID],
        ]], 'basedOn'];
        yield 'status amended' => [['status' => 'amended'], 'status'];
        yield 'status registered, which has no result yet' => [['status' => 'registered'], 'status'];
        yield 'an impossible date' => [['effectiveDateTime' => '2026-02-30T09:00:00Z'], 'effectiveDateTime'];
        yield 'a panel' => [['hasMember' => [['reference' => 'Observation/' . self::ORDER_UUID]]], 'hasMember'];
        yield 'no value' => [['valueQuantity' => null], 'value'];
        yield 'a value type not stored' => [['valueQuantity' => null, 'valueBoolean' => true], 'valueBoolean'];
        yield 'quantity without a number' => [['valueQuantity' => ['unit' => 'mg/dL']], 'valueQuantity'];
        yield 'quantity with a comparator' => [['valueQuantity' => ['value' => 0.5, 'comparator' => '<', 'code' => 'mg/dL']], 'valueQuantity'];
        yield 'an impossible issued' => [['issued' => '2026-13-01T09:00:00Z'], 'issued'];
        yield 'DNR marker' => [['valueQuantity' => null, 'valueString' => 'dnr '], 'valueString'];
        yield 'a number sent as text' => [['valueQuantity' => null, 'valueString' => '10'], 'valueString'];
        yield 'two notes' => [['note' => [['text' => 'fasting'], ['text' => 'hemolyzed']]], 'note'];
        yield 'range with one bound' => [['referenceRange' => [['low' => ['value' => 3.9]]]], 'referenceRange'];
        yield 'two ranges' => [['referenceRange' => [['text' => 'adult'], ['text' => 'child']]], 'referenceRange'];
        yield 'two interpretations' => [['interpretation' => [['text' => 'High'], ['text' => 'Low']]], 'interpretation'];
    }

    public function testRangeTextIsKeptAsText(): void
    {
        $this->assertSame('negative', $this->parse(self::observation(['referenceRange' => [['text' => 'negative']]]))['range']);
    }

    /**
     * A range written as low and high has to read back as low and high, zero and negative
     * bounds included; anything else is read back as text.
     */
    public function testReferenceRangeBounds(): void
    {
        $this->assertSame(['low' => 3.9, 'high' => 5.5], FhirObservationLaboratoryService::referenceRangeBounds('3.9-5.5'));
        $this->assertSame(['low' => 0.0, 'high' => 5.0], FhirObservationLaboratoryService::referenceRangeBounds('0-5'));
        $this->assertSame(['low' => -2.0, 'high' => 3.0], FhirObservationLaboratoryService::referenceRangeBounds('-2-3'));
        $this->assertSame(['low' => -5.0, 'high' => -1.0], FhirObservationLaboratoryService::referenceRangeBounds(' -5 - -1 '));
        $this->assertNull(FhirObservationLaboratoryService::referenceRangeBounds('negative'));
        $this->assertNull(FhirObservationLaboratoryService::referenceRangeBounds('<200'));
        $this->assertNull(FhirObservationLaboratoryService::referenceRangeBounds('10-20 mg/dL'));
        $this->assertNull(FhirObservationLaboratoryService::referenceRangeBounds(''));
    }

    public function testDecimalStringHasNoExponentAndNoBinaryNoise(): void
    {
        $this->assertSame('0', FhirObservationLaboratoryService::decimalString(0.0));
        $this->assertSame('0', FhirObservationLaboratoryService::decimalString(0));
        $this->assertSame('5.2', FhirObservationLaboratoryService::decimalString(5.2));
        $this->assertSame('-3.25', FhirObservationLaboratoryService::decimalString(-3.25));
        $this->assertSame('0.3', FhirObservationLaboratoryService::decimalString(0.1 + 0.2));
        $this->assertSame('1234.5678', FhirObservationLaboratoryService::decimalString(1234.5678));
        $this->assertSame('0.0000001', FhirObservationLaboratoryService::decimalString(1.0E-7));
        $this->assertSame('1000000000000000', FhirObservationLaboratoryService::decimalString(1.0E15));
        $this->assertNull(FhirObservationLaboratoryService::decimalString(INF));
    }

    public function testEveryWritableStatusReadsBackAsItself(): void
    {
        foreach (ProcedureResultStatus::cases() as $status) {
            if ($status === ProcedureResultStatus::Incomplete) {
                // Served as registered, which isn't written: a result is written with its value.
                $this->assertSame('registered', $status->fhirStatus());
                continue;
            }
            $this->assertSame($status, ProcedureResultStatus::fromFhir($status->fhirStatus()), $status->value);
        }
        $this->assertNull(ProcedureResultStatus::fromFhir('registered'));
        $this->assertNull(ProcedureResultStatus::fromFhir('amended'));
        $this->assertNull(ProcedureResultStatus::fromFhir('unknown'));
    }

    public function testValidatorCountsCharactersNotBytes(): void
    {
        $validator = new ProcedureResultValidator();
        // 31 two-byte characters fit a varchar(31); 32 do not.
        $this->assertSame([], $validator->validate(['units' => str_repeat('µ', 31)]));
        $this->assertArrayHasKey('units', $validator->validate(['units' => str_repeat('µ', 32)]));
        $this->assertArrayHasKey('result', $validator->validate(['result' => str_repeat('9', 256)]));
        $this->assertArrayHasKey('result', $validator->validate(['result' => '']));
        $this->assertArrayHasKey('result_status', $validator->validate(['result_status' => 'preliminary']));
        $this->assertArrayHasKey('result_data_type', $validator->validate(['result_data_type' => 'F']));
    }
}

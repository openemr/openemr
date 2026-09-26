<?php

/**
 * Isolated tests for the FHIR Observation vitals write parse path.
 *
 * parseFhirResource() is the whole of the vitals write contract that does not
 * need a database: which codes are writable, which are rejected and why, and the
 * unit conversion back into the units `form_vitals` stores. Covering it here keeps
 * that matrix fast and runnable without a data layer; the DB-backed round trip is
 * covered by FhirObservationVitalsServiceCrudTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Observation;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirObservationVitalsWriteTest extends TestCase
{
    private const PATIENT_UUID = '9c1a2b3d-4e5f-4a6b-8c7d-9e0f1a2b3c4d';
    private const ENCOUNTER_UUID = '1a2b3c4d-5e6f-4a7b-8c9d-0e1f2a3b4c5d';

    private FhirObservationVitalsService $service;
    private string $originalTimezone;

    protected function setUp(): void
    {
        $this->originalTimezone = date_default_timezone_get();
        // Fixed zone so the effectiveDateTime offset conversions below are deterministic.
        date_default_timezone_set('America/New_York');

        // The constructor builds a VitalsService, which pulls in the legacy data layer.
        // parseFhirResource() touches neither, so the object is built without it to keep
        // this test in the isolated suite.
        $this->service = (new \ReflectionClass(FhirObservationVitalsService::class))
            ->newInstanceWithoutConstructor();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private static function observation(array $overrides): array
    {
        $base = [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => 'vital-signs',
                ]],
            ]],
            'subject' => ['reference' => 'Patient/' . self::PATIENT_UUID],
            'encounter' => ['reference' => 'Encounter/' . self::ENCOUNTER_UUID],
            'effectiveDateTime' => '2026-03-04T09:30:00-05:00',
        ];

        // A null override removes the element, which is how the "missing element" cases
        // below are expressed.
        return array_filter(array_merge($base, $overrides), fn($value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private static function loincCode(string $code): array
    {
        return ['coding' => [['system' => 'http://loinc.org', 'code' => $code]]];
    }

    /**
     * @param array<string, mixed> $json
     * @return array<string, mixed>
     */
    private function parse(array $json): array
    {
        $parsed = $this->service->parseFhirResource(new FHIRObservation($json));
        $stringKeyed = [];
        foreach ($parsed as $key => $value) {
            $stringKeyed[(string) $key] = $value;
        }

        return $stringKeyed;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function arrayValue(mixed $value): array
    {
        if (!is_array($value)) {
            $this->fail('expected an array, got ' . get_debug_type($value));
        }

        return $value;
    }

    private function stringValue(mixed $value): string
    {
        if (!is_string($value)) {
            $this->fail('expected a string, got ' . get_debug_type($value));
        }

        return $value;
    }

    private function floatValue(mixed $value): float
    {
        if (!is_numeric($value)) {
            $this->fail('expected a numeric value, got ' . get_debug_type($value));
        }

        return (float) $value;
    }

    /**
     * @param \ReflectionClass<FhirObservationVitalsService> $reflection
     * @return list<string>
     */
    private static function constantKeys(\ReflectionClass $reflection, string $name): array
    {
        $constant = $reflection->getReflectionConstant($name);
        if ($constant === false) {
            throw new \RuntimeException('missing constant ' . $name);
        }
        $value = $constant->getValue();
        if (!is_array($value)) {
            throw new \RuntimeException($name . ' is not an array');
        }

        return array_map(strval(...), array_keys($value));
    }

    /**
     * @param array<string, mixed> $json
     * @param array<string, float|int> $expectedColumns
     */
    #[DataProvider('writableVitalsProvider')]
    public function testWritableVitalsParseIntoStorageColumns(array $json, array $expectedColumns): void
    {
        $parsed = $this->parse($json);

        $this->assertArrayNotHasKey('__validation_error__', $parsed);
        $this->assertSame(self::PATIENT_UUID, $parsed['puuid']);
        $this->assertSame(self::ENCOUNTER_UUID, $parsed['euuid']);
        $columns = $this->arrayValue($parsed['columns'] ?? null);
        foreach ($expectedColumns as $column => $value) {
            $this->assertArrayHasKey($column, $columns);
            $this->assertEqualsWithDelta($value, $this->floatValue($columns[$column] ?? null), 0.0001, $column);
        }
        $this->assertSame(array_keys($expectedColumns), array_map(strval(...), array_keys($columns)));
    }

    /**
     * @return array<string, array{array<string, mixed>, array<string, float|int>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function writableVitalsProvider(): array
    {
        return [
            // form_vitals stores weight in pounds; VitalsService::search() converts it out
            // to kg when the units_of_measurement global asks for metric, so a client can
            // legitimately send either spelling and the write has to convert back.
            'weight in kilograms converts to pounds' => [
                self::observation([
                    'code' => self::loincCode('29463-7'),
                    'valueQuantity' => ['value' => 70, 'unit' => 'kg', 'code' => 'kg'],
                ]),
                ['weight' => 154.323584],
            ],
            // Converted weights of 1000 lb and up used to come back as 1-2 lb: the value went
            // through a number_format() string whose thousands comma the float cast stopped at.
            'weight over 1000 lb keeps its thousands' => [
                self::observation([
                    'code' => self::loincCode('29463-7'),
                    'valueQuantity' => ['value' => 500, 'code' => 'kg'],
                ]),
                ['weight' => 1102.311311],
            ],
            'weight in grams over 1000 lb keeps its thousands' => [
                self::observation([
                    'code' => self::loincCode('29463-7'),
                    'valueQuantity' => ['value' => 500000, 'code' => 'g'],
                ]),
                ['weight' => 1102.311311],
            ],
            'weight already in pounds is stored as sent' => [
                self::observation([
                    'code' => self::loincCode('29463-7'),
                    'valueQuantity' => ['value' => 154.32, 'code' => 'lb'],
                ]),
                ['weight' => 154.32],
            ],
            'height in centimetres converts to inches' => [
                self::observation([
                    'code' => self::loincCode('8302-2'),
                    'valueQuantity' => ['value' => 180, 'code' => 'cm'],
                ]),
                ['height' => 70.866142],
            ],
            'temperature in celsius converts to fahrenheit' => [
                self::observation([
                    'code' => self::loincCode('8310-5'),
                    'valueQuantity' => ['value' => 37, 'code' => 'Cel'],
                ]),
                ['temperature' => 98.6],
            ],
            'heart rate needs no conversion' => [
                self::observation([
                    'code' => self::loincCode('8867-4'),
                    'valueQuantity' => ['value' => 72, 'code' => '/min'],
                ]),
                ['pulse' => 72],
            ],
            'blood pressure reads both components' => [
                self::observation([
                    'code' => self::loincCode('85354-9'),
                    'component' => [
                        [
                            'code' => self::loincCode('8480-6'),
                            'valueQuantity' => ['value' => 120, 'code' => 'mm[Hg]'],
                        ],
                        [
                            'code' => self::loincCode('8462-4'),
                            'valueQuantity' => ['value' => 80, 'code' => 'mm[Hg]'],
                        ],
                    ],
                ]),
                ['bps' => 120, 'bpd' => 80],
            ],
            'pulse oximetry reads value and component' => [
                self::observation([
                    'code' => self::loincCode('59408-5'),
                    'valueQuantity' => ['value' => 98, 'code' => '%'],
                    'component' => [[
                        'code' => self::loincCode('3151-8'),
                        'valueQuantity' => ['value' => 2, 'code' => 'L/min'],
                    ]],
                ]),
                ['oxygen_flow_rate' => 2, 'oxygen_saturation' => 98],
            ],
        ];
    }

    public function testEffectiveDateTimeIsNormalizedToServerTimezone(): void
    {
        $parsed = $this->parse(self::observation([
            'code' => self::loincCode('8867-4'),
            'valueQuantity' => ['value' => 72, 'code' => '/min'],
            // Deliberately not the server's own offset: on 2026-03-04 America/New_York is
            // UTC-05:00, so sending -05:00 here would pass even if the offset were dropped
            // rather than applied. This is the same instant expressed as UTC.
            'effectiveDateTime' => '2026-03-04T14:30:00Z',
        ]));

        // The row this Observation coalesces onto is keyed by encounter + date, so two
        // clients sending the same instant in different offsets have to land on one row.
        $this->assertSame('2026-03-04 09:30:00', $parsed['date']);
    }

    /**
     * @param array<string, mixed> $json
     */
    #[DataProvider('rejectedObservationProvider')]
    public function testUnwritableObservationsAreRejected(
        array $json,
        string $expectedField,
        string $expectedMessageFragment
    ): void {
        $parsed = $this->parse($json);

        $this->assertArrayHasKey('__validation_error__', $parsed);
        $this->assertSame($expectedField, $parsed['__validation_field__'] ?? null);
        $this->assertStringContainsString(
            $expectedMessageFragment,
            $this->stringValue($parsed['__validation_error__'] ?? null)
        );
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rejectedObservationProvider(): array
    {
        return [
            'BMI is derived, not stored from the client' => [
                self::observation([
                    'code' => self::loincCode('39156-5'),
                    'valueQuantity' => ['value' => 22, 'code' => 'kg/m2'],
                ]),
                'code',
                'derived from height and weight',
            ],
            'the vital signs panel is assembled on read' => [
                self::observation(['code' => self::loincCode('85353-1')]),
                'code',
                'assembled from its members on read',
            ],
            'average blood pressure is calculated' => [
                self::observation(['code' => self::loincCode('96607-7')]),
                'code',
                'calculated across encounters',
            ],
            'temperature location has no standalone row' => [
                self::observation(['code' => self::loincCode('8327-9')]),
                'code',
                'recorded with the temperature reading',
            ],
            'a code outside the vitals set is refused' => [
                self::observation([
                    'code' => self::loincCode('1234-5'),
                    'valueQuantity' => ['value' => 1, 'code' => 'kg'],
                ]),
                'code',
                'is not a vital sign OpenEMR can store',
            ],
            'a unit that cannot be converted is refused rather than stored bare' => [
                self::observation([
                    'code' => self::loincCode('29463-7'),
                    'valueQuantity' => ['value' => 70, 'code' => 'furlong'],
                ]),
                'valueQuantity',
                'cannot be converted to "lb"',
            ],
            'an unconvertible component unit is refused' => [
                self::observation([
                    'code' => self::loincCode('85354-9'),
                    'component' => [[
                        'code' => self::loincCode('8480-6'),
                        'valueQuantity' => ['value' => 120, 'code' => 'furlong'],
                    ]],
                ]),
                'component',
                'cannot be converted to "mm[Hg]"',
            ],
            'code is required' => [
                self::observation([
                    'valueQuantity' => ['value' => 72, 'code' => '/min'],
                ]),
                'code',
                'Observation.code is required',
            ],
            'subject is required' => [
                self::observation([
                    'code' => self::loincCode('8867-4'),
                    'valueQuantity' => ['value' => 72, 'code' => '/min'],
                    'subject' => null,
                ]),
                'subject',
                'must reference a Patient',
            ],
            'encounter is required because vitals are an encounter form' => [
                self::observation([
                    'code' => self::loincCode('8867-4'),
                    'valueQuantity' => ['value' => 72, 'code' => '/min'],
                    'encounter' => null,
                ]),
                'encounter',
                'stored as an encounter form',
            ],
            'effectiveDateTime is required because it identifies the row' => [
                self::observation([
                    'code' => self::loincCode('8867-4'),
                    'valueQuantity' => ['value' => 72, 'code' => '/min'],
                    'effectiveDateTime' => null,
                ]),
                'effectiveDateTime',
                'identifies the vitals reading being written',
            ],
            'an Observation carrying no value is refused' => [
                self::observation(['code' => self::loincCode('8867-4')]),
                'value',
                'carries no value OpenEMR can store',
            ],
        ];
    }

    public function testEveryReadableVitalsCodeIsEitherWritableOrExplicitlyRejected(): void
    {
        $reflection = new \ReflectionClass(FhirObservationVitalsService::class);
        $readable = self::constantKeys($reflection, 'COLUMN_MAPPINGS');
        $writable = self::constantKeys($reflection, 'VITALS_WRITE_COLUMNS');
        $rejected = self::constantKeys($reflection, 'VITALS_UNWRITABLE_CODES');

        // A code the read side emits but the write side has never been told about would
        // fall through to the generic "not a vital sign" message, which is misleading --
        // it is a vital sign, it just has no decision recorded for it.
        $this->assertSame(
            [],
            array_values(array_diff($readable, $writable, $rejected)),
            'every code the read side emits needs a write decision recorded for it'
        );
    }
}

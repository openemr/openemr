<?php

/**
 * Isolated tests for mapping FHIR Patient.active onto patient_data.active.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Services\Patient\PatientActiveStatus;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use PHPUnit\Framework\TestCase;

final class FhirPatientServiceActiveTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    private function parse(FHIRPatient $patient): array
    {
        // parseFhirResource() only maps the resource, so the database-backed constructor is not needed
        $service = (new \ReflectionClass(FhirPatientService::class))->newInstanceWithoutConstructor();
        $patient->setId(new FHIRId('11111111-2222-3333-4444-555555555555'));
        return $service->parseFhirResource($patient);
    }

    public function testActiveTrueMapsToOne(): void
    {
        $data = $this->parse((new FHIRPatient())->setActive(new FHIRBoolean(true)));

        $this->assertSame(1, $data['active'] ?? null);
    }

    public function testActiveFalseMapsToZero(): void
    {
        $data = $this->parse((new FHIRPatient())->setActive(new FHIRBoolean(false)));

        $this->assertSame(0, $data['active'] ?? null);
    }

    public function testMissingActiveLeavesTheColumnAlone(): void
    {
        $data = $this->parse(new FHIRPatient());

        $this->assertArrayNotHasKey('active', $data);
    }

    public function testDeceasedDateIsMappedAndMakesTheRecordInactive(): void
    {
        $patient = (new FHIRPatient())
            ->setActive(new FHIRBoolean(true))
            ->setDeceasedDateTime(new FHIRDateTime('2026-11-15'));

        $data = $this->parse($patient);
        $this->assertSame('2026-11-15 00:00:00', $data['deceased_date'] ?? null);

        $status = new PatientActiveStatus(new FrozenClock(new DateTimeImmutable('2026-11-21', new DateTimeZone('UTC'))));
        $saved = $status->apply($data, ['active' => '1']);
        $this->assertSame(0, $saved['active']);
        $this->assertSame(PatientActiveStatus::DECEASED_REASON, $saved['inactive_reason']);
        $this->assertSame('2026-11-15', $saved['inactive_date']);
    }

    public function testDeceasedDateTimeIsConvertedToTheServerTimeZone(): void
    {
        $data = $this->parse((new FHIRPatient())->setDeceasedDateTime(new FHIRDateTime('2026-11-15T14:30:00+00:00')));

        $expected = (new DateTimeImmutable('2026-11-15T14:30:00+00:00'))
            ->setTimezone(new DateTimeZone(date_default_timezone_get()))
            ->format('Y-m-d H:i:s');
        $this->assertSame($expected, $data['deceased_date'] ?? null);
    }

    public function testDeceasedDateTimeWithMoreThanSixFractionalDigitsIsMapped(): void
    {
        $data = $this->parse((new FHIRPatient())->setDeceasedDateTime(new FHIRDateTime('2026-11-15T14:30:00.1234567Z')));

        $expected = (new DateTimeImmutable('2026-11-15T14:30:00+00:00'))
            ->setTimezone(new DateTimeZone(date_default_timezone_get()))
            ->format('Y-m-d H:i:s');
        $this->assertSame($expected, $data['deceased_date'] ?? null);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidDeceasedDateProvider(): array
    {
        return [
            'date only, February 30' => ['2026-02-30'],
            'timestamp, February 30' => ['2026-02-30T14:30:00Z'],
            'timestamp, hour 25' => ['2026-11-15T25:30:00Z'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidDeceasedDateProvider')]
    public function testInvalidDeceasedDateIsNotStored(string $value): void
    {
        $data = $this->parse((new FHIRPatient())->setDeceasedDateTime(new FHIRDateTime($value)));

        $this->assertArrayNotHasKey('deceased_date', $data);
    }

    public function testYearAndMonthDeceasedDateIsLeftOutWithoutAnError(): void
    {
        $patient = (new FHIRPatient())->setDeceasedDateTime(new FHIRDateTime('2026-11'));
        $service = (new \ReflectionClass(FhirPatientService::class))->newInstanceWithoutConstructor();
        $check = new \ReflectionMethod($service, 'invalidDeceasedDateResult');

        $this->assertArrayNotHasKey('deceased_date', $this->parse($patient));
        $this->assertNull($check->invoke($service, $patient));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidDeceasedDateProvider')]
    public function testInvalidDeceasedDateIsRefusedWithAValidationError(string $value): void
    {
        $service = (new \ReflectionClass(FhirPatientService::class))->newInstanceWithoutConstructor();
        $patient = (new FHIRPatient())->setDeceasedDateTime(new FHIRDateTime($value));

        $inserted = $service->insert($patient);
        $updated = $service->update('11111111-2222-3333-4444-555555555555', $patient);

        foreach ([$inserted, $updated] as $result) {
            $messages = $result->getValidationMessages();
            $this->assertIsArray($messages);
            $this->assertArrayHasKey('deceasedDateTime', $messages);
        }
    }

    /**
     * @param list<string> $codes
     */
    private function convertActiveSearch(array $codes, ?string $modifier = null): TokenSearchField
    {
        $field = new TokenSearchField('active', $codes);
        $field->setValues(array_map(static fn(string $code): TokenSearchValue => new TokenSearchValue($code), $codes));
        if ($modifier !== null) {
            $field->setModifier($modifier);
        }
        $service = (new \ReflectionClass(FhirPatientService::class))->newInstanceWithoutConstructor();
        (new \ReflectionMethod($service, 'convertActiveSearchCodes'))->invoke($service, $field);
        return $field;
    }

    public function testActiveSearchCodesBecomeTheStoredValues(): void
    {
        $field = $this->convertActiveSearch(['true', 'false']);

        $codes = array_map(static fn(mixed $value): mixed => $value instanceof TokenSearchValue ? $value->getCode() : null, $field->getValues());
        $this->assertSame(['1', '0'], $codes);
    }

    public function testActiveSearchRejectsACodeThatIsNotTrueOrFalse(): void
    {
        $this->expectException(SearchFieldException::class);
        $this->convertActiveSearch(['false', 'unknown']);
    }

    public function testActiveSearchRejectsTheNotModifier(): void
    {
        $this->expectException(SearchFieldException::class);
        $this->convertActiveSearch(['false'], 'not');
    }

    public function testActiveSearchLeavesMissingAlone(): void
    {
        $field = $this->convertActiveSearch(['true'], SearchModifier::MISSING);

        $value = $field->getValues()[0];
        $this->assertInstanceOf(TokenSearchValue::class, $value);
        $this->assertSame('true', $value->getCode());
    }

    public function testDeceasedFalseClearsTheStoredDeathDate(): void
    {
        $data = $this->parse((new FHIRPatient())->setActive(new FHIRBoolean(true))->setDeceasedBoolean(new FHIRBoolean(false)));
        $this->assertArrayHasKey('deceased_date', $data);
        $this->assertNull($data['deceased_date']);

        $status = new PatientActiveStatus(new FrozenClock(new DateTimeImmutable('2026-11-21', new DateTimeZone('UTC'))));
        $saved = $status->apply($data, [
            'active' => '0',
            'deceased_date' => '2026-11-15 00:00:00',
            'inactive_reason' => PatientActiveStatus::DECEASED_REASON,
            'inactive_date' => '2026-11-15',
        ]);
        $this->assertSame(1, $saved['active']);
        $this->assertSame('', $saved['inactive_reason']);
    }

    public function testActivePatientWithoutDeceasedDateStaysActive(): void
    {
        $data = $this->parse((new FHIRPatient())->setActive(new FHIRBoolean(true)));

        $this->assertArrayNotHasKey('deceased_date', $data);
        $this->assertSame(1, $data['active'] ?? null);
    }
}

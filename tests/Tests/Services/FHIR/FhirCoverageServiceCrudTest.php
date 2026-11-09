<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirCoverageService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Coverage Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirCoverageServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRCoverage $fhirCoverageFixture;
    private FhirCoverageService $fhirCoverageService;
    private string $patientUuid;
    private string $insurerUuid;
    private bool $hadCountryList = false;
    private string $originalCountryList = '';

    /** A list owned by this test, so the country tests do not depend on the site's country list. */
    private const COUNTRY_LIST = 'phpunit_coverage_country';

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $this->hadCountryList = $globals->has('country_list');
        $this->originalCountryList = $globals->getString('country_list');

        $this->fixtureManager = new FixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $this->insurerUuid = $this->fixtureManager->installInsuranceCompanyFixture();

        $fixture = (array) $this->fixtureManager->getSingleFhirCoverageFixture();
        $fixture['beneficiary'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['payor'] = [['reference' => 'Organization/' . $this->insurerUuid]];
        $this->fhirCoverageFixture = new FHIRCoverage($fixture);

        $this->fhirCoverageService = new FhirCoverageService();
        $this->fhirCoverageService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($this->hadCountryList) {
            $globals->set('country_list', $this->originalCountryList);
        } else {
            $globals->remove('country_list');
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM `list_options` WHERE `list_id` = ?', [self::COUNTRY_LIST]);
        $this->fixtureManager->removeCoverageFixtures();
        $this->fixtureManager->removePatientFixtures();
        $this->fixtureManager->removeInsuranceCompanyFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirCoverageFixture->setId(new FHIRId());
        $processingResult = $this->fhirCoverageService->insert($this->fhirCoverageFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    /**
     * A self-subscribed Coverage copies the subscriber's demographics from the patient. A patient
     * country that is not an option of the country list (the list ships with 'USA' only; charts
     * hold 'US', 'United States', ...) must not fail the write: FHIR Coverage does not carry a
     * subscriber country, so the client can neither see nor correct it.
     */
    #[Test]
    public function testInsertSucceedsWhenThePatientCountryIsNotACountryListOption(): void
    {
        $this->useCountryListWithOnlyUsa();
        QueryUtils::sqlStatementThrowException(
            'UPDATE `patient_data` SET `country_code` = ? WHERE `uuid` = ?',
            ['United States', UuidRegistry::uuidToBytes($this->patientUuid)]
        );

        $this->fhirCoverageFixture->setId(new FHIRId());
        $processingResult = $this->fhirCoverageService->insert($this->fhirCoverageFixture);

        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );
        $uuid = $this->firstDataRow($processingResult)['uuid'];
        $this->assertIsString($uuid);
        $stored = QueryUtils::querySingleRow(
            'SELECT `subscriber_country` FROM `insurance_data` WHERE `uuid` = ?',
            [UuidRegistry::uuidToBytes($uuid)]
        );
        $this->assertIsArray($stored);
        $this->assertContains($stored['subscriber_country'], [null, ''], 'an unlisted country is not copied to the subscriber');
    }

    #[Test]
    public function testInsertCopiesAPatientCountryThatIsACountryListOption(): void
    {
        $this->useCountryListWithOnlyUsa();
        QueryUtils::sqlStatementThrowException(
            'UPDATE `patient_data` SET `country_code` = ? WHERE `uuid` = ?',
            ['USA', UuidRegistry::uuidToBytes($this->patientUuid)]
        );

        $this->fhirCoverageFixture->setId(new FHIRId());
        $processingResult = $this->fhirCoverageService->insert($this->fhirCoverageFixture);

        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );
        $uuid = $this->firstDataRow($processingResult)['uuid'];
        $this->assertIsString($uuid);
        $stored = QueryUtils::querySingleRow(
            'SELECT `subscriber_country` FROM `insurance_data` WHERE `uuid` = ?',
            [UuidRegistry::uuidToBytes($uuid)]
        );
        $this->assertIsArray($stored);
        $this->assertSame('USA', $stored['subscriber_country']);
    }

    #[Test]
    public function testInsertWithUnresolvableBeneficiary(): void
    {
        // getUnregisteredUuid(), not UuidRegistry::createUuid(): createUuid() inserts a
        // uuid_registry row, and this test never creates the patient_data row that would
        // carry it, so teardown -- which finds rows to delete through their targets --
        // cannot see it and the registry row survives every run. An unregistered uuid is
        // just as unresolvable, which is all the test needs.
        $bogusPatientUuid = $this->fixtureManager->getUnregisteredUuid();
        $this->fhirCoverageFixture->setId(new FHIRId());
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['beneficiary'] = ['reference' => 'Patient/' . $bogusPatientUuid];
        $fixture = new FHIRCoverage($payload);

        $processingResult = $this->fhirCoverageService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirCoverageFixture->setId(new FHIRId());
        $processingResult = $this->fhirCoverageService->insert($this->fhirCoverageFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $fhirId = $this->firstDataRow($processingResult)['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['subscriberId'] = 'test-fixture-policy-002-updated';
        $updated = new FHIRCoverage($payload);

        $actualResult = $this->fhirCoverageService->update($fhirId, $updated);
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        $this->assertNotEmpty($actualResult->getData());
        // FhirServiceBase::update re-parses the updated row through parseOpenEMRRecord,
        // so getData()[0] is the FHIRCoverage object. subscriberId carries policy_number.
        $updatedData = $actualResult->getData();
        $this->assertIsArray($updatedData);
        $this->assertArrayHasKey(0, $updatedData);
        $updatedResource = $updatedData[0];
        $this->assertInstanceOf(FHIRCoverage::class, $updatedResource);
        $this->assertSame('test-fixture-policy-002-updated', $updatedResource->getSubscriberId()->getValue());
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        // Use a syntactically valid uuid that does not exist in insurance_data.
        // ('bad-uuid' would throw InvalidUuidStringException before validation runs.)
        $nonExistentUuid = '00000000-0000-0000-0000-000000000000';
        $actualResult = $this->fhirCoverageService->update($nonExistentUuid, $this->fhirCoverageFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertSame([], $actualResult->getData());
    }

    #[Test]
    public function testInsertWithStatusInconsistentWithDatesReturnsValidationError(): void
    {
        // Fixture period is 2024-01-01 -> 2099-12-31 (active by derivation). Claiming
        // status=cancelled should produce a 422-style validation error rather than
        // silently dropping the modifier element.
        $this->fhirCoverageFixture->setId(new FHIRId());
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['status'] = 'cancelled';
        $fixture = new \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage($payload);

        $result = $this->fhirCoverageService->insert($fixture);
        $this->assertFalse($result->isValid());
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('status', $messages);
    }

    #[Test]
    public function testInsertWithStatusEnteredInErrorReturnsValidationError(): void
    {
        $this->fhirCoverageFixture->setId(new FHIRId());
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['status'] = 'entered-in-error';
        $fixture = new \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage($payload);

        $result = $this->fhirCoverageService->insert($fixture);
        $this->assertFalse($result->isValid());
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('status', $messages);
    }

    /**
     * Coverage.type comes from the policy's insurer: its CQM Source of Payment, a code of the
     * Payer Type value set US Core binds the type to, when it has one, else the ActCode for its
     * insurance type.
     *
     * @param list<string> $expected each coding as "system|code|display"
     */
    #[Test]
    #[DataProvider('coverageTypeProvider')]
    public function testReadTypeComesFromTheInsurer(?int $insTypeCode, ?int $cqmSop, array $expected): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `insurance_companies` SET `ins_type_code` = ?, `cqm_sop` = ? WHERE `uuid` = ?',
            [$insTypeCode, $cqmSop, UuidRegistry::uuidToBytes($this->insurerUuid)]
        );

        [, $coverage] = $this->insertAndRead($this->fhirCoverageFixture);

        $this->assertSame($expected, $this->typeCodings($coverage));
    }

    /**
     * @return array<string, array{0: ?int, 1: ?int, 2: list<string>}>
     * @codeCoverageIgnore
     */
    public static function coverageTypeProvider(): array
    {
        $public = 'http://terminology.hl7.org/CodeSystem/v3-ActCode|PUBLICPOL|public healthcare';
        $plan = 'http://terminology.hl7.org/CodeSystem/v3-ActCode|HIP|health insurance plan policy';
        return [
            'Medicaid' => [3, null, [$public]],
            'Medicare Part B' => [2, null, [$public]],
            'Veterans Administration Plan' => [24, null, [$public]],
            'HMO Medicare Risk' => [15, null, [$public]],
            'Commercial Insurance Co.' => [17, null, [$plan]],
            'Workers Compensation Health Plan' => [
                25,
                null,
                ["http://terminology.hl7.org/CodeSystem/v3-ActCode|WCBPOL|worker's compensation"],
            ],
            'Automobile Medical' => [16, null, ['http://terminology.hl7.org/CodeSystem/v3-ActCode|AUTOPOL|automobile']],
            'Disability' => [
                18,
                null,
                ['http://terminology.hl7.org/CodeSystem/v3-ActCode|DIS|disability insurance policy'],
            ],
            'Self Pay, sent as its source of payment' => [8, null, ['https://nahdo.org/sopt|81|']],
            'no insurance type' => [null, null, [$plan]],
            'Medicaid, with the Medicaid source of payment' => [3, 2, ['https://nahdo.org/sopt|2|']],
            'Medicare Part B, with a Medicare source of payment' => [2, 11, ['https://nahdo.org/sopt|11|']],
            // a QRDA import stores the payer's source of payment code in both columns
            'private insurance, as an import stores it' => [5, 5, ['https://nahdo.org/sopt|5|']],
        ];
    }

    /**
     * The insurer's codes as a read without bound values gives them (strings), and blank or
     * unusable ones, which fall back to HIP with no source of payment.
     *
     * @param list<string> $expected each coding as "system|code|display"
     */
    #[Test]
    #[DataProvider('typeCodeAsReadProvider')]
    public function testTypeReadsCodesThatArriveAsStrings(mixed $insTypeCode, mixed $cqmSop, array $expected): void
    {
        $coverage = $this->fhirCoverageService->parseOpenEMRRecord([
            'uuid' => '00000000-0000-4000-8000-000000000000',
            'ins_type_code' => $insTypeCode,
            'cqm_sop' => $cqmSop,
        ]);

        $this->assertSame($expected, $this->typeCodings($coverage));
    }

    /**
     * @return array<string, array{0: mixed, 1: mixed, 2: list<string>}>
     * @codeCoverageIgnore
     */
    public static function typeCodeAsReadProvider(): array
    {
        $plan = 'http://terminology.hl7.org/CodeSystem/v3-ActCode|HIP|health insurance plan policy';
        return [
            'strings' => ['3', '2', ['https://nahdo.org/sopt|2|']],
            'Medicaid as a string' => [
                '3',
                null,
                ['http://terminology.hl7.org/CodeSystem/v3-ActCode|PUBLICPOL|public healthcare'],
            ],
            'Self Pay as a string' => ['8', null, ['https://nahdo.org/sopt|81|']],
            'self-pay source of payment on a commercial insurer' => ['17', '81', ['https://nahdo.org/sopt|81|']],
            'blank and zero' => ['', '0', [$plan]],
            'unusable' => ['Medicaid', ' ', [$plan]],
            'absent' => [null, null, [$plan]],
            'a type no row has' => [99, null, [$plan]],
        ];
    }

    /**
     * A Period's end is inclusive, so an end of "2024-12" covers December through its last day.
     * The create keeps the end it was given (#7676), and the read gives the same period back.
     */
    #[Test]
    #[DataProvider('periodEndProvider')]
    public function testCreateStoresTheLastDayOfThePeriodEnd(string $periodEnd, string $expectedDateEnd): void
    {
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['period'] = ['start' => '2024-01-01', 'end' => $periodEnd];
        // the period has ended, so the status OpenEMR derives from it is cancelled
        $payload['status'] = 'cancelled';

        [$uuid, $coverage] = $this->insertAndRead(new FHIRCoverage($payload));

        $stored = QueryUtils::fetchSingleValue(
            'SELECT `date_end` FROM `insurance_data` WHERE `uuid` = ?',
            'date_end',
            [UuidRegistry::uuidToBytes($uuid)]
        );
        $this->assertSame($expectedDateEnd, $stored);

        $read = $this->toArray($coverage);
        $this->assertSame('cancelled', $read['status'] ?? null);
        $period = $read['period'] ?? null;
        $this->assertIsArray($period);
        $this->assertIsString($period['start'] ?? null);
        $this->assertIsString($period['end'] ?? null);
        $this->assertSame('2024-01-01', substr($period['start'], 0, 10));
        $this->assertSame($expectedDateEnd, substr($period['end'], 0, 10));
        $this->assertLessThanOrEqual(
            new \DateTimeImmutable($period['end']),
            new \DateTimeImmutable($period['start'])
        );
    }

    /**
     * A Coverage with no period.end is open-ended: the create stores no end date and the read
     * gives none back.
     */
    #[Test]
    public function testCreateWithoutAPeriodEndIsOpenEnded(): void
    {
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['period'] = ['start' => '2024-01-01'];

        [$uuid, $coverage] = $this->insertAndRead(new FHIRCoverage($payload));

        $stored = QueryUtils::querySingleRow(
            'SELECT `date`, `date_end` FROM `insurance_data` WHERE `uuid` = ?',
            [UuidRegistry::uuidToBytes($uuid)]
        );
        $this->assertIsArray($stored);
        $this->assertSame('2024-01-01', $stored['date'] ?? null);
        $this->assertNull($stored['date_end'] ?? null);

        $read = $this->toArray($coverage);
        $this->assertSame('active', $read['status'] ?? null);
        $period = $read['period'] ?? null;
        $this->assertIsArray($period);
        $this->assertArrayNotHasKey('end', $period);
    }

    /**
     * An update ends the period and reopens it: a period with no end clears the stored end, and a
     * resource with no period keeps the stored dates. Each answer reads as a read of it would.
     */
    #[Test]
    public function testUpdateEndsAndReopensThePeriod(): void
    {
        [$uuid] = $this->insertAndRead($this->fhirCoverageFixture);
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['id'] = $uuid;
        unset($payload['status']);

        $ended = $payload;
        $ended['period'] = ['start' => '2024-01-01', 'end' => '2024-12'];
        $response = $this->updateAndRespond($uuid, $ended);
        $this->assertSame('2024-12-31', $this->storedDateEnd($uuid));
        $this->assertSame('cancelled', $response['status'] ?? null);
        $this->assertSame(
            ['http://terminology.hl7.org/CodeSystem/v3-ActCode|PUBLICPOL|public healthcare'],
            $this->typeCodings(new FHIRCoverage($response))
        );
        $beneficiary = $response['beneficiary'] ?? null;
        $this->assertIsArray($beneficiary);
        $this->assertSame('Patient/' . $this->patientUuid, $beneficiary['reference'] ?? null);

        $withoutPeriod = $payload;
        unset($withoutPeriod['period']);
        $this->updateAndRespond($uuid, $withoutPeriod);
        $this->assertSame('2024-12-31', $this->storedDateEnd($uuid));

        $reopened = $payload;
        $reopened['period'] = ['start' => '2024-01-01'];
        $response = $this->updateAndRespond($uuid, $reopened);
        $this->assertNull($this->storedDateEnd($uuid));
        $this->assertSame('active', $response['status'] ?? null);
        $period = $response['period'] ?? null;
        $this->assertIsArray($period);
        $this->assertArrayNotHasKey('end', $period);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     * @codeCoverageIgnore
     */
    public static function periodEndProvider(): array
    {
        return [
            'year' => ['2024', '2024-12-31'],
            'month' => ['2024-12', '2024-12-31'],
            'full date' => ['2024-12-15', '2024-12-15'],
        ];
    }

    /**
     * Inserts the Coverage and reads it back through the FHIR read.
     *
     * The read leaves out a policy whose insurer id is 0, and the insurer fixture is inserted
     * with id 0, so it is given the next id first, as CoverageFhirApiTest does.
     *
     * @return array{0: string, 1: FHIRCoverage} its uuid and the resource read
     */
    private function insertAndRead(FHIRCoverage $coverage): array
    {
        $nextInsurerId = QueryUtils::fetchSingleValue(
            'SELECT COALESCE(MAX(id), 0) + 1 AS id FROM insurance_companies',
            'id'
        );
        $this->assertIsNumeric($nextInsurerId);
        QueryUtils::sqlStatementThrowException(
            'UPDATE insurance_companies SET id = ? WHERE uuid = ? AND id = 0',
            [$nextInsurerId, UuidRegistry::uuidToBytes($this->insurerUuid)]
        );

        $coverage->setId(new FHIRId());
        $inserted = $this->fhirCoverageService->insert($coverage);
        $this->assertTrue(
            $inserted->isValid(),
            'Insert should succeed: ' . json_encode($inserted->getValidationMessages())
        );
        $uuid = $this->firstDataRow($inserted)['uuid'] ?? null;
        $this->assertIsString($uuid);

        $read = $this->fhirCoverageService->getOne($uuid);
        $this->assertTrue($read->isValid());
        $data = $read->getData();
        $this->assertIsArray($data);
        $resource = $data[0] ?? null;
        $this->assertInstanceOf(FHIRCoverage::class, $resource);

        return [$uuid, $resource];
    }

    /**
     * Updates the Coverage through the FHIR service and gives the resource it answers with.
     *
     * @param array<mixed> $payload
     * @return array<mixed>
     */
    private function updateAndRespond(string $uuid, array $payload): array
    {
        $result = $this->fhirCoverageService->update($uuid, new FHIRCoverage($payload));
        $this->assertTrue(
            $result->isValid(),
            'Update should succeed: ' . json_encode($result->getValidationMessages())
        );
        $data = $result->getData();
        $this->assertIsArray($data);
        $resource = $data[0] ?? null;
        $this->assertInstanceOf(FHIRCoverage::class, $resource);

        return $this->toArray($resource);
    }

    private function storedDateEnd(string $uuid): mixed
    {
        return QueryUtils::fetchSingleValue(
            'SELECT `date_end` FROM `insurance_data` WHERE `uuid` = ?',
            'date_end',
            [UuidRegistry::uuidToBytes($uuid)]
        );
    }

    /**
     * The Coverage's type codings, each as "system|code|display".
     *
     * @return list<string>
     */
    private function typeCodings(FHIRCoverage $coverage): array
    {
        $type = $this->toArray($coverage)['type'] ?? null;
        $this->assertIsArray($type);
        $codings = $type['coding'] ?? null;
        $this->assertIsArray($codings);
        $flat = [];
        foreach ($codings as $coding) {
            $this->assertIsArray($coding);
            $system = $coding['system'] ?? '';
            $code = $coding['code'] ?? '';
            $display = $coding['display'] ?? '';
            $this->assertIsString($system);
            $this->assertIsString($code);
            $this->assertIsString($display);
            $flat[] = $system . '|' . $code . '|' . $display;
        }

        return $flat;
    }

    /**
     * @return array<mixed>
     */
    private function toArray(FHIRCoverage $coverage): array
    {
        $decoded = json_decode(json_encode($coverage, JSON_THROW_ON_ERROR), true);
        $this->assertIsArray($decoded);

        return $decoded;
    }

    /**
     * Points the country_list global at a list holding the single option 'USA'.
     */
    private function useCountryListWithOnlyUsa(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `list_options` WHERE `list_id` = ?', [self::COUNTRY_LIST]);
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `activity`) VALUES (?, ?, ?, 1)',
            [self::COUNTRY_LIST, 'USA', 'USA']
        );
        OEGlobalsBag::getInstance()->set('country_list', self::COUNTRY_LIST);
    }

    /**
     * Reads the first row of a ProcessingResult, asserting the shape as it goes so a
     * failed insert surfaces as a test failure rather than a type error downstream.
     *
     * @return array<mixed>
     */
    private function firstDataRow(ProcessingResult $result): array
    {
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $row = $data[0];
        $this->assertIsArray($row);

        return $row;
    }
}

<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitionerRole;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirPractitionerRoleService;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR PractitionerRole Service CRUD Tests
 *
 * PractitionerRole binds an existing practitioner (users row) to an existing facility,
 * with optional role + specialty codes from `us-core-provider-role` /
 * `us-core-provider-specialty` list_options. Storage is the EAV `facility_user_ids`
 * table: one marker row per role (carries the FHIR uuid) plus sibling rows for
 * role_code and specialty_code.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirPractitionerRoleServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private PractitionerFixtureManager $practitionerFixtureManager;
    private FacilityFixtureManager $facilityFixtureManager;
    private FHIRPractitionerRole $fhirPractitionerRoleFixture;
    private FhirPractitionerRoleService $fhirPractitionerRoleService;
    private string $practitionerUuid;
    private string $facilityUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->practitionerFixtureManager = new PractitionerFixtureManager();
        $this->facilityFixtureManager = new FacilityFixtureManager();

        // Install one practitioner and one facility; capture their uuids. Both lookups are
        // scoped to the fixture prefix so a pre-existing row with a higher id cannot be
        // picked up on a shared database. The practitioner lookup additionally requires a
        // non-empty npi, which PractitionerValidator makes mandatory for these fixtures.
        $this->practitionerFixtureManager->installPractitionerFixtures();
        $practitionerRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM users WHERE fname LIKE 'test-fixture-%' "
            . "AND npi IS NOT NULL AND npi != '' ORDER BY id DESC LIMIT 1",
            []
        );
        $this->assertIsArray($practitionerRow);
        if (($practitionerRow['uuid'] ?? '') === '') {
            $this->markTestSkipped('Practitioner fixture did not produce a queryable row');
        }
        $this->practitionerUuid = UuidRegistry::uuidToString($practitionerRow['uuid']);

        $this->facilityFixtureManager->installFacilityFixtures();
        $facilityRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM facility WHERE name LIKE 'test-fixture%' ORDER BY id DESC LIMIT 1",
            []
        );
        $this->assertIsArray($facilityRow);
        if (($facilityRow['uuid'] ?? '') === '') {
            $this->markTestSkipped('Facility fixture did not produce a queryable row');
        }
        $this->facilityUuid = UuidRegistry::uuidToString($facilityRow['uuid']);

        $fixture = (array) $this->fixtureManager->getSingleFhirPractitionerRoleFixture();
        $fixture['practitioner'] = ['reference' => 'Practitioner/' . $this->practitionerUuid];
        $fixture['organization'] = ['reference' => 'Organization/' . $this->facilityUuid];
        $this->fhirPractitionerRoleFixture = new FHIRPractitionerRole($fixture);

        $this->fhirPractitionerRoleService = new FhirPractitionerRoleService();
        $this->fhirPractitionerRoleService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerRoleFixtures();
        $this->practitionerFixtureManager->removePractitionerFixtures();
        $this->facilityFixtureManager->removeInstalledFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirPractitionerRoleFixture->setId(new FHIRId());
        $result = $this->fhirPractitionerRoleService->insert($this->fhirPractitionerRoleFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $this->firstDataRow($result);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvablePractitioner(): void
    {
        // getUnregisteredUuid(), not UuidRegistry::createUuid(): createUuid() inserts a
        // uuid_registry row, and this test never creates the users row that would
        // carry it, so teardown -- which finds rows to delete through their targets --
        // cannot see it and the registry row survives every run. An unregistered uuid is
        // just as unresolvable, which is all the test needs.
        $bogusUuid = $this->fixtureManager->getUnregisteredUuid();
        $this->fhirPractitionerRoleFixture->setId(new FHIRId());
        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        $payload['practitioner'] = ['reference' => 'Practitioner/' . $bogusUuid];
        $fixture = new FHIRPractitionerRole($payload);

        $result = $this->fhirPractitionerRoleService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testInsertWithUnresolvableOrganization(): void
    {
        // getUnregisteredUuid(), not UuidRegistry::createUuid(): createUuid() inserts a
        // uuid_registry row, and this test never creates the facility row that would
        // carry it, so teardown -- which finds rows to delete through their targets --
        // cannot see it and the registry row survives every run. An unregistered uuid is
        // just as unresolvable, which is all the test needs.
        $bogusUuid = $this->fixtureManager->getUnregisteredUuid();
        $this->fhirPractitionerRoleFixture->setId(new FHIRId());
        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        $payload['organization'] = ['reference' => 'Organization/' . $bogusUuid];
        $fixture = new FHIRPractitionerRole($payload);

        $result = $this->fhirPractitionerRoleService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testInsertWithoutCodeIsStillReadable(): void
    {
        // PractitionerRole.code is 0..* in R4 and insert() documents role_code as optional, but
        // search() used to join the resolved role code with an inner join, so a role created
        // without one was created successfully and then omitted from every read -- the caller
        // got a 201 carrying a uuid that getOne() could not find. The admin UI produces the same
        // state outside FHIR: interface/usergroup/facility_user.php writes a row for every FACUSR
        // field on save, so a provider entered with no role has a role_code row holding '', which
        // the read subquery's field_value != '' filter drops.
        $this->fhirPractitionerRoleFixture->setId(new FHIRId());
        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        unset($payload['code']);
        $fixture = new FHIRPractitionerRole($payload);

        $insertResult = $this->fhirPractitionerRoleService->insert($fixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert without a code should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($fhirId);

        $readBack = $this->fhirPractitionerRoleService->getOne($fhirId);
        $this->assertTrue($readBack->isValid(), 'Read-back should succeed');
        $readRecords = $readBack->getData();
        $this->assertIsArray($readRecords);
        $this->assertArrayHasKey(
            0,
            $readRecords,
            'A PractitionerRole created without a code must still be readable by its uuid'
        );
        $serialized = json_decode((string) json_encode($readRecords[0]), true);
        $this->assertIsArray($serialized);
        $this->assertSame($fhirId, $serialized['id'] ?? null, 'Read-back should return the created role');
    }

    #[Test]
    public function testInsertWithUnknownRoleCodeIsRejected(): void
    {
        // A code that matches no us-core-provider-role option resolves to nothing on read, so
        // storing it would answer 201 for a code the caller can never read back. Rejecting it
        // keeps the write honest rather than silently dropping the value.
        $this->fhirPractitionerRoleFixture->setId(new FHIRId());
        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        $codes = $payload['code'] ?? [];
        $this->assertIsArray($codes);
        $this->assertArrayHasKey(0, $codes);
        $this->assertIsArray($codes[0]);
        $coding = $codes[0]['coding'] ?? [];
        $this->assertIsArray($coding);
        $this->assertArrayHasKey(0, $coding);
        $this->assertIsArray($coding[0]);
        $coding[0]['code'] = 'not-a-real-provider-role';
        $codes[0]['coding'] = $coding;
        $payload['code'] = $codes;
        $fixture = new FHIRPractitionerRole($payload);

        $result = $this->fhirPractitionerRoleService->insert($fixture);
        $this->assertFalse($result->isValid(), 'An unknown role code should be rejected');
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirPractitionerRoleFixture->setId(new FHIRId());
        $insertResult = $this->fhirPractitionerRoleService->insert($this->fhirPractitionerRoleFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        // Pick a different valid role code from list_options
        $codes = $payload['code'] ?? [];
        $this->assertIsArray($codes);
        $this->assertArrayHasKey(0, $codes);
        $this->assertIsArray($codes[0]);
        $coding = $codes[0]['coding'] ?? [];
        $this->assertIsArray($coding);
        $this->assertArrayHasKey(0, $coding);
        $this->assertIsArray($coding[0]);
        $coding[0]['code'] = '111N00000X'; // Chiropractor
        $codes[0]['coding'] = $coding;
        $payload['code'] = $codes;
        $updated = new FHIRPractitionerRole($payload);

        $result = $this->fhirPractitionerRoleService->update($fhirId, $updated);
        $this->assertTrue(
            $result->isValid(),
            'Update should succeed: ' . json_encode($result->getValidationMessages())
        );
        $this->assertNotEmpty($result->getData());

        // Read back rather than trusting update()'s answer: a service that accepted the write
        // and stored nothing satisfies every assertion above.
        //
        // The assertion looks for the code anywhere in the serialized `code` block rather than
        // at a fixed path. parseOpenEMRRecord() calls FHIRCodeableConcept::addCoding() with the
        // bare stored string, and that method appends its argument as-is, so `coding` currently
        // serializes as a list of strings instead of Coding objects. Pinning that path would
        // bake the malformed shape into the test; searching the block proves the new code was
        // persisted and is reported either way.
        $readBack = $this->fhirPractitionerRoleService->getOne($fhirId);
        $this->assertTrue($readBack->isValid(), 'Read-back should succeed');
        $readRecords = $readBack->getData();
        $this->assertIsArray($readRecords);
        $this->assertArrayHasKey(0, $readRecords);
        $serialized = json_decode((string) json_encode($readRecords[0]), true);
        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('code', $serialized);
        $this->assertStringContainsString(
            '111N00000X',
            (string) json_encode($serialized['code']),
            'PractitionerRole role code should be updated'
        );
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $result = $this->fhirPractitionerRoleService->update('bad-uuid', $this->fhirPractitionerRoleFixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
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

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
 * @copyright Copyright (c) 2026 OpenCoreEMR
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
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'users']))->createUuid()
        );
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
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'facility']))->createUuid()
        );
        $this->fhirPractitionerRoleFixture->setId(new FHIRId());
        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        $payload['organization'] = ['reference' => 'Organization/' . $bogusUuid];
        $fixture = new FHIRPractitionerRole($payload);

        $result = $this->fhirPractitionerRoleService->insert($fixture);
        $this->assertFalse($result->isValid());
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

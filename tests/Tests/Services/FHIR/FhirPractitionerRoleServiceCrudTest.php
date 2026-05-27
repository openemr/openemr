<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitionerRole;
use OpenEMR\Services\FHIR\FhirPractitionerRoleService;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
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

        // Install one practitioner and one facility; capture their uuids.
        // Practitioner fixtures require npi (per the PractitionerValidator); they
        // also share `users.fname` with patient fixtures' pubpid prefix on a
        // shared DB, so we look up by npi range rather than fname pattern.
        $this->practitionerFixtureManager->installPractitionerFixtures();
        $practitionerRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM users WHERE fname LIKE 'test-fixture-%' "
            . "AND npi IS NOT NULL AND npi != '' ORDER BY id DESC LIMIT 1",
            []
        );
        if (!is_array($practitionerRow) || empty($practitionerRow['uuid'])) {
            $this->markTestSkipped('Practitioner fixture did not produce a queryable row');
        }
        $this->practitionerUuid = UuidRegistry::uuidToString($practitionerRow['uuid']);

        $this->facilityFixtureManager->installFacilityFixtures();
        $facilityRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM facility ORDER BY id DESC LIMIT 1",
            []
        );
        if (!is_array($facilityRow) || empty($facilityRow['uuid'])) {
            $this->markTestSkipped('Facility fixture did not produce a queryable row');
        }
        $this->facilityUuid = UuidRegistry::uuidToString($facilityRow['uuid']);

        $fixture = (array) $this->fixtureManager->getSingleFhirPractitionerRoleFixture();
        $fixture['practitioner'] = ['reference' => 'Practitioner/' . $this->practitionerUuid];
        $fixture['organization'] = ['reference' => 'Organization/' . $this->facilityUuid];
        $this->fhirPractitionerRoleFixture = new FHIRPractitionerRole($fixture);

        $this->fhirPractitionerRoleService = new FhirPractitionerRoleService();
        $this->fhirPractitionerRoleService->setSystemLogger(new SystemLogger(Level::Critical));
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
        $this->fhirPractitionerRoleFixture->setId(null);
        $result = $this->fhirPractitionerRoleService->insert($this->fhirPractitionerRoleFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $result->getData()[0];
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvablePractitioner(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'users']))->createUuid()
        );
        $this->fhirPractitionerRoleFixture->setId(null);
        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        $payload['practitioner'] = ['reference' => 'Practitioner/' . $bogusUuid];
        $fixture = new FHIRPractitionerRole($payload);

        $result = $this->fhirPractitionerRoleService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }

    #[Test]
    public function testInsertWithUnresolvableOrganization(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'facility']))->createUuid()
        );
        $this->fhirPractitionerRoleFixture->setId(null);
        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        $payload['organization'] = ['reference' => 'Organization/' . $bogusUuid];
        $fixture = new FHIRPractitionerRole($payload);

        $result = $this->fhirPractitionerRoleService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirPractitionerRoleFixture->setId(null);
        $insertResult = $this->fhirPractitionerRoleService->insert($this->fhirPractitionerRoleFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $insertResult->getData()[0]['uuid'];

        $payload = $this->fhirPractitionerRoleFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        // Pick a different valid role code from list_options
        $payload['code'][0]['coding'][0]['code'] = '111N00000X'; // Chiropractor
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
        $this->assertEquals(0, count($result->getData()));
    }
}

<?php

/**
 * FHIR PractitionerRole API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/PractitionerRole
 * so routing, scope enforcement, and serialization are exercised end to end —
 * the path the service-layer CRUD tests bypass.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class PractitionerRoleFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/PractitionerRole';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private PractitionerFixtureManager $practitionerFixtureManager;
    private FacilityFixtureManager $facilityFixtureManager;
    /** @var array<string, mixed> */
    private array $fhirFixture;
    private string $practitionerUuid;
    private string $facilityUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->practitionerFixtureManager = new PractitionerFixtureManager();
        $this->facilityFixtureManager = new FacilityFixtureManager();

        $this->practitionerFixtureManager->installPractitionerFixtures();
        $practitionerRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM users WHERE fname LIKE 'test-fixture-%' "
            . "AND npi IS NOT NULL AND npi != '' ORDER BY id DESC LIMIT 1",
            []
        );
        $this->assertIsArray($practitionerRow);
        $this->practitionerUuid = UuidRegistry::uuidToString($practitionerRow['uuid']);

        $this->facilityFixtureManager->installFacilityFixtures();
        $facilityRow = QueryUtils::querySingleRow(
            'SELECT uuid FROM facility ORDER BY id DESC LIMIT 1',
            []
        );
        $this->assertIsArray($facilityRow);
        $this->facilityUuid = UuidRegistry::uuidToString($facilityRow['uuid']);

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/practitioner-role.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['practitioner'] = ['reference' => 'Practitioner/' . $this->practitionerUuid];
        $fixture['organization'] = ['reference' => 'Organization/' . $this->facilityUuid];
        unset($fixture['id']);
        $stringKeyedFixture = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $stringKeyedFixture[$key] = $value;
        }
        $this->fhirFixture = $stringKeyedFixture;
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerRoleFixtures();
        $this->practitionerFixtureManager->removePractitionerFixtures();
        $this->facilityFixtureManager->removeInstalledFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPostCreatesPractitionerRole(): void
    {
        $response = $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture);
        $body = $response->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            'POST ' . self::RESOURCE_URL . ' should return 201. Body: ' . $body
        );
        $contents = json_decode($body, true);
        $this->assertIsArray($contents);
        $this->assertArrayHasKey('uuid', $contents, 'Create response should carry the new resource uuid');
        $this->assertIsString($contents['uuid']);
    }

    public function testPutUpdatesPractitionerRole(): void
    {
        $created = json_decode(
            $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture)->getBody()->getContents(),
            true
        );
        $this->assertIsArray($created);
        $this->assertArrayHasKey('uuid', $created);
        $id = $created['uuid'];
        $this->assertIsString($id);

        $updated = $this->fhirFixture;
        $updated['id'] = $id;
        $putResponse = $this->testClient->put(self::RESOURCE_URL, $id, $updated);
        $this->assertSame(
            Response::HTTP_OK,
            $putResponse->getStatusCode(),
            'PUT ' . self::RESOURCE_URL . '/{id} should return 200. Body: ' . $putResponse->getBody()->getContents()
        );
    }

    public function testPostWithoutPractitionerReturnsError(): void
    {
        $invalid = $this->fhirFixture;
        unset($invalid['practitioner']);
        $response = $this->testClient->post(self::RESOURCE_URL, $invalid);
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'POST without practitioner should return 400. Body: ' . $response->getBody()->getContents()
        );
    }
}

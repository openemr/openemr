<?php

/**
 * FHIR Appointment API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/Appointment
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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class AppointmentFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/Appointment';
    private const ID_KEY = 'pc_uuid';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private FacilityFixtureManager $facilityFixtureManager;
    /** @var array<string, mixed> */
    private array $fhirFixture;
    private string $patientUuid;
    private string $facilityUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->facilityFixtureManager = new FacilityFixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $firstPatient = $patients[0];
        $this->assertIsArray($firstPatient);
        $patientRecord = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$firstPatient['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $this->facilityFixtureManager->installFacilityFixtures();
        $facilityRow = QueryUtils::querySingleRow(
            'SELECT uuid FROM facility ORDER BY id DESC LIMIT 1',
            []
        );
        $this->assertIsArray($facilityRow);
        $this->facilityUuid = UuidRegistry::uuidToString($facilityRow['uuid']);

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/appointment.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['participant'] = [
            [
                'actor' => ['reference' => 'Patient/' . $this->patientUuid],
                'status' => 'accepted',
            ],
            [
                'actor' => ['reference' => 'Location/' . $this->facilityUuid],
                'status' => 'accepted',
            ],
        ];
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
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM openemr_postcalendar_events WHERE pc_hometext LIKE 'test-fixture%'"
        );
        $this->fixtureManager->removePatientFixtures();
        $this->facilityFixtureManager->removeInstalledFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPostCreatesAppointment(): void
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
        $this->assertArrayHasKey(self::ID_KEY, $contents, 'Create response should carry the new resource id');
        $this->assertIsString($contents[self::ID_KEY]);
    }

    public function testPostWithoutParticipantReturnsError(): void
    {
        $invalid = $this->fhirFixture;
        unset($invalid['participant']);
        $response = $this->testClient->post(self::RESOURCE_URL, $invalid);
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'POST without participant should return 400. Body: ' . $response->getBody()->getContents()
        );
    }
}

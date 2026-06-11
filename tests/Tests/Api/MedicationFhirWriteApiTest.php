<?php

/**
 * FHIR Medication API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/Medication
 * so routing, scope enforcement, and serialization are exercised end to end —
 * the path the service-layer CRUD tests bypass. Medication is master data with
 * no patient compartment, so the negative case drops the required code instead
 * of a patient reference.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class MedicationFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/Medication';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    /** @var array<string, mixed> */
    private array $fhirFixture;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/medication.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
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
        $this->fixtureManager->removeMedicationFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPostCreatesMedication(): void
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

    public function testPutUpdatesMedication(): void
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

    public function testPostWithoutCodeReturnsError(): void
    {
        $invalid = $this->fhirFixture;
        unset($invalid['code']);
        $response = $this->testClient->post(self::RESOURCE_URL, $invalid);
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'POST without code should return 400. Body: ' . $response->getBody()->getContents()
        );
    }
}

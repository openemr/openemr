<?php

/**
 * FHIR Person API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/Person
 * so routing, scope enforcement, and serialization are exercised end to end —
 * the path the service-layer CRUD tests bypass. Person has no patient
 * compartment; the negative case strips the required NPI identifier.
 *
 * No PUT case: a Person created through the FHIR API has neither a username
 * nor an abook_type, and PractitionerService::search() (which backs the
 * post-update re-fetch) requires one of those. The update writes the row but
 * the re-fetch returns empty, so the HTTP layer maps the successful update to
 * 404. The service-layer CRUD test documents the same re-fetch quirk.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class PersonFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/Person';

    private ApiTestClient $testClient;
    private PractitionerFixtureManager $practitionerFixtureManager;
    /** @var array<string, mixed> */
    private array $fhirFixture;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->practitionerFixtureManager = new PractitionerFixtureManager();

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/persons.json'),
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
        // Shared cleanup with Practitioner — writes go to the same `users` table
        $this->practitionerFixtureManager->removePractitionerFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPostCreatesPerson(): void
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

    public function testPostWithoutNpiReturnsError(): void
    {
        $invalid = $this->fhirFixture;
        $invalid['identifier'] = [];
        $response = $this->testClient->post(self::RESOURCE_URL, $invalid);
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'POST without NPI identifier should return 400. Body: ' . $response->getBody()->getContents()
        );
    }
}

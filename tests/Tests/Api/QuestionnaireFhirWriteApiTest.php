<?php

/**
 * FHIR Questionnaire API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/Questionnaire
 * so routing, scope enforcement, and serialization are exercised end to end —
 * the path the service-layer CRUD tests bypass.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class QuestionnaireFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/Questionnaire';
    private const RESOURCE_TYPE = 'Questionnaire';
    private const TITLE_PREFIX = 'test-fixture Questionnaire';

    private ApiTestClient $testClient;
    /** @var array<string, mixed> */
    private array $fhirFixture;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/questionnaire.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        // the repository keys questionnaires by title, so each run gets its own
        $fixture['title'] = self::TITLE_PREFIX . ' ' . bin2hex(random_bytes(4));
        unset($fixture['id'], $fixture['url']);

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
            "DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_repository WHERE name LIKE ?)",
            [self::TITLE_PREFIX . '%']
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM questionnaire_repository WHERE name LIKE ?",
            [self::TITLE_PREFIX . '%']
        );
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPostCreatesQuestionnaire(): void
    {
        $response = $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture);
        $body = $response->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            'POST ' . self::RESOURCE_URL . ' should return 201. Body: ' . $body
        );
        $contents = json_decode($body, true);
        $this->assertIsArray($contents, 'Create response should be a JSON object. Body: ' . $body);
        $this->assertArrayHasKey('uuid', $contents, 'Create response should carry the new resource uuid');
        $this->assertIsString($contents['uuid']);
    }

    public function testPutUpdatesQuestionnaire(): void
    {
        $createResponse = $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture);
        $createBody = $createResponse->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $createResponse->getStatusCode(),
            'POST ' . self::RESOURCE_URL . ' should return 201. Body: ' . $createBody
        );
        $created = json_decode($createBody, true);
        $this->assertIsArray($created, 'Create response should be a JSON object. Body: ' . $createBody);
        $this->assertArrayHasKey('uuid', $created, 'Create response should carry the new resource id. Body: ' . $createBody);
        $id = $created['uuid'];
        $this->assertIsString($id);

        $updated = $this->fhirFixture;
        $updated['id'] = $id;
        $updated['status'] = 'retired';
        $putResponse = $this->testClient->put(self::RESOURCE_URL, $id, $updated);
        $putBody = $putResponse->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_OK,
            $putResponse->getStatusCode(),
            'PUT ' . self::RESOURCE_URL . '/{id} should return 200. Body: ' . $putBody
        );
        // FhirServiceBase::update() re-shapes the stored row through parseOpenEMRRecord()
        // to build this body. A service that leaves that on FhirServiceBaseEmptyTrait
        // answers a successful PUT with null, which the status code alone does not reveal.
        $putContents = json_decode($putBody, true);
        $this->assertIsArray(
            $putContents,
            'PUT should answer with the updated resource, not a null body. Body: ' . $putBody
        );
        $this->assertSame(self::RESOURCE_TYPE, $putContents['resourceType'] ?? null);
        $this->assertSame($id, $putContents['id'] ?? null);

        // GET /fhir/Questionnaire/{uuid} — the resource was list-only until this route landed,
        // so a client could create one and then have no way to read it back by id.
        $readResponse = $this->testClient->get(self::RESOURCE_URL . '/' . $id);
        $readBody = $readResponse->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_OK,
            $readResponse->getStatusCode(),
            'GET ' . self::RESOURCE_URL . '/{id} should return 200. Body: ' . $readBody
        );
        $readContents = json_decode($readBody, true);
        $this->assertIsArray($readContents, 'Body: ' . $readBody);
        $this->assertSame(self::RESOURCE_TYPE, $readContents['resourceType'] ?? null);
        $this->assertSame($id, $readContents['id'] ?? null);
    }

    public function testPostWithoutStatusReturnsError(): void
    {
        $invalid = $this->fhirFixture;
        unset($invalid['status']);
        $response = $this->testClient->post(self::RESOURCE_URL, $invalid);
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'POST without status should return 400. Body: ' . $response->getBody()->getContents()
        );
    }
}

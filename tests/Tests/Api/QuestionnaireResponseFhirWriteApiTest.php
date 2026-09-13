<?php

/**
 * FHIR QuestionnaireResponse API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/QuestionnaireResponse
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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class QuestionnaireResponseFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/QuestionnaireResponse';
    private const RESOURCE_TYPE = 'QuestionnaireResponse';
    private const QUESTIONNAIRE_TITLE_PREFIX = 'test-fixture QR Questionnaire';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    /** @var array<string, mixed> */
    private array $fhirFixture;
    private string $questionnaireUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $firstPatient = $patients[0];
        $this->assertIsArray($firstPatient);
        $patientRecord = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$firstPatient['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $this->questionnaireUuid = $this->installQuestionnaire();

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/questionnaire-response.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['questionnaire'] = 'Questionnaire/' . $this->questionnaireUuid;
        $fixture['subject'] = ['reference' => 'Patient/' . $patientUuid];
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
        // setUp() assigns testClient before it fetches a token and fixtureManager after, so a
        // failed token fetch leaves fixtureManager uninitialized. PHPUnit still runs tearDown()
        // after a failed setUp(), and touching a typed property before initialization raises an
        // Error that aborts the rest of the cleanup -- taking the OAuth client teardown below
        // with it and leaking a registered client per failed run.
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_response WHERE questionnaire_name LIKE ?)",
            [self::QUESTIONNAIRE_TITLE_PREFIX . '%']
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM questionnaire_response WHERE questionnaire_name LIKE ?",
            [self::QUESTIONNAIRE_TITLE_PREFIX . '%']
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_repository WHERE name LIKE ?)",
            [self::QUESTIONNAIRE_TITLE_PREFIX . '%']
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM questionnaire_repository WHERE name LIKE ?",
            [self::QUESTIONNAIRE_TITLE_PREFIX . '%']
        );
        if (isset($this->fixtureManager)) {
            $this->fixtureManager->removePatientFixtures();
        }
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPostCreatesQuestionnaireResponse(): void
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

    public function testPutUpdatesQuestionnaireResponse(): void
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
        $updated['status'] = 'amended';
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
    }

    public function testPostWithoutSubjectReturnsError(): void
    {
        $invalid = $this->fhirFixture;
        unset($invalid['subject']);
        $response = $this->testClient->post(self::RESOURCE_URL, $invalid);
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'POST without subject should return 400. Body: ' . $response->getBody()->getContents()
        );
    }

    /**
     * Installs a questionnaire in the repository and returns its uuid.
     */
    private function installQuestionnaire(): string
    {
        $questionnaireData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/questionnaire.json'),
            true
        );
        $this->assertIsArray($questionnaireData);
        $questionnaire = $questionnaireData[0];
        $this->assertIsArray($questionnaire);
        $questionnaire['title'] = self::QUESTIONNAIRE_TITLE_PREFIX . ' ' . bin2hex(random_bytes(4));
        unset($questionnaire['id'], $questionnaire['url']);

        $rowId = (new QuestionnaireService())->saveQuestionnaireResource($questionnaire);
        $this->assertNotEmpty($rowId);
        $binUuid = QueryUtils::fetchSingleValue('SELECT uuid FROM questionnaire_repository WHERE id = ?', 'uuid', [$rowId]);

        return UuidRegistry::uuidToString($binUuid);
    }
}

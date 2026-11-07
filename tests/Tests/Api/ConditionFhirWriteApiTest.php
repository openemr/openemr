<?php

/**
 * FHIR Condition API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/Condition
 * so routing, scope enforcement, and serialization are exercised end to end —
 * the path the service-layer CRUD tests bypass.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ConditionFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/Condition';
    private const RESOURCE_TYPE = 'Condition';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    /** @var list<string> */
    private array $auditResourceUuids = [];
    /** @var array<string, mixed> */
    private array $fhirFixture;
    private string $patientUuid;

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
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/condition.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
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
        foreach ($this->auditResourceUuids as $resourceUuid) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_write_audit` WHERE `resource_uuid` = ?',
                [$resourceUuid]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM lists WHERE type = 'medical_problem' AND comments LIKE 'test-fixture%'"
        );
        if (isset($this->fixtureManager)) {
            $this->fixtureManager->removePatientFixtures();
        }
        // cleanupClient() goes in finally. setAuthTokenOrFail() can fail after getClient()
        // has already registered the OAuth client, and id_token is still null at that point,
        // so cleanupRevokeAuth() posts a logout with no id_token_hint. If that request throws,
        // the registered client outlives the run and every rerun leaves another behind.
        try {
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
    }

    public function testPostCreatesCondition(): void
    {
        $response = $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture);
        $body = $response->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            'POST ' . self::RESOURCE_URL . ' should return 201. Body: ' . $body
        );
        $contents = $this->decodeJsonObject($body, 'Create response should be a JSON object. Body: ' . $body);
        $this->assertArrayHasKey('uuid', $contents, 'Create response should carry the new resource uuid');
        $resourceUuid = $this->stringFromArray($contents, 'uuid');
        $this->auditResourceUuids[] = $resourceUuid;

        $auditRow = $this->querySingleRow(
            'SELECT `client_id`, `resource_type`, `resource_uuid`, `action`, `scope`, `token_id`,
                `before_snapshot`, `after_snapshot`, `api_log_id`
             FROM `api_write_audit` WHERE `resource_uuid` = ? AND `action` = ?',
            [$resourceUuid, 'create']
        );
        $auditCount = $this->querySingleRow(
            'SELECT COUNT(*) AS `count` FROM `api_write_audit` WHERE `resource_uuid` = ? AND `action` = ?',
            [$resourceUuid, 'create']
        );
        $this->assertEquals(1, $auditCount['count']);
        $this->assertSame($this->testClient->getClientId(), $auditRow['client_id']);
        $this->assertSame(self::RESOURCE_TYPE, $auditRow['resource_type']);
        $this->assertSame($resourceUuid, $auditRow['resource_uuid']);
        $this->assertStringContainsString(self::RESOURCE_TYPE, $this->stringFromArray($auditRow, 'scope'));
        $this->assertNotSame('', $auditRow['token_id']);
        $this->assertNull($auditRow['before_snapshot']);
        if ($auditRow['after_snapshot'] !== null) {
            $this->assertJsonSnapshot($auditRow['after_snapshot']);
        }
        if ($auditRow['api_log_id'] !== null) {
            $linkedApiLog = $this->querySingleRow(
                'SELECT `id` FROM `api_log` WHERE `id` = ?',
                [$auditRow['api_log_id']]
            );
            $this->assertArrayHasKey('id', $linkedApiLog);
        }
    }

    public function testPutUpdatesCondition(): void
    {
        $createResponse = $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture);
        $createBody = $createResponse->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $createResponse->getStatusCode(),
            'POST ' . self::RESOURCE_URL . ' should return 201. Body: ' . $createBody
        );
        $created = $this->decodeJsonObject($createBody, 'Create response should be a JSON object. Body: ' . $createBody);
        $this->assertArrayHasKey('uuid', $created, 'Create response should carry the new resource id. Body: ' . $createBody);
        $id = $this->stringFromArray($created, 'uuid');
        $this->auditResourceUuids[] = $id;

        $updated = $this->fhirFixture;
        $updated['id'] = $id;
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

        $updateAudit = $this->querySingleRow(
            'SELECT `before_snapshot`, `after_snapshot` FROM `api_write_audit`
             WHERE `resource_uuid` = ? AND `action` = ?',
            [$id, 'update']
        );
        $updateAuditCount = $this->querySingleRow(
            'SELECT COUNT(*) AS `count` FROM `api_write_audit` WHERE `resource_uuid` = ? AND `action` = ?',
            [$id, 'update']
        );
        $this->assertEquals(1, $updateAuditCount['count']);
        if ($updateAudit['before_snapshot'] !== null) {
            $this->assertJsonSnapshot($updateAudit['before_snapshot']);
        }
        if ($updateAudit['after_snapshot'] !== null) {
            $this->assertJsonSnapshot($updateAudit['after_snapshot']);
        }
    }

    public function testPostWithoutSubjectReturnsError(): void
    {
        $auditCountBefore = $this->querySingleRow('SELECT COUNT(*) AS `count` FROM `api_write_audit`');
        $invalid = $this->fhirFixture;
        unset($invalid['subject']);
        $response = $this->testClient->post(self::RESOURCE_URL, $invalid);
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'POST without subject should return 400. Body: ' . $response->getBody()->getContents()
        );
        $auditCountAfter = $this->querySingleRow('SELECT COUNT(*) AS `count` FROM `api_write_audit`');
        $this->assertEquals($auditCountBefore['count'], $auditCountAfter['count']);
    }

    /**
     * @param list<mixed> $parameters
     * @return array<mixed>
     */
    private function querySingleRow(string $sql, array $parameters = []): array
    {
        $row = QueryUtils::querySingleRow($sql, $parameters);
        if (!is_array($row)) {
            self::fail('Expected the database query to return a row.');
        }
        return $row;
    }

    /**
     * @return array<mixed>
     */
    private function decodeJsonObject(string $json, string $message): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            self::fail($message);
        }
        return $decoded;
    }

    /**
     * @param array<mixed> $values
     */
    private function stringFromArray(array $values, string $key): string
    {
        $value = $values[$key] ?? null;
        if (!is_string($value)) {
            self::fail('Expected a string value for ' . $key . '.');
        }
        return $value;
    }

    private function assertJsonSnapshot(mixed $snapshot): void
    {
        if (!is_string($snapshot)) {
            self::fail('Expected an audit snapshot string.');
        }
        $this->decodeJsonObject($snapshot, 'Audit snapshot should be a JSON object.');
    }
}

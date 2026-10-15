<?php

/**
 * FHIR Task API tests: read and search.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for:
 *   GET /fhir/Task          (search, returns a Bundle)
 *   GET /fhir/Task/:uuid    (read one)
 *
 * There is no write route yet, so the tasks are seeded straight into the task
 * table and removed in tearDown, with two fixture patients.
 */
class TaskFhirApiTest extends TestCase
{
    private ApiTestClient $testClient;
    private ConditionFixtureManager $fixtures;

    /** @var array{uuid: string, pid: int} */
    private array $patient;

    /** @var array{uuid: string, pid: int} */
    private array $otherPatient;

    /** @var array<string, string> task uuids by name */
    private array $tasks = [];

    private string $serviceRequestUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtures = new ConditionFixtureManager();
        $this->patient = self::patientOf($this->fixtures->createTestPatient());
        $this->otherPatient = self::patientOf($this->fixtures->createTestPatient());

        // The focus only has to look like a reference; nothing joins on it.
        $this->serviceRequestUuid = UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());
        $this->tasks['referral'] = $this->insertTask($this->patient['pid'], [
            'status' => 'requested',
            'code' => 'fulfill',
            'description' => 'Referral to a food pantry',
            'focus_type' => 'ServiceRequest',
            'focus_uuid' => $this->serviceRequestUuid,
            'owner_type' => 'Organization',
            'requester_type' => 'Practitioner',
        ]);
        $this->tasks['questionnaire'] = $this->insertTask($this->patient['pid'], [
            'status' => 'completed',
            'code' => 'complete-questionnaire',
            'part_of_uuid' => $this->tasks['referral'],
        ]);
        $this->tasks['other'] = $this->insertTask($this->otherPatient['pid'], ['status' => 'requested']);
    }

    protected function tearDown(): void
    {
        if ($this->tasks !== []) {
            $uuids = array_values($this->tasks);
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM task WHERE uuid IN (' . implode(',', array_fill(0, count($uuids), '?')) . ')',
                array_map(UuidRegistry::uuidToBytes(...), $uuids)
            );
        }
        $this->fixtures->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * @param array<mixed> $patient
     * @return array{uuid: string, pid: int}
     */
    private static function patientOf(array $patient): array
    {
        $uuid = $patient['uuid'] ?? null;
        $pid = $patient['pid'] ?? null;
        if (!is_string($uuid) || !is_int($pid)) {
            throw new \RuntimeException('the fixture patient has no uuid or pid');
        }
        return ['uuid' => $uuid, 'pid' => $pid];
    }

    /**
     * @param array<string, string> $columns
     * @return string the task's uuid
     */
    private function insertTask(int $pid, array $columns): string
    {
        $uuid = UuidRegistry::getRegistryForTable('task')->createUuid();
        $row = $columns + ['intent' => 'order', 'authored_on' => '2026-10-15 09:00:00'];
        foreach (['focus_uuid', 'part_of_uuid'] as $uuidColumn) {
            if (isset($row[$uuidColumn])) {
                $row[$uuidColumn] = UuidRegistry::uuidToBytes($row[$uuidColumn]);
            }
        }
        foreach (['owner', 'requester'] as $reference) {
            if (isset($row[$reference . '_type'])) {
                $row[$reference . '_uuid'] = (new UuidRegistry(['disable_tracker' => true]))->createUuid();
            }
        }
        $row['uuid'] = $uuid;
        $row['patient_id'] = (string)$pid;
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO task (' . implode(', ', array_keys($row)) . ') VALUES (' . implode(', ', array_fill(0, count($row), '?')) . ')',
            array_values($row)
        );
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * @param array<string, string> $query
     * @return list<array<mixed>> the resources in the bundle
     */
    private function search(array $query): array
    {
        $result = $this->testClient->get('/apis/default/fhir/Task', $query);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());
        $bundle = json_decode((string)$result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertSame('Bundle', $bundle['resourceType'] ?? null);
        $entries = $bundle['entry'] ?? [];
        $this->assertIsArray($entries);
        $resources = [];
        foreach ($entries as $entry) {
            $resource = self::at($entry, 'resource');
            $this->assertIsArray($resource);
            $resources[] = $resource;
        }
        return $resources;
    }

    /**
     * @param list<array<mixed>> $resources
     * @return list<string>
     */
    private static function ids(array $resources): array
    {
        $ids = [];
        foreach ($resources as $resource) {
            $id = $resource['id'] ?? null;
            $ids[] = is_string($id) ? $id : '';
        }
        sort($ids);
        return $ids;
    }

    /** A value deep in decoded JSON, or null when any step is missing. */
    private static function at(mixed $value, string|int ...$path): mixed
    {
        foreach ($path as $step) {
            if (!is_array($value) || !array_key_exists($step, $value)) {
                return null;
            }
            $value = $value[$step];
        }
        return $value;
    }

    /**
     * @return array<mixed>
     */
    private function read(string $uuid): array
    {
        $result = $this->testClient->get('/apis/default/fhir/Task/' . $uuid);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());
        $task = json_decode((string)$result->getBody()->getContents(), true);
        $this->assertIsArray($task);
        return $task;
    }

    public function testSearchByPatientReturnsOnlyTheirTasks(): void
    {
        $expected = [$this->tasks['referral'], $this->tasks['questionnaire']];
        sort($expected);
        $this->assertSame($expected, self::ids($this->search(['patient' => $this->patient['uuid']])));
        $this->assertSame([$this->tasks['other']], self::ids($this->search(['patient' => $this->otherPatient['uuid']])));
    }

    public function testSearchByStatusFocusAndParent(): void
    {
        $this->assertSame(
            [$this->tasks['questionnaire']],
            self::ids($this->search(['patient' => $this->patient['uuid'], 'status' => 'completed']))
        );
        $this->assertSame(
            [$this->tasks['referral']],
            self::ids($this->search(['focus' => 'ServiceRequest/' . $this->serviceRequestUuid]))
        );
        $this->assertSame(
            [$this->tasks['questionnaire']],
            self::ids($this->search(['part-of' => 'Task/' . $this->tasks['referral']]))
        );
    }

    public function testReadOneMapsTheRecord(): void
    {
        $task = $this->read($this->tasks['referral']);

        $this->assertSame('Task', $task['resourceType'] ?? null);
        $this->assertSame($this->tasks['referral'], $task['id'] ?? null);
        $this->assertSame('requested', $task['status'] ?? null);
        $this->assertSame('order', $task['intent'] ?? null);
        $this->assertSame('Referral to a food pantry', $task['description'] ?? null);
        $this->assertSame(
            [['system' => 'http://hl7.org/fhir/CodeSystem/task-code', 'code' => 'fulfill', 'display' => 'fulfill']],
            self::at($task, 'code', 'coding')
        );
        $this->assertSame('Patient/' . $this->patient['uuid'], self::at($task, 'for', 'reference'));
        $this->assertSame('ServiceRequest/' . $this->serviceRequestUuid, self::at($task, 'focus', 'reference'));
        $owner = self::at($task, 'owner', 'reference');
        $this->assertIsString($owner);
        $this->assertStringStartsWith('Organization/', $owner);
        $requester = self::at($task, 'requester', 'reference');
        $this->assertIsString($requester);
        $this->assertStringStartsWith('Practitioner/', $requester);
        $this->assertArrayHasKey('authoredOn', $task);
        $this->assertArrayHasKey('lastModified', $task);
    }

    public function testReadOneShowsItsParent(): void
    {
        $task = $this->read($this->tasks['questionnaire']);
        $this->assertSame([['reference' => 'Task/' . $this->tasks['referral'], 'type' => 'Task']], $task['partOf'] ?? null);
        $this->assertSame('completed', $task['status'] ?? null);
    }

    public function testUnknownTaskIsNotFound(): void
    {
        $unknown = UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());
        $result = $this->testClient->get('/apis/default/fhir/Task/' . $unknown);
        $this->assertSame(Response::HTTP_NOT_FOUND, $result->getStatusCode());
    }
}

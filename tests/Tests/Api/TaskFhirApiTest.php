<?php

/**
 * FHIR Task API tests: read, search, create and update.
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
 *   POST /fhir/Task         (create)
 *   PUT /fhir/Task/:uuid    (update, with If-Match)
 *
 * The read tests' tasks are seeded straight into the task table; the write
 * tests make theirs through the API. All are removed in tearDown, with the
 * two fixture patients.
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

    public function testCountPagesTheSearch(): void
    {
        $result = $this->testClient->get('/apis/default/fhir/Task', ['patient' => $this->patient['uuid'], '_count' => '1']);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());
        $bundle = json_decode((string)$result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $entries = $bundle['entry'] ?? null;
        $this->assertIsArray($entries);
        // The generic controller's bundles carry only a self link, so there is no next link to check.
        $this->assertCount(1, $entries, '_count=1 returns one task of the two');

        $first = self::at($entries, 0, 'resource', 'id');
        $page2 = $this->search(['patient' => $this->patient['uuid'], '_count' => '1', '_offset' => '1']);
        $this->assertCount(1, $page2);
        $this->assertNotSame($first, $page2[0]['id'] ?? null, 'the second page holds the other task');

        // _offset on its own skips that many and returns the rest.
        $rest = $this->search(['patient' => $this->patient['uuid'], '_offset' => '1']);
        $this->assertCount(1, $rest, '_offset=1 skips one of the two');
        $this->assertSame($page2[0]['id'] ?? null, $rest[0]['id'] ?? null);
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

    /**
     * A Task body for one of the fixture patients.
     *
     * @param array<string, mixed> $changes
     * @return array<string, mixed>
     */
    private function body(array $changes = [], ?string $patientUuid = null): array
    {
        return array_merge([
            'resourceType' => 'Task',
            'status' => 'requested',
            'intent' => 'order',
            'code' => ['coding' => [['system' => 'http://hl7.org/fhir/CodeSystem/task-code', 'code' => 'fulfill']]],
            'description' => 'Referral to medically tailored meals',
            'for' => ['reference' => 'Patient/' . ($patientUuid ?? $this->patient['uuid'])],
            'focus' => ['reference' => 'ServiceRequest/' . $this->serviceRequestUuid],
        ], $changes);
    }

    /** A string from a decoded response, or '' when it isn't one. */
    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Creates a task through the API and keeps its uuid for tearDown.
     *
     * @param array<string, mixed> $body
     * @return array{0: int, 1: mixed, 2: string} status, decoded body, ETag
     */
    private function create(array $body): array
    {
        $result = $this->testClient->post('/apis/default/fhir/Task', $body);
        $json = json_decode((string)$result->getBody()->getContents(), true);
        $id = self::text(self::at($json, 'id'));
        if ($result->getStatusCode() === Response::HTTP_CREATED && $id !== '') {
            $this->tasks['created-' . count($this->tasks)] = $id;
        }
        return [$result->getStatusCode(), $json, $result->getHeaderLine('ETag')];
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     * @return array{0: int, 1: mixed, 2: string} status, decoded body, ETag
     */
    private function update(string $uuid, array $body, array $headers = []): array
    {
        $result = $this->testClient->put('/apis/default/fhir/Task', $uuid, $body + ['id' => $uuid], $headers);
        return [$result->getStatusCode(), json_decode((string)$result->getBody()->getContents(), true), $result->getHeaderLine('ETag')];
    }

    /**
     * The validation errors of a 400, by element.
     *
     * @return array<mixed>
     */
    private function errors(mixed $result): array
    {
        $errors = self::at($result, 'validationErrors');
        $this->assertIsArray($errors);
        return $errors;
    }

    public function testCreateReturnsTheSavedTaskAtVersionOne(): void
    {
        [$status, $task, $etag] = $this->create($this->body(['id' => 'chosen-by-client', 'lastModified' => '2001-01-01T00:00:00Z']));
        $this->assertSame(Response::HTTP_CREATED, $status, (string)json_encode($task));
        $this->assertSame('Task', self::at($task, 'resourceType'));
        $id = self::text(self::at($task, 'id'));
        $this->assertNotSame('chosen-by-client', $id, 'the server picks the id');
        $this->assertSame('1', self::at($task, 'meta', 'versionId'));
        // The ETag header carries the same version, but OpenEMR's Docker image removes ETag
        // headers ("Header unset ETag" in openemr.conf), so the body's versionId is what a client reads.
        $this->assertContains($etag, ['W/"1"', ''], 'when present, the version');
        $this->assertSame('requested', self::at($task, 'status'));
        $this->assertSame('Patient/' . $this->patient['uuid'], self::at($task, 'for', 'reference'));
        $this->assertSame('ServiceRequest/' . $this->serviceRequestUuid, self::at($task, 'focus', 'reference'));
        $this->assertNotSame('', self::text(self::at($task, 'authoredOn')), 'set by the server when the body leaves it out');
        $this->assertStringNotContainsString('2001', self::text(self::at($task, 'lastModified')), 'lastModified is the server\'s');

        $read = $this->testClient->get('/apis/default/fhir/Task/' . $id);
        $this->assertSame(Response::HTTP_OK, $read->getStatusCode());
        $this->assertContains($read->getHeaderLine('ETag'), ['W/"1"', '']);
    }

    public function testUpdateRaisesTheVersionAndHonoursIfMatch(): void
    {
        [, $task] = $this->create($this->body());
        $uuid = self::text(self::at($task, 'id'));

        [$status, $updated, $etag] = $this->update($uuid, $this->body(['status' => 'accepted']), ['If-Match' => 'W/"1"']);
        $this->assertSame(Response::HTTP_OK, $status, (string)json_encode($updated));
        $this->assertSame('accepted', self::at($updated, 'status'));
        $this->assertSame('2', self::at($updated, 'meta', 'versionId'));
        $this->assertContains($etag, ['W/"2"', '']);
        $this->assertSame(self::at($task, 'authoredOn'), self::at($updated, 'authoredOn'), 'authoredOn is kept');

        // Someone else's view of version 1 is now stale.
        [$status, $outcome] = $this->update($uuid, $this->body(['status' => 'in-progress']), ['If-Match' => 'W/"1"']);
        $this->assertSame(Response::HTTP_PRECONDITION_FAILED, $status);
        $this->assertSame('OperationOutcome', self::at($outcome, 'resourceType'));

        [$status] = $this->update($uuid, $this->body(['status' => 'in-progress']), ['If-Match' => 'version one']);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'If-Match has to be a version');

        // Without If-Match the update applies to whatever is there.
        [$status, $updated] = $this->update($uuid, $this->body(['status' => 'in-progress']));
        $this->assertSame(Response::HTTP_OK, $status);
        $this->assertSame('3', self::at($updated, 'meta', 'versionId'));
    }

    public function testATaskStaysWithItsPatient(): void
    {
        [, $task] = $this->create($this->body());
        $uuid = self::text(self::at($task, 'id'));
        [$status, $result] = $this->update($uuid, $this->body([], $this->otherPatient['uuid']));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status);
        $this->assertArrayHasKey('for', $this->errors($result));
        $read = $this->read($uuid);
        $this->assertSame('Patient/' . $this->patient['uuid'], self::at($read, 'for', 'reference'));
        $this->assertSame('1', self::at($read, 'meta', 'versionId'), 'nothing changed');
    }

    public function testAFinishedTaskCanOnlyBeMarkedEnteredInError(): void
    {
        [, $task] = $this->create($this->body(['status' => 'completed']));
        $uuid = self::text(self::at($task, 'id'));
        [$status, $result] = $this->update($uuid, $this->body(['status' => 'in-progress']));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status);
        $this->assertArrayHasKey('status', $this->errors($result));
        [$status] = $this->update($uuid, $this->body(['status' => 'entered-in-error']));
        $this->assertSame(Response::HTTP_OK, $status);
        [$status] = $this->update($uuid, $this->body(['status' => 'requested']));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status);
    }

    public function testReferencesMustBeOfTheTypesTheWorkflowsUse(): void
    {
        [$status, $result] = $this->create($this->body(['focus' => ['reference' => 'Patient/' . $this->patient['uuid']]]));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status);
        $this->assertArrayHasKey('focus', $this->errors($result));
        [$status, $result] = $this->create($this->body(['owner' => ['reference' => 'Device/' . $this->serviceRequestUuid]]));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status);
        $this->assertArrayHasKey('owner', $this->errors($result));
        [$status] = $this->create($this->body(['owner' => ['reference' => 'Organization/' . $this->serviceRequestUuid]]));
        $this->assertSame(Response::HTTP_CREATED, $status);
    }

    public function testAParentTaskMustBeForTheSamePatient(): void
    {
        $partOf = static fn(string $uuid): array => ['partOf' => [['reference' => 'Task/' . $uuid]]];
        [$status, $result] = $this->create($this->body($partOf($this->tasks['other'])));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'the other patient\'s task');
        $this->assertArrayHasKey('partOf', $this->errors($result));
        $unknown = UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());
        [$status] = $this->create($this->body($partOf($unknown)));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'no such task');
        [$status, $child] = $this->create($this->body($partOf($this->tasks['referral'])));
        $this->assertSame(Response::HTTP_CREATED, $status);
        $this->assertSame('Task/' . $this->tasks['referral'], self::at($child, 'partOf', 0, 'reference'));
        $childUuid = self::text(self::at($child, 'id'));
        [$status] = $this->update($childUuid, $this->body($partOf($childUuid)));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'not part of itself');
    }

    public function testSubjectSearchesByPatientLikePatient(): void
    {
        $bySubject = array_column($this->search(['subject' => 'Patient/' . $this->patient['uuid']]), 'id');
        $byPatient = array_column($this->search(['patient' => $this->patient['uuid']]), 'id');
        $this->assertContains($this->tasks['referral'], $bySubject);
        $this->assertEqualsCanonicalizing($byPatient, $bySubject);
        $this->assertNotContains($this->tasks['other'], $bySubject);
    }
}

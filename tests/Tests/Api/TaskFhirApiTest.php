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
use OpenEMR\Services\FHIR\FhirTaskService;
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

    private string $questionnaireUuid;
    private TaskReferenceFixtures $references;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtures = new ConditionFixtureManager();
        $this->patient = self::patientOf($this->fixtures->createTestPatient());
        $this->otherPatient = self::patientOf($this->fixtures->createTestPatient());

        // References only have to look like ones in the tasks put in the table here (a read doesn't
        // join on them). A body's references are looked up, so its Questionnaire focus is a real one.
        $this->serviceRequestUuid = UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());
        $this->references = new TaskReferenceFixtures($this->testClient);
        $this->questionnaireUuid = $this->references->questionnaire();
        $this->tasks['referral'] = $this->insertTask($this->patient['pid'], [
            'status' => 'requested',
            'code' => 'fulfill',
            'code_system' => FhirTaskService::TASK_CODE_SYSTEM,
            'description' => 'Referral to a food pantry',
            'focus_type' => 'ServiceRequest',
            'focus_uuid' => $this->serviceRequestUuid,
            'owner_type' => 'Organization',
            'requester_type' => 'Practitioner',
        ]);
        $this->tasks['questionnaire'] = $this->insertTask($this->patient['pid'], [
            'status' => 'completed',
            'code' => 'complete-questionnaire',
            'code_system' => 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp',
            'part_of_uuid' => $this->tasks['referral'],
        ]);
        $this->tasks['other'] = $this->insertTask($this->otherPatient['pid'], ['status' => 'requested']);
    }

    protected function tearDown(): void
    {
        if ($this->tasks !== []) {
            $uuids = array_values($this->tasks);
            foreach (['task_input', 'task_output'] as $table) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM $table WHERE task_id IN (SELECT id FROM task WHERE uuid IN (" . implode(',', array_fill(0, count($uuids), '?')) . '))',
                    array_map(UuidRegistry::uuidToBytes(...), $uuids)
                );
            }
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM task WHERE uuid IN (' . implode(',', array_fill(0, count($uuids), '?')) . ')',
                array_map(UuidRegistry::uuidToBytes(...), $uuids)
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'task' AND uuid IN (" . implode(',', array_fill(0, count($uuids), '?')) . ')',
                array_map(UuidRegistry::uuidToBytes(...), $uuids)
            );
        }
        if (isset($this->references)) {
            $this->references->remove();
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
        $this->assertSame(
            [['system' => 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp', 'code' => 'complete-questionnaire', 'display' => 'complete-questionnaire']],
            self::at($task, 'code', 'coding')
        );
    }

    public function testAQuestionnaireTaskCanBePutBackAsItWasRead(): void
    {
        $task = [];
        foreach ($this->read($this->tasks['questionnaire']) as $element => $value) {
            $this->assertIsString($element);
            $task[$element] = $value;
        }
        [$status, $updated] = $this->update($this->tasks['questionnaire'], $task);
        $this->assertSame(Response::HTTP_OK, $status, (string)json_encode($updated));
        $this->assertSame(self::at($task, 'code'), self::at($updated, 'code'));
        $this->assertSame('2', self::at($updated, 'meta', 'versionId'));
    }

    public function testAPatientOwnerMustBeTheTasksPatient(): void
    {
        [$status, $result] = $this->create($this->body(['owner' => ['reference' => 'Patient/' . $this->otherPatient['uuid']]]));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status);
        $this->assertSame(['owner'], array_keys($this->errors($result)));
        [$status, $task] = $this->create($this->body(['owner' => ['reference' => 'Patient/' . $this->patient['uuid']]]));
        $this->assertSame(Response::HTTP_CREATED, $status, (string)json_encode($task));
        [$status, $result] = $this->update(self::text(self::at($task, 'id')), $this->body(['owner' => ['reference' => 'Patient/' . $this->otherPatient['uuid']]]));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status);
        $this->assertSame(['owner'], array_keys($this->errors($result)));
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
            // A Questionnaire is outside the patient's record, so it only has to be on this server. A
            // ServiceRequest focus has to be the patient's own order (TaskInputOutputFhirApiTest).
            'focus' => ['reference' => 'Questionnaire/' . $this->questionnaireUuid],
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
        $this->assertSame('Questionnaire/' . $this->questionnaireUuid, self::at($task, 'focus', 'reference'));
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

        // A stale If-Match doesn't change that: the patient is checked before the version, so the
        // answer names the patient, not the task's version.
        [$status, $result] = $this->update($uuid, $this->body([], $this->otherPatient['uuid']), ['If-Match' => 'W/"9"']);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, (string)json_encode($result));
        $this->assertArrayHasKey('for', $this->errors($result));
    }

    public function testABadAuthoredOnIsNamedAndAnUpdateIgnoresIt(): void
    {
        [$status, $result] = $this->create($this->body(['authoredOn' => 'yesterday']));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status);
        $this->assertArrayHasKey('authoredOn', $this->errors($result), (string)json_encode($result));

        [, $task] = $this->create($this->body());
        $uuid = self::text(self::at($task, 'id'));
        [$status, $updated] = $this->update($uuid, $this->body(['status' => 'accepted', 'authoredOn' => 'yesterday']));
        $this->assertSame(Response::HTTP_OK, $status, (string)json_encode($updated));
        $this->assertSame(self::at($task, 'authoredOn'), self::at($updated, 'authoredOn'), 'the stored authoredOn is kept');
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
        [$status] = $this->create($this->body(['owner' => ['reference' => 'Organization/' . $this->references->organization()]]));
        $this->assertSame(Response::HTTP_CREATED, $status);
    }

    /**
     * References outside the patient's record have to be on this server: there's no compartment to
     * check them in, but a made-up one is refused, naming the element.
     */
    public function testReferencesOutsideTheRecordMustExist(): void
    {
        $unknown = UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());
        $cases = [
            'focus' => ['focus' => ['reference' => 'Questionnaire/' . $unknown]],
            'owner' => ['owner' => ['reference' => 'Organization/' . $unknown]],
            'requester' => ['requester' => ['reference' => 'Practitioner/' . $unknown]],
            'input[0]' => ['input' => [['type' => ['text' => 'Who to see'], 'valueReference' => ['reference' => 'PractitionerRole/' . $unknown]]]],
        ];
        foreach ($cases as $element => $changes) {
            [$status, $result] = $this->create($this->body($changes));
            $this->assertSame(Response::HTTP_BAD_REQUEST, $status, $element . ': ' . json_encode($result));
            $errors = $this->errors($result);
            $this->assertSame([$element], array_keys($errors), $element);
            $this->assertStringContainsString('on this server', self::text($errors[$element]), $element);
        }

        // The same references to records that exist are accepted.
        [$status, $result] = $this->create($this->body([
            'owner' => ['reference' => 'Organization/' . $this->references->organization()],
            'requester' => ['reference' => 'Practitioner/' . $this->references->practitioner()],
            'input' => [['type' => ['text' => 'Who to see'], 'valueReference' => ['reference' => 'PractitionerRole/' . $this->references->practitionerRole()]]],
        ]));
        $this->assertSame(Response::HTTP_CREATED, $status, (string)json_encode($result));
    }

    /**
     * A Practitioner reference only has to be a user here. FHIR's Practitioner search leaves out
     * users with no NPI, but core names them as Practitioners, so a task can name them too.
     */
    public function testAPractitionerWithNoNpiCanOwnATask(): void
    {
        $practitioner = 'Practitioner/' . $this->references->practitionerWithoutNpi();
        [$status, $result] = $this->create($this->body(['owner' => ['reference' => $practitioner], 'requester' => ['reference' => $practitioner]]));
        $this->assertSame(Response::HTTP_CREATED, $status, (string)json_encode($result));
        $read = $this->read(self::text(self::at($result, 'id')));
        $this->assertSame($practitioner, self::at($read, 'owner', 'reference'));
    }

    /**
     * An address book organization is a users row too, but FHIR serves it as an Organization, so a task
     * can name it as one and not as a Practitioner.
     */
    public function testAnAddressBookOrganizationIsAnOrganizationNotAPractitioner(): void
    {
        $uuid = $this->references->addressBookOrganization();
        [$status, $result] = $this->create($this->body(['owner' => ['reference' => 'Practitioner/' . $uuid]]));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, (string)json_encode($result));
        $this->assertSame(['owner'], array_keys($this->errors($result)));
        [$status, $result] = $this->create($this->body(['owner' => ['reference' => 'Organization/' . $uuid]]));
        $this->assertSame(Response::HTTP_CREATED, $status, (string)json_encode($result));
    }

    /**
     * The reference fixtures take away what saving their records wrote elsewhere too: saving a
     * questionnaire also assigns it to the dashboard (content_assignments).
     */
    public function testTheReferenceFixturesLeaveNothingBehind(): void
    {
        $count = static function (string $table): int {
            $n = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM `$table`", 'n');
            return is_numeric($n) ? (int)$n : -1;
        };
        $tables = ['content_assignments', 'questionnaire_repository', 'users', 'uuid_registry'];
        $before = array_map($count, $tables);
        $fixtures = new TaskReferenceFixtures($this->testClient);
        $fixtures->questionnaire();
        $fixtures->practitionerWithoutNpi();
        $fixtures->addressBookOrganization();
        $this->assertNotSame($before, array_map($count, $tables), 'the fixtures wrote rows');
        $fixtures->remove();
        $this->assertSame(array_combine($tables, $before), array_combine($tables, array_map($count, $tables)));
    }

    /**
     * Task.code is a code and its system, so a code search reads both, as a FHIR token does:
     * system|code needs both, a bare code matches in any system, |code only where none was recorded
     * (a write always records one, so |code finds no task written through the API).
     */
    public function testCodeSearchHonoursTheSystem(): void
    {
        $patient = ['patient' => $this->patient['uuid']];
        $questionnaire = $this->tasks['questionnaire'];
        $referral = $this->tasks['referral'];
        $temp = 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp';

        $this->assertSame([$questionnaire], self::ids($this->search($patient + ['code' => $temp . '|complete-questionnaire'])), 'system|code');
        $this->assertSame([], self::ids($this->search($patient + ['code' => 'http://hl7.org/fhir/CodeSystem/task-code|complete-questionnaire'])), 'the code in another system');
        $this->assertSame([$questionnaire], self::ids($this->search($patient + ['code' => 'complete-questionnaire'])), 'a bare code, any system');
        $this->assertSame([], self::ids($this->search($patient + ['code' => '|complete-questionnaire'])), '|code: it has a system');
        $this->assertSame([$referral], self::ids($this->search($patient + ['code' => FhirTaskService::TASK_CODE_SYSTEM . '|fulfill'])), 'system|code, as the task reads back');
        $this->assertSame([], self::ids($this->search($patient + ['code' => '|fulfill'])), '|code: it has a system');
        $this->assertSame([$referral], self::ids($this->search($patient + ['code' => 'fulfill'])), 'a bare code, any system');
    }

    /**
     * A modifier on code is the search's own: code:missing=true finds the tasks with no code.
     */
    public function testACodeSearchKeepsItsModifier(): void
    {
        $this->assertSame([$this->tasks['other']], self::ids($this->search(['patient' => $this->otherPatient['uuid'], 'code:missing' => 'true'])), 'a task with no code');
        $this->assertSame([], self::ids($this->search(['patient' => $this->patient['uuid'], 'code:missing' => 'true'])), 'every task has a code');
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

    public function testAParentChainCannotLoop(): void
    {
        $partOf = static fn(string $uuid): array => ['partOf' => [['reference' => 'Task/' . $uuid]]];
        [, $a] = $this->create($this->body());
        $aUuid = self::text(self::at($a, 'id'));
        [, $b] = $this->create($this->body($partOf($aUuid)));
        $bUuid = self::text(self::at($b, 'id'));

        // A part of B, when B is part of A.
        [$status, $result] = $this->update($aUuid, $this->body($partOf($bUuid)));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'a two-task loop');
        $this->assertArrayHasKey('partOf', $this->errors($result));

        // A part of C, when C is part of B and B part of A.
        [, $c] = $this->create($this->body($partOf($bUuid)));
        $cUuid = self::text(self::at($c, 'id'));
        [$status, $result] = $this->update($aUuid, $this->body($partOf($cUuid)));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'a three-task loop');
        $this->assertArrayHasKey('partOf', $this->errors($result));
        $this->assertSame('1', self::at($this->read($aUuid), 'meta', 'versionId'), 'nothing changed');

        // A parent outside the chain is still fine.
        [$status] = $this->update($aUuid, $this->body($partOf($this->tasks['referral'])));
        $this->assertSame(Response::HTTP_OK, $status);
    }

    public function testSubjectSearchesByPatientLikePatient(): void
    {
        $bySubject = array_column($this->search(['subject' => 'Patient/' . $this->patient['uuid']]), 'id');
        $byPatient = array_column($this->search(['patient' => $this->patient['uuid']]), 'id');
        $this->assertContains($this->tasks['referral'], $bySubject);
        $this->assertEqualsCanonicalizing($byPatient, $bySubject);
        $this->assertNotContains($this->tasks['other'], $bySubject);
    }

    public function testUpdatingAMalformedOrUnknownIdIsNotFound(): void
    {
        // With If-Match, the service finds no task before it compares versions.
        foreach (['abc', UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid())] as $id) {
            [$status] = $this->update($id, $this->body(), ['If-Match' => 'W/"1"']);
            $this->assertSame(Response::HTTP_NOT_FOUND, $status, $id);
            [$status] = $this->update($id, $this->body());
            $this->assertSame(Response::HTTP_NOT_FOUND, $status, $id . ' without If-Match');
        }
    }
}

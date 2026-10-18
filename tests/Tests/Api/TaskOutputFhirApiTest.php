<?php

/**
 * FHIR Task outputs through the API (part 3 of #14521): what the work produced,
 * as references to a QuestionnaireResponse, an Observation or a Procedure for
 * the task's patient. Each is a real record made for the test: a response
 * posted through the QuestionnaireResponse API, a heart rate posted through the
 * Observation API, and a procedure order from the fixtures.
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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\ProcedureOrderFixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskOutputFhirApiTest extends TestCase
{
    private const SDC_TEMP = 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp';
    private const SDOHCC_TEMP = 'http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes';

    private ApiTestClient $testClient;

    private ProcedureOrderFixtureManager $orders;

    private string $patientUuid;

    private string $otherPatientUuid;

    private string $questionnaireUuid;

    /** This run's questionnaire title, so tearDown deletes this run's responses and no one else's. */
    private string $questionnaireTitle;

    private string $encounterUuid;

    private string $responseUuid;

    private string $otherResponseUuid;

    private string $observationUuid;

    private string $procedureUuid;

    /** @var list<string> */
    private array $tasks = [];

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        // The orders bring the patients with them. A FHIR Procedure is a procedure order.
        $this->orders = new ProcedureOrderFixtureManager();
        $this->orders->installFixtures();
        $orderId = $this->orders->getInstalledOrderIds()[0] ?? null;
        $order = QueryUtils::querySingleRow(
            'SELECT pd.uuid AS puuid, pd.pid FROM procedure_order po JOIN patient_data pd ON pd.pid = po.patient_id WHERE po.procedure_order_id = ?',
            [$orderId]
        );
        $this->assertIsArray($order);
        $this->assertIsString($order['puuid']);
        $this->patientUuid = UuidRegistry::uuidToString($order['puuid']);
        // The fixture orders have no uuid until the Procedure read gives them one.
        $procedures = $this->testClient->get('/apis/default/fhir/Procedure', ['patient' => $this->patientUuid]);
        $bundle = json_decode((string)$procedures->getBody()->getContents(), true);
        $this->procedureUuid = self::text(self::at($bundle, 'entry', 0, 'resource', 'id'));
        $this->assertNotSame('', $this->procedureUuid, 'a Procedure for the patient: ' . json_encode($bundle));
        $other = QueryUtils::fetchSingleValue(
            "SELECT uuid FROM patient_data WHERE pubpid LIKE 'test-fixture%' AND pid <> ? LIMIT 1",
            'uuid',
            [$order['pid']]
        );
        $this->assertIsString($other, 'a second fixture patient');
        $this->otherPatientUuid = UuidRegistry::uuidToString($other);

        $this->questionnaireUuid = $this->installQuestionnaire();
        $this->responseUuid = $this->postResponse($this->patientUuid);
        $this->otherResponseUuid = $this->postResponse($this->otherPatientUuid);
        $this->encounterUuid = $this->insertEncounter();
        $this->observationUuid = $this->postHeartRate();
    }

    protected function tearDown(): void
    {
        if ($this->tasks !== []) {
            $bytes = array_map(UuidRegistry::uuidToBytes(...), $this->tasks);
            $in = implode(',', array_fill(0, count($bytes), '?'));
            QueryUtils::sqlStatementThrowException("DELETE FROM task_output WHERE task_id IN (SELECT id FROM task WHERE uuid IN ($in))", $bytes);
            QueryUtils::sqlStatementThrowException("DELETE FROM task WHERE uuid IN ($in)", $bytes);
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'task' AND uuid IN ($in)", $bytes);
        }
        if (isset($this->questionnaireTitle)) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_response WHERE questionnaire_name = ?)',
                [$this->questionnaireTitle]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM questionnaire_response WHERE questionnaire_name = ?', [$this->questionnaireTitle]);
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_repository WHERE name = ?)',
                [$this->questionnaireTitle]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM questionnaire_repository WHERE name = ?', [$this->questionnaireTitle]);
        }
        (new FixtureManager())->removeObservationFixtures();
        if (isset($this->encounterUuid)) {
            QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE uuid = ?', [UuidRegistry::uuidToBytes($this->encounterUuid)]);
        }
        if (isset($this->orders)) {
            $this->orders->removeFixtures();
        }
        try {
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
    }

    public function testEachKindOfOutputIsKeptAndReadBack(): void
    {
        $outputs = [
            self::outputTo('QuestionnaireResponse', $this->responseUuid, ['coding' => [['system' => self::SDC_TEMP, 'code' => 'questionnaire-response']]]),
            self::outputTo('Observation', $this->observationUuid, ['text' => 'Heart rate taken at the visit']),
            self::outputTo('Procedure', $this->procedureUuid, ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'resulting-activity']], 'text' => 'Service delivered']),
        ];
        [$status, $task] = $this->create($this->questionnaireTask(['output' => $outputs]));
        $this->assertSame(Response::HTTP_CREATED, $status, (string)json_encode($task));
        $this->assertSame($outputs, self::at($task, 'output'), 'as given, in order');
        $this->assertSame([['system' => self::SDC_TEMP, 'code' => 'complete-questionnaire', 'display' => 'complete-questionnaire']], self::at($task, 'code', 'coding'));

        $uuid = self::text(self::at($task, 'id'));
        $this->assertSame($outputs, self::at($this->read($uuid), 'output'), 'read');
        $found = $this->testClient->get('/apis/default/fhir/Task', ['_id' => $uuid]);
        $bundle = json_decode((string)$found->getBody()->getContents(), true);
        $this->assertSame($outputs, self::at($bundle, 'entry', 0, 'resource', 'output'), 'searched');
    }

    public function testAnOutputMustBeOnThisServerAndForTheTasksPatient(): void
    {
        $cases = [
            'the other patient\'s response' => self::outputTo('QuestionnaireResponse', $this->otherResponseUuid),
            'no such observation' => self::outputTo('Observation', UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid())),
            'a procedure named as an observation' => self::outputTo('Observation', $this->procedureUuid),
            'a response on another server' => ['type' => ['text' => 'Response'], 'valueReference' => ['reference' => 'https://example.org/fhir/QuestionnaireResponse/' . $this->responseUuid]],
        ];
        foreach ($cases as $case => $output) {
            [$status, $result] = $this->create($this->questionnaireTask(['output' => [self::outputTo('QuestionnaireResponse', $this->responseUuid), $output]]));
            $this->assertSame(Response::HTTP_BAD_REQUEST, $status, $case);
            $this->assertSame(['output[1]'], array_keys($this->errors($result)), $case);
        }
    }

    public function testAPutReplacesTheOutputs(): void
    {
        [, $task] = $this->create($this->questionnaireTask(['status' => 'in-progress', 'output' => [self::outputTo('QuestionnaireResponse', $this->responseUuid)]]));
        $uuid = self::text(self::at($task, 'id'));

        $replaced = [self::outputTo('Procedure', $this->procedureUuid), self::outputTo('Observation', $this->observationUuid)];
        [$status, $updated] = $this->update($uuid, $this->questionnaireTask(['status' => 'completed', 'output' => $replaced]));
        $this->assertSame(Response::HTTP_OK, $status, (string)json_encode($updated));
        $this->assertSame($replaced, self::at($this->read($uuid), 'output'));

        // A body without outputs leaves the task with none.
        [$status] = $this->update($uuid, $this->questionnaireTask(['status' => 'completed']));
        $this->assertSame(Response::HTTP_OK, $status);
        $this->assertArrayNotHasKey('output', $this->read($uuid));
        $this->assertSame(0, $this->storedOutputs($uuid));
    }

    public function testARefusedUpdateLeavesTheOutputs(): void
    {
        $kept = [self::outputTo('QuestionnaireResponse', $this->responseUuid)];
        [, $task] = $this->create($this->questionnaireTask(['status' => 'in-progress', 'output' => $kept]));
        $uuid = self::text(self::at($task, 'id'));

        [$status] = $this->update($uuid, $this->questionnaireTask(['output' => [self::outputTo('Procedure', $this->procedureUuid)]]), ['If-Match' => 'W/"7"']);
        $this->assertSame(Response::HTTP_PRECONDITION_FAILED, $status, 'a stale version');
        [$status] = $this->update($uuid, $this->questionnaireTask(['output' => [self::outputTo('QuestionnaireResponse', $this->otherResponseUuid)]]));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'another patient\'s response');

        $read = $this->read($uuid);
        $this->assertSame($kept, self::at($read, 'output'));
        $this->assertSame('1', self::at($read, 'meta', 'versionId'));
        $this->assertSame(1, $this->storedOutputs($uuid));
    }

    /**
     * The questionnaire workflow's task: the patient fills in a questionnaire, and the response is its output.
     *
     * @param array<string, mixed> $changes
     * @return array<string, mixed>
     */
    private function questionnaireTask(array $changes = []): array
    {
        return array_merge([
            'resourceType' => 'Task',
            'status' => 'completed',
            'intent' => 'order',
            'code' => ['coding' => [['system' => self::SDC_TEMP, 'code' => 'complete-questionnaire']]],
            'for' => ['reference' => 'Patient/' . $this->patientUuid],
            'focus' => ['reference' => 'Questionnaire/' . $this->questionnaireUuid],
            'owner' => ['reference' => 'Patient/' . $this->patientUuid],
        ], $changes);
    }

    /**
     * @param array<string, mixed> $type
     * @return array<string, mixed>
     */
    private static function outputTo(string $resourceType, string $uuid, array $type = ['text' => 'Result']): array
    {
        return ['type' => $type, 'valueReference' => ['reference' => $resourceType . '/' . $uuid, 'type' => $resourceType]];
    }

    private function storedOutputs(string $taskUuid): int
    {
        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS n FROM task_output WHERE task_id = (SELECT id FROM task WHERE uuid = ?)',
            'n',
            [UuidRegistry::uuidToBytes($taskUuid)]
        );
        return is_numeric($count) ? (int)$count : -1;
    }

    /**
     * @param array<string, mixed> $body
     * @return array{0: int, 1: mixed}
     */
    private function create(array $body): array
    {
        $result = $this->testClient->post('/apis/default/fhir/Task', $body);
        $json = json_decode((string)$result->getBody()->getContents(), true);
        $id = self::text(self::at($json, 'id'));
        if ($result->getStatusCode() === Response::HTTP_CREATED && $id !== '') {
            $this->tasks[] = $id;
        }
        return [$result->getStatusCode(), $json];
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     * @return array{0: int, 1: mixed}
     */
    private function update(string $uuid, array $body, array $headers = []): array
    {
        $result = $this->testClient->put('/apis/default/fhir/Task', $uuid, $body + ['id' => $uuid], $headers);
        return [$result->getStatusCode(), json_decode((string)$result->getBody()->getContents(), true)];
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

    /**
     * @return array<mixed>
     */
    private function errors(mixed $result): array
    {
        $errors = self::at($result, 'validationErrors');
        $this->assertIsArray($errors, (string)json_encode($result));
        return $errors;
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

    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * A decoded JSON object as the request body it was read from.
     *
     * @param array<mixed> $json
     * @return array<string, mixed>
     */
    private static function stringKeyed(array $json): array
    {
        $body = [];
        foreach ($json as $key => $value) {
            self::assertIsString($key);
            $body[$key] = $value;
        }
        return $body;
    }

    private function installQuestionnaire(): string
    {
        $data = json_decode((string)file_get_contents(__DIR__ . '/../Fixtures/FHIR/questionnaire.json'), true);
        $questionnaire = is_array($data) ? ($data[0] ?? null) : null;
        $this->assertIsArray($questionnaire);
        $this->questionnaireTitle = 'test-fixture Task output Questionnaire ' . bin2hex(random_bytes(4));
        $questionnaire['title'] = $this->questionnaireTitle;
        unset($questionnaire['id'], $questionnaire['url']);
        $rowId = (new QuestionnaireService())->saveQuestionnaireResource($questionnaire);
        $this->assertNotEmpty($rowId);
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM questionnaire_repository WHERE id = ?', 'uuid', [$rowId]);
        $this->assertIsString($uuid);
        return UuidRegistry::uuidToString($uuid);
    }

    /** Posts a response to this run's questionnaire for the patient and returns its id. */
    private function postResponse(string $patientUuid): string
    {
        $data = json_decode((string)file_get_contents(__DIR__ . '/../Fixtures/FHIR/questionnaire-response.json'), true);
        $response = is_array($data) ? ($data[0] ?? null) : null;
        $this->assertIsArray($response);
        $response['questionnaire'] = 'Questionnaire/' . $this->questionnaireUuid;
        $response['subject'] = ['reference' => 'Patient/' . $patientUuid];
        unset($response['id']);
        $result = $this->testClient->post('/apis/default/fhir/QuestionnaireResponse', self::stringKeyed($response));
        $body = (string)$result->getBody()->getContents();
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), $body);
        // The generic controller answers a create with the new record's row id and uuid.
        $uuid = self::text(self::at(json_decode($body, true), 'uuid'));
        $this->assertNotSame('', $uuid, $body);
        return $uuid;
    }

    /** Vitals are an encounter form, so the heart rate needs a real encounter for the patient. */
    private function insertEncounter(): string
    {
        $data = json_decode((string)file_get_contents(__DIR__ . '/../Fixtures/FHIR/encounter.json'), true);
        $encounter = is_array($data) ? ($data[0] ?? null) : null;
        $this->assertIsArray($encounter);
        $encounter['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $inserted = (new FhirEncounterService())->insert(new FHIREncounter($encounter));
        $this->assertTrue($inserted->isValid(), (string)json_encode($inserted->getValidationMessages()));
        $uuid = self::at($inserted->getData(), 0, 'euuid');
        $this->assertIsString($uuid);
        return $uuid;
    }

    private function postHeartRate(): string
    {
        $data = json_decode((string)file_get_contents(__DIR__ . '/../Fixtures/FHIR/observation.json'), true);
        $observation = is_array($data) ? ($data[1] ?? null) : null;
        $this->assertIsArray($observation);
        $observation['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $observation['encounter'] = ['reference' => 'Encounter/' . $this->encounterUuid];
        unset($observation['id']);
        $result = $this->testClient->post('/apis/default/fhir/Observation', self::stringKeyed($observation));
        $body = (string)$result->getBody()->getContents();
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), $body);
        // The generic controller answers a create with the new record's row id and uuid.
        $uuid = self::text(self::at(json_decode($body, true), 'uuid'));
        $this->assertNotSame('', $uuid, $body);
        return $uuid;
    }
}

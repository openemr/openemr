<?php

/**
 * FHIR Task inputs and outputs through the API (part 3 of #14521), as SDOH
 * Clinical Care's TaskForPatient and TaskForReferralManagement write them:
 * the questionnaire to fill in (canonical), its PDF, whom to contact; and what
 * came back, as a record or in words. The records are real ones made for the
 * test: responses posted through the QuestionnaireResponse API, a heart rate
 * through the Observation API, a parent through the RelatedPerson API, PDFs
 * filed in the patient's Documents the way Clinic Forms files them, and a
 * procedure order from the fixtures.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use Document;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Services\UserService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\ProcedureOrderFixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskInputOutputFhirApiTest extends TestCase
{
    private const SDC_TEMP = 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp';
    private const SDOHCC_TEMP = 'http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes';

    private ApiTestClient $testClient;

    private ProcedureOrderFixtureManager $orders;

    private string $patientUuid;

    private int $patientId;

    private string $otherPatientUuid;

    private int $otherPatientId;

    private string $questionnaireUuid;

    private string $questionnaireUrl;

    /** This run's questionnaire title, so tearDown deletes this run's responses and no one else's. */
    private string $questionnaireTitle;

    private string $encounterUuid;

    private string $responseUuid;

    private string $otherResponseUuid;

    private string $observationUuid;

    private string $procedureUuid;

    private string $parentUuid;

    private string $otherParentUuid;

    private string $pdfUuid;

    private string $otherPdfUuid;

    /** @var list<int> */
    private array $documents = [];

    /** @var list<string> */
    private array $documentFiles = [];

    private bool $hadDocumentStorageMethod = false;

    private mixed $previousDocumentStorageMethod = null;

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
        $this->assertIsNumeric($order['pid']);
        $this->patientUuid = UuidRegistry::uuidToString($order['puuid']);
        $this->patientId = (int)$order['pid'];
        $other = QueryUtils::querySingleRow("SELECT uuid, pid FROM patient_data WHERE pubpid LIKE 'test-fixture%' AND pid <> ? LIMIT 1", [$this->patientId]);
        $this->assertIsArray($other, 'a second fixture patient');
        $this->assertIsString($other['uuid']);
        $this->assertIsNumeric($other['pid']);
        $this->otherPatientUuid = UuidRegistry::uuidToString($other['uuid']);
        $this->otherPatientId = (int)$other['pid'];
        // The fixture orders have no uuid until the Procedure read gives them one.
        $procedures = $this->testClient->get('/apis/default/fhir/Procedure', ['patient' => $this->patientUuid]);
        $bundle = json_decode((string)$procedures->getBody()->getContents(), true);
        $this->procedureUuid = self::text(self::at($bundle, 'entry', 0, 'resource', 'id'));
        $this->assertNotSame('', $this->procedureUuid, 'a Procedure for the patient: ' . json_encode($bundle));

        $this->questionnaireUuid = $this->installQuestionnaire();
        $url = QueryUtils::fetchSingleValue('SELECT source_url FROM questionnaire_repository WHERE uuid = ?', 'source_url', [UuidRegistry::uuidToBytes($this->questionnaireUuid)]);
        $this->assertIsString($url);
        $this->questionnaireUrl = $url;
        $this->responseUuid = $this->postResponse($this->patientUuid);
        $this->otherResponseUuid = $this->postResponse($this->otherPatientUuid);
        $this->encounterUuid = $this->insertEncounter();
        $this->observationUuid = $this->postHeartRate();
        $this->parentUuid = $this->postRelatedPerson($this->patientUuid);
        $this->otherParentUuid = $this->postRelatedPerson($this->otherPatientUuid);

        $this->hadDocumentStorageMethod = array_key_exists('document_storage_method', $GLOBALS);
        $this->previousDocumentStorageMethod = $GLOBALS['document_storage_method'] ?? null;
        $GLOBALS['document_storage_method'] = Document::STORAGE_METHOD_FILESYSTEM;
        $this->pdfUuid = $this->fileDocument($this->patientId);
        $this->otherPdfUuid = $this->fileDocument($this->otherPatientId);
    }

    protected function tearDown(): void
    {
        if ($this->tasks !== []) {
            $bytes = array_map(UuidRegistry::uuidToBytes(...), $this->tasks);
            $in = implode(',', array_fill(0, count($bytes), '?'));
            foreach (['task_input', 'task_output'] as $table) {
                QueryUtils::sqlStatementThrowException("DELETE FROM $table WHERE task_id IN (SELECT id FROM task WHERE uuid IN ($in))", $bytes);
            }
            QueryUtils::sqlStatementThrowException("DELETE FROM task WHERE uuid IN ($in)", $bytes);
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'task' AND uuid IN ($in)", $bytes);
        }
        foreach ($this->documents as $documentId) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'documents' AND uuid IN (SELECT uuid FROM documents WHERE id = ?)", [$documentId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM categories_to_documents WHERE document_id = ?', [$documentId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM documents WHERE id = ?', [$documentId]);
        }
        foreach ($this->documentFiles as $file) {
            if (is_file($file)) {
                unlink($file);
                // createDocument() made a directory for the fixture patient; drop it once empty.
                $directory = dirname($file);
                if (scandir($directory) === ['.', '..']) {
                    rmdir($directory);
                }
            }
        }
        if ($this->hadDocumentStorageMethod) {
            $GLOBALS['document_storage_method'] = $this->previousDocumentStorageMethod;
        } else {
            unset($GLOBALS['document_storage_method']);
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
        $fixtures = new FixtureManager();
        $fixtures->removeObservationFixtures();
        $fixtures->removeRelatedPersonFixtures();
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
            self::outputTo('DocumentReference', $this->pdfUuid, ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'questionnaire-pdf-completed']]]),
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

    public function testThePatientsQuestionnaireTaskAsSdohClinicalCareWritesIt(): void
    {
        // TaskForPatient: the questionnaire as a canonical input, with its PDF, its address and
        // its category; owned by the patient's mother, who fills it in for them.
        $inputs = [
            ['type' => ['coding' => [['system' => self::SDC_TEMP, 'code' => 'questionnaire']]], 'valueCanonical' => $this->questionnaireUrl],
            ['type' => ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'questionnaire-pdf']]], 'valueReference' => ['reference' => 'DocumentReference/' . $this->pdfUuid, 'type' => 'DocumentReference']],
            ['type' => ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'questionnaire-url']]], 'valueUrl' => 'https://example.org/forms/phq-9'],
            ['type' => ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'questionnaire-category']]], 'valueCodeableConcept' => ['coding' => [['system' => 'http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'code' => 'risk-questionnaire']]]],
        ];
        $outputs = [
            self::outputTo('QuestionnaireResponse', $this->responseUuid, ['coding' => [['system' => self::SDC_TEMP, 'code' => 'questionnaire-response']]]),
            self::outputTo('DocumentReference', $this->pdfUuid, ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'questionnaire-pdf-completed']]]),
        ];
        $body = $this->questionnaireTask(['owner' => ['reference' => 'RelatedPerson/' . $this->parentUuid], 'input' => $inputs, 'output' => $outputs]);
        unset($body['focus']);
        [$status, $task] = $this->create($body);
        $this->assertSame(Response::HTTP_CREATED, $status, (string)json_encode($task));
        $read = $this->read(self::text(self::at($task, 'id')));
        $this->assertSame($inputs, self::at($read, 'input'));
        $this->assertSame($outputs, self::at($read, 'output'));
        $this->assertSame('RelatedPerson/' . $this->parentUuid, self::at($read, 'owner', 'reference'));
    }

    public function testAMakeContactTaskKeepsTheContactsAndTheOneChosen(): void
    {
        $food = 'Organization/' . UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());
        $inputs = [['type' => ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'contact-entity']]], 'valueReference' => ['reference' => $food, 'type' => 'Organization']]];
        $outputs = [
            ['type' => ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'chosen-contact']]], 'valueMarkdown' => 'Called the **food pantry** on Tuesday'],
            ['type' => ['text' => 'What was done'], 'valueCodeableConcept' => ['coding' => [['system' => 'http://example.org/fhir/CodeSystem/activities', 'code' => 'food-delivered']], 'text' => 'Food delivered']],
        ];
        $body = $this->questionnaireTask([
            'status' => 'completed',
            'code' => ['coding' => [['system' => self::SDOHCC_TEMP, 'code' => 'make-contact']]],
            'input' => $inputs,
            'output' => $outputs,
        ]);
        unset($body['focus']);
        [$status, $task] = $this->create($body);
        $this->assertSame(Response::HTTP_CREATED, $status, (string)json_encode($task));
        $read = $this->read(self::text(self::at($task, 'id')));
        $this->assertSame($inputs, self::at($read, 'input'));
        $this->assertSame($outputs, self::at($read, 'output'));
        $this->assertSame(self::SDOHCC_TEMP, self::at($read, 'code', 'coding', 0, 'system'));
    }

    public function testWhatATaskNamesInTheRecordMustBeTheTasksPatients(): void
    {
        // Each list starts with one the task may name, so the fault is the second.
        $good = ['input' => self::outputTo('DocumentReference', $this->pdfUuid), 'output' => self::outputTo('QuestionnaireResponse', $this->responseUuid)];
        $cases = [
            'the other patient\'s response' => ['output', self::outputTo('QuestionnaireResponse', $this->otherResponseUuid)],
            'no such observation' => ['output', self::outputTo('Observation', UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid()))],
            'a procedure named as an observation' => ['output', self::outputTo('Observation', $this->procedureUuid)],
            'the other patient\'s PDF' => ['output', self::outputTo('DocumentReference', $this->otherPdfUuid)],
            'the other patient\'s PDF to fill in' => ['input', self::outputTo('DocumentReference', $this->otherPdfUuid)],
            'a response on another server' => ['output', ['type' => ['text' => 'Response'], 'valueReference' => ['reference' => 'https://example.org/fhir/QuestionnaireResponse/' . $this->responseUuid]]],
        ];
        foreach ($cases as $case => [$element, $entry]) {
            [$status, $result] = $this->create($this->questionnaireTask([$element => [$good[$element], $entry]]));
            $this->assertSame(Response::HTTP_BAD_REQUEST, $status, $case);
            $this->assertSame([$element . '[1]'], array_keys($this->errors($result)), $case);
        }
        [$status, $result] = $this->create($this->questionnaireTask(['owner' => ['reference' => 'RelatedPerson/' . $this->otherParentUuid]]));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'the other patient\'s mother');
        $this->assertSame(['owner'], array_keys($this->errors($result)));
    }

    public function testAPutReplacesTheInputsAndOutputs(): void
    {
        $canonical = ['type' => ['coding' => [['system' => self::SDC_TEMP, 'code' => 'questionnaire']]], 'valueCanonical' => $this->questionnaireUrl];
        [, $task] = $this->create($this->questionnaireTask([
            'status' => 'in-progress',
            'input' => [$canonical],
            'output' => [self::outputTo('QuestionnaireResponse', $this->responseUuid)],
        ]));
        $uuid = self::text(self::at($task, 'id'));

        $inputs = [self::outputTo('DocumentReference', $this->pdfUuid), $canonical];
        $outputs = [self::outputTo('Procedure', $this->procedureUuid), self::outputTo('Observation', $this->observationUuid)];
        [$status, $updated] = $this->update($uuid, $this->questionnaireTask(['status' => 'completed', 'input' => $inputs, 'output' => $outputs]));
        $this->assertSame(Response::HTTP_OK, $status, (string)json_encode($updated));
        $read = $this->read($uuid);
        $this->assertSame($inputs, self::at($read, 'input'));
        $this->assertSame($outputs, self::at($read, 'output'));

        // A body without them leaves the task with none.
        [$status] = $this->update($uuid, $this->questionnaireTask(['status' => 'completed']));
        $this->assertSame(Response::HTTP_OK, $status);
        $read = $this->read($uuid);
        $this->assertArrayNotHasKey('input', $read);
        $this->assertArrayNotHasKey('output', $read);
        $this->assertSame([0, 0], [$this->stored('task_input', $uuid), $this->stored('task_output', $uuid)]);
    }

    public function testARefusedUpdateLeavesTheInputsAndOutputs(): void
    {
        $input = [['type' => ['text' => 'Instructions'], 'valueString' => 'Bring your glasses']];
        $output = [self::outputTo('QuestionnaireResponse', $this->responseUuid)];
        [, $task] = $this->create($this->questionnaireTask(['status' => 'in-progress', 'input' => $input, 'output' => $output]));
        $uuid = self::text(self::at($task, 'id'));

        [$status] = $this->update($uuid, $this->questionnaireTask(['output' => [self::outputTo('Procedure', $this->procedureUuid)]]), ['If-Match' => 'W/"7"']);
        $this->assertSame(Response::HTTP_PRECONDITION_FAILED, $status, 'a stale version');
        [$status] = $this->update($uuid, $this->questionnaireTask(['output' => [self::outputTo('QuestionnaireResponse', $this->otherResponseUuid)]]));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $status, 'another patient\'s response');

        $read = $this->read($uuid);
        $this->assertSame($input, self::at($read, 'input'));
        $this->assertSame($output, self::at($read, 'output'));
        $this->assertSame('1', self::at($read, 'meta', 'versionId'));
        $this->assertSame([1, 1], [$this->stored('task_input', $uuid), $this->stored('task_output', $uuid)]);
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
     * An input or output that references a record.
     *
     * @param array<string, mixed> $type
     * @return array<string, mixed>
     */
    private static function outputTo(string $resourceType, string $uuid, array $type = ['text' => 'Result']): array
    {
        return ['type' => $type, 'valueReference' => ['reference' => $resourceType . '/' . $uuid, 'type' => $resourceType]];
    }

    /** @param 'task_input'|'task_output' $table */
    private function stored(string $table, string $taskUuid): int
    {
        $count = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM $table WHERE task_id = (SELECT id FROM task WHERE uuid = ?)",
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
        $this->questionnaireTitle = 'test-fixture Task input Questionnaire ' . bin2hex(random_bytes(4));
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
        return $this->postFixture('QuestionnaireResponse', $response);
    }

    /** Posts the patient's mother and returns her id. */
    private function postRelatedPerson(string $patientUuid): string
    {
        $data = json_decode((string)file_get_contents(__DIR__ . '/../Fixtures/FHIR/related-person.json'), true);
        $person = is_array($data) ? ($data[0] ?? null) : null;
        $this->assertIsArray($person);
        $person['patient'] = ['reference' => 'Patient/' . $patientUuid];
        // Each patient's mother is her own person: the same name twice is refused as a duplicate.
        $person['name'] = [['use' => 'official', 'family' => 'test-fixture-Mother-' . bin2hex(random_bytes(3)), 'given' => ['test-fixture-Ana']]];
        unset($person['id']);
        return $this->postFixture('RelatedPerson', $person);
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
        return $this->postFixture('Observation', $observation);
    }

    /**
     * Posts a resource and returns its uuid: the generic controller answers a create with the new
     * record's row id and uuid.
     *
     * @param array<mixed> $resource
     */
    private function postFixture(string $type, array $resource): string
    {
        $result = $this->testClient->post('/apis/default/fhir/' . $type, self::stringKeyed($resource));
        $body = (string)$result->getBody()->getContents();
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), $body);
        $uuid = self::text(self::at(json_decode($body, true), 'uuid'));
        $this->assertNotSame('', $uuid, $body);
        return $uuid;
    }

    /** Files a PDF in the patient's Documents as Clinic Forms does, and returns its DocumentReference id. */
    private function fileDocument(int $pid): string
    {
        $user = (new UserService())->getSystemUser();
        $this->assertIsArray($user);
        $category = QueryUtils::fetchSingleValue("SELECT id FROM categories WHERE name = 'Medical Record'", 'id', []);
        $this->assertIsNumeric($category);
        $data = "%PDF-1.4\n% test-fixture Task form\n";
        $document = new Document();
        $error = $document->createDocument((string)$pid, (int)$category, 'test-fixture-task-form-' . uniqid() . '.pdf', 'application/pdf', $data, '', 1, (int)$user['id']);
        $this->assertSame('', $error);
        $id = $document->get_id();
        $this->assertIsNumeric($id);
        $this->documents[] = (int)$id;
        $file = $document->get_url_filepath();
        if (is_string($file)) {
            $this->documentFiles[] = $file;
        }
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM documents WHERE id = ?', 'uuid', [$id]);
        $this->assertIsString($uuid);
        return UuidRegistry::uuidToString($uuid);
    }
}

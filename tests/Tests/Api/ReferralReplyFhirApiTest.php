<?php

/**
 * A referral's reply and its Task (#14710): a reply recorded in the Counter-Referral section
 * completes the Task with the reply filed as its output, and the one who made the referral
 * hears when the receiving system accepts, rejects or completes the Task through the API.
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
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Services\TaskService;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\HttpFoundation\Response;

class ReferralReplyFhirApiTest extends TestCase
{
    private const FHIR_URL = '/apis/default/fhir/';

    private ApiTestClient $testClient;
    private ConditionFixtureManager $fixtures;
    private mixed $globalBefore;
    private bool $hadStorageMethod = false;
    private mixed $storageMethodBefore = null;

    /** @var array{uuid: string, pid: int} */
    private array $patient;

    /** @var array<string, array{uuid: string, id: int}> */
    private array $people = [];

    /** @var list<int> transaction ids */
    private array $transactions = [];

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->fixtures = new ConditionFixtureManager();
        $patient = $this->fixtures->createTestPatient();
        $this->assertIsString($patient['uuid'] ?? null);
        $this->assertIsInt($patient['pid'] ?? null);
        $this->patient = ['uuid' => $patient['uuid'], 'pid' => $patient['pid']];

        $this->globalBefore = OEGlobalsBag::getInstance()->get(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value);
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, '1');
        // The reply is filed in this process, so its document goes to the file system here.
        $this->hadStorageMethod = array_key_exists('document_storage_method', $GLOBALS);
        $this->storageMethodBefore = $GLOBALS['document_storage_method'] ?? null;
        $GLOBALS['document_storage_method'] = Document::STORAGE_METHOD_FILESYSTEM;

        $this->people['doctor'] = $this->insertPerson(['username' => 'zq-reply-referrer', 'fname' => 'Rae', 'lname' => 'Zq', 'npi' => '1245319599', 'authorized' => '1']);
        $this->people['pantry'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Pantry']);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, $this->globalBefore);
        foreach ($this->transactions as $id) {
            foreach (QueryUtils::fetchRecords("SELECT id FROM documents WHERE foreign_reference_table = 'transactions' AND foreign_reference_id = ?", [$id]) as $row) {
                if (!is_numeric($row['id'] ?? null)) {
                    continue;
                }
                $document = new Document((int)$row['id']);
                $file = $document->get_url_filepath();
                QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'documents' AND uuid IN (SELECT uuid FROM documents WHERE id = ?)", [$row['id']]);
                QueryUtils::sqlStatementThrowException('DELETE FROM categories_to_documents WHERE document_id = ?', [$row['id']]);
                QueryUtils::sqlStatementThrowException('DELETE FROM documents WHERE id = ?', [$row['id']]);
                if (is_string($file) && is_file($file)) {
                    unlink($file);
                    $directory = dirname($file);
                    if (scandir($directory) === ['.', '..']) {
                        rmdir($directory);
                    }
                }
            }
            $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$id]);
            if (is_string($uuid)) {
                QueryUtils::sqlStatementThrowException('DELETE FROM task_output WHERE task_id IN (SELECT id FROM task WHERE focus_uuid = ?)', [$uuid]);
                QueryUtils::sqlStatementThrowException('DELETE FROM task WHERE focus_uuid = ?', [$uuid]);
            }
            QueryUtils::sqlStatementThrowException('DELETE FROM lbt_data WHERE form_id = ?', [$id]);
            QueryUtils::sqlStatementThrowException('DELETE FROM transactions WHERE id = ?', [$id]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM pnotes WHERE pid = ?', [$this->patient['pid']]);
        foreach ($this->people as $person) {
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$person['id']]);
        }
        if ($this->hadStorageMethod) {
            $GLOBALS['document_storage_method'] = $this->storageMethodBefore;
        } else {
            unset($GLOBALS['document_storage_method']);
        }
        $this->fixtures->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testARecordedReplyCompletesTheTaskWithTheReplyAsItsOutput(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'Food insecurity']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_from' => 'Zq intake desk', 'reply_findings' => 'Enrolled in weekly delivery', 'reply_recommend' => 'Recheck in 3 months']);

        $this->afterSave($id);

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $outputs = self::at($task, 'output');
        $this->assertIsArray($outputs);
        $this->assertCount(1, $outputs);
        $this->assertSame([['system' => 'http://loinc.org', 'code' => '11488-4']], self::at($outputs, 0, 'type', 'coding'));
        $this->assertSame('Consult note', self::at($outputs, 0, 'type', 'text'));
        $reference = self::at($outputs, 0, 'valueReference', 'reference');
        $this->assertIsString($reference);
        $this->assertStringStartsWith('DocumentReference/', $reference);

        // The reply is a document in the patient's chart, linked to the referral, readable as a DocumentReference.
        $documentReference = $this->read('DocumentReference', substr($reference, strlen('DocumentReference/')));
        $this->assertSame('text/plain', self::at($documentReference, 'content', 0, 'attachment', 'contentType'));
        $documents = QueryUtils::fetchRecords("SELECT id, foreign_id FROM documents WHERE foreign_reference_table = 'transactions' AND foreign_reference_id = ?", [$id]);
        $this->assertCount(1, $documents);
        $this->assertEquals($this->patient['pid'], $documents[0]['foreign_id']);
        $documentId = $documents[0]['id'] ?? null;
        $this->assertIsNumeric($documentId);
        $text = (new Document((int)$documentId))->get_data();
        $this->assertIsString($text);
        $this->assertStringContainsString('Counter-Referral', $text);
        $this->assertStringContainsString('Findings: Enrolled in weekly delivery', $text);
        $this->assertStringContainsString('Recommendations: Recheck in 3 months', $text);
        $this->assertStringContainsString('Refer To: Zq Reply Pantry', $text);

        // Saving the referral again files nothing more.
        $this->afterSave($id);
        $this->assertCount(1, QueryUtils::fetchRecords("SELECT id FROM documents WHERE foreign_reference_table = 'transactions' AND foreign_reference_id = ?", [$id]));
        $outputsAgain = self::at($this->onlyTask($id), 'output');
        $this->assertIsArray($outputsAgain);
        $this->assertCount(1, $outputsAgain);
    }

    public function testAReplyLeavesATaskInAFinalStatusAsItIs(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $taskUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($taskUuid);
        (new TaskService())->update($taskUuid, ['status' => 'rejected'], null);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Seen elsewhere']);

        $this->afterSave($id);

        $this->assertSame('rejected', $this->onlyTask($id)['status'] ?? null);
        $this->assertSame([], QueryUtils::fetchRecords("SELECT id FROM documents WHERE foreign_reference_table = 'transactions' AND foreign_reference_id = ?", [$id]));
    }

    public function testNoReplyMeansNoChangeAndTheGlobalTurnsItOff(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null, 'no Reply Date yet');

        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, '0');
        $this->saveFields($id, ['reply_date' => '2026-10-18']);
        $this->afterSave($id);
        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null, 'the global is off');
    }

    public function testTheReferrerHearsWhenTheRecipientActsOnTheTask(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'Food insecurity']);
        $this->afterSave($id);
        $task = $this->onlyTask($id);
        $taskUuid = $task['id'] ?? null;
        $this->assertIsString($taskUuid);

        $this->assertSame(Response::HTTP_OK, $this->putStatus($task, 'accepted'));
        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'accepted'), 'the same status again');
        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'in-progress'), 'a status the referrer is not told about');
        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'completed'));

        $notes = QueryUtils::fetchRecords('SELECT title, assigned_to, body, message_status FROM pnotes WHERE pid = ? ORDER BY id', [$this->patient['pid']]);
        $this->assertCount(2, $notes, 'accepted, then completed');
        $bodies = [];
        foreach ($notes as $note) {
            $this->assertSame('Referral', $note['title']);
            $this->assertSame('zq-reply-referrer', $note['assigned_to']);
            $this->assertSame('New', $note['message_status']);
            $body = $note['body'] ?? null;
            $this->assertIsString($body);
            $this->assertStringContainsString('Referral to Zq Reply Pantry', $body);
            $this->assertStringContainsString('(Referral Date 2026-10-10)', $body);
            $bodies[] = $body;
        }
        $this->assertStringContainsString(': Accepted', $bodies[0]);
        $this->assertStringContainsString(': Completed', $bodies[1]);
    }

    public function testWithTheGlobalOffTheReferrerHearsNothing(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, '0');

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'accepted'));

        $this->assertSame([], QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]));
    }

    /**
     * The Task's update is committed before the referrer is told, so a notice that fails is logged
     * rather than thrown at the client whose PUT already succeeded.
     */
    public function testANoticeThatFailsIsLoggedNotThrown(): void
    {
        $logger = self::logger();
        $tasks = new class extends TaskService {
            public function getOne(string $uuid): ?array
            {
                throw new \RuntimeException('the database went away');
            }
        };

        (new ReferralTaskService(taskService: $tasks, logger: $logger))->taskStatusChanged('a-task-uuid', 'requested', 'accepted', null);

        $this->assertSame([['error', 'A referral\'s Task changed status but the referrer was not told']], $logger->records);
    }

    /**
     * A save that filed the reply and then lost the Task's update leaves the document: the next
     * save completes the Task with that document rather than filing a second one.
     */
    public function testAReplyFiledBeforeItsTaskUpdateFailedIsNotFiledAgain(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $failing = new class extends TaskService {
            public function update(string $uuid, array $fields, ?int $userId, ?int $expectedVersion = null, ?array $inputs = null, ?array $outputs = null): bool
            {
                throw new \RuntimeException('the Task could not be updated');
            }
        };

        $this->assertNull((new ReferralTaskService(taskService: $failing, logger: self::logger()))->afterSave($id, null));
        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null);
        $this->assertCount(1, $this->replyDocuments($id), 'the reply was filed before the update failed');

        $this->afterSave($id);

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $documents = $this->replyDocuments($id);
        $this->assertCount(1, $documents, 'no second copy');
        $outputs = self::at($task, 'output');
        $this->assertIsArray($outputs);
        $this->assertCount(1, $outputs);
        $documentUuid = QueryUtils::fetchSingleValue('SELECT uuid FROM documents WHERE id = ?', 'uuid', [$documents[0]]);
        $this->assertIsString($documentUuid);
        $this->assertSame('DocumentReference/' . UuidRegistry::uuidToString($documentUuid), self::at($outputs, 0, 'valueReference', 'reference'));
    }

    /**
     * A recipient that rejects the Task between the reply's read and its write keeps their
     * rejection: the reply doesn't write completed over it.
     */
    public function testARejectionMadeWhileTheReplyIsRecordedStands(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18']);

        $this->assertNull($this->replyRacing(['status' => 'rejected'])->recordReply($id, null));

        $this->assertSame('rejected', $this->onlyTask($id)['status'] ?? null);
    }

    /**
     * A recipient's change that leaves the Task open (a new output) is read again and kept: the
     * reply completes the Task alongside it.
     */
    public function testAnOutputAddedWhileTheReplyIsRecordedIsKept(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18']);
        $theirs = ['type_text' => 'Intake note', 'value_element' => 'valueString', 'value_text' => 'Seen at intake'];

        $this->assertIsString($this->replyRacing(['status' => 'in-progress'], [$theirs])->recordReply($id, null));

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $outputs = self::at($task, 'output');
        $this->assertIsArray($outputs);
        $this->assertCount(2, $outputs, 'theirs, then the reply');
        $this->assertSame('Seen at intake', self::at($outputs, 0, 'valueString'));
        $this->assertSame([['system' => 'http://loinc.org', 'code' => '11488-4']], self::at($outputs, 1, 'type', 'coding'));
    }

    /**
     * A reply that can't be filed (no Medical Record category here) leaves the Task open, so the
     * Task never reaches completed without the reply as its output.
     */
    public function testAReplyThatCannotBeFiledLeavesTheTaskOpen(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18']);
        $categories = QueryUtils::fetchTableColumn("SELECT id FROM categories WHERE name = 'Medical Record'", 'id');
        $this->assertNotSame([], $categories);
        $marks = implode(', ', array_fill(0, count($categories), '?'));
        QueryUtils::sqlStatementThrowException("UPDATE categories SET name = 'Zq Medical Record' WHERE id IN ($marks)", $categories);
        try {
            $this->assertNull((new ReferralTaskService(logger: self::logger()))->recordReply($id, null));
        } finally {
            QueryUtils::sqlStatementThrowException("UPDATE categories SET name = 'Medical Record' WHERE id IN ($marks)", $categories);
        }

        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null);
        $this->assertSame([], $this->replyDocuments($id));

        $this->afterSave($id);
        $this->assertSame('completed', $this->onlyTask($id)['status'] ?? null, 'the next save files it');
    }

    /**
     * A ReferralTaskService whose first write to the Task finds the recipient changed it just
     * before: the change is made at the version the reply read, then the reply's write goes ahead.
     *
     * @param array<string, string> $change
     * @param ?list<array<string, string>> $outputs the recipient's outputs, or null to leave them
     */
    private function replyRacing(array $change, ?array $outputs = null): ReferralTaskService
    {
        $tasks = new class ($change, $outputs) extends TaskService {
            private bool $raced = false;

            /**
             * @param array<string, string> $change
             * @param ?list<array<string, string>> $theirOutputs
             */
            public function __construct(private readonly array $change, private readonly ?array $theirOutputs)
            {
                parent::__construct();
            }

            public function update(string $uuid, array $fields, ?int $userId, ?int $expectedVersion = null, ?array $inputs = null, ?array $outputs = null): bool
            {
                if (!$this->raced) {
                    $this->raced = true;
                    parent::update($uuid, $this->change, null, null, null, $this->theirOutputs);
                }
                return parent::update($uuid, $fields, $userId, $expectedVersion, $inputs, $outputs);
            }
        };
        return new ReferralTaskService(taskService: $tasks, logger: self::logger());
    }

    /**
     * @return list<int> the ids of the replies filed for the referral
     */
    private function replyDocuments(int $transactionId): array
    {
        $ids = [];
        foreach (QueryUtils::fetchTableColumn("SELECT id FROM documents WHERE foreign_reference_table = 'transactions' AND foreign_reference_id = ?", 'id', [$transactionId]) as $id) {
            if (is_numeric($id)) {
                $ids[] = (int)$id;
            }
        }
        return $ids;
    }

    /**
     * @return AbstractLogger&object{records: list<array{mixed, string}>}
     */
    private static function logger(): AbstractLogger
    {
        return new class extends AbstractLogger {
            /** @var list<array{mixed, string}> level and message */
            public array $records = [];

            /** @param array<mixed> $context */
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string)$message];
            }
        };
    }

    private function afterSave(int $transactionId): void
    {
        (new ReferralTaskService())->afterSave($transactionId, null);
    }

    /**
     * Changes a Task's status through PUT /fhir/Task, as the receiving system would.
     *
     * @param array<mixed> $task
     */
    private function putStatus(array $task, string $status): int
    {
        $uuid = $task['id'] ?? null;
        $this->assertIsString($uuid);
        $body = [];
        foreach ($task as $element => $value) {
            if (is_string($element) && $element !== 'meta') {
                $body[$element] = $value;
            }
        }
        $body['status'] = $status;
        $response = $this->testClient->put(self::FHIR_URL . 'Task', $uuid, $body);
        return $response->getStatusCode();
    }

    /**
     * The referral's one Task, read through the API.
     *
     * @return array<mixed>
     * @phpstan-impure the Task changes between reads
     */
    private function onlyTask(int $transactionId): array
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$transactionId]);
        $this->assertIsString($uuid);
        $response = $this->testClient->get(self::FHIR_URL . 'Task', ['focus' => 'ServiceRequest/' . UuidRegistry::uuidToString($uuid)]);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $bundle = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($bundle);
        $entries = is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [];
        $this->assertCount(1, $entries);
        $task = self::at($entries, 0, 'resource');
        $this->assertIsArray($task);
        return $task;
    }

    /**
     * @return array<mixed>
     */
    private function read(string $type, string $uuid): array
    {
        $response = $this->testClient->get(self::FHIR_URL . $type . '/' . $uuid);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $resource = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($resource);
        return $resource;
    }

    /**
     * A referral as add_transaction.php saves a new one: no uuid, and only the fields with a value.
     *
     * @param array<string, int|string> $fields
     */
    private function saveReferral(array $fields): int
    {
        $id = (int)QueryUtils::sqlInsert(
            "INSERT INTO transactions SET title = 'LBTref', user = 'admin', groupname = 'Default', authorized = 1, date = NOW(), pid = ?",
            [$this->patient['pid']]
        );
        $this->transactions[] = $id;
        $this->saveFields($id, $fields);
        return $id;
    }

    /**
     * @param array<string, int|string> $fields
     */
    private function saveFields(int $transactionId, array $fields): void
    {
        foreach ($fields as $field => $value) {
            QueryUtils::sqlStatementThrowException('REPLACE INTO lbt_data SET field_value = ?, form_id = ?, field_id = ?', [(string)$value, $transactionId, $field]);
        }
    }

    /**
     * The value at a path of keys in a decoded resource, or null.
     *
     * @param array<mixed> $resource
     */
    private static function at(array $resource, string|int ...$path): mixed
    {
        $value = $resource;
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * @param array<string, string> $columns
     * @return array{uuid: string, id: int}
     */
    private function insertPerson(array $columns): array
    {
        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
        $row = $columns + ['username' => '', 'active' => '1', 'authorized' => '0'];
        $row['uuid'] = $uuid;
        $id = QueryUtils::sqlInsert(
            'INSERT INTO users (' . implode(', ', array_keys($row)) . ') VALUES (' . implode(', ', array_fill(0, count($row), '?')) . ')',
            array_values($row)
        );
        return ['uuid' => UuidRegistry::uuidToString($uuid), 'id' => (int)$id];
    }
}

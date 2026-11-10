<?php

/**
 * A referral's reply and its Task (#14710): a reply recorded in the Counter-Referral section
 * completes the Task with the reply filed as its output (a Reply Date the referral screen clears
 * again undoes that and makes a new Task), and the one who made the referral hears when the
 * receiving system accepts, rejects, completes, fails or cancels the Task through the API.
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
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\DirectMessaging\DirectMessageLogRepository;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Translation\TranslationCache;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTask;
use OpenEMR\Services\FHIR\FhirTaskService;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\ReferralDisplay;
use OpenEMR\Services\ReferralFormLoaded;
use OpenEMR\Services\ReferralNoticeOutcome;
use OpenEMR\Services\ReferralService;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Services\TaskService;
use OpenEMR\Services\UserService;
use OpenEMR\Services\Utils\DateFormatterUtils;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReferralReplyFhirApiTest extends TestCase
{
    private const FHIR_URL = '/apis/default/fhir/';

    private ApiTestClient $testClient;
    private ConditionFixtureManager $fixtures;
    /**
     * Each global a test set, as it was: in this process, and as the API's process reads it, from the
     * globals table (null when it had no row).
     *
     * @var array<string, array{mixed, mixed}>
     */
    private array $globalsBefore = [];
    private bool $hadStorageMethod = false;
    private mixed $storageMethodBefore = null;

    /** @var array{uuid: string, pid: int} */
    private array $patient;

    /** @var array<string, array{uuid: string, id: int}> */
    private array $people = [];

    /** @var list<int> transaction ids */
    private array $transactions = [];

    /** @var list<int> documents a test filed as a Direct reply's; tearDown() removes their links, and them with the referral's */
    private array $directDocuments = [];

    /** @var list<string> addresses a test's notices emailed, whose queued emails tearDown() removes */
    private array $emailsQueuedTo = [];

    /** @var list<string> the fields a test added to the referral's layout */
    private array $layoutFields = [];

    /** @var list<array{string, mixed, mixed}> the layout rows a test moved to another group: field, seq, the group it had */
    private array $movedLayoutFields = [];

    /** The language id useLanguage() gives the session: one no site has, whose translations the test sets. */
    private const TEST_LANGUAGE = 9999;

    /** @var ?array{bool, mixed} whether the session had a language before useLanguage(), and which */
    private ?array $languageBefore = null;

    /** @var ?array{bool, mixed} whether documentStoredRemotely was set before storeDocumentsRemotely(), and to what */
    private ?array $remoteStorageBefore = null;

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

        $this->setReferralTasks('1');
        // The reply is filed in this process, so its document goes to the file system here.
        $this->hadStorageMethod = array_key_exists('document_storage_method', $GLOBALS);
        $this->storageMethodBefore = $GLOBALS['document_storage_method'] ?? null;
        $GLOBALS['document_storage_method'] = Document::STORAGE_METHOD_FILESYSTEM;

        $this->people['doctor'] = $this->insertPerson(['username' => 'zq-reply-referrer', 'fname' => 'Rae', 'lname' => 'Zq', 'npi' => '1245319599', 'authorized' => '1']);
        $this->people['pantry'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Pantry']);
    }

    /**
     * Removes what each test made: notices, Direct log rows, queued emails, documents, Tasks, referrals and people.
     */
    protected function tearDown(): void
    {
        foreach ($this->movedLayoutFields as [$field, $seq, $group]) {
            QueryUtils::sqlStatementThrowException("UPDATE layout_options SET group_id = ? WHERE form_id = 'LBTref' AND field_id = ? AND seq = ?", [$group, $field, $seq]);
        }
        foreach ($this->layoutFields as $field) {
            QueryUtils::sqlStatementThrowException("DELETE FROM layout_options WHERE form_id = 'LBTref' AND field_id = ?", [$field]);
        }
        if ($this->remoteStorageBefore !== null) {
            [$had, $value] = $this->remoteStorageBefore;
            if ($had) {
                OEGlobalsBag::getInstance()->set('documentStoredRemotely', $value);
            } else {
                OEGlobalsBag::getInstance()->remove('documentStoredRemotely');
                unset($GLOBALS['documentStoredRemotely']);
            }
        }
        if ($this->languageBefore !== null) {
            [$had, $language] = $this->languageBefore;
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            if ($had) {
                $session->set('language_choice', $language);
            } else {
                $session->remove('language_choice');
            }
            TranslationCache::reset();
        }
        foreach ($this->globalsBefore as $name => [$before, $storedBefore]) {
            OEGlobalsBag::getInstance()->set($name, $before);
            if (is_scalar($storedBefore)) {
                QueryUtils::sqlStatementThrowException('UPDATE globals SET gl_value = ? WHERE gl_name = ? AND gl_index = 0', [(string) $storedBefore, $name]);
            } else {
                QueryUtils::sqlStatementThrowException('DELETE FROM globals WHERE gl_name = ? AND gl_index = 0', [$name]);
            }
        }
        foreach ($this->transactions as $id) {
            QueryUtils::sqlStatementThrowException('DELETE FROM referral_notice WHERE transaction_id = ?', [$id]);
            QueryUtils::sqlStatementThrowException('DELETE FROM direct_message_log WHERE referral_id = ?', [$id]);
        }
        foreach ($this->directDocuments as $documentId) {
            QueryUtils::sqlStatementThrowException('DELETE FROM gprelations WHERE type1 = 1 AND id1 = ?', [$documentId]);
        }
        foreach ($this->emailsQueuedTo as $address) {
            QueryUtils::sqlStatementThrowException('DELETE FROM email_queue WHERE recipient = ?', [$address]);
        }
        foreach ($this->transactions as $id) {
            foreach (QueryUtils::fetchRecords("SELECT id FROM documents WHERE foreign_reference_table = 'transactions' AND foreign_reference_id = ?", [$id]) as $row) {
                if (!is_numeric($row['id'] ?? null)) {
                    continue;
                }
                $document = new Document((int)$row['id']);
                $file = $document->get_url_filepath();
                QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'documents' AND uuid IN (SELECT uuid FROM documents WHERE id = ?)", [$row['id']]);
                // The stored file's name, which createDocument() registers as a drive uuid.
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE document_drive = 1 AND uuid IN (SELECT drive_uuid FROM documents WHERE id = ?)', [$row['id']]);
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
                QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'task' AND uuid IN (SELECT uuid FROM task WHERE focus_uuid = ?)", [$uuid]);
                QueryUtils::sqlStatementThrowException('DELETE FROM task_input WHERE task_id IN (SELECT id FROM task WHERE focus_uuid = ?)', [$uuid]);
                QueryUtils::sqlStatementThrowException('DELETE FROM task_output WHERE task_id IN (SELECT id FROM task WHERE focus_uuid = ?)', [$uuid]);
                QueryUtils::sqlStatementThrowException('DELETE FROM task WHERE focus_uuid = ?', [$uuid]);
                // The referral's own, which the service gave it on its first save.
                QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'transactions' AND uuid = ?", [$uuid]);
            }
            QueryUtils::sqlStatementThrowException('DELETE FROM lbt_data WHERE form_id = ?', [$id]);
            QueryUtils::sqlStatementThrowException('DELETE FROM transactions WHERE id = ?', [$id]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM pnotes WHERE pid = ?', [$this->patient['pid']]);
        foreach ($this->people as $person) {
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$person['id']]);
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'users' AND uuid = ?", [UuidRegistry::uuidToBytes($person['uuid'])]);
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
        // The seeded layout's titles, in its order, and Refer To's name as the address book has it. A
        // retry finds the reply it filed by its name, made from the reply's stored values, not by this text.
        $this->assertSame(
            "Counter-Referral\nReferral Date: 2026-10-10\nRefer To: Zq Reply Pantry\n\n"
            . "Reply Date: 2026-10-18\nReply From: Zq intake desk\nFindings: Enrolled in weekly delivery\nRecommendations: Recheck in 3 months\n",
            $text
        );

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

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function reasonsThatOnlyLookLikeTheServicesOwn(): array
    {
        return [
            'another case' => [strtolower(ReferralTaskService::REASON_SENT_ELSEWHERE)],
            'a trailing space' => [ReferralTaskService::REASON_SENT_ELSEWHERE . ' '],
            'an accent' => [str_replace('recipient', "r\u{00E9}cipient", ReferralTaskService::REASON_SENT_ELSEWHERE)],
        ];
    }

    /**
     * A recipient's cancellation whose reason only looks like one of the service's (another case, a
     * trailing space, an accent) is the recipient's own, which the table's collation would take for
     * the service's: the service counts it, so a save makes no new Task, and the referral's status
     * reads it too.
     */
    #[DataProvider('reasonsThatOnlyLookLikeTheServicesOwn')]
    public function testAReasonThatOnlyLooksLikeTheServicesOwnIsTheRecipients(string $reason): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $taskUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($taskUuid);
        // As the recipient's write stores it.
        (new TaskService())->update($taskUuid, ['status' => 'cancelled', 'status_reason' => $reason], null);

        $this->afterSave($id);

        $this->assertSame($reason, self::at($this->onlyTask($id), 'statusReason', 'text'), 'no new Task');
        $this->assertSame('cancelled', $this->taskStatus($id));
        $this->assertSame([$id => 'cancelled'], (new ReferralService(createMissingUuids: false))->getTaskStatuses([$id]));
    }

    /**
     * A Reply Date saved by mistake, then cleared: the reply completed the Task with the reply's own
     * reason, and the save that clears it marks that Task entered-in-error with the service's reason
     * and gives the recipient one new Task, which the referral's status then reads. The filed reply
     * stays in the chart and on the old Task, as a record, and a later save adds no Task.
     */
    public function testAClearedReplyDateUndoesTheTaskItCompletedAndMakesOneNewTask(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $replied = $this->onlyTask($id);
        $this->assertSame('completed', $replied['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_RECORDED, self::at($replied, 'statusReason', 'text'));
        $repliedUuid = $replied['id'] ?? null;
        $this->assertIsString($repliedUuid);

        // As the Transactions screen clears a field: its lbt_data row is deleted. Its form had loaded the date.
        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $this->saveFromForm($id, '2026-10-18', 'pantry');

        $undone = $this->read('Task', $repliedUuid);
        $this->assertSame('entered-in-error', $undone['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_CLEARED, self::at($undone, 'statusReason', 'text'));
        $this->assertStringContainsString("Findings: Enrolled\n", $this->outputText($undone, 0), 'the reply stays on it, as a record');
        $this->assertCount(1, $this->replyDocuments($id), 'and in the chart');
        $tasks = $this->tasksOf($id);
        $this->assertCount(2, $tasks, 'one new Task');
        $new = array_values(array_filter($tasks, static fn(array $task): bool => ($task['id'] ?? null) !== $repliedUuid));
        $this->assertCount(1, $new);
        $this->assertSame('requested', $new[0]['status'] ?? null);
        $this->assertSame('Organization/' . $this->people['pantry']['uuid'], self::at($new[0], 'owner', 'reference'));
        // The referral's status reads the new Task, wherever it's read.
        $referral = (new ReferralService(createMissingUuids: false))->getReferral($id);
        $this->assertIsArray($referral);
        $this->assertSame('requested', $referral['task_status'] ?? null);
        $this->assertSame([$id => 'requested'], (new ReferralService(createMissingUuids: false))->getTaskStatuses([$id]));
        $this->assertSame('active', $this->read('ServiceRequest', $this->referralUuid($id))['status'] ?? null);

        $this->afterSave($id);
        $this->assertCount(2, $this->tasksOf($id), 'a later save adds none');
    }

    /**
     * A cleared reply is undone even for a recipient FHIR no longer serves (their NPI cleared), who
     * can't be given a new Task: the referral then has no Task that decides its status, so it reads
     * open, not entered in error.
     */
    public function testAClearedReplyWithNoNewTaskLeavesTheReferralOpen(): void
    {
        $this->people['specialist'] = $this->insertPerson(['abook_type' => 'spe', 'fname' => 'Cara', 'lname' => 'Zq', 'npi' => '1234567893']);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['specialist']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Seen']);
        $this->afterSave($id);
        $this->assertSame('completed', $this->onlyTask($id)['status'] ?? null);
        QueryUtils::sqlStatementThrowException("UPDATE users SET npi = '' WHERE id = ?", [$this->people['specialist']['id']]);

        $this->saveFields($id, ['reply_date' => '']);
        $this->saveFromForm($id, '2026-10-18', 'specialist');

        $this->assertSame('entered-in-error', $this->onlyTask($id)['status'] ?? null, 'undone, and no new Task');
        $referral = (new ReferralService(createMissingUuids: false))->getReferral($id);
        $this->assertIsArray($referral);
        $this->assertNull($referral['task_status'] ?? null);
        $this->assertSame([$id => null], (new ReferralService(createMissingUuids: false))->getTaskStatuses([$id]));
        $this->assertSame('active', $this->read('ServiceRequest', $this->referralUuid($id))['status'] ?? null);
    }

    /**
     * A Task the recipient completed carries no reason of the reply's: a Reply Date recorded after it
     * leaves it as it is, and clearing the Reply Date doesn't undo it or make a new Task.
     */
    public function testAClearedReplyDateLeavesATaskTheRecipientCompleted(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'completed'));
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);

        $this->saveFields($id, ['reply_date' => '']);
        $this->saveFromForm($id, '2026-10-18', 'pantry');

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertNull(self::at($task, 'statusReason'));
    }

    /**
     * The Transactions screen deletes every field a save leaves blank, so a form loaded before another
     * user recorded the reply, and saved after, deletes the Reply Date. That form loaded none, so its
     * save undoes nothing: the Task stays completed by the reply, and no new Task is made. Nor does a
     * save through the REST API, which never writes the Reply Date and passes no form's.
     */
    public function testAStaleFormThatDeletesTheReplyDateUndoesNothing(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        // Another user records the reply.
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);

        // The form loaded before that saves its blank Reply Date: the row is deleted.
        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $this->saveFromForm($id, '', 'pantry');
        $this->afterSave($id);

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_RECORDED, self::at($task, 'statusReason', 'text'));
        $this->assertSame('completed', $this->taskStatus($id));
    }

    /**
     * A layout that makes the Reply Date read-only renders it disabled, so the form doesn't post it
     * and the screen deletes the stored date as a blank. Nobody cleared it, so the save undoes
     * nothing, though the form loaded the date.
     */
    public function testAReadOnlyReplyDateFieldUndoesNothing(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);

        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $this->saveFromForm($id, '2026-10-18', 'pantry', replyDatePosted: false);

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_RECORDED, self::at($task, 'statusReason', 'text'));
    }

    /**
     * Clearing the Reply Date in the same save that sends the referral to someone else undoes the
     * Task the reply completed, though it's the earlier recipient's: the new recipient gets a Task,
     * and when the referral comes back to the first, they get a new one too.
     */
    public function testAReplyClearedAsTheReferralGoesElsewhereIsUndone(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Clinic']);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $repliedUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($repliedUuid);

        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $this->saveFields($id, ['refer_to' => $this->people['clinic']['id']]);
        $this->saveFromForm($id, '2026-10-18', 'pantry');

        $undone = $this->read('Task', $repliedUuid);
        $this->assertSame('entered-in-error', $undone['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_CLEARED, self::at($undone, 'statusReason', 'text'));
        $this->assertSame('requested', $this->taskOf($id, 'clinic')['status'] ?? null);
        $this->assertSame('requested', $this->taskStatus($id));

        // Back to the pantry, from a form that loaded no Reply Date.
        $this->saveFields($id, ['refer_to' => $this->people['pantry']['id']]);
        $this->saveFromForm($id, '', 'clinic');

        $pantry = array_values(array_filter(
            $this->tasksOf($id),
            fn(array $task): bool => self::at($task, 'owner', 'reference') === 'Organization/' . $this->people['pantry']['uuid'] && ($task['id'] ?? null) !== $repliedUuid
        ));
        $this->assertCount(1, $pantry, 'the pantry gets a new Task');
        $this->assertSame('requested', $pantry[0]['status'] ?? null);
        $this->assertSame('cancelled', $this->taskOf($id, 'clinic')['status'] ?? null);
        $this->assertSame('requested', $this->taskStatus($id));
    }

    /**
     * Clearing the Reply Date in the same save that clears Refer To undoes the Task the reply
     * completed too: the referral then has no recipient, and no Task decides its status.
     */
    public function testAReplyClearedWithTheRecipientIsUndone(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);

        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id IN ('reply_date', 'refer_to')", [$id]);
        $this->saveFromForm($id, '2026-10-18', 'pantry');

        $task = $this->onlyTask($id);
        $this->assertSame('entered-in-error', $task['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_CLEARED, self::at($task, 'statusReason', 'text'));
        $this->assertNull($this->taskStatus($id));
    }

    /**
     * A Reply Date written over for another recipient is no longer the first recipient's reply. The
     * pantry replied, completing its Task; staff then sent the referral to the clinic and wrote the
     * clinic's Reply Date over the pantry's (the clinic had no Task, so nothing was completed).
     * Clearing that date from a form that showed the clinic finds no Task of the clinic's that a reply
     * completed: the log says nothing was undone, and the pantry's answer stands. The clinic gets a
     * Task, and a return to the pantry asks it nothing more.
     */
    public function testAReplyDateWrittenOverForAnotherRecipientLeavesTheFirstAnswer(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Clinic']);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $answeredUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($answeredUuid);
        $this->saveFields($id, ['refer_to' => $this->people['clinic']['id'], 'reply_date' => '2026-10-25', 'reply_findings' => 'Seen in clinic']);
        $this->afterSave($id);
        $this->assertCount(1, $this->tasksOf($id), 'the clinic has no Task');

        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $logger = self::logger();
        $this->saveFromForm($id, '2026-10-25', 'clinic', logger: $logger);

        $answered = $this->read('Task', $answeredUuid);
        $this->assertSame('completed', $answered['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_RECORDED, self::at($answered, 'statusReason', 'text'));
        $this->assertContains(['info', 'A referral\'s Reply Date was cleared, and the recipient its form showed has no Task of the referral\'s, so there was nothing to undo'], $logger->records);
        $this->assertSame('requested', $this->taskOf($id, 'clinic')['status'] ?? null);

        // Back to the pantry, from a form that showed no Reply Date.
        $this->saveFields($id, ['refer_to' => $this->people['pantry']['id']]);
        $this->saveFromForm($id, '', 'clinic');

        $this->assertSame('completed', $this->taskOf($id, 'pantry')['status'] ?? null, 'no new Task for the pantry');
        $this->assertSame('completed', $this->taskStatus($id));
    }

    /**
     * A recipient whose address book entry was deleted after their reply completed their Task can't
     * be found from the Refer To the form showed: clearing the Reply Date undoes nothing, the Task
     * stays completed, and the log says that Refer To no longer exists.
     */
    public function testAClearedReplyOfADeletedRecipientIsLoggedNotUndone(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $taskUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($taskUuid);
        QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$this->people['pantry']['id']]);

        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $logger = self::logger();
        $this->saveFromForm($id, '2026-10-18', 'pantry', logger: $logger);

        // Read as stored: the Task's owner is gone, so the API wouldn't serve it whole.
        $task = (new TaskService())->getOne($taskUuid);
        $this->assertIsArray($task);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_RECORDED, $task['status_reason'] ?? null);
        $this->assertContains(['warning', 'A referral\'s Reply Date was cleared but the Refer To its form showed no longer exists, so its reply\'s Task was not undone'], $logger->records);
    }

    /**
     * A Reply Date recorded on a referral that names no recipient completed no Task: clearing it from
     * a form that showed no Refer To undoes nothing, and the log says the form showed none.
     */
    public function testAClearedReplyFromAFormThatShowedNoRecipientIsLogged(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'reply_date' => '2026-10-18', 'body' => 'x']);
        $this->afterSave($id);

        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $logger = self::logger();
        $this->saveFromForm($id, '2026-10-18', null, logger: $logger);

        $this->assertSame([], $this->tasksOf($id));
        $this->assertContains(['warning', 'A referral\'s Reply Date was cleared but its form showed no Refer To, so no reply\'s Task was undone'], $logger->records);
    }

    /**
     * Clearing a Reply Date undoes the latest Task a reply completed for the recipient the form
     * showed, not an earlier one. The pantry's first reply completed its Task; a stale form deleted
     * that Reply Date, undoing nothing; an API client then made the pantry another Task of the
     * referral's kind, and a second reply completed it. Clearing the second Reply Date undoes the
     * second Task and leaves the first reply's.
     */
    public function testAClearedReplyDateUndoesTheLatestReplyOfTheRecipientTheFormShowed(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $firstUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($firstUuid);
        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $this->saveFromForm($id, '', 'pantry');
        $secondUuid = $this->postTaskFor($id, 'pantry');
        $this->saveFields($id, ['reply_date' => '2026-10-25', 'reply_findings' => 'Seen again']);
        $this->afterSave($id);
        $this->assertSame('completed', $this->read('Task', $secondUuid)['status'] ?? null, 'the second reply completed the second Task');

        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $this->saveFromForm($id, '2026-10-25', 'pantry');

        $second = $this->read('Task', $secondUuid);
        $this->assertSame('entered-in-error', $second['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_CLEARED, self::at($second, 'statusReason', 'text'));
        $first = $this->read('Task', $firstUuid);
        $this->assertSame('completed', $first['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_RECORDED, self::at($first, 'statusReason', 'text'));
    }

    /**
     * Undoing the reply names the Task's version it read: a recipient who marks the Task
     * entered-in-error themselves just before keeps their reason, their Task still counts, and the
     * save makes no new one.
     */
    public function testARecipientsChangeJustBeforeTheReplyIsUndoneStands(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '']);

        $this->replyRacing(['status' => 'entered-in-error', 'status_reason' => 'Recorded for the wrong patient'])->afterSave($id, null, new ReferralFormLoaded(true, $this->people['pantry']['id']));

        $task = $this->onlyTask($id);
        $this->assertSame('entered-in-error', $task['status'] ?? null);
        $this->assertSame('Recorded for the wrong patient', self::at($task, 'statusReason', 'text'));
    }

    /**
     * A reply cleared by mistake and then recorded again: the undone Task stays entered-in-error,
     * and the reply completes the recipient's new Task, with the reply already filed as its output.
     */
    public function testAReplyRecordedAgainAfterBeingClearedCompletesTheNewTask(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $firstUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($firstUuid);
        $filed = self::at($this->read('Task', $firstUuid), 'output', 0, 'valueReference', 'reference');
        $this->assertIsString($filed);
        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $this->saveFromForm($id, '2026-10-18', 'pantry');

        $this->saveFields($id, ['reply_date' => '2026-10-18']);
        $this->afterSave($id);

        $this->assertSame('entered-in-error', $this->read('Task', $firstUuid)['status'] ?? null);
        $second = array_values(array_filter($this->tasksOf($id), static fn(array $task): bool => ($task['id'] ?? null) !== $firstUuid));
        $this->assertCount(1, $second);
        $this->assertSame('completed', $second[0]['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_RECORDED, self::at($second[0], 'statusReason', 'text'));
        $this->assertSame($filed, self::at($second[0], 'output', 0, 'valueReference', 'reference'), 'the reply already filed');
        $this->assertCount(1, $this->replyDocuments($id));
        $this->assertSame('completed', $this->taskStatus($id));
    }

    /**
     * An undo that's skipped (here the save's Task step doesn't get the referral's lock) isn't
     * retried, since the Reply Date is already deleted: the Task stays completed by the reply.
     * Entering the Reply Date again leaves that Task as it is, and clearing it again from a form
     * that shows it undoes it.
     */
    public function testASkippedUndoIsMadeUpByEnteringTheReplyDateAgainAndClearingIt(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $taskUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($taskUuid);
        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $lockMissed = new class (new UserService()) extends ReferralTaskService {
            // As createForReferral() returns when the lock isn't acquired: before it undoes anything.
            public function createForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): ?string
            {
                return null;
            }
        };
        $lockMissed->createForSavedTransaction('LBTref', $id, null, new ReferralFormLoaded(true, $this->people['pantry']['id']));
        $this->assertSame('completed', $this->read('Task', $taskUuid)['status'] ?? null, 'not undone');

        $this->saveFields($id, ['reply_date' => '2026-10-18']);
        $this->afterSave($id);
        $this->assertSame('completed', $this->read('Task', $taskUuid)['status'] ?? null, 'entered again, it changes nothing');
        $this->assertCount(1, $this->tasksOf($id));
        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", [$id]);
        $this->saveFromForm($id, '2026-10-18', 'pantry');

        $undone = $this->read('Task', $taskUuid);
        $this->assertSame('entered-in-error', $undone['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_REPLY_CLEARED, self::at($undone, 'statusReason', 'text'));
        $this->assertCount(2, $this->tasksOf($id), 'and one new Task');
    }

    /**
     * A save that records no reply (no Reply Date) settles that with one read: it takes no lock and
     * reads no more of the referral.
     */
    public function testASaveWithoutAReplyReadsNoMoreOfTheReferral(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $referrals = new class (createMissingUuids: false) extends ReferralService {
            public int $reads = 0;

            public function getReferralUuid(int $transactionId): ?string
            {
                $this->reads++;
                return parent::getReferralUuid($transactionId);
            }

            public function getReferral(int $transactionId): ?array
            {
                $this->reads++;
                return parent::getReferral($transactionId);
            }
        };

        $this->assertNull((new ReferralTaskService(userService: new UserService(), referralService: $referrals))->recordReply($id, null));

        $this->assertSame(0, $referrals->reads);
        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null);
    }

    public function testNoReplyMeansNoChangeAndTheGlobalTurnsItOff(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null, 'no Reply Date yet');

        $this->setReferralTasks('0');
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
            $bodies[] = $body;
        }
        $this->assertStringEndsWith(' to zq-reply-referrer) The Task for the referral to Zq Reply Pantry made on 2026-10-10 was marked accepted.', $bodies[0]);
        $this->assertStringEndsWith(' to zq-reply-referrer) The Task for the referral to Zq Reply Pantry made on 2026-10-10 was marked completed.', $bodies[1]);
    }

    /**
     * A recipient that rejects the referral with a reason (Task.statusReason) has it passed on: the
     * referrer's notice ends with it.
     */
    public function testARejectionCarriesTheRecipientsReason(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'rejected', ['statusReason' => ['text' => 'Outside our service area']]));

        $this->assertSame('Outside our service area', self::at($this->onlyTask($id), 'statusReason', 'text'));
        $body = QueryUtils::fetchSingleValue('SELECT body FROM pnotes WHERE pid = ?', 'body', [$this->patient['pid']]);
        $this->assertIsString($body);
        $this->assertStringEndsWith(' to zq-reply-referrer) The Task for the referral to Zq Reply Pantry made on 2026-10-10 was marked rejected: Outside our service area', $body);
    }

    /**
     * The recipient's reason goes into the notice on one line: Messages (oeFormatPatientNote())
     * reformats a YYYY-MM-DD that starts a line, as it does each entry's header, so a dated line in
     * the reason would look like another entry's header.
     */
    public function testTheRecipientsReasonIsKeptOnOneLine(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $reason = "Outside our service area\n\n2026-10-20  seen\telsewhere \n";

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'rejected', ['statusReason' => ['text' => $reason]]));

        $body = QueryUtils::fetchSingleValue('SELECT body FROM pnotes WHERE pid = ?', 'body', [$this->patient['pid']]);
        $this->assertIsString($body);
        $this->assertStringNotContainsString("\n", $body);
        $this->assertStringEndsWith(' The Task for the referral to Zq Reply Pantry made on 2026-10-10 was marked rejected: Outside our service area 2026-10-20 seen elsewhere', $body);
    }

    /**
     * The reason is folded as UTF-8: a character whose bytes include A0 (à is C3 A0) comes through
     * whole, and a no-break space counts as a space.
     */
    public function testTheReasonIsFoldedAsUtf8(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);

        $reason = "Home visits stop at\u{00A0}30 km, \u{00E0} la carte only";
        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'rejected', ['statusReason' => ['text' => $reason]]));

        $body = QueryUtils::fetchSingleValue('SELECT body FROM pnotes WHERE pid = ?', 'body', [$this->patient['pid']]);
        $this->assertIsString($body);
        $this->assertTrue(mb_check_encoding($body, 'UTF-8'));
        $this->assertStringEndsWith(": Home visits stop at 30 km, \u{00E0} la carte only", $body);
    }

    /**
     * A recipient that marks the Task failed is news to the referrer too, with their reason when
     * they gave one (Task.statusReason).
     */
    public function testTheReferrerHearsWhenTheTaskFails(): void
    {
        $plain = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($plain);
        $withReason = $this->saveReferral(['refer_date' => '2026-10-12', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($withReason);

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($plain), 'failed'));
        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($withReason), 'failed', ['statusReason' => ['text' => 'The client could not be reached']]));

        $bodies = [];
        foreach (QueryUtils::fetchRecords('SELECT body FROM pnotes WHERE pid = ? ORDER BY id', [$this->patient['pid']]) as $note) {
            $body = $note['body'] ?? null;
            $this->assertIsString($body);
            $bodies[] = $body;
        }
        $this->assertCount(2, $bodies);
        $this->assertStringEndsWith(' to zq-reply-referrer) The Task for the referral to Zq Reply Pantry made on 2026-10-10 was marked failed.', $bodies[0]);
        $this->assertStringEndsWith(' to zq-reply-referrer) The Task for the referral to Zq Reply Pantry made on 2026-10-12 was marked failed: The client could not be reached', $bodies[1]);
    }

    /**
     * A recipient that cancels the Task through the API is news to the referrer, with their reason
     * (Task.statusReason) when they gave one, as a rejection is.
     */
    public function testTheReferrerHearsWhenTheRecipientCancelsTheTask(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'cancelled', ['statusReason' => ['text' => 'Client moved away']]));

        $notes = QueryUtils::fetchRecords('SELECT title, assigned_to, body FROM pnotes WHERE pid = ?', [$this->patient['pid']]);
        $this->assertCount(1, $notes);
        $this->assertSame('Referral', $notes[0]['title'] ?? null);
        $this->assertSame('zq-reply-referrer', $notes[0]['assigned_to'] ?? null);
        $body = $notes[0]['body'] ?? null;
        $this->assertIsString($body);
        $this->assertStringEndsWith(' to zq-reply-referrer) The Task for the referral to Zq Reply Pantry made on 2026-10-10 was marked cancelled: Client moved away', $body);
    }

    /**
     * The service's own cancellations are written without the API, so they tell no one: the
     * referral sent to another recipient, then deleted, cancels each open Task with the service's
     * reason and files no message.
     */
    public function testTheServicesOwnCancellationsTellNoOne(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Clinic']);
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);

        $this->saveFields($id, ['refer_to' => $this->people['clinic']['id']]);
        $this->afterSave($id);
        $pantry = $this->taskOf($id, 'pantry');
        $this->assertSame('cancelled', $pantry['status'] ?? null);
        $this->assertSame(ReferralTaskService::REASON_SENT_ELSEWHERE, self::at($pantry, 'statusReason', 'text'));
        $this->assertSame(1, (new ReferralTaskService(new UserService()))->cancelForDeletedReferral($id, null));
        $this->assertSame('cancelled', $this->taskOf($id, 'clinic')['status'] ?? null);

        $this->assertSame([], QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]));
    }

    /**
     * The notice and the filed reply keep the referral's dates as stored (YYYY-MM-DD), whatever date
     * format the site or the user saving it shows: the text is stored, then read by people whose
     * formats differ and by the receiving system.
     */
    public function testStoredDatesStayYearMonthDayWhateverTheDateFormat(): void
    {
        $this->setGlobal('date_display_format', (string)DateFormatterUtils::DATE_FORMAT_US);
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'accepted'));
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled in weekly delivery']);

        $this->afterSave($id);

        $body = QueryUtils::fetchSingleValue('SELECT body FROM pnotes WHERE pid = ?', 'body', [$this->patient['pid']]);
        $this->assertIsString($body);
        $this->assertStringEndsWith(' The Task for the referral to Zq Reply Pantry made on 2026-10-10 was marked accepted.', $body);
        $text = $this->outputText($this->onlyTask($id), 0);
        $this->assertStringContainsString("Referral Date: 2026-10-10\n", $text);
        $this->assertStringContainsString("Reply Date: 2026-10-18\n", $text);
    }

    /**
     * The notice is filed as other messages are (addPnote()): its date is the service's clock, it
     * carries who last changed it and when, and without a Referral Date its sentence names none.
     */
    public function testTheNoticeIsDatedByTheClock(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $taskUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($taskUuid);
        $clock = new FrozenClock(new \DateTimeImmutable('2026-01-05 09:30:00'));

        (new ReferralTaskService(new UserService(), clock: $clock))->taskStatusChanged($this->taskRecord($taskUuid), 'requested', 'rejected', null);

        $notes = QueryUtils::fetchRecords('SELECT date, body, user, update_date FROM pnotes WHERE pid = ?', [$this->patient['pid']]);
        $this->assertCount(1, $notes);
        $this->assertSame('2026-01-05 09:30:00', $notes[0]['date'] ?? null);
        $this->assertSame('FHIR API', $notes[0]['user'] ?? null);
        $this->assertNotNull($notes[0]['update_date'] ?? null, 'addPnote() records when the message was last changed');
        $body = $notes[0]['body'] ?? null;
        $this->assertIsString($body);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d \(FHIR API to zq-reply-referrer\) The Task for the referral to Zq Reply Pantry was marked rejected\.$/', $body);
    }

    /**
     * FhirTaskService hands a referral Task's status change to the referral's notice, with the Task
     * as it read it after the update, the status the Task had and the one it has now.
     */
    public function testAStatusChangeIsHandedToTheReferralsNotice(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $task = $this->onlyTask($id);
        $taskUuid = $task['id'] ?? null;
        $this->assertIsString($taskUuid);
        $notices = new class (new UserService()) extends ReferralTaskService {
            /** @var list<array{mixed, mixed, string, string}> task uuid, status as handed over, from, to */
            public array $changes = [];

            public function taskStatusChanged(array $task, string $from, string $to, ?int $userId): void
            {
                $this->changes[] = [$task['uuid'] ?? null, $task['status'] ?? null, $from, $to];
            }
        };
        $service = new FhirTaskService(referralTaskService: $notices);
        unset($task['meta']);
        $task['status'] = 'accepted';

        $result = $service->update($taskUuid, new FHIRTask($task));

        $this->assertTrue($result->isValid(), (string)json_encode($result->getValidationMessages()));
        $this->assertSame([[$taskUuid, 'accepted', 'requested', 'accepted']], $notices->changes);
    }

    /**
     * A Task's status change through FhirTaskService reads the Task once, whether or not it is a
     * referral's: the referral's notice takes the record FhirTaskService read, and a Task of
     * another kind isn't handed over at all.
     */
    public function testAStatusChangeReadsTheTaskOnce(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $referralTask = $this->onlyTask($id);
        $referralUuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$id]);
        $this->assertIsString($referralUuid);
        $approval = (new TaskService())->insert([
            'status' => 'requested', 'intent' => 'order', 'code' => 'approve', 'code_system' => FhirTaskService::TASK_CODE_SYSTEM, 'focus_type' => 'ServiceRequest',
            'focus_uuid' => UuidRegistry::uuidToString($referralUuid), 'owner_type' => 'Organization', 'owner_uuid' => $this->people['pantry']['uuid'],
        ], $this->patient['pid'], null);

        foreach (['the referral\'s Task' => $referralTask, 'an approval Task' => $this->read('Task', $approval)] as $which => $task) {
            $taskUuid = $task['id'] ?? null;
            $this->assertIsString($taskUuid);
            $reads = new class extends TaskService {
                public int $reads = 0;

                public function getOne(string $uuid): ?array
                {
                    $this->reads++;
                    return parent::getOne($uuid);
                }
            };
            $service = new FhirTaskService(referralTaskService: new ReferralTaskService(userService: new UserService(), taskService: $reads));
            $service->setTaskService($reads);
            unset($task['meta']);
            $task['status'] = 'accepted';

            $result = $service->update($taskUuid, new FHIRTask($task));

            $this->assertTrue($result->isValid(), (string)json_encode($result->getValidationMessages()));
            $this->assertSame(1, $reads->reads, $which);
        }
        $this->assertCount(1, QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]), 'the referral\'s Task only');
    }

    /**
     * Only the referral's own Task is news to the referrer: a recipient's sub-Task, or a Task of
     * another kind with the referral as its focus, completed through the API files no message.
     */
    public function testTheReferrerHearsNothingOfOtherTasksAboutTheReferral(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $own = $this->onlyTask($id);
        $ownUuid = $own['id'] ?? null;
        $this->assertIsString($ownUuid);
        $referralUuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$id]);
        $this->assertIsString($referralUuid);
        $about = [
            'status' => 'requested', 'intent' => 'order', 'code_system' => FhirTaskService::TASK_CODE_SYSTEM, 'focus_type' => 'ServiceRequest',
            'focus_uuid' => UuidRegistry::uuidToString($referralUuid), 'owner_type' => 'Organization', 'owner_uuid' => $this->people['pantry']['uuid'],
        ];
        $subTask = (new TaskService())->insert($about + ['code' => 'fulfill', 'part_of_uuid' => $ownUuid], $this->patient['pid'], null);
        $approval = (new TaskService())->insert($about + ['code' => 'approve'], $this->patient['pid'], null);

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->read('Task', $subTask), 'completed'));
        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->read('Task', $approval), 'completed'));
        $this->assertSame([], QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]));

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->read('Task', $ownUuid), 'accepted'));
        $this->assertCount(1, QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]), 'the referral\'s own Task');
    }

    /**
     * A Task of the referral's own kind that an API client made for someone other than Refer To
     * isn't the recipient's: accepted, it files no message, which would report another owner's Task
     * as the referral's Task to Refer To.
     */
    public function testTheReferrerHearsNothingOfAnotherOwnersTask(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Clinic']);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $theirs = $this->postTaskFor($id, 'clinic');

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->read('Task', $theirs), 'accepted'));
        $this->assertSame([], QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]));

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->taskOf($id, 'pantry'), 'accepted'));
        $this->assertCount(1, QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]), 'Refer To\'s Task');
    }

    /**
     * A PUT can't take a referral's Task away from the referral (#15137). What's refused changes
     * nothing, and the same Task still takes a status: a recipient's usual PUT.
     */
    public function testAPutCanNotTakeTheTaskAwayFromTheReferral(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Clinic']);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $task = $this->taskOf($id, 'pantry');
        $taskUuid = $task['id'] ?? null;
        $this->assertIsString($taskUuid);
        $clinic = ['reference' => 'Organization/' . $this->people['clinic']['uuid']];
        $body = [];
        foreach ($task as $name => $value) {
            if (is_string($name)) {
                $body[$name] = $value;
            }
        }

        foreach (
            [
                'owner' => ['owner' => $clinic],
                'code' => ['code' => ['coding' => [['system' => FhirTaskService::TASK_CODE_SYSTEM, 'code' => 'approve']]]],
                'partOf' => ['partOf' => [['reference' => 'Task/' . $this->postTaskFor($id, 'clinic')]]],
            ] as $element => $change
        ) {
            $response = $this->testClient->put(self::FHIR_URL . 'Task', $taskUuid, array_merge($body, $change));
            $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), $element);
            $result = json_decode((string)$response->getBody(), true);
            $this->assertIsArray($result);
            $this->assertIsArray($result['validationErrors'] ?? null);
            $this->assertSame([$element], array_keys($result['validationErrors']));
        }

        $this->assertSame($task, $this->taskOf($id, 'pantry'), 'nothing changed');
        $this->assertSame(Response::HTTP_OK, $this->putStatus($task, 'accepted'));
        $this->assertSame('accepted', $this->taskOf($id, 'pantry')['status'] ?? null);
    }

    /**
     * After a re-send (#15137), the reply completes the new Task, and the Task they turned down stays
     * as it was.
     */
    public function testAReplyAfterAReSendCompletesTheNewTask(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $rejected = $this->taskOf($id, 'pantry');
        $rejectedUuid = $rejected['id'] ?? null;
        $this->assertIsString($rejectedUuid);
        $this->assertSame(Response::HTTP_OK, $this->putStatus($rejected, 'rejected'));
        $new = (new ReferralTaskService(new UserService()))->resend($id, $rejectedUuid, null);
        $this->assertIsString($new);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);

        $this->afterSave($id);

        $this->assertSame('completed', $this->read('Task', $new)['status'] ?? null);
        $this->assertSame('rejected', $this->read('Task', $rejectedUuid)['status'] ?? null);
    }

    /**
     * A reply completes Refer To's Task, not a later one of the same kind that an API client made
     * for someone else.
     */
    public function testAReplyCompletesTheRecipientsTaskNotAnotherOwners(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Clinic']);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $theirs = $this->postTaskFor($id, 'clinic');
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);

        $this->afterSave($id);

        $pantry = $this->taskOf($id, 'pantry');
        $this->assertSame('completed', $pantry['status'] ?? null);
        $this->assertStringContainsString('Refer To: Zq Reply Pantry', $this->outputText($pantry, 0));
        $other = $this->read('Task', $theirs);
        $this->assertNotSame('completed', $other['status'] ?? null);
        $this->assertArrayNotHasKey('output', $other);
    }

    /**
     * A new Task tells the recipient by email without patient details.
     */
    public function testANewTaskTellsTheRecipientByEmailWithoutPatientDetails(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Notice Clinic', 'email' => 'intake@zq-clinic.example']);
        $this->emailsQueuedTo[] = 'intake@zq-clinic.example';
        $this->setGlobal(GlobalConnectorsEnum::FHIR_REFERRAL_NOTIFY_RECIPIENT->value, '1');
        $this->setGlobal('patient_reminder_sender_email', 'front@zq-practice.example');
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['clinic']['id'], 'body' => 'Zq private reason']);

        $this->afterSave($id);

        $notice = QueryUtils::querySingleRow('SELECT outcome, address, task_uuid FROM referral_notice WHERE transaction_id = ?', [$id]);
        $this->assertIsArray($notice);
        $this->assertSame(ReferralNoticeOutcome::EmailQueued->value, $notice['outcome'] ?? null);
        $this->assertSame('intake@zq-clinic.example', $notice['address'] ?? null);
        $this->assertIsString($notice['task_uuid'] ?? null);
        $this->assertSame($this->taskOf($id, 'clinic')['id'] ?? null, UuidRegistry::uuidToString($notice['task_uuid']));
        $emails = QueryUtils::fetchRecords('SELECT sender, subject, body FROM email_queue WHERE recipient = ?', ['intake@zq-clinic.example']);
        $this->assertCount(1, $emails);
        $this->assertSame('front@zq-practice.example', $emails[0]['sender'] ?? null);
        $subject = $emails[0]['subject'] ?? null;
        $body = $emails[0]['body'] ?? null;
        $this->assertIsString($subject);
        $this->assertIsString($body);
        $email = $subject . ' ' . $body;
        $this->assertStringContainsString('has sent you a referral', $email);
        // Nothing of the patient or the referral goes by plain email.
        $patient = QueryUtils::querySingleRow('SELECT fname, lname FROM patient_data WHERE pid = ?', [$this->patient['pid']]);
        $this->assertIsArray($patient);
        foreach ([$patient['fname'] ?? '', $patient['lname'] ?? '', 'Zq private reason'] as $detail) {
            $this->assertIsString($detail);
            if ($detail !== '') {
                $this->assertStringNotContainsString($detail, $email);
            }
        }

        // A later save makes no new Task, so no new notice.
        $this->afterSave($id);
        $this->assertCount(1, QueryUtils::fetchRecords('SELECT id FROM referral_notice WHERE transaction_id = ?', [$id]));
    }

    /**
     * A recipient who can not be told is shown to staff.
     */
    public function testARecipientWhoCanNotBeToldIsShownToStaff(): void
    {
        $this->setGlobal(GlobalConnectorsEnum::FHIR_REFERRAL_NOTIFY_RECIPIENT->value, '1');
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);

        $this->afterSave($id);

        $this->assertSame(ReferralNoticeOutcome::NoAddress->value, QueryUtils::fetchSingleValue('SELECT outcome FROM referral_notice WHERE transaction_id = ?', 'outcome', [$id]));
        $block = (new ReferralDisplay())->communicationForTransactionPage('LBTref', $id);
        $this->assertStringContainsString('Recipient could not be notified', $block);
        $this->assertStringContainsString('Please contact the recipient.', $block);
    }

    /**
     * A re-send tells the recipient again.
     */
    public function testAReSendTellsTheRecipientAgain(): void
    {
        $this->setGlobal(GlobalConnectorsEnum::FHIR_REFERRAL_NOTIFY_RECIPIENT->value, '1');
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $rejected = $this->taskOf($id, 'pantry');
        $rejectedUuid = $rejected['id'] ?? null;
        $this->assertIsString($rejectedUuid);
        $this->assertSame(Response::HTTP_OK, $this->putStatus($rejected, 'rejected'));

        $new = (new ReferralTaskService(new UserService()))->resend($id, $rejectedUuid, null);

        $this->assertIsString($new);
        $told = QueryUtils::fetchRecords('SELECT task_uuid FROM referral_notice WHERE transaction_id = ? ORDER BY id', [$id]);
        $this->assertCount(2, $told);
        $this->assertIsString($told[1]['task_uuid'] ?? null);
        $this->assertSame($new, UuidRegistry::uuidToString($told[1]['task_uuid']));
    }

    /**
     * With notices off the recipient is not told.
     */
    public function testWithNoticesOffTheRecipientIsNotTold(): void
    {
        $this->setGlobal(GlobalConnectorsEnum::FHIR_REFERRAL_NOTIFY_RECIPIENT->value, '0');
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);

        $this->afterSave($id);

        $this->assertSame([], QueryUtils::fetchRecords('SELECT id FROM referral_notice WHERE transaction_id = ?', [$id]));
        $this->assertSame('', (new ReferralDisplay())->communicationForTransactionPage('LBTref', $id));
    }

    /**
     * A confirmed Direct reply completes the Task with its documents.
     */
    public function testAConfirmedDirectReplyCompletesTheTaskWithItsDocuments(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $documentId = $this->fileInChart($id, 'consult.xml', '<ClinicalDocument/>');
        $replyId = $this->receiveDirectReply($id, 'Consult attached.', [$documentId]);
        $service = new ReferralTaskService(new UserService());
        $taskUuid = $service->confirmableTask($id);
        $this->assertSame($this->taskOf($id, 'pantry')['id'] ?? null, $taskUuid);
        $this->assertIsString($taskUuid);
        $this->assertStringContainsString('Confirm it completes the referral', (new ReferralDisplay())->communicationForTransactionPage('LBTref', $id, true));

        $this->assertSame($taskUuid, $service->completeWithDirectReply($id, $taskUuid, $replyId, null));

        $task = $this->read('Task', $taskUuid);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertSame(ReferralTaskService::REPLY_OUTPUT_TYPE['type_text'], self::at($task, 'output', 0, 'type', 'text'));
        $documentUuid = QueryUtils::fetchSingleValue('SELECT uuid FROM documents WHERE id = ?', 'uuid', [$documentId]);
        $this->assertIsString($documentUuid);
        $this->assertSame('DocumentReference/' . UuidRegistry::uuidToString($documentUuid), self::at($task, 'output', 0, 'valueReference', 'reference'));
        // The referral reads as answered, on the day the reply came.
        $received = QueryUtils::fetchSingleValue('SELECT create_ts FROM direct_message_log WHERE id = ?', 'create_ts', [$replyId]);
        $this->assertIsString($received);
        $this->assertSame(substr($received, 0, 10), QueryUtils::fetchSingleValue("SELECT field_value FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", 'field_value', [$id]));
        $this->assertNull($service->confirmableTask($id));
        // A second click does nothing.
        $this->assertNull($service->completeWithDirectReply($id, $taskUuid, $replyId, null));
    }

    /**
     * A confirmed text-only Direct reply is filed as a document.
     */
    public function testAConfirmedTextOnlyDirectReplyIsFiledAsADocument(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $replyId = $this->receiveDirectReply($id, 'Seen today, plan to follow up.', []);
        $service = new ReferralTaskService(new UserService());
        $taskUuid = $service->confirmableTask($id);
        $this->assertIsString($taskUuid);

        $this->assertSame($taskUuid, $service->completeWithDirectReply($id, $taskUuid, $replyId, null));

        $documents = QueryUtils::fetchRecords("SELECT id, name FROM documents WHERE foreign_reference_table = 'transactions' AND foreign_reference_id = ?", [$id]);
        $this->assertCount(1, $documents);
        $this->assertSame('direct-reply-' . $replyId . '.txt', $documents[0]['name'] ?? null);
        $this->assertIsNumeric($documents[0]['id'] ?? null);
        $this->assertStringContainsString('Seen today, plan to follow up.', (string)(new Document((int)$documents[0]['id']))->get_data());
        $this->assertSame('completed', $this->read('Task', $taskUuid)['status'] ?? null);
    }

    /**
     * A stale confirm changes nothing.
     */
    public function testAStaleConfirmChangesNothing(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $replyId = $this->receiveDirectReply($id, 'Seen.', []);
        $service = new ReferralTaskService(new UserService());
        $task = $this->taskOf($id, 'pantry');
        $taskUuid = $task['id'] ?? null;
        $this->assertIsString($taskUuid);

        // A Task id the page didn't show, and a reply filed with another referral, both do nothing.
        $this->assertNull($service->completeWithDirectReply($id, '00000000-0000-4000-8000-000000000000', $replyId, null));
        $this->assertNull($service->completeWithDirectReply($id, $taskUuid, $replyId + 1000000, null));
        // Nor does a Task the recipient has since rejected.
        $this->assertSame(Response::HTTP_OK, $this->putStatus($task, 'rejected'));
        $this->assertNull($service->completeWithDirectReply($id, $taskUuid, $replyId, null));
        $this->assertSame('rejected', $this->read('Task', $taskUuid)['status'] ?? null);
        $this->assertNull(QueryUtils::fetchSingleValue("SELECT field_value FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", 'field_value', [$id]));
    }

    /**
     * The Reply Date goes in before the Task is completed, and comes back out when the Task's update
     * fails, so a referral never reads as answered while its Task stays open from a confirm.
     */
    public function testAConfirmWhoseTaskUpdateFailsLeavesNoReplyDate(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $replyId = $this->receiveDirectReply($id, 'Seen.', []);
        $taskUuid = (new ReferralTaskService(new UserService()))->confirmableTask($id);
        $this->assertIsString($taskUuid);
        $failing = new class extends TaskService {
            public ?string $replyDateDuringUpdate = null;

            /**
             * Notes the referral's Reply Date as the Task is updated, then fails.
             */
            public function update(string $uuid, array $fields, ?int $userId, ?int $expectedVersion = null, ?array $inputs = null, ?array $outputs = null): bool
            {
                $date = QueryUtils::fetchSingleValue("SELECT field_value FROM lbt_data WHERE field_id = 'reply_date' AND form_id = (SELECT id FROM transactions WHERE uuid = (SELECT focus_uuid FROM task WHERE uuid = ?))", 'field_value', [UuidRegistry::uuidToBytes($uuid)]);
                $this->replyDateDuringUpdate = is_string($date) ? $date : null;
                throw new \RuntimeException('database went away');
            }
        };

        try {
            (new ReferralTaskService(new UserService(), taskService: $failing))->completeWithDirectReply($id, $taskUuid, $replyId, null);
            $this->fail('the failure of the update is thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('database went away', $e->getMessage());
        }

        $this->assertNotNull($failing->replyDateDuringUpdate, 'the Reply Date was in place while the Task was updated');
        $this->assertNull(QueryUtils::fetchSingleValue("SELECT field_value FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", 'field_value', [$id]));
        $this->assertSame($taskUuid, (new ReferralTaskService(new UserService()))->confirmableTask($id), 'and confirming can be tried again');
    }

    /**
     * A Reply Date a user saves while a confirm is under way is theirs: the confirm's failure takes out
     * only the date it wrote.
     */
    public function testAFailedConfirmKeepsAReplyDateAUserSavedMeanwhile(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $replyId = $this->receiveDirectReply($id, 'Seen.', []);
        $taskUuid = (new ReferralTaskService(new UserService()))->confirmableTask($id);
        $this->assertIsString($taskUuid);
        $failing = new class ($id) extends TaskService {
            /**
             * Keeps the referral whose Reply Date the user saves.
             */
            public function __construct(private readonly int $referralId)
            {
                parent::__construct();
            }

            /**
             * Saves a Reply Date as a user on the Transactions screen would, then fails.
             */
            public function update(string $uuid, array $fields, ?int $userId, ?int $expectedVersion = null, ?array $inputs = null, ?array $outputs = null): bool
            {
                QueryUtils::sqlStatementThrowException("REPLACE INTO lbt_data SET form_id = ?, field_id = 'reply_date', field_value = '2026-01-02'", [$this->referralId]);
                throw new \RuntimeException('database went away');
            }
        };

        try {
            (new ReferralTaskService(new UserService(), taskService: $failing))->completeWithDirectReply($id, $taskUuid, $replyId, null);
            $this->fail('the failure of the update is thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('database went away', $e->getMessage());
        }

        $this->assertSame('2026-01-02', QueryUtils::fetchSingleValue("SELECT field_value FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", 'field_value', [$id]));
    }

    /**
     * A Reply Date a user saved after the page was shown, before the confirm, is theirs: the confirm
     * writes nothing over it and leaves the Task for that save to complete.
     */
    public function testAConfirmAfterAUserSavedAReplyDateChangesNothing(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $replyId = $this->receiveDirectReply($id, 'Seen.', []);
        $service = new ReferralTaskService(new UserService());
        $taskUuid = $service->confirmableTask($id);
        $this->assertIsString($taskUuid);
        $this->saveFields($id, ['reply_date' => '2026-01-02']);

        $this->assertNull($service->completeWithDirectReply($id, $taskUuid, $replyId, null));

        $this->assertSame('2026-01-02', QueryUtils::fetchSingleValue("SELECT field_value FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", 'field_value', [$id]));
        $this->assertSame('requested', $this->read('Task', $taskUuid)['status'] ?? null);
    }

    /**
     * A file in the patient's chart, as PhiMailReceiver stores a matched reply's attachment. It names the
     * referral only so tearDown() removes it with the referral's documents.
     */
    private function fileInChart(int $transactionId, string $name, string $content): int
    {
        $category = QueryUtils::fetchSingleValue("SELECT id FROM categories WHERE name = 'Medical Record' ORDER BY id LIMIT 1", 'id', []);
        $this->assertIsNumeric($category);
        $document = new Document();
        $this->assertSame('', $document->createDocument((string)$this->patient['pid'], (int)$category, $name, 'text/xml', $content, '', 1, 1, null, null, (string)$transactionId, 'transactions'));
        $id = $document->get_id();
        $this->assertIsNumeric($id);
        $this->directDocuments[] = (int)$id;
        return (int)$id;
    }

    /**
     * A Direct reply as PhiMailReceiver files one it matched to a message sent with the referral: a note
     * in the patient's chart linked to its documents, logged with the referral.
     *
     * @param list<int> $documentIds
     * @return int its direct_message_log id
     */
    private function receiveDirectReply(int $transactionId, string $text, array $documentIds): int
    {
        $noteId = (int)QueryUtils::sqlInsert(
            "INSERT INTO pnotes (date, body, pid, user, groupname, activity, authorized, title, assigned_to, deleted, message_status) VALUES (NOW(), ?, ?, 'phimail-service', 'Default', 1, 0, 'Unassigned', 'admin', 0, 'New')",
            [$text, $this->patient['pid']]
        );
        foreach ($documentIds as $documentId) {
            QueryUtils::sqlStatementThrowException('INSERT INTO gprelations (type1, id1, type2, id2) VALUES (1, ?, 6, ?)', [$documentId, $noteId]);
        }
        $messageId = 'zq-reply-' . uniqid('', true);
        (new DirectMessageLogRepository())->recordReceived($messageId, 'consult@zq-clinic.example', 'practice@direct.example', 1, $this->patient['pid'], $transactionId, $noteId);
        $id = QueryUtils::fetchSingleValue("SELECT id FROM direct_message_log WHERE msg_type = 'R' AND msg_id = ?", 'id', [$messageId]);
        $this->assertIsNumeric($id);
        return (int)$id;
    }

    /**
     * The global, in this process and in the globals table: a Task's status change comes in through the API,
     * whose process reads it from the database, so the referrer's notice, which respects it, follows that.
     */
    private function setReferralTasks(string $value): void
    {
        $this->setGlobal(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, $value);
    }

    /**
     * A global, in this process and in the globals table, put back in tearDown().
     */
    private function setGlobal(string $name, string $value): void
    {
        $this->globalsBefore[$name] ??= [
            OEGlobalsBag::getInstance()->get($name),
            QueryUtils::fetchSingleValue('SELECT gl_value FROM globals WHERE gl_name = ? AND gl_index = 0', 'gl_value', [$name]),
        ];
        OEGlobalsBag::getInstance()->set($name, $value);
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?) ON DUPLICATE KEY UPDATE gl_value = VALUES(gl_value)',
            [$name, $value]
        );
    }

    public function testWithTheGlobalOffTheReferrerHearsNothing(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->setReferralTasks('0');

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'accepted'));

        $this->assertSame([], QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]));
    }

    /**
     * A referrer who has left (their user made inactive) isn't sent a notice no one would read: the
     * PUT still succeeds, and the service logs that no one was told.
     */
    public function testAReferrerWhoHasLeftIsNotSentTheNotice(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        QueryUtils::sqlStatementThrowException('UPDATE users SET active = 0 WHERE id = ?', [$this->people['doctor']['id']]);

        $this->assertSame(Response::HTTP_OK, $this->putStatus($this->onlyTask($id), 'accepted'));
        $this->assertSame([], QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]));

        $taskUuid = $this->onlyTask($id)['id'] ?? null;
        $this->assertIsString($taskUuid);
        $logger = self::logger();
        (new ReferralTaskService(new UserService(), logger: $logger))->taskStatusChanged($this->taskRecord($taskUuid), 'accepted', 'completed', null);
        $this->assertSame([['warning', 'A referral\'s Task changed status but its referrer is not an active user, so no one was told']], $logger->records);
        $this->assertSame([], QueryUtils::fetchRecords('SELECT id FROM pnotes WHERE pid = ?', [$this->patient['pid']]));
    }

    /**
     * The Task's update is committed before the referrer is told, so a notice that fails is logged
     * rather than thrown at the client whose PUT already succeeded.
     */
    public function testANoticeThatFailsIsLoggedNotThrown(): void
    {
        $logger = self::logger();
        $referrals = new class (createMissingUuids: false) extends ReferralService {
            public function getReferralByUuid(string $uuid): ?array
            {
                throw new \RuntimeException('the database went away');
            }
        };
        $task = ['uuid' => 'a-task-uuid', 'code' => 'fulfill', 'part_of_uuid' => null, 'focus_type' => 'ServiceRequest', 'focus_uuid' => 'a-referral-uuid'];

        (new ReferralTaskService(new UserService(), referralService: $referrals, logger: $logger))->taskStatusChanged($task, 'requested', 'accepted', null);

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

        $this->assertNull((new ReferralTaskService(new UserService(), taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
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
     * Refer To's entry renamed between a save that filed the reply and lost the Task's update and
     * the next save doesn't make the reply a new one: the next save completes the Task with the
     * document already filed, which keeps the name it was filed with.
     */
    public function testARecipientRenamedBeforeTheRetryFilesNoSecondCopy(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->assertNull((new ReferralTaskService(new UserService(), taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
        $this->assertCount(1, $this->replyDocuments($id), 'filed before the update failed');
        QueryUtils::sqlStatementThrowException('UPDATE users SET organization = ? WHERE id = ?', ['Zq Reply Pantry Renamed', $this->people['pantry']['id']]);

        $this->afterSave($id);

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertCount(1, $this->replyDocuments($id), 'no second copy');
        $this->assertStringContainsString("Refer To: Zq Reply Pantry\n", $this->outputText($task, 0));
    }

    /**
     * A filed reply moved to another patient's chart (Document::change_patient() keeps its link to
     * the referral) isn't this patient's reply any more: the next save files a new one in this
     * patient's chart and completes the Task with it.
     */
    public function testAReplyMovedToAnotherChartIsFiledAgainForThisPatient(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->assertNull((new ReferralTaskService(new UserService(), taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
        $moved = $this->replyDocuments($id);
        $this->assertCount(1, $moved, 'filed before the update failed');
        // Another chart: a patient id this test's patient doesn't have.
        QueryUtils::sqlStatementThrowException('UPDATE documents SET foreign_id = ? WHERE id = ?', [$this->patient['pid'] + 900000, $moved[0]]);

        $this->afterSave($id);

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertCount(2, $this->replyDocuments($id), 'a new one for this patient');
        $reference = self::at($task, 'output', 0, 'valueReference', 'reference');
        $this->assertIsString($reference);
        $owner = QueryUtils::fetchSingleValue('SELECT foreign_id FROM documents WHERE uuid = ?', 'foreign_id', [UuidRegistry::uuidToBytes(substr($reference, strlen('DocumentReference/')))]);
        $this->assertEquals($this->patient['pid'], $owner);
    }

    /**
     * A reply edited after a save that filed it and then lost the Task's update is a new reply:
     * the next save files it and completes the Task with it, not with the text it replaced.
     */
    public function testAReplyEditedAfterItsTaskUpdateFailedIsFiledAnew(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->assertNull((new ReferralTaskService(new UserService(), taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
        $this->assertCount(1, $this->replyDocuments($id), 'the first text was filed before the update failed');
        $this->saveFields($id, ['reply_findings' => 'Enrolled in weekly delivery']);

        $this->afterSave($id);

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertCount(2, $this->replyDocuments($id), 'the edited reply is filed beside the first');
        $outputs = self::at($task, 'output');
        $this->assertIsArray($outputs);
        $this->assertCount(1, $outputs);
        $this->assertStringContainsString('Findings: Enrolled in weekly delivery', $this->outputText($task, 0));
    }

    /**
     * A reply filed while one user saved the referral, then retried by a user who works in another
     * language, is the same reply: the retry finds the filed document by its name, made from the
     * reply's stored values, and files no second copy, and the text takes the layout's titles as
     * stored, not translated.
     */
    public function testAReplyRetriedInAnotherLanguageIsNotFiledAgain(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->assertNull((new ReferralTaskService(new UserService(), taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
        $this->assertCount(1, $this->replyDocuments($id), 'filed before the update failed');

        $this->useLanguage(['Counter-Referral' => 'Zq Contrarreferencia', 'Reply Date' => 'Zq Fecha de respuesta', 'Findings' => 'Zq Conclusiones']);
        $this->assertSame('Zq Conclusiones', xl('Findings'), 'the retry runs in the other language');
        $this->afterSave($id);

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertCount(1, $this->replyDocuments($id), 'no second copy');
        $this->assertSame(
            "Counter-Referral\nReferral Date: 2026-10-10\nRefer To: Zq Reply Pantry\n\nReply Date: 2026-10-18\nFindings: Enrolled\n",
            $this->outputText($task, 0)
        );
    }

    /**
     * A field a site added to the Counter-Referral section is in the reply when its id starts with
     * reply_, under the title the site gave it and in the form's order. One with another id stays in
     * the referral but isn't filed with the reply, and one the site set to unused is left out.
     */
    public function testASiteFieldInTheCounterReferralSectionIsInTheReplyWhenNamedReply(): void
    {
        $group = QueryUtils::fetchSingleValue("SELECT group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'reply_date'", 'group_id', []);
        $this->assertIsString($group);
        $this->addLayoutField('reply_zq_follow_up', $group, 'Zq Follow-up Plan', 97, 1);
        $this->addLayoutField('zq_site_note', $group, 'Zq Site Note', 98, 1);
        $this->addLayoutField('reply_zq_unused', $group, 'Zq Unused Field', 99, 0);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, [
            'reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled',
            'reply_zq_follow_up' => 'Call in a week', 'zq_site_note' => 'Not filed', 'reply_zq_unused' => 'Not in use',
        ]);

        $this->afterSave($id);

        $text = $this->outputText($this->onlyTask($id), 0);
        $this->assertStringEndsWith("\nFindings: Enrolled\nZq Follow-up Plan: Call in a week\n", $text);
        $this->assertStringNotContainsString('Not filed', $text);
        $this->assertStringNotContainsString('Not in use', $text);
    }

    /**
     * A site field whose id starts with reply_ is in the reply wherever the layout puts it: in a
     * subgroup of the Counter-Referral section (its group id is the section's and one character
     * more) or in another group, in the form's order.
     */
    public function testAReplyNamedSiteFieldIsInTheReplyWhereverItIs(): void
    {
        $group = QueryUtils::fetchSingleValue("SELECT group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'reply_date'", 'group_id', []);
        $this->assertIsString($group);
        $referralGroup = QueryUtils::fetchSingleValue("SELECT group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'refer_to'", 'group_id', []);
        $this->assertIsString($referralGroup);
        $this->addLayoutField('reply_zq_sub_note', $group . '1', 'Zq Subgroup Note', 1, 1);
        $this->addLayoutField('reply_zq_elsewhere', $referralGroup, 'Zq Intake Code', 99, 1);
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled', 'reply_zq_sub_note' => 'Seen by the social worker', 'reply_zq_elsewhere' => 'ZQ-7']);

        $this->afterSave($id);

        $this->assertSame(
            "Counter-Referral\nReferral Date: 2026-10-10\nRefer To: Zq Reply Pantry\n\n"
            . "Zq Intake Code: ZQ-7\nReply Date: 2026-10-18\nFindings: Enrolled\nZq Subgroup Note: Seen by the social worker\n",
            $this->outputText($this->onlyTask($id), 0)
        );
    }

    /**
     * A site that keeps the reply in the referral's own group (a one-tab form) files only the reply:
     * under the heading Counter-Referral, with nothing of the referral itself (Refer By, the reason, the
     * billing facility, its fields' stored ids).
     */
    public function testAReplyKeptInTheReferralsOwnGroupCarriesNothingElseOfTheReferral(): void
    {
        $group = QueryUtils::fetchSingleValue("SELECT group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'refer_to'", 'group_id', []);
        $this->assertIsString($group);
        $this->moveReplyFieldsTo($group);
        $id = $this->saveReferral([
            'refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'],
            'body' => 'Food insecurity', 'billing_facility_id' => '3',
        ]);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);

        $this->afterSave($id);

        $this->assertSame(
            "Counter-Referral\nReferral Date: 2026-10-10\nRefer To: Zq Reply Pantry\n\nReply Date: 2026-10-18\nFindings: Enrolled\n",
            $this->outputText($this->onlyTask($id), 0)
        );
    }

    /**
     * A site that moved only the Reply Date next to Refer To, leaving the other reply fields in
     * their section, still files the whole reply, and nothing of the referral itself.
     */
    public function testAReplyDateMovedIntoTheReferralsGroupKeepsTheRestOfTheReply(): void
    {
        $group = QueryUtils::fetchSingleValue("SELECT group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'refer_to'", 'group_id', []);
        $this->assertIsString($group);
        $this->moveLayoutFields($group, ['reply_date']);
        $id = $this->saveReferral([
            'refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'],
            'body' => 'Food insecurity', 'billing_facility_id' => '3',
        ]);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_from' => 'Zq intake desk', 'reply_findings' => 'Enrolled']);

        $this->afterSave($id);

        $this->assertSame(
            "Counter-Referral\nReferral Date: 2026-10-10\nRefer To: Zq Reply Pantry\n\nReply Date: 2026-10-18\nReply From: Zq intake desk\nFindings: Enrolled\n",
            $this->outputText($this->onlyTask($id), 0)
        );
    }

    /**
     * A site that moved the Reply Date next to Refer To and added a reply field (an id that starts
     * with reply_) to the Counter-Referral section still files that field with the reply.
     */
    public function testAReplyNamedSiteFieldLeftInTheSectionIsInTheReplyWhenTheReplyDateMoves(): void
    {
        $section = QueryUtils::fetchSingleValue("SELECT group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'reply_date'", 'group_id', []);
        $this->assertIsString($section);
        $group = QueryUtils::fetchSingleValue("SELECT group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'refer_to'", 'group_id', []);
        $this->assertIsString($group);
        $this->moveLayoutFields($group, ['reply_date']);
        $this->addLayoutField('reply_zq_follow_up', $section, 'Zq Follow-up Plan', 98, 1);
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'Food insecurity']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled', 'reply_zq_follow_up' => 'Call in a week']);

        $this->afterSave($id);

        $this->assertSame(
            "Counter-Referral\nReferral Date: 2026-10-10\nRefer To: Zq Reply Pantry\n\nReply Date: 2026-10-18\nFindings: Enrolled\nZq Follow-up Plan: Call in a week\n",
            $this->outputText($this->onlyTask($id), 0)
        );
    }

    /**
     * A Reply Date grouped with some of the referral's own fields (Refer By and the reason) but not
     * Refer To files none of them with the reply.
     */
    public function testAReplyGroupedWithSomeOfTheReferralsFieldsTakesNoneOfThem(): void
    {
        $group = QueryUtils::fetchSingleValue("SELECT group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id = 'refer_to'", 'group_id', []);
        $this->assertIsString($group);
        // A subgroup of the referral's group, without Refer To.
        $this->moveLayoutFields($group . '1', ['reply_date', 'refer_from', 'body']);
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'Food insecurity']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);

        $this->afterSave($id);

        $this->assertSame(
            "Counter-Referral\nReferral Date: 2026-10-10\nRefer To: Zq Reply Pantry\n\nReply Date: 2026-10-18\nFindings: Enrolled\n",
            $this->outputText($this->onlyTask($id), 0)
        );
    }

    /**
     * A referral sent on to another recipient after the first one's reply was recorded: the
     * second recipient's reply is filed as its own document and completes their Task, and the
     * first recipient's Task keeps the first reply.
     */
    public function testASecondRecipientsReplyIsFiledForTheirTask(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Clinic']);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        // Sent on: a referral with a Reply Date gets no new Task, so the Reply Date is cleared first.
        $this->saveFields($id, ['refer_to' => $this->people['clinic']['id'], 'reply_date' => '']);
        $this->afterSave($id);
        $this->assertSame('requested', $this->taskOf($id, 'clinic')['status'] ?? null);
        $this->saveFields($id, ['reply_date' => '2026-10-25', 'reply_findings' => 'Seen in clinic']);

        $this->afterSave($id);

        $this->assertCount(2, $this->replyDocuments($id));
        $clinic = $this->taskOf($id, 'clinic');
        $this->assertSame('completed', $clinic['status'] ?? null);
        $clinicReply = $this->outputText($clinic, 0);
        $this->assertStringContainsString('Refer To: Zq Reply Clinic', $clinicReply);
        $this->assertStringContainsString('Findings: Seen in clinic', $clinicReply);
        $pantryReply = $this->outputText($this->taskOf($id, 'pantry'), 0);
        $this->assertStringContainsString('Refer To: Zq Reply Pantry', $pantryReply);
        $this->assertStringContainsString('Findings: Enrolled', $pantryReply);
    }

    /**
     * One save sends the referral on and records its reply, and its Task step doesn't get the
     * referral's lock, so the first recipient's open Task isn't cancelled: the reply, which names
     * the new Refer To, isn't recorded on it. The next save cancels it.
     */
    public function testAReplyIsNotRecordedOnTheTaskOfARecipientTheReferralLeft(): void
    {
        $this->people['clinic'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Reply Clinic']);
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['refer_to' => $this->people['clinic']['id'], 'reply_date' => '2026-10-25', 'reply_findings' => 'Seen in clinic']);
        $lockMissed = new class (new UserService()) extends ReferralTaskService {
            // As createForReferral() returns when the lock isn't acquired: before it cancels anything.
            public function createForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): ?string
            {
                return null;
            }
        };

        $lockMissed->afterSave($id, null);

        $this->assertSame('requested', $this->taskOf($id, 'pantry')['status'] ?? null);
        $this->assertSame([], $this->replyDocuments($id));

        $this->afterSave($id);
        $this->assertSame('cancelled', $this->taskOf($id, 'pantry')['status'] ?? null, 'the next save');
    }

    /**
     * A reply whose document isn't saved (createDocument() answers with an error) leaves the Task
     * open, as a missing category does.
     */
    public function testAReplyWhoseDocumentIsNotSavedLeavesTheTaskOpen(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18']);
        $logger = self::logger();
        $unsaved = static fn(): Document => new class extends Document {
            public function createDocument(
                mixed $patient_id,
                mixed $category_id,
                mixed $filename,
                mixed $mimetype,
                mixed &$data,
                mixed $higher_level_path = '',
                int $path_depth = 1,
                mixed $owner = 0,
                mixed $tmpfile = null,
                mixed $date_expires = null,
                mixed $foreign_reference_id = null,
                mixed $foreign_reference_table = null,
                mixed $eid = '',
            ): string {
                return 'the disk is full';
            }
        };

        $this->assertNull((new ReferralTaskService(new UserService(), logger: $logger, documentFactory: $unsaved))->recordReply($id, null));

        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null);
        $this->assertSame([['warning', 'A referral\'s reply was not filed, so its Task was left open']], $logger->records);
    }

    /**
     * A reply an earlier save filed, then lost the Task's update for, isn't attached once the Task
     * has gone final: the next save finds the Task final and leaves it as the recipient has it.
     */
    public function testAReplyFiledByAnEarlierSaveIsNotAttachedOnceTheTaskIsFinal(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->assertNull((new ReferralTaskService(new UserService(), taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
        $task = $this->onlyTask($id);
        $this->assertSame('requested', $task['status'] ?? null);
        $taskUuid = $task['id'] ?? null;
        $this->assertIsString($taskUuid);
        $this->assertCount(1, $this->replyDocuments($id), 'filed before the update failed');
        (new TaskService())->update($taskUuid, ['status' => 'rejected'], null);

        $this->afterSave($id);

        $again = $this->onlyTask($id);
        $this->assertSame('rejected', $again['status'] ?? null);
        $this->assertArrayNotHasKey('output', $again);
        $this->assertCount(1, $this->replyDocuments($id));
    }

    /**
     * A recipient that takes the reply out of a completed Task's outputs (a PUT that keeps the
     * status and drops the output) keeps it out: a later save of the referral writes nothing to the
     * Task, so its version stays where the recipient left it.
     */
    public function testAReplyTheRecipientRemovedStaysRemoved(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->afterSave($id);
        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $outputs = self::at($task, 'output');
        $this->assertIsArray($outputs);
        $this->assertCount(1, $outputs);
        $taskUuid = $task['id'] ?? null;
        $this->assertIsString($taskUuid);
        (new TaskService())->update($taskUuid, [], null, null, null, []);
        $version = $this->taskVersion($taskUuid);

        $this->afterSave($id);

        $this->assertArrayNotHasKey('output', $this->onlyTask($id));
        $this->assertSame($version, $this->taskVersion($taskUuid), 'the save wrote nothing to the Task');
        $this->assertCount(1, $this->replyDocuments($id));
    }

    /**
     * A reply no signed-in user saved is filed under the system user; when there's none, it isn't
     * filed under whoever's session happens to be active: the log says why, and the Task stays open.
     */
    public function testAReplyWithNoUserToFileItUnderIsNotFiled(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $noSystemUser = new class extends UserService {
            public function getSystemUser(): bool
            {
                return false;
            }
        };
        $logger = self::logger();

        $this->assertNull((new ReferralTaskService(logger: $logger, userService: $noSystemUser))->recordReply($id, null));

        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null);
        $this->assertSame([], $this->replyDocuments($id));
        $this->assertContains(['warning', 'A referral\'s reply was not filed: there is no user to file it under'], $logger->records);
    }

    /**
     * A recipient that rejects the Task between the reply's filing and its write keeps their
     * rejection: the reply doesn't write completed over it. The reply already filed is attached as
     * the Task's output, so it isn't left in the chart without its Task.
     */
    public function testARejectionMadeWhileTheReplyIsRecordedStandsWithTheReplyAttached(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $logger = self::logger();

        $this->assertNull($this->replyRacing(['status' => 'rejected'], null, $logger)->recordReply($id, null));

        $task = $this->onlyTask($id);
        $this->assertSame('rejected', $task['status'] ?? null);
        $outputs = self::at($task, 'output');
        $this->assertIsArray($outputs);
        $this->assertCount(1, $outputs);
        $this->assertCount(1, $this->replyDocuments($id));
        $this->assertStringContainsString("Findings: Enrolled\n", $this->outputText($task, 0));
        $this->assertSame([['info', 'A referral\'s reply was filed as its Task reached a final status, so it was attached to the Task as it is']], $logger->records);

        // The next save finds it attached and changes nothing.
        $this->afterSave($id);
        $this->assertCount(1, $this->replyDocuments($id));
        $again = self::at($this->onlyTask($id), 'output');
        $this->assertIsArray($again);
        $this->assertCount(1, $again);
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
     * A reply an offsite store takes (its PatientDocumentStoreOffsite listener marks the document
     * stored remotely, and no documents row is written) counts as filed: the Task is completed
     * without it as an output, the log says so, and the reply is uploaded once, though the Task is
     * read again in the same save, and not again on the next save.
     */
    public function testAReplyStoredRemotelyCompletesTheTaskWithoutALocalDocument(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->storeDocumentsRemotely();
        /** @var \ArrayObject<int, mixed> $uploads each upload's file name */
        $uploads = new \ArrayObject();
        $counting = static fn(): Document => new class ($uploads) extends Document {
            /** @param \ArrayObject<int, mixed> $uploads */
            public function __construct(private readonly \ArrayObject $uploads)
            {
                parent::__construct();
            }

            public function createDocument(
                mixed $patient_id,
                mixed $category_id,
                mixed $filename,
                mixed $mimetype,
                mixed &$data,
                mixed $higher_level_path = '',
                int $path_depth = 1,
                mixed $owner = 0,
                mixed $tmpfile = null,
                mixed $date_expires = null,
                mixed $foreign_reference_id = null,
                mixed $foreign_reference_table = null,
                mixed $eid = '',
            ): string {
                $this->uploads->append($filename);
                return parent::createDocument($patient_id, $category_id, $filename, $mimetype, $data, $higher_level_path, $path_depth, $owner, $tmpfile, $date_expires, $foreign_reference_id, $foreign_reference_table, $eid);
            }
        };
        $logger = self::logger();

        $this->assertIsString($this->replyRacing(['status' => 'in-progress'], null, $logger, $counting)->recordReply($id, null));

        $task = $this->onlyTask($id);
        $this->assertSame('completed', $task['status'] ?? null);
        $this->assertArrayNotHasKey('output', $task);
        $this->assertSame([], $this->replyDocuments($id));
        $this->assertCount(1, $uploads, 'uploaded once, though the Task was read again');
        $this->assertContains(['info', 'A referral\'s reply went to remote storage, so its Task was completed without the reply as its output'], $logger->records);

        $this->assertNull((new ReferralTaskService(new UserService(), logger: self::logger(), documentFactory: $counting))->recordReply($id, null));
        $this->assertCount(1, $uploads, 'the next save uploads nothing more');
    }

    /**
     * A reply an offsite store took while the recipient put the Task in a final status has no
     * local document to attach: the Task keeps the recipient's status, and the log says why the
     * reply isn't on it.
     */
    public function testAReplyStoredOffsiteAsTheTaskWentFinalIsLogged(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->storeDocumentsRemotely();
        $logger = self::logger();

        $this->assertNull($this->replyRacing(['status' => 'rejected'], null, $logger)->recordReply($id, null));

        $task = $this->onlyTask($id);
        $this->assertSame('rejected', $task['status'] ?? null);
        $this->assertArrayNotHasKey('output', $task);
        $this->assertSame([['info', 'A referral\'s reply went to remote storage but its Task went final first, so it isn\'t attached']], $logger->records);
    }

    /**
     * A listener may mark the upload with any value PHP takes as true, not only a boolean:
     * Document::createDocument() takes it as stored offsite, and so does the reply.
     */
    public function testAnyTrueMarkOfRemoteStorageCountsAsStoredOffsite(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->storeDocumentsRemotely('uploaded');

        $this->assertIsString((new ReferralTaskService(new UserService(), logger: self::logger()))->recordReply($id, null));

        $this->assertSame('completed', $this->onlyTask($id)['status'] ?? null);
        $this->assertSame([], $this->replyDocuments($id));
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
            $this->assertNull((new ReferralTaskService(new UserService(), logger: self::logger()))->recordReply($id, null));
        } finally {
            QueryUtils::sqlStatementThrowException("UPDATE categories SET name = 'Medical Record' WHERE id IN ($marks)", $categories);
        }

        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null);
        $this->assertSame([], $this->replyDocuments($id));

        $this->afterSave($id);
        $this->assertSame('completed', $this->onlyTask($id)['status'] ?? null, 'the next save files it');
    }

    /**
     * A Task's version, as the task table has it.
     */
    private function taskVersion(string $taskUuid): int
    {
        $version = QueryUtils::fetchSingleValue('SELECT version FROM task WHERE uuid = ?', 'version', [UuidRegistry::uuidToBytes($taskUuid)]);
        $this->assertIsNumeric($version);
        return (int)$version;
    }

    /**
     * A Task as TaskService reads it, as FhirTaskService hands it to the referral's notice.
     *
     * @return array<string, mixed>
     */
    private function taskRecord(string $taskUuid): array
    {
        $task = (new TaskService())->getOne($taskUuid);
        $this->assertIsArray($task);
        return $task;
    }

    /**
     * A TaskService whose updates fail, as when a save files the reply and then loses the Task's update.
     */
    private static function tasksThatFailToUpdate(): TaskService
    {
        return new class extends TaskService {
            public function update(string $uuid, array $fields, ?int $userId, ?int $expectedVersion = null, ?array $inputs = null, ?array $outputs = null): bool
            {
                throw new \RuntimeException('the Task could not be updated');
            }
        };
    }

    /**
     * A field in the referral's layout (a single-line text field), removed in tearDown().
     */
    private function addLayoutField(string $field, string $group, string $title, int $seq, int $uor): void
    {
        $this->layoutFields[] = $field;
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor) VALUES ('LBTref', ?, ?, ?, ?, 2, ?)",
            [$field, $group, $title, $seq, $uor]
        );
    }

    /**
     * Moves the Counter-Referral fields (reply_*) to another group of the referral's layout, as a
     * site that keeps the whole referral in one group has them; tearDown() puts them back.
     */
    private function moveReplyFieldsTo(string $group): void
    {
        $fields = [];
        foreach (QueryUtils::fetchTableColumn("SELECT field_id FROM layout_options WHERE form_id = 'LBTref' AND field_id LIKE 'reply\\_%'", 'field_id') as $field) {
            if (is_string($field)) {
                $fields[] = $field;
            }
        }
        $this->moveLayoutFields($group, $fields);
    }

    /**
     * Moves fields of the referral's layout to another group, as a site may have rearranged them;
     * tearDown() puts them back.
     *
     * @param list<string> $fields
     */
    private function moveLayoutFields(string $group, array $fields): void
    {
        $marks = implode(', ', array_fill(0, count($fields), '?'));
        $rows = QueryUtils::fetchRecords("SELECT field_id, seq, group_id FROM layout_options WHERE form_id = 'LBTref' AND field_id IN ($marks)", $fields);
        $this->assertCount(count($fields), $rows);
        foreach ($rows as $row) {
            $field = $row['field_id'] ?? null;
            $this->assertIsString($field);
            $this->movedLayoutFields[] = [$field, $row['seq'] ?? null, $row['group_id'] ?? null];
            QueryUtils::sqlStatementThrowException("UPDATE layout_options SET group_id = ? WHERE form_id = 'LBTref' AND field_id = ? AND seq = ?", [$group, $field, $row['seq'] ?? null]);
        }
    }

    /**
     * Marks documents as an offsite store's PatientDocumentStoreOffsite listener does, so
     * Document::createDocument() writes no documents row; tearDown() puts it back.
     */
    private function storeDocumentsRemotely(mixed $mark = true): void
    {
        $this->remoteStorageBefore ??= [OEGlobalsBag::getInstance()->has('documentStoredRemotely'), OEGlobalsBag::getInstance()->get('documentStoredRemotely')];
        OEGlobalsBag::getInstance()->set('documentStoredRemotely', $mark);
    }

    /**
     * Gives the session a language whose translations are these, as a user who works in another
     * language has; tearDown() puts the session's language back. The translation cache holds them,
     * so xl() finds them without a row in the translation tables.
     *
     * @param array<string, string> $translations
     */
    private function useLanguage(array $translations): void
    {
        $this->setGlobal('disable_translation', '0');
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $this->languageBefore ??= [$session->has('language_choice'), $session->get('language_choice')];
        foreach ($translations as $constant => $definition) {
            TranslationCache::set(self::TEST_LANGUAGE, $constant, $definition);
        }
        $session->set('language_choice', self::TEST_LANGUAGE);
    }

    /**
     * A ReferralTaskService whose first write to the Task finds the recipient changed it just
     * before: the change is made at the version the reply read, then the reply's write goes ahead.
     *
     * @param array<string, string> $change
     * @param ?list<array<string, string>> $outputs the recipient's outputs, or null to leave them
     * @param (\Closure(): Document)|null $documentFactory
     */
    private function replyRacing(array $change, ?array $outputs = null, ?AbstractLogger $logger = null, ?\Closure $documentFactory = null): ReferralTaskService
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
        return new ReferralTaskService(new UserService(), taskService: $tasks, logger: $logger ?? self::logger(), documentFactory: $documentFactory);
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
        (new ReferralTaskService(new UserService()))->afterSave($transactionId, null);
    }

    /**
     * A save from the Transactions screen whose form showed this Reply Date ('' for none) and one of
     * the test's people as Refer To (null for none), posted with the Reply Date's own field left
     * blank, or without it, as a read-only one (disabled) is, and handed over as add_transaction.php
     * hands it.
     */
    private function saveFromForm(int $transactionId, string $replyDateLoaded, ?string $personLoaded, bool $replyDatePosted = true, ?AbstractLogger $logger = null): void
    {
        $posted = [
            ReferralFormLoaded::REPLY_DATE_FIELD => $replyDateLoaded,
            ReferralFormLoaded::REFER_TO_FIELD => $personLoaded === null ? '' : (string)$this->people[$personLoaded]['id'],
        ] + ($replyDatePosted ? ['form_reply_date' => ''] : []);
        (new ReferralTaskService(new UserService(), logger: $logger))
            ->createForSavedTransaction('LBTref', $transactionId, null, ReferralFormLoaded::fromRequest(new Request([], $posted)));
    }

    /**
     * The referral's Task status as ReferralService reads it (taskStatusSql()).
     *
     * @phpstan-impure the Task changes between reads
     */
    private function taskStatus(int $transactionId): ?string
    {
        $referral = (new ReferralService(createMissingUuids: false))->getReferral($transactionId);
        $this->assertIsArray($referral);
        $status = $referral['task_status'] ?? null;
        $this->assertTrue($status === null || is_string($status));
        return $status;
    }

    /**
     * Changes a Task's status through PUT /fhir/Task, as the receiving system would.
     *
     * @param array<mixed> $task
     * @param array<string, mixed> $elements other elements the PUT sets (statusReason, say)
     */
    private function putStatus(array $task, string $status, array $elements = []): int
    {
        $uuid = $task['id'] ?? null;
        $this->assertIsString($uuid);
        $body = [];
        foreach ($task as $element => $value) {
            if (is_string($element) && $element !== 'meta') {
                $body[$element] = $value;
            }
        }
        $body = array_merge($body, $elements);
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
     * Each of the referral's Tasks, read through the API.
     *
     * @return list<array<mixed>>
     * @phpstan-impure the Tasks change between reads
     */
    private function tasksOf(int $transactionId): array
    {
        $response = $this->testClient->get(self::FHIR_URL . 'Task', ['focus' => 'ServiceRequest/' . $this->referralUuid($transactionId)]);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $bundle = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($bundle);
        $tasks = [];
        foreach (is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [] as $entry) {
            if (is_array($entry) && is_array($entry['resource'] ?? null)) {
                $tasks[] = $entry['resource'];
            }
        }
        return $tasks;
    }

    /**
     * The referral's uuid, as text.
     */
    private function referralUuid(int $transactionId): string
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$transactionId]);
        $this->assertIsString($uuid);
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * The referral's Task that one of the test's people owns, read through the API.
     *
     * @return array<mixed>
     * @phpstan-impure the Task changes between reads
     */
    private function taskOf(int $transactionId, string $person): array
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$transactionId]);
        $this->assertIsString($uuid);
        $response = $this->testClient->get(self::FHIR_URL . 'Task', [
            'focus' => 'ServiceRequest/' . UuidRegistry::uuidToString($uuid),
            'owner' => 'Organization/' . $this->people[$person]['uuid'],
        ]);
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
     * A Task of the referral's own kind (code fulfill, not part of another) for one of the test's
     * people, made through POST /fhir/Task as an API client would make it.
     *
     * @return string its uuid
     */
    private function postTaskFor(int $transactionId, string $person): string
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$transactionId]);
        $this->assertIsString($uuid);
        $response = $this->testClient->post(self::FHIR_URL . 'Task', [
            'resourceType' => 'Task',
            'status' => 'requested',
            'intent' => 'order',
            'code' => ['coding' => [['system' => FhirTaskService::TASK_CODE_SYSTEM, 'code' => 'fulfill']]],
            'for' => ['reference' => 'Patient/' . $this->patient['uuid']],
            'focus' => ['reference' => 'ServiceRequest/' . UuidRegistry::uuidToString($uuid)],
            'owner' => ['reference' => 'Organization/' . $this->people[$person]['uuid']],
        ]);
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), (string)$response->getBody());
        $task = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($task);
        $taskUuid = $task['id'] ?? null;
        $this->assertIsString($taskUuid);
        return $taskUuid;
    }

    /**
     * The text of the document a Task's output refers to.
     *
     * @param array<mixed> $task
     */
    private function outputText(array $task, int $output): string
    {
        $reference = self::at($task, 'output', $output, 'valueReference', 'reference');
        $this->assertIsString($reference);
        $this->assertStringStartsWith('DocumentReference/', $reference);
        $documentId = QueryUtils::fetchSingleValue('SELECT id FROM documents WHERE uuid = ?', 'id', [UuidRegistry::uuidToBytes(substr($reference, strlen('DocumentReference/')))]);
        $this->assertIsNumeric($documentId);
        $text = (new Document((int)$documentId))->get_data();
        $this->assertIsString($text);
        return $text;
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

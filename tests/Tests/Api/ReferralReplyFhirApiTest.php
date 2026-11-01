<?php

/**
 * A referral's reply and its Task (#14710): a reply recorded in the Counter-Referral section
 * completes the Task with the reply filed as its output, and the one who made the referral
 * hears when the receiving system accepts, rejects, completes or fails the Task through the API.
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
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Translation\TranslationCache;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTask;
use OpenEMR\Services\FHIR\FhirTaskService;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\ReferralService;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Services\TaskService;
use OpenEMR\Services\UserService;
use OpenEMR\Services\Utils\DateFormatterUtils;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
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

    /** @var list<string> the fields a test added to the referral's layout */
    private array $layoutFields = [];

    /** @var list<array{string, mixed, mixed}> the layout rows a test moved to another group: field, seq, the group it had */
    private array $movedLayoutFields = [];

    /** The language id useLanguage() gives the session: one no site has, whose translations the test sets. */
    private const TEST_LANGUAGE = 9999;

    /** @var ?array{bool, mixed} whether the session had a language before useLanguage(), and which */
    private ?array $languageBefore = null;

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

    protected function tearDown(): void
    {
        foreach ($this->movedLayoutFields as [$field, $seq, $group]) {
            QueryUtils::sqlStatementThrowException("UPDATE layout_options SET group_id = ? WHERE form_id = 'LBTref' AND field_id = ? AND seq = ?", [$group, $field, $seq]);
        }
        foreach ($this->layoutFields as $field) {
            QueryUtils::sqlStatementThrowException("DELETE FROM layout_options WHERE form_id = 'LBTref' AND field_id = ?", [$field]);
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
        // The fixture removes its patient but not the patient's uuid.
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'patient_data' AND uuid = ?", [UuidRegistry::uuidToBytes($this->patient['uuid'])]);
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
        // The seeded layout's titles, in its order. The text depends only on the stored layout and
        // values, so a retry finds the reply it filed.
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
     * The notice and the filed reply keep the referral's dates as stored (YYYY-MM-DD), whatever date
     * format the site or the user saving it shows: the text is stored, then read by people whose
     * formats differ and by the receiving system, and a retry finds the filed reply by its text.
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

        (new ReferralTaskService(clock: $clock))->taskStatusChanged($this->taskRecord($taskUuid), 'requested', 'rejected', null);

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
        $notices = new class extends ReferralTaskService {
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
     * isn't the recipient's: accepted, it files no message, which would say Refer To accepted.
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
        (new ReferralTaskService(logger: $logger))->taskStatusChanged($this->taskRecord($taskUuid), 'accepted', 'completed', null);
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

        (new ReferralTaskService(referralService: $referrals, logger: $logger))->taskStatusChanged($task, 'requested', 'accepted', null);

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

        $this->assertNull((new ReferralTaskService(taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
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
     * A reply edited after a save that filed it and then lost the Task's update is a new reply:
     * the next save files it and completes the Task with it, not with the text it replaced.
     */
    public function testAReplyEditedAfterItsTaskUpdateFailedIsFiledAnew(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->assertNull((new ReferralTaskService(taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
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
     * language, is the same reply: its text takes the layout's titles as stored, not translated, so
     * the retry finds the filed document by its text and files no second copy.
     */
    public function testAReplyRetriedInAnotherLanguageIsNotFiledAgain(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-10', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);
        $this->afterSave($id);
        $this->saveFields($id, ['reply_date' => '2026-10-18', 'reply_findings' => 'Enrolled']);
        $this->assertNull((new ReferralTaskService(taskService: self::tasksThatFailToUpdate(), logger: self::logger()))->afterSave($id, null));
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
        $lockMissed = new class extends ReferralTaskService {
            // As createForReferral() returns when the lock isn't acquired: before it cancels anything.
            public function createForReferral(int $transactionId, ?int $userId): ?string
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

        $this->assertNull((new ReferralTaskService(logger: $logger, documentFactory: $unsaved))->recordReply($id, null));

        $this->assertSame('requested', $this->onlyTask($id)['status'] ?? null);
        $this->assertSame([['warning', 'A referral\'s reply was not filed, so its Task was left open']], $logger->records);
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

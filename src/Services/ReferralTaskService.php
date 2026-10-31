<?php

/**
 * The FHIR Task that tracks a referral (#14710). When the fhir_referral_tasks global is on
 * and a referral (the Transactions screen's LBTref form) is saved, its recipient gets a
 * Task: status requested, intent order, code fulfill, focus the referral's ServiceRequest,
 * for its patient, owned by the one it is sent to (Refer To) and requested by the one who
 * made it (Refer By). When the referral's Counter-Referral section records a reply, the
 * reply is filed as a document and becomes the Task's output, completing it. When the
 * receiving system accepts, rejects or completes the Task through the FHIR API, the one who
 * made the referral gets a message.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\FhirTaskService;
use OpenEMR\Services\FHIR\FhirTaskWriteRules;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\Search\TokenSearchField;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

class ReferralTaskService
{
    /** FHIR Task statuses a Task doesn't move on from. */
    private const FINISHED_STATUSES = ['completed', 'cancelled', 'entered-in-error', 'failed', 'rejected'];

    /** GET_LOCK's name for a referral's Task, prefixed as core's other advisory locks are (within MySQL's 64 characters). */
    private const LOCK_PREFIX = 'openemr.referral_task.';

    /** How long a save waits for another save of the same referral to finish making its Task. */
    private const LOCK_SECONDS = 5;

    /** The reasons this service cancels a Task with. A Task a recipient cancelled carries its own, or none. */
    public const REASON_SENT_ELSEWHERE = 'The referral was sent to another recipient.';
    public const REASON_NO_RECIPIENT = 'The referral no longer names a recipient.';
    public const REASON_DELETED = 'The referral was deleted.';
    public const OWN_REASONS = [self::REASON_SENT_ELSEWHERE, self::REASON_NO_RECIPIENT, self::REASON_DELETED];

    /** How many times a cancellation reads again when a recipient's change slips in between its read and its write. */
    private const CANCEL_PASSES = 3;

    /**
     * Each collaborator is the caller's, or made the first time it's needed rather than as a default
     * here: loading BaseService requires custom/code_types.inc.php, which queries the database, and
     * ReferralTaskServiceTest's isolated cases build this class.
     */
    public function __construct(
        private ?ReferralService $referralService = null,
        private ?TaskService $taskService = null,
        private ?ClockInterface $clock = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    private function getReferralService(): ReferralService
    {
        return $this->referralService ??= new ReferralService(createMissingUuids: false);
    }

    private function getTaskService(): TaskService
    {
        return $this->taskService ??= new TaskService();
    }

    private function getClock(): ClockInterface
    {
        return $this->clock ??= ServiceContainer::getClock();
    }

    private function getLogger(): LoggerInterface
    {
        return $this->logger ??= ServiceContainer::getLogger();
    }

    /** LOINC 11488-4, the type of the reply a Task's output holds. */
    private const REPLY_OUTPUT_TYPE = ['type_system' => 'http://loinc.org', 'type_code' => '11488-4', 'type_text' => 'Consult note'];

    /** Task statuses the referring provider hears about (#14710). */
    private const NOTIFIED_STATUSES = ['accepted', 'rejected', 'completed'];

    /** The Counter-Referral section's fields, in the form's order. */
    private const REPLY_FIELDS = ['reply_date', 'reply_from', 'reply_init_diag', 'reply_final_diag', 'reply_documents', 'reply_findings', 'reply_services', 'reply_recommend', 'reply_rx_refer'];

    /**
     * After a transaction is saved: a Task for its referral's recipient, unless they have one.
     * One Task per referral and recipient, so saving the referral again adds none. When Refer To
     * changes, or is cleared, the open Tasks of anyone else are cancelled with the reason, so they
     * leave that recipient's queue and stay as a record; a recipient the referral returns to gets a
     * new Task (one this service cancelled doesn't count, one they cancelled or finished does).
     * No new Task when the global is off, the referral has a Reply Date (it's answered), or the
     * recipient isn't a Practitioner or Organization in FHIR, since a Task's owner has to be one.
     * With the global off, a referral that already has open Tasks still has the other recipients'
     * cancelled, so turning the setting off doesn't leave them in a queue.
     *
     * Only the Tasks this service makes count as the referral's: code fulfill, not part of another.
     *
     * @return ?string the new Task's uuid
     */
    public function createForReferral(int $transactionId, ?int $userId): ?string
    {
        $tracking = OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value);
        if (!$tracking && !self::hasOpenTasks($transactionId)) {
            return null;
        }
        self::createMissingUuids($transactionId);
        $referralUuid = $this->getReferralService()->getReferralUuid($transactionId);
        if ($referralUuid === null) {
            return null;
        }
        // Two saves of the referral at once would each find no Task and each make one, and the task
        // table doesn't prevent it: a lock on the referral is held across the check and the insert,
        // so the second save finds the first one's Task. The referral is read under the lock, so each
        // save acts on the Refer To saved last.
        $lock = self::LOCK_PREFIX . $referralUuid;
        $locked = self::lock($lock);
        if ($locked !== true) {
            // Another save held it past the wait, or GET_LOCK failed: the Task comes with the referral's
            // next save, and the log names the referral.
            $this->getLogger()->warning('A referral\'s Task was not made: its lock was not acquired', [
                'referral_uuid' => $referralUuid,
                'reason' => $locked === false ? 'another save held it past the wait' : 'GET_LOCK failed',
            ]);
            return null;
        }
        try {
            $referral = $this->getReferralService()->getReferral($transactionId);
            if ($referral === null) {
                return null;
            }
            $patientId = $referral['patient_id'] ?? null;
            $owner = self::party($referral, 'performer');
            // Kept by Refer To's own uuid, whether or not FHIR serves them now: a recipient whose entry lost its
            // NPI still hasn't been sent elsewhere.
            $recipientUuid = self::text($referral, 'performer_uuid');
            $this->cancelOpenTasks(
                $referralUuid,
                $recipientUuid,
                $recipientUuid === null ? self::REASON_NO_RECIPIENT : self::REASON_SENT_ELSEWHERE,
                $userId
            );
            if (
                !$tracking || $owner === null || !is_numeric($patientId)
                || self::text($referral, 'reply_date') !== null || $this->hasTask($referralUuid, $owner[1])
            ) {
                return null;
            }
            $requester = self::party($referral, 'requester');
            return $this->getTaskService()->insert([
                'status' => 'requested',
                'intent' => 'order',
                'code' => 'fulfill',
                // #14669 renders a code only with its system, so the referral's Task records task-code's.
                'code_system' => FhirTaskService::TASK_CODE_SYSTEM,
                'focus_type' => 'ServiceRequest',
                'focus_uuid' => $referralUuid,
                'owner_type' => $owner[0],
                'owner_uuid' => $owner[1],
                'requester_type' => $requester[0] ?? null,
                'requester_uuid' => $requester[1] ?? null,
                'authored_on' => $this->getClock()->now()->format('Y-m-d H:i:s'),
            ], (int)$patientId, $userId);
        } finally {
            self::unlock($lock);
        }
    }

    /**
     * createForReferral() for whatever the Transactions screen just saved: nothing unless it
     * is a referral, with its id and the signed-in user's as the screen has them.
     *
     * @return ?string the new Task's uuid
     */
    public function createForSavedTransaction(mixed $formId, mixed $transactionId, mixed $userId): ?string
    {
        if ($formId !== ReferralStatus::FORM_ID || !is_numeric($transactionId)) {
            return null;
        }
        return $this->afterSave((int)$transactionId, is_numeric($userId) ? (int)$userId : null);
    }

    /**
     * After a referral is saved and committed: its Task (createForReferral()), and the Task's
     * completion when the referral records a reply (recordReply()). A failure is logged, not thrown,
     * since the referral is saved either way and its next save makes up what this one missed (a
     * recipient that has a Task is skipped, and a reply completes a Task that isn't final yet).
     * Thrown, it would answer a REST client's successful save with an error, and a retry would save
     * the referral twice. It catches every exception a library throws (RuntimeException,
     * LogicException, the audit log's DBAL ones) but not \Error, which the project's
     * ForbiddenCatchTypeRule keeps visible: a TypeError is a bug.
     *
     * @return ?string the new Task's uuid; null when none was made or a step failed
     */
    public function afterSave(int $transactionId, ?int $userId): ?string
    {
        try {
            $uuid = $this->createForReferral($transactionId, $userId);
            $this->recordReply($transactionId, $userId);
            return $uuid;
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            $this->getLogger()->error('A referral was saved but its FHIR Task was not updated', [
                'transaction_id' => $transactionId,
                'exception' => $e,
            ]);
            return null;
        }
    }

    /**
     * When a referral records a reply (a Reply Date), its latest Task is completed, with the
     * Counter-Referral section filed in the patient's documents as its output. Nothing when
     * the global is off, there's no Task, or the Task is already in a final status, so saving
     * the referral again files no second document.
     *
     * @return ?string the completed Task's uuid
     */
    public function recordReply(int $transactionId, ?int $userId): ?string
    {
        if (!OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)) {
            return null;
        }
        $referral = $this->getReferralService()->getReferral($transactionId);
        $referralUuid = $referral === null ? null : self::text($referral, 'uuid');
        $patientId = $referral['patient_id'] ?? null;
        if ($referral === null || $referralUuid === null || !is_numeric($patientId) || self::text($referral, 'reply_date') === null) {
            return null;
        }
        $task = $this->latestTask($referralUuid);
        $status = $task === null ? null : self::text($task, 'status');
        $taskUuid = $task === null ? null : self::text($task, 'uuid');
        if ($task === null || $taskUuid === null || $status === null || in_array($status, FhirTaskWriteRules::FINAL_STATUSES, true)) {
            return null;
        }
        $outputs = [];
        foreach (is_array($task['outputs'] ?? null) ? $task['outputs'] : [] as $output) {
            $row = [];
            foreach (is_array($output) ? $output : [] as $column => $value) {
                if (is_string($column) && (is_string($value) || $value === null)) {
                    $row[$column] = $value;
                }
            }
            if ($row !== []) {
                $outputs[] = $row;
            }
        }
        $document = $this->fileReply($transactionId, (int)$patientId, $referral, $userId);
        if ($document !== null) {
            $outputs[] = self::REPLY_OUTPUT_TYPE + ['value_element' => 'valueReference', 'value_type' => 'DocumentReference', 'value_uuid' => $document];
        }
        $this->getTaskService()->update($taskUuid, ['status' => 'completed'], $userId, null, null, $outputs);
        return $taskUuid;
    }

    /**
     * After the FHIR API changes a Task's status: when the Task tracks a referral and is now
     * accepted, rejected or completed, a message to the one who made the referral (Refer By),
     * in their inbox under the Referral type.
     */
    public function taskStatusChanged(string $taskUuid, string $from, string $to, ?int $userId): void
    {
        if ($from === $to || !in_array($to, self::NOTIFIED_STATUSES, true)) {
            return;
        }
        $task = $this->getTaskService()->getOne($taskUuid);
        $focus = $task === null ? null : self::text($task, 'focus_uuid');
        if ($task === null || ($task['focus_type'] ?? null) !== 'ServiceRequest' || $focus === null) {
            return;
        }
        $referral = $this->getReferralService()->getReferralByUuid($focus);
        $referrer = $referral['refer_from'] ?? null;
        $patientId = $referral['patient_id'] ?? null;
        if ($referral === null || !is_numeric($referrer) || !is_numeric($patientId)) {
            return;
        }
        $recipient = QueryUtils::fetchSingleValue('SELECT username FROM users WHERE id = ?', 'username', [(int)$referrer]);
        if (!is_string($recipient) || $recipient === '') {
            return;
        }
        $sender = $userId === null ? null : QueryUtils::fetchSingleValue('SELECT username FROM users WHERE id = ?', 'username', [$userId]);
        $sender = is_string($sender) && $sender !== '' ? $sender : 'FHIR API';
        $text = sprintf(
            '%s %s: %s',
            xl('Referral to'),
            self::text($referral, 'performer_name') ?? xl('Unknown recipient'),
            match ($to) {
                'accepted' => xl('Accepted'),
                'rejected' => xl('Rejected'),
                default => xl('Completed'),
            }
        );
        $referred = self::text($referral, 'refer_date');
        if ($referred !== null) {
            $text .= ' (' . xl('Referral Date') . ' ' . $referred . ')';
        }
        // As other background notices are filed (labNotice(), the Direct message check).
        QueryUtils::sqlInsert(
            'INSERT INTO pnotes (date, body, pid, user, groupname, authorized, activity, title, assigned_to, message_status) VALUES (NOW(), ?, ?, ?, ?, 0, 1, ?, ?, ?)',
            [date('Y-m-d H:i') . " ($sender to $recipient) $text", (int)$patientId, $sender, 'Default', 'Referral', $recipient, 'New']
        );
    }

    /**
     * The referral's latest Task, as TaskService returns it, with its outputs.
     *
     * @return ?array<string, mixed>
     */
    private function latestTask(string $referralUuid): ?array
    {
        $tasks = $this->getTaskService()->search(['focus_uuid' => new TokenSearchField('focus_uuid', [$referralUuid], true)])->getData();
        $latest = null;
        foreach (is_array($tasks) ? $tasks : [] as $task) {
            if (is_array($task) && is_numeric($task['id'] ?? null) && ($latest === null || (int)$task['id'] > (int)$latest['id'])) {
                $latest = $task;
            }
        }
        if ($latest === null) {
            return null;
        }
        $record = [];
        foreach ($latest as $column => $value) {
            if (is_string($column)) {
                $record[$column] = $value;
            }
        }
        return $record;
    }

    /**
     * The Counter-Referral section as a text document in the patient's Medical Record,
     * linked to the referral. Returns its uuid, or null when there's no Medical Record
     * category or the document couldn't be saved.
     *
     * @param array<string, mixed> $referral
     */
    private function fileReply(int $transactionId, int $patientId, array $referral, ?int $userId): ?string
    {
        $category = QueryUtils::fetchSingleValue("SELECT id FROM categories WHERE name = 'Medical Record' ORDER BY id LIMIT 1", 'id', []);
        if (!is_numeric($category)) {
            return null;
        }
        $fields = [];
        foreach (QueryUtils::fetchRecords("SELECT field_id, field_value FROM lbt_data WHERE form_id = ?", [$transactionId]) as $row) {
            if (is_string($row['field_id'] ?? null) && is_string($row['field_value'] ?? null) && trim($row['field_value']) !== '') {
                $fields[$row['field_id']] = trim($row['field_value']);
            }
        }
        $labels = [
            'reply_date' => xl('Reply Date'), 'reply_from' => xl('Reply From'), 'reply_init_diag' => xl('Presumed Diagnosis'),
            'reply_final_diag' => xl('Final Diagnosis'), 'reply_documents' => xl('Documents'), 'reply_findings' => xl('Findings'),
            'reply_services' => xl('Services Provided'), 'reply_recommend' => xl('Recommendations'), 'reply_rx_refer' => xl('Prescriptions/Referrals'),
        ];
        $lines = [xl('Counter-Referral'), xl('Referral Date') . ': ' . ($fields['refer_date'] ?? ''), xl('Refer To') . ': ' . (self::text($referral, 'performer_name') ?? ''), ''];
        foreach (self::REPLY_FIELDS as $field) {
            if (isset($fields[$field])) {
                $lines[] = $labels[$field] . ': ' . $fields[$field];
            }
        }
        $data = implode("\n", $lines) . "\n";
        $owner = $userId ?? (new UserService())->getSystemUser()['id'] ?? 0;
        $document = new \Document();
        $error = $document->createDocument(
            (string)$patientId,
            (int)$category,
            'counter-referral-' . $transactionId . '.txt',
            'text/plain',
            $data,
            '',
            1,
            (int)$owner,
            null,
            null,
            (string)$transactionId,
            ReferralService::TABLE_NAME
        );
        if ($error !== '') {
            return null;
        }
        $id = $document->get_id();
        if (!is_numeric($id)) {
            return null;
        }
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM documents WHERE id = ?', 'uuid', [(int)$id]);
        return is_string($uuid) ? UuidRegistry::uuidToString($uuid) : null;
    }

    /**
     * Gives the referral, its patient and the two people it names a uuid when they have none
     * (the Transactions screen and the address book save rows without one), row by row, so a
     * save doesn't fill in every row of those tables.
     */
    private static function createMissingUuids(int $transactionId): void
    {
        $row = QueryUtils::querySingleRow(
            "SELECT t.pid,
                (SELECT d.field_value FROM lbt_data AS d WHERE d.form_id = t.id AND d.field_id = 'refer_from') AS refer_from,
                (SELECT d.field_value FROM lbt_data AS d WHERE d.form_id = t.id AND d.field_id = 'refer_to') AS refer_to
            FROM transactions AS t WHERE t.id = ? AND t.title = ?",
            [$transactionId, ReferralService::FORM_ID]
        );
        if (!is_array($row)) {
            return;
        }
        UuidRegistry::createMissingUuidForRow(ReferralService::TABLE_NAME, 'id', $transactionId);
        if (is_numeric($row['pid'] ?? null)) {
            UuidRegistry::createMissingUuidForRow('patient_data', 'pid', (int)$row['pid']);
        }
        foreach (['refer_from', 'refer_to'] as $person) {
            if (is_numeric($row[$person] ?? null)) {
                UuidRegistry::createMissingUuidForRow('users', 'id', (int)$row[$person]);
            }
        }
    }

    /**
     * A deleted referral's open Tasks are cancelled, so a recipient isn't left with a Task whose
     * focus is gone. Called before the transaction is deleted; a finished Task stays as it is.
     *
     * @return int how many Tasks were cancelled
     */
    public function cancelForDeletedReferral(int $transactionId, ?int $userId): int
    {
        $uuid = $this->getReferralService()->getReferralUuid($transactionId);
        return $uuid === null ? 0 : $this->cancelOpenTasks($uuid, null, self::REASON_DELETED, $userId);
    }

    /**
     * cancelForDeletedReferral() for each referral of a patient being deleted, before their
     * transactions go.
     *
     * @return int how many Tasks were cancelled
     */
    public function cancelForDeletedPatient(int $patientId, ?int $userId): int
    {
        $cancelled = 0;
        foreach (QueryUtils::fetchTableColumn('SELECT id FROM transactions WHERE pid = ? AND title = ?', 'id', [$patientId, ReferralService::FORM_ID]) as $id) {
            if (is_numeric($id)) {
                $cancelled += $this->cancelForDeletedReferral((int)$id, $userId);
            }
        }
        return $cancelled;
    }

    /**
     * Whether the referral has an open Task of this service's: with the global off, a save does
     * nothing more when it hasn't (one indexed query).
     */
    private static function hasOpenTasks(int $transactionId): bool
    {
        $found = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM transactions AS t
                JOIN task ON task.focus_type = 'ServiceRequest' AND task.focus_uuid = t.uuid
                WHERE t.id = ? AND t.title = ? AND task.code = 'fulfill' AND task.part_of_uuid IS NULL
                AND task.status NOT IN ('" . implode("', '", self::FINISHED_STATUSES) . "')",
            'n',
            [$transactionId, ReferralService::FORM_ID]
        );
        return is_numeric($found) && (int)$found > 0;
    }

    /**
     * Cancels the referral's open Tasks, all of them or all but one owner's, with the reason. Each
     * update names the version the search read, so a recipient's change in between isn't written
     * over. The Tasks are read again when one moved: a Task finished in between (a PUT to completed)
     * stays as it is; one still open (accepted, say) is cancelled at its new version.
     *
     * @return int how many Tasks were cancelled
     */
    private function cancelOpenTasks(string $referralUuid, ?string $keepOwnerUuid, string $reason, ?int $userId): int
    {
        $cancelled = 0;
        for ($pass = 0; $pass < self::CANCEL_PASSES; $pass++) {
            $moved = 0;
            foreach ($this->ownTasks($referralUuid) as $task) {
                $taskUuid = $task['uuid'] ?? null;
                $version = $task['version'] ?? null;
                if (
                    !is_string($taskUuid) || !is_numeric($version)
                    || in_array($task['status'] ?? null, self::FINISHED_STATUSES, true)
                    || ($keepOwnerUuid !== null && ($task['owner_uuid'] ?? null) === $keepOwnerUuid)
                ) {
                    continue;
                }
                if ($this->getTaskService()->update($taskUuid, ['status' => 'cancelled', 'status_reason' => $reason], $userId, (int)$version)) {
                    $cancelled++;
                } else {
                    $moved++;
                }
            }
            if ($moved === 0) {
                break;
            }
        }
        return $cancelled;
    }

    /**
     * The referral's Tasks that this service makes, optionally one owner's: code fulfill and not part
     * of another Task, so a recipient's own sub-Tasks with the same focus aren't taken for them.
     *
     * @return list<array<mixed>>
     */
    private function ownTasks(string $referralUuid, ?string $ownerUuid = null): array
    {
        $search = ['focus_uuid' => new TokenSearchField('focus_uuid', [$referralUuid], true)];
        if ($ownerUuid !== null) {
            $search['owner_uuid'] = new TokenSearchField('owner_uuid', [$ownerUuid], true);
        }
        $tasks = $this->getTaskService()->search($search)->getData();
        $own = [];
        foreach (is_array($tasks) ? $tasks : [] as $task) {
            if (is_array($task) && ($task['code'] ?? null) === 'fulfill' && in_array($task['part_of_uuid'] ?? null, [null, ''], true)) {
                $own[] = $task;
            }
        }
        return $own;
    }

    /**
     * GET_LOCK, as BackgroundServiceRunner takes its lock: true when it's acquired, false when another
     * session held it past the wait, null when GET_LOCK failed. The database session holds the lock, so
     * a process that dies releases it.
     */
    private static function lock(string $name): ?bool
    {
        $row = QueryUtils::querySingleRow('SELECT GET_LOCK(?, ?) AS got', [$name, self::LOCK_SECONDS], false);
        $got = is_array($row) ? ($row['got'] ?? null) : null;
        return match ($got) {
            1, '1' => true,
            0, '0' => false,
            default => null,
        };
    }

    private static function unlock(string $name): void
    {
        try {
            QueryUtils::fetchRecordsNoLog('SELECT RELEASE_LOCK(?) AS released', [$name]);
        } catch (\RuntimeException | \LogicException | DbalException) {
            // The database releases it anyway when the session ends, and a failure here mustn't take the
            // place of what the try returned or threw.
        }
    }

    /**
     * Whether the recipient has a Task for the referral: one they finished or cancelled themselves
     * stays theirs, but one this service cancelled (the referral went elsewhere, then came back)
     * doesn't count.
     */
    private function hasTask(string $referralUuid, string $ownerUuid): bool
    {
        foreach ($this->ownTasks($referralUuid, $ownerUuid) as $task) {
            if (($task['status'] ?? null) !== 'cancelled' || !in_array($task['status_reason'] ?? null, self::OWN_REASONS, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * The resource type and uuid FHIR serves a referral's requester or recipient as, or null.
     *
     * @param array<string, mixed> $referral
     * @return ?array{string, string}
     */
    private static function party(array $referral, string $prefix): ?array
    {
        $uuid = self::text($referral, $prefix . '_uuid');
        if ($uuid === null) {
            return null;
        }
        if (in_array($referral[$prefix . '_is_practitioner'] ?? null, [1, '1', true], true)) {
            return ['Practitioner', $uuid];
        }
        if (in_array($referral[$prefix . '_is_organization'] ?? null, [1, '1', true], true)) {
            return ['Organization', $uuid];
        }
        return null;
    }

    /**
     * @param array<string, mixed> $record
     */
    private static function text(array $record, string $column): ?string
    {
        $value = $record[$column] ?? null;
        return is_string($value) && trim($value) !== '' ? $value : null;
    }
}

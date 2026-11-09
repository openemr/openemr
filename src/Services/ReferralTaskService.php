<?php

/**
 * The FHIR Task that tracks a referral (#14710). When the fhir_referral_tasks global is on
 * and a referral (the Transactions screen's LBTref form) is saved, its recipient gets a
 * Task: status requested, intent order, code fulfill, focus the referral's ServiceRequest,
 * for its patient, owned by the one it is sent to (Refer To) and requested by the one who
 * made it (Refer By). When the referral's Counter-Referral section records a reply, the
 * reply is filed as a document and becomes the Task's output, completing it; a Reply Date
 * cleared again on the referral screen marks that Task entered-in-error, and the recipient gets
 * a new one when FHIR serves them. When the Task is marked accepted, rejected, completed,
 * failed or cancelled through the FHIR API, the one who made the referral gets a message.
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

    /**
     * The recipient's answers a referral can be sent to them again after: they turned it down, couldn't
     * do it, or called it off. A status of this kind with one of this service's own reasons isn't
     * their answer, and doesn't count for them (countsForRecipient()).
     */
    private const RESENDABLE_STATUSES = ['rejected', 'failed', 'cancelled'];

    /** GET_LOCK's name for a referral's Task, prefixed as core's other advisory locks are (within MySQL's 64 characters). */
    private const LOCK_PREFIX = 'openemr.referral_task.';

    /** How long a save waits for another save of the same referral to finish making its Task. */
    private const LOCK_SECONDS = 5;

    /**
     * The reasons this service sets a Task aside with: it cancels one when the referral goes elsewhere,
     * loses its recipient or is deleted, and marks one entered-in-error when the reply that completed it
     * is cleared. A Task a recipient cancelled carries its own reason, or none.
     */
    public const REASON_SENT_ELSEWHERE = 'The referral was sent to another recipient.';
    public const REASON_NO_RECIPIENT = 'The referral no longer names a recipient.';
    public const REASON_DELETED = 'The referral was deleted.';
    public const REASON_REPLY_CLEARED = 'The referral\'s reply was cleared.';
    public const OWN_REASONS = [self::REASON_SENT_ELSEWHERE, self::REASON_NO_RECIPIENT, self::REASON_DELETED, self::REASON_REPLY_CLEARED];

    /** The statuses this service sets a Task aside in, each time with one of its own reasons (OWN_REASONS). */
    public const SET_ASIDE_STATUSES = ['cancelled', 'entered-in-error'];

    /**
     * The reason on a Task a reply completed, so a later save can tell it from one the recipient
     * completed: only this one is undone when the Reply Date is cleared.
     */
    public const REASON_REPLY_RECORDED = 'Completed by the reply recorded on the referral.';

    /** How many times a cancellation reads again when a recipient's change slips in between its read and its write. */
    private const CANCEL_PASSES = 3;

    /** How many times a reply reads the Task again when a recipient's change slips in between its read and its write. */
    private const WRITE_PASSES = 3;

    /**
     * The UserService (the system user a reply no signed-in user saved is filed under) is the
     * caller's. The other collaborators are the caller's, or made the first time they're needed
     * rather than as defaults here: loading BaseService requires custom/code_types.inc.php, which
     * queries the database, and ReferralTaskServiceTest's isolated cases build this class. Each reply
     * is a new Document, the record being made rather than a service, so a test can only swap in
     * how one is made.
     *
     * @param (\Closure(): \Document)|null $documentFactory makes the Document a reply is filed as
     */
    public function __construct(
        private readonly UserService $userService,
        private ?ReferralService $referralService = null,
        private ?TaskService $taskService = null,
        private ?ClockInterface $clock = null,
        private ?LoggerInterface $logger = null,
        private readonly ?\Closure $documentFactory = null,
        private ?ReferralNoticeService $noticeService = null,
    ) {
    }

    /**
     * The service that tells a recipient about their Task, made the first time it's needed.
     */
    private function getNoticeService(): ReferralNoticeService
    {
        return $this->noticeService ??= new ReferralNoticeService();
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

    private function newDocument(): \Document
    {
        return $this->documentFactory === null ? new \Document() : ($this->documentFactory)();
    }

    /**
     * LOINC 11488-4, the type of the reply a Task's output holds, and of the reply the eCQMs read
     * (Qdm\Services\ConsultantReportService).
     */
    public const REPLY_OUTPUT_TYPE = ['type_system' => 'http://loinc.org', 'type_code' => '11488-4', 'type_text' => 'Consult note'];

    /**
     * After a transaction is saved: a Task for its referral's recipient, unless they have one that
     * counts (recipientTask()), so saving the referral again adds none. A recipient gets another
     * Task only when this service has set theirs aside. When Refer To changes, or is cleared, the
     * open Tasks of anyone else are cancelled with the reason, so they leave that recipient's queue
     * and stay as a record; a recipient the referral returns to gets a new Task (one this service
     * cancelled doesn't count, one they cancelled or finished does). A save that cleared the Reply
     * Date its form showed first has the Task that reply completed undone: the Task of the recipient
     * the form showed, whether or not the same save sends the referral elsewhere or clears Refer To
     * (undoClearedReply()), so the same save gives Refer To a new Task.
     * No new Task when the global is off, the referral has a Reply Date (it's answered), or the
     * recipient isn't a Practitioner or Organization in FHIR, since a Task's owner has to be one.
     * With the global off, a referral that already has open Tasks still has the other recipients'
     * cancelled, so turning the setting off doesn't leave them in a queue.
     *
     * Only the Tasks this service makes count as the referral's: code fulfill, not part of another.
     *
     * @param ?ReferralFormLoaded $loaded what the saving form showed, before this save, when it posted
     *     the Reply Date's field (ReferralFormLoaded::fromRequest()); null from a caller with no form
     *     (the REST API, which never writes the Reply Date) or a form that didn't post the field, so
     *     its save never undoes a reply
     * With the fhir_referral_notify_recipient global on, the recipient is told about a new Task
     * (notifyRecipient()), once the referral's lock is released.
     *
     * @return ?string the new Task's uuid
     */
    public function createForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): ?string
    {
        $uuid = $this->createTaskForReferral($transactionId, $userId, $loaded);
        if ($uuid !== null) {
            $this->notifyRecipient($transactionId, $uuid, $userId);
        }
        return $uuid;
    }

    /**
     * createForReferral() without the notice.
     */
    private function createTaskForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded): ?string
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
            // next save, and the log names the referral. A cleared reply this save was to undo isn't
            // undone later (undoClearedReply()), so the log says so.
            $context = [
                'transaction_id' => $transactionId,
                'referral_uuid' => $referralUuid,
                'reason' => $locked === false ? 'another save held it past the wait' : 'GET_LOCK failed',
            ];
            if ($tracking && $loaded?->showedReply === true && self::storedReplyDate($transactionId) === null) {
                $this->getLogger()->warning('A referral\'s Task was not made and the reply its form cleared was not undone, nor will it be: its lock was not acquired', $context);
            } else {
                $this->getLogger()->warning('A referral\'s Task was not made: its lock was not acquired', $context);
            }
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
            if (!$tracking || self::text($referral, 'reply_date') !== null) {
                return null;
            }
            // When this save cleared the Reply Date its form showed, the Task that reply completed, the
            // recipient's the form showed, is undone first, whatever Refer To is now. A form that showed none
            // (a stale form, saved after another user recorded the reply), or didn't post the field (a
            // read-only Reply Date), undoes nothing.
            if ($loaded?->showedReply === true) {
                $this->undoClearedReply($transactionId, $referralUuid, $loaded->referTo, $userId);
            }
            if (
                $recipientUuid === null || $owner === null || !is_numeric($patientId)
                || $this->recipientTask($referralUuid, $recipientUuid) !== null
            ) {
                return null;
            }
            return $this->insertTask($referral, $referralUuid, $owner, (int)$patientId, $userId);
        } finally {
            self::unlock($lock);
        }
    }

    /**
     * The recipient's Task as a new one, requested of them.
     *
     * @param array<string, mixed> $referral as ReferralService::getReferral() reads it
     * @param array{string, string} $owner from party()
     * @return string the new Task's uuid
     */
    private function insertTask(array $referral, string $referralUuid, array $owner, int $patientId, ?int $userId): string
    {
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
        ], $patientId, $userId);
    }

    /**
     * The uuid of the Task a re-send would follow on from, which is what the referral screen's
     * Re-send button posts, or null when the referral can't be re-sent: the global is off, the
     * referral has a reply or no recipient FHIR serves, or the recipient's latest Task isn't one
     * they turned down, failed or cancelled (RESENDABLE_STATUSES).
     */
    public function resendableTask(int $transactionId): ?string
    {
        if (!OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)) {
            return null;
        }
        $referralUuid = $this->getReferralService()->getReferralUuid($transactionId);
        $referral = $referralUuid === null ? null : $this->getReferralService()->getReferral($transactionId);
        return $referralUuid === null || $referral === null ? null : $this->resendableTaskOf($referral, $referralUuid);
    }

    /**
     * Sends a referral to its recipient again after they turned it down, as a new requested Task. The
     * old one stays as the record, and the new one is the referral's status from here on, which
     * reopens it, since the latest Task that counts for the recipient decides (recipientTask(),
     * ReferralService::taskStatusSql()). A save of the referral never does this, so nobody re-sends by accident.
     *
     * It happens only if the Task the page showed is still the one to follow on from: a second click,
     * a stale page, or a recipient's change since then all make nothing new. It runs under the
     * referral's lock, as a save does.
     *
     * As with a new Task, the recipient is told about it (notifyRecipient()).
     *
     * @param string $taskUuid what resendableTask() found when the page was shown
     * @return ?string the new Task's uuid
     */
    public function resend(int $transactionId, string $taskUuid, ?int $userId): ?string
    {
        $uuid = $this->resendTask($transactionId, $taskUuid, $userId);
        if ($uuid !== null) {
            $this->notifyRecipient($transactionId, $uuid, $userId);
        }
        return $uuid;
    }

    /**
     * resend() without the notice.
     */
    private function resendTask(int $transactionId, string $taskUuid, ?int $userId): ?string
    {
        if (!OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)) {
            return null;
        }
        $referralUuid = $this->getReferralService()->getReferralUuid($transactionId);
        if ($referralUuid === null) {
            return null;
        }
        $lock = self::LOCK_PREFIX . $referralUuid;
        $locked = self::lock($lock);
        if ($locked !== true) {
            $this->getLogger()->warning('A referral was not re-sent: its lock was not acquired', [
                'transaction_id' => $transactionId,
                'referral_uuid' => $referralUuid,
                'reason' => $locked === false ? 'another save held it past the wait' : 'GET_LOCK failed',
            ]);
            return null;
        }
        try {
            $referral = $this->getReferralService()->getReferral($transactionId);
            $owner = $referral === null ? null : self::party($referral, 'performer');
            $patientId = $referral['patient_id'] ?? null;
            if ($referral === null || $owner === null || !is_numeric($patientId) || $this->resendableTaskOf($referral, $referralUuid) !== $taskUuid) {
                return null;
            }
            return $this->insertTask($referral, $referralUuid, $owner, (int)$patientId, $userId);
        } finally {
            self::unlock($lock);
        }
    }

    /**
     * The uuid of the recipient's Task a Direct reply can complete (completeWithDirectReply()), which
     * the referral page's Confirm button posts, or null when there's none to complete: the global is
     * off, the referral already records a reply or has no recipient, or the recipient's Task is final.
     */
    public function confirmableTask(int $transactionId): ?string
    {
        if (!OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)) {
            return null;
        }
        $referralUuid = $this->getReferralService()->getReferralUuid($transactionId);
        $referral = $referralUuid === null ? null : $this->getReferralService()->getReferral($transactionId);
        return $referralUuid === null || $referral === null ? null : $this->confirmableTaskOf($referral, $referralUuid);
    }

    /**
     * Staff confirm that a Direct reply filed with the referral (PhiMailReceiver matched it to a
     * message sent with it) answers it. A reply can be a question or a refusal, so it never completes
     * the Task on its own: this is the one click that does, as recording a Reply Date on the form does.
     *
     * The recipient's Task is completed with REASON_REPLY_RECORDED and the reply's documents as its
     * outputs (Consult note, REPLY_OUTPUT_TYPE): the attachments that came with it, or, for a reply with
     * only a text body, that text filed in the patient's documents. The referral's Reply Date is set to
     * the day the reply came, before the Task is completed and taken back out if it isn't, so the
     * referral reads as answered everywhere, and clearing it undoes the Task as it would any reply
     * (undoClearedReply()).
     *
     * It happens only if the Task the page showed is still the one to complete: a second click, a stale
     * page or a recipient's change since then all do nothing. It runs under the referral's lock, as a
     * save does.
     *
     * @param string $taskUuid what confirmableTask() found when the page was shown
     * @param int $replyId the reply's direct_message_log id
     * @return ?string the completed Task's uuid
     */
    public function completeWithDirectReply(int $transactionId, string $taskUuid, int $replyId, ?int $userId): ?string
    {
        if (!OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)) {
            return null;
        }
        $reply = self::directReply($transactionId, $replyId);
        $referralUuid = $this->getReferralService()->getReferralUuid($transactionId);
        if ($reply === null || $referralUuid === null) {
            return null;
        }
        $lock = self::LOCK_PREFIX . $referralUuid;
        $locked = self::lock($lock);
        if ($locked !== true) {
            $this->getLogger()->warning('A referral\'s Direct reply was not confirmed: its lock was not acquired', [
                'transaction_id' => $transactionId,
                'referral_uuid' => $referralUuid,
                'reason' => $locked === false ? 'another save held it past the wait' : 'GET_LOCK failed',
            ]);
            return null;
        }
        try {
            $referral = $this->getReferralService()->getReferral($transactionId);
            $patientId = $referral['patient_id'] ?? null;
            if ($referral === null || !is_numeric($patientId) || $this->confirmableTaskOf($referral, $referralUuid) !== $taskUuid) {
                return null;
            }
            $documents = $this->directReplyDocuments($transactionId, (int)$patientId, $replyId, $reply['note_id'], $userId);
            if ($documents === null) {
                $this->getLogger()->warning('A referral\'s Direct reply could not be filed, so its Task was left open', [
                    'transaction_id' => $transactionId,
                    'reply_id' => $replyId,
                ]);
                return null;
            }
            $recipientUuid = self::text($referral, 'performer_uuid');
            if ($recipientUuid === null) {
                return null;
            }
            // The Reply Date goes first, so a completed Task always has one: a Task left open with a
            // Reply Date is completed by the referral's next save (recordReply()), while a completed one
            // without it would never get one. It's taken back out when the Task isn't completed. It's only
            // claimed while the referral has none: a Reply Date a user saved since the page was shown wins,
            // and the confirm does nothing.
            $replyDate = substr($reply['received_at'], 0, 10);
            if (!self::claimReplyDate($transactionId, $replyDate)) {
                return null;
            }
            try {
                $completed = $this->completeRecipientTask($transactionId, $referralUuid, $recipientUuid, $taskUuid, $replyId, $documents, $userId);
            } catch (\RuntimeException | \LogicException | DbalException $e) {
                self::clearReplyDate($transactionId, $replyDate);
                throw $e;
            }
            if ($completed === null) {
                self::clearReplyDate($transactionId, $replyDate);
            }
            return $completed;
        } finally {
            self::unlock($lock);
        }
    }

    /**
     * Completes the recipient's Task with a Direct reply's documents as its outputs, reading it again
     * when the recipient changes it in between, as long as it's still the Task the page showed and open.
     *
     * @param list<string> $documents the reply's document uuids
     * @return ?string the Task's uuid, or null when it wasn't completed
     */
    private function completeRecipientTask(int $transactionId, string $referralUuid, string $recipientUuid, string $taskUuid, int $replyId, array $documents, ?int $userId): ?string
    {
        for ($pass = 0; $pass < self::WRITE_PASSES; $pass++) {
            $task = $this->recipientTask($referralUuid, $recipientUuid);
            $version = $task['version'] ?? null;
            if ($task === null || self::text($task, 'uuid') !== $taskUuid || !is_numeric($version)) {
                return null;
            }
            if (in_array(self::text($task, 'status'), FhirTaskWriteRules::FINAL_STATUSES, true)) {
                return null;
            }
            $outputs = self::outputRows($task);
            foreach ($documents as $document) {
                $outputs = self::withReply(['outputs' => $outputs], $document);
            }
            $completed = ['status' => 'completed', 'status_reason' => self::REASON_REPLY_RECORDED];
            if ($this->getTaskService()->update($taskUuid, $completed, $userId, (int)$version, null, $outputs)) {
                return $taskUuid;
            }
        }
        $this->getLogger()->warning("A referral's Direct reply was filed but its Task kept changing, so it was left as the recipient has it", [
            'transaction_id' => $transactionId,
            'reply_id' => $replyId,
        ]);
        return null;
    }

    /**
     * Takes the Reply Date that completeWithDirectReply() set back out, when its Task wasn't completed.
     * Only that date: the Transactions screen writes lbt_data before it takes the referral's lock, so a
     * Reply Date a user saved in between is theirs, and stays.
     */
    private static function clearReplyDate(int $transactionId, string $date): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date' AND field_value = ?", [$transactionId, $date]);
    }

    /**
     * @param array<string, mixed> $referral as ReferralService::getReferral() reads it
     */
    private function confirmableTaskOf(array $referral, string $referralUuid): ?string
    {
        $recipientUuid = self::text($referral, 'performer_uuid');
        if ($recipientUuid === null || self::text($referral, 'reply_date') !== null) {
            return null;
        }
        $task = $this->recipientTask($referralUuid, $recipientUuid);
        $status = $task === null ? null : self::text($task, 'status');
        return $task !== null && $status !== null && !in_array($status, FhirTaskWriteRules::FINAL_STATUSES, true) ? self::text($task, 'uuid') : null;
    }

    /**
     * A Direct reply filed with this referral, by its log id.
     *
     * @return ?array{received_at: string, note_id: ?int}
     */
    private static function directReply(int $transactionId, int $replyId): ?array
    {
        $row = QueryUtils::querySingleRow(
            "SELECT create_ts, note_id FROM direct_message_log WHERE id = ? AND msg_type = 'R' AND referral_id = ?",
            [$replyId, $transactionId]
        );
        if (!is_array($row) || !is_string($row['create_ts'] ?? null)) {
            return null;
        }
        return ['received_at' => $row['create_ts'], 'note_id' => is_numeric($row['note_id'] ?? null) ? (int)$row['note_id'] : null];
    }

    /**
     * The uuids of a Direct reply's documents in the patient's chart: those its note links to, or, with
     * none, its note's text filed as a document (once, by its name). Empty when an offsite store took
     * that text; null when it couldn't be filed.
     *
     * @return ?list<string>
     */
    private function directReplyDocuments(int $transactionId, int $patientId, int $replyId, ?int $noteId, ?int $userId): ?array
    {
        if ($noteId === null) {
            return null;
        }
        $rows = QueryUtils::fetchRecords(
            'SELECT d.id, d.uuid FROM gprelations AS g JOIN documents AS d ON d.id = g.id1
                WHERE g.type1 = 1 AND g.type2 = 6 AND g.id2 = ? AND d.foreign_id = ? AND d.deleted = 0 ORDER BY d.id',
            [$noteId, $patientId]
        );
        $documents = [];
        foreach ($rows as $row) {
            if (!is_numeric($row['id'] ?? null)) {
                continue;
            }
            if (!is_string($row['uuid'] ?? null)) {
                UuidRegistry::createMissingUuidForRow('documents', 'id', (int)$row['id']);
                $row['uuid'] = QueryUtils::fetchSingleValue('SELECT uuid FROM documents WHERE id = ?', 'uuid', [(int)$row['id']]);
            }
            if (is_string($row['uuid'])) {
                $documents[] = UuidRegistry::uuidToString($row['uuid']);
            }
        }
        if ($documents !== []) {
            return $documents;
        }
        $name = 'direct-reply-' . $replyId . '.txt';
        $filed = $this->filedReply($transactionId, $patientId, $name);
        if ($filed !== null) {
            return [$filed];
        }
        $body = QueryUtils::fetchSingleValue('SELECT body FROM pnotes WHERE id = ? AND pid = ?', 'body', [$noteId, $patientId]);
        if (!is_string($body) || trim($body) === '') {
            return null;
        }
        $document = $this->fileReply($transactionId, $patientId, $name, $body, $userId);
        return match (true) {
            is_string($document) => [$document],
            $document === false => [],
            default => null,
        };
    }

    /**
     * Sets the referral's Reply Date only if it has none (no row, or a blank one), so a date a user saved
     * in the meantime is never written over. The statements skip the SQL log, whose own insert would
     * otherwise be the row count read here.
     *
     * @return bool whether this call set it
     */
    private static function claimReplyDate(int $transactionId, string $date): bool
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE lbt_data SET field_value = ? WHERE form_id = ? AND field_id = 'reply_date' AND TRIM(COALESCE(field_value, '')) = ''",
            [$date, $transactionId],
            noLog: true
        );
        $updated = QueryUtils::affectedRows();
        if ($updated === 1) {
            return true;
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT IGNORE INTO lbt_data (form_id, field_id, field_value) VALUES (?, 'reply_date', ?)",
            [$transactionId, $date],
            noLog: true
        );
        return QueryUtils::affectedRows() === 1;
    }

    /**
     * Tells the recipient about the Task just made for them (ReferralNoticeService), which records how
     * it went for the referral's page. The Task is made either way: a notice that fails is logged and
     * recorded, never thrown, so a REST client's save doesn't answer with an error and get retried.
     */
    private function notifyRecipient(int $transactionId, string $taskUuid, ?int $userId): void
    {
        try {
            $this->getNoticeService()->notify($transactionId, $taskUuid, $userId);
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            $this->getLogger()->error("A referral's Task was made but its recipient's notice failed", [
                'transaction_id' => $transactionId,
                'task_uuid' => $taskUuid,
                'exception' => $e,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $referral as ReferralService::getReferral() reads it
     */
    private function resendableTaskOf(array $referral, string $referralUuid): ?string
    {
        $recipientUuid = self::text($referral, 'performer_uuid');
        if ($recipientUuid === null || self::text($referral, 'reply_date') !== null || self::party($referral, 'performer') === null) {
            return null;
        }
        $task = $this->recipientTask($referralUuid, $recipientUuid);
        return $task !== null && in_array($task['status'] ?? null, self::RESENDABLE_STATUSES, true) ? self::text($task, 'uuid') : null;
    }

    /**
     * afterSave() (the referral's Task, then its reply) for whatever the Transactions screen just
     * saved: nothing unless it is a referral, with its id, the signed-in user's and what the
     * screen's form showed, as the screen has them.
     *
     * @param ?ReferralFormLoaded $loaded what the form showed (ReferralFormLoaded::fromRequest()),
     *     which tells a Reply Date the user cleared from one a stale form deleted
     * @return ?string the new Task's uuid
     */
    public function createForSavedTransaction(mixed $formId, mixed $transactionId, mixed $userId, ?ReferralFormLoaded $loaded = null): ?string
    {
        if ($formId !== ReferralStatus::FORM_ID || !is_numeric($transactionId)) {
            return null;
        }
        return $this->afterSave((int)$transactionId, is_numeric($userId) ? (int)$userId : null, $loaded);
    }

    /**
     * After a referral is saved and committed: its Task (createForReferral()), and the Task's
     * completion when the referral records a reply (recordReply()). A failure is logged, not thrown,
     * since the referral is saved either way and its next save makes up what this one missed (a
     * recipient that has a Task is skipped, and a reply completes a Task that isn't final yet). An
     * undo of a cleared reply is the exception: the Reply Date is already deleted, so later forms
     * load none and the undo isn't retried. The log names the transaction, and re-entering the Reply
     * Date and clearing it again undoes it. Thrown, a failure would answer a REST client's
     * successful save with an error, and a retry would save the referral twice. It catches every
     * exception a library throws (RuntimeException, LogicException, the audit log's DBAL ones) but
     * not \Error, which the project's ForbiddenCatchTypeRule keeps visible: a TypeError is a bug.
     *
     * @param ?ReferralFormLoaded $loaded what the saving form showed (createForReferral())
     * @return ?string the new Task's uuid; null when none was made or a step failed
     */
    public function afterSave(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): ?string
    {
        try {
            $uuid = $this->createForReferral($transactionId, $userId, $loaded);
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
     * When a referral records a reply (a Reply Date), its recipient's Task (recipientTask(), the
     * one the referral's status reads) is completed, with the reply (replyText()) filed in the
     * patient's documents as its output and REASON_REPLY_RECORDED as its reason, so clearing the
     * Reply Date can undo it (undoClearedReply()). Nothing when the global is off, Refer To has no
     * Task, or the Task is already in a final status. A Task someone else owns (an earlier
     * recipient's still open, or one an API client made for another owner) isn't completed with it.
     *
     * It runs under the referral's lock, as createForReferral() does, so two saves at once file
     * one document. A reply is filed once: a save that finds the same reply already filed (an
     * earlier save filed it, then lost the Task's update) uses that document, while a reply edited
     * since, or another recipient's after the referral was sent on, is filed anew. The Task isn't
     * completed without it, so a reply that couldn't be filed is tried again on the next save. A
     * reply an offsite store takes (a PatientDocumentStoreOffsite listener, which leaves no local
     * documents row) counts as filed: the Task is completed without it as an output, the log says
     * so, and the reply is uploaded once per save, and not again once the Task is completed.
     * The update names the version it read, so a recipient's change in between (a PUT to
     * rejected, or a new output) isn't written over: the Task is read again. One that has gone
     * final during this save keeps the status the recipient gave it, and the reply this save filed,
     * or found already filed, while the Task was still open (its first read found it open) is
     * attached as its output. A save that finds the Task already final attaches nothing: a reply
     * filed by an earlier save that then lost the Task's update stays unlinked if the Task goes
     * final before the next save, since the Task keeps no history to tell a reply never attached
     * from an output the recipient removed, and a removal is never undone.
     *
     * @return ?string the completed Task's uuid
     */
    public function recordReply(int $transactionId, ?int $userId): ?string
    {
        if (!OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)) {
            return null;
        }
        // Most saves record no reply: one read of the Reply Date settles that before the lock is taken.
        // Under the lock the referral is read whole, as the save may have changed it since.
        if (self::storedReplyDate($transactionId) === null) {
            return null;
        }
        $referralUuid = $this->getReferralService()->getReferralUuid($transactionId);
        if ($referralUuid === null) {
            return null;
        }
        $lock = self::LOCK_PREFIX . $referralUuid;
        $locked = self::lock($lock);
        if ($locked !== true) {
            // The reply completes the Task on the referral's next save.
            $this->getLogger()->warning('A referral\'s reply was not recorded on its Task: its lock was not acquired', [
                'referral_uuid' => $referralUuid,
                'reason' => $locked === false ? 'another save held it past the wait' : 'GET_LOCK failed',
            ]);
            return null;
        }
        try {
            return $this->recordReplyLocked($transactionId, $referralUuid, $userId);
        } finally {
            self::unlock($lock);
        }
    }

    /**
     * recordReply() once it holds the referral's lock.
     */
    private function recordReplyLocked(int $transactionId, string $referralUuid, ?int $userId): ?string
    {
        $referral = $this->getReferralService()->getReferral($transactionId);
        $patientId = $referral['patient_id'] ?? null;
        $recipientUuid = $referral === null ? null : self::text($referral, 'performer_uuid');
        if ($referral === null || !is_numeric($patientId) || $recipientUuid === null || self::text($referral, 'reply_date') === null) {
            return null;
        }
        $document = null;
        for ($pass = 0; $pass < self::WRITE_PASSES; $pass++) {
            $task = $this->recipientTask($referralUuid, $recipientUuid);
            $status = $task === null ? null : self::text($task, 'status');
            $taskUuid = $task === null ? null : self::text($task, 'uuid');
            $version = $task['version'] ?? null;
            if ($task === null || $taskUuid === null || $status === null || !is_numeric($version)) {
                return null;
            }
            if (in_array($status, FhirTaskWriteRules::FINAL_STATUSES, true)) {
                // A save that finds the Task already final attaches nothing, even when an earlier save
                // filed the reply, as the recipient may have removed it from the outputs. A Task that
                // went final during this save (the recipient's PUT to rejected came in between) gets
                // the reply this save filed, or found already filed, while the Task was still open:
                // attached as an output at the version read, without touching the status. A reply an
                // offsite store took has no local document to attach, and the log says so.
                if ($document === false) {
                    $this->getLogger()->info('A referral\'s reply went to remote storage but its Task went final first, so it isn\'t attached', [
                        'transaction_id' => $transactionId,
                        'task_uuid' => $taskUuid,
                        'status' => $status,
                    ]);
                    return null;
                }
                if (!is_string($document) || in_array($document, array_column(self::outputRows($task), 'value_uuid'), true)) {
                    return null;
                }
                if ($this->getTaskService()->update($taskUuid, [], $userId, (int)$version, null, self::withReply($task, $document))) {
                    $this->getLogger()->info('A referral\'s reply was filed as its Task reached a final status, so it was attached to the Task as it is', [
                        'transaction_id' => $transactionId,
                        'task_uuid' => $taskUuid,
                        'status' => $status,
                    ]);
                    return null;
                }
                continue;
            }
            $document ??= $this->replyDocument($transactionId, (int)$patientId, $referral, $userId);
            if ($document === null) {
                $this->getLogger()->warning('A referral\'s reply was not filed, so its Task was left open', [
                    'transaction_id' => $transactionId,
                    'task_uuid' => $taskUuid,
                ]);
                return null;
            }
            $outputs = $document === false ? self::outputRows($task) : self::withReply($task, $document);
            // Completed with the reply's own reason, which a later save that finds the Reply Date cleared
            // looks for (undoClearedReply()).
            $completed = ['status' => 'completed', 'status_reason' => self::REASON_REPLY_RECORDED];
            if ($this->getTaskService()->update($taskUuid, $completed, $userId, (int)$version, null, $outputs)) {
                if ($document === false) {
                    $this->getLogger()->info('A referral\'s reply went to remote storage, so its Task was completed without the reply as its output', [
                        'transaction_id' => $transactionId,
                        'task_uuid' => $taskUuid,
                    ]);
                }
                return $taskUuid;
            }
        }
        // The Task kept changing under this save; its next save tries again, with the same document
        // when the reply hasn't changed.
        $this->getLogger()->warning('A referral\'s reply was filed but its Task kept changing, so it was left as the recipient has it', [
            'transaction_id' => $transactionId,
            'document_uuid' => $document,
        ]);
        return null;
    }

    /**
     * A Task's outputs with the reply's document as the last of them, once however many times it's
     * recorded.
     *
     * @param array<string, mixed> $task
     * @return list<array<string, string|null>>
     */
    private static function withReply(array $task, string $document): array
    {
        $outputs = [];
        foreach (self::outputRows($task) as $row) {
            if (($row['value_uuid'] ?? null) !== $document) {
                $outputs[] = $row;
            }
        }
        $outputs[] = self::REPLY_OUTPUT_TYPE + ['value_element' => 'valueReference', 'value_type' => 'DocumentReference', 'value_uuid' => $document];
        return $outputs;
    }

    /**
     * A Task's outputs as TaskService::update() takes them back.
     *
     * @param array<string, mixed> $task
     * @return list<array<string, string|null>>
     */
    private static function outputRows(array $task): array
    {
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
        return $outputs;
    }

    /**
     * The uuid of the referral's reply as a document: the one already filed when it's the same
     * reply (filedReply()), else a new one (fileReply()).
     *
     * @param array<string, mixed> $referral
     * @return string|false|null the document's uuid; false when an offsite store took it, which
     *     leaves no local document; null when it wasn't filed
     */
    private function replyDocument(int $transactionId, int $patientId, array $referral, ?int $userId): string|false|null
    {
        $values = self::storedValues($transactionId);
        $name = self::replyFileName($transactionId, $values);
        return $this->filedReply($transactionId, $patientId, $name) ?? $this->fileReply($transactionId, $patientId, $name, self::replyText($values, $referral), $userId);
    }

    /**
     * The uuid of a reply fileReply() already filed for the referral under this name, or null when
     * there's none. The name carries a key made from the reply's stored values (replyFileName()):
     * a reply edited since it was filed, or another recipient's after the referral was sent on, has
     * another name and is filed anew, while a change outside the reply (Refer To's entry renamed, a
     * layout title changed) finds the reply already filed. Only one still in the referral's
     * patient's chart counts: Document::change_patient() moves a document to another chart and keeps
     * its link to the referral, and that one isn't this patient's reply any more.
     */
    private function filedReply(int $transactionId, int $patientId, string $name): ?string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT id, uuid FROM documents WHERE foreign_reference_table = ? AND foreign_reference_id = ? AND foreign_id = ? AND name = ? AND deleted = 0 ORDER BY id DESC LIMIT 1',
            [ReferralService::TABLE_NAME, $transactionId, $patientId, $name]
        );
        if (!is_array($row) || !is_numeric($row['id'] ?? null)) {
            return null;
        }
        if (!is_string($row['uuid'] ?? null)) {
            UuidRegistry::createMissingUuidForRow('documents', 'id', (int)$row['id']);
            $row['uuid'] = QueryUtils::fetchSingleValue('SELECT uuid FROM documents WHERE id = ?', 'uuid', [(int)$row['id']]);
        }
        return is_string($row['uuid']) ? UuidRegistry::uuidToString($row['uuid']) : null;
    }

    /**
     * The name a reply is filed under: the referral's id and the first 12 hex digits of the SHA-256
     * of a key made from the reply's stored values (each reply_ field and Refer To's stored id, in
     * field id order). Display names and layout titles stay out of it, so renaming them doesn't
     * make the same reply look new; editing a reply field, or sending the referral to someone else,
     * does.
     *
     * @param array<string, string> $values the referral's stored values (storedValues())
     */
    private static function replyFileName(int $transactionId, array $values): string
    {
        $key = [];
        foreach ($values as $field => $value) {
            if ($field === 'refer_to' || str_starts_with($field, 'reply_')) {
                $key[$field] = $field . '=' . $value;
            }
        }
        ksort($key, SORT_STRING);
        return 'counter-referral-' . $transactionId . '-' . substr(hash('sha256', implode("\n", $key)), 0, 12) . '.txt';
    }

    /**
     * The referral's stored values (lbt_data) that have something in them, trimmed, by field id.
     *
     * @return array<string, string>
     */
    private static function storedValues(int $transactionId): array
    {
        $values = [];
        foreach (QueryUtils::fetchRecords("SELECT field_id, field_value FROM lbt_data WHERE form_id = ?", [$transactionId]) as $row) {
            if (is_string($row['field_id'] ?? null) && is_string($row['field_value'] ?? null) && trim($row['field_value']) !== '') {
                $values[$row['field_id']] = trim($row['field_value']);
            }
        }
        return $values;
    }

    /**
     * After the FHIR API changes a Task's status: when the Task is a referral's own (isOwnTask()),
     * owned by the referral's recipient (Refer To), and is now accepted, rejected, completed,
     * failed or cancelled, a message to the one who made the referral (Refer By), in their inbox
     * under the Referral type; a rejection, a failure or a cancellation carries the Task's reason
     * when it has one. This service's own writes (the cancellations when the referral goes
     * elsewhere or is deleted, a reply's completion) go to TaskService, not through the API, so
     * they aren't told. The message says what the referral's Task to Refer To was marked, so a
     * recipient's sub-Task, another Task with the referral as its focus, or one an API client made
     * for someone else doesn't count. It doesn't say who marked it (nothing yet ties an API client
     * to the recipient); its header names the user who made the change. Nothing when the global is
     * off, or when Refer By is no longer an active user (that is logged).
     *
     * The caller hands over the Task as it read it after the update (TaskService::getOne()), so it
     * isn't read again. Called once the Task's update is committed, so a failure is logged, not
     * thrown: thrown, it would answer the client's successful PUT with an error, and a retry naming
     * the version it read would then meet a version conflict for a change already made.
     *
     * @param array<string, mixed> $task
     */
    public function taskStatusChanged(array $task, string $from, string $to, ?int $userId): void
    {
        $notice = ReferralTaskNotice::tryFrom($to);
        if (
            $from === $to || $notice === null || !self::isReferralTask($task)
            || !OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)
        ) {
            return;
        }
        try {
            $this->notifyReferrer($task, $notice, $userId);
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            $this->getLogger()->error('A referral\'s Task changed status but the referrer was not told', [
                'task_uuid' => $task['uuid'] ?? null,
                'status' => $to,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Whether a Task, as TaskService reads it, could be a referral's own: one this service makes
     * (isOwnTask()) with a ServiceRequest as its focus. FhirTaskService asks this before handing a
     * status change over, so a questionnaire's or another kind of Task costs nothing more.
     *
     * @param array<mixed> $task
     */
    public static function isReferralTask(array $task): bool
    {
        return self::isOwnTask($task) && ($task['focus_type'] ?? null) === 'ServiceRequest' && is_string($task['focus_uuid'] ?? null);
    }

    /**
     * What's wrong with a PUT to a referral's own Task, by FHIR element: it keeps the referral it's
     * for, who it's for, and the code and missing parent that make it the referral's. Changing them
     * would quietly take the Task out of the referral: its recipient's notices would stop, and the
     * next save of the referral would cancel it. A referral is sent elsewhere from its Refer To.
     * Empty for any other Task, a lab order's among them.
     *
     * @param array<mixed> $stored the Task as TaskService::getForWrite() reads it
     * @param array<string, string|null> $fields the task fields of the PUT
     * @return array<string, string>
     */
    public function writeProblems(array $stored, array $fields): array
    {
        $focus = self::isReferralTask($stored) && is_string($stored['focus_uuid'] ?? null) ? $stored['focus_uuid'] : null;
        if ($focus === null) {
            return [];
        }
        $problems = self::lockedChanges($stored, $fields);
        // Looked up last: nearly every PUT changes none of these, and the referral's query is the costly part.
        return $problems === [] || $this->getReferralService()->getReferralByUuid($focus) === null ? [] : $problems;
    }

    /**
     * The elements a PUT changes that a referral's own Task keeps. An owner it never had can be set,
     * as an API client's Task may start without one.
     *
     * @param array<mixed> $stored
     * @param array<string, string|null> $fields
     * @return array<string, string>
     */
    public static function lockedChanges(array $stored, array $fields): array
    {
        $same = static function (string $column) use ($stored, $fields): bool {
            $before = $stored[$column] ?? null;
            return strcasecmp(is_string($before) ? $before : '', $fields[$column] ?? '') === 0;
        };
        $problems = [];
        if (!$same('code')) {
            $problems['code'] = "Task.code can't change on a referral's Task";
        }
        if (!$same('focus_type') || !$same('focus_uuid')) {
            $problems['focus'] = "Task.focus can't change on a referral's Task";
        }
        if (($stored['owner_uuid'] ?? null) !== null && (!$same('owner_type') || !$same('owner_uuid'))) {
            $problems['owner'] = "Task.owner can't change on a referral's Task: send the referral elsewhere from its Refer To";
        }
        if (($fields['part_of_uuid'] ?? null) !== null) {
            $problems['partOf'] = "Task.partOf can't be set on a referral's Task";
        }
        return $problems;
    }

    /**
     * The message taskStatusChanged() files: to Refer By, about the Task's new status.
     *
     * @param array<string, mixed> $task
     */
    private function notifyReferrer(array $task, ReferralTaskNotice $notice, ?int $userId): void
    {
        $taskUuid = self::text($task, 'uuid');
        $focus = self::text($task, 'focus_uuid');
        if ($taskUuid === null || $focus === null) {
            return;
        }
        $referral = $this->getReferralService()->getReferralByUuid($focus);
        $referrer = $referral['refer_from'] ?? null;
        $patientId = $referral['patient_id'] ?? null;
        $recipientUuid = $referral === null ? null : self::text($referral, 'performer_uuid');
        if (
            $referral === null || !is_numeric($referrer) || !is_numeric($patientId)
            || $recipientUuid === null || ($task['owner_uuid'] ?? null) !== $recipientUuid
        ) {
            return;
        }
        // Only to someone who can still sign in: a referrer who has left (their user made inactive)
        // would never read it, so the log says the notice went nowhere.
        $recipient = QueryUtils::fetchSingleValue('SELECT username FROM users WHERE id = ? AND active = 1', 'username', [(int)$referrer]);
        if (!is_string($recipient) || $recipient === '') {
            $this->getLogger()->warning('A referral\'s Task changed status but its referrer is not an active user, so no one was told', [
                'task_uuid' => $taskUuid,
                'status' => $notice->value,
            ]);
            return;
        }
        $sender = $userId === null ? null : QueryUtils::fetchSingleValue('SELECT username FROM users WHERE id = ?', 'username', [$userId]);
        $sender = is_string($sender) && $sender !== '' ? $sender : 'FHIR API';
        // The recipient's reason on one line: Messages reformats a YYYY-MM-DD that starts a line, as it does
        // each entry's header, so a dated line in the reason would look like another entry's header. Read
        // as UTF-8, so no byte of a character (the A0 in à) is taken for a space; a reason that isn't
        // UTF-8 is left out.
        $reason = preg_replace('/\s+/u', ' ', self::text($task, 'status_reason') ?? '');
        $reason = is_string($reason) && trim($reason) !== '' ? trim($reason) : null;
        $text = $notice->text(self::text($referral, 'performer_name') ?? xl('the recipient'), self::text($referral, 'refer_date'), $reason);
        // As other background notices are filed, from $sender, dated by the service's clock (addPnote()
        // writes the time that opens the body itself).
        addPnote((int)$patientId, $text, 0, 1, 'Referral', $recipient, $this->getClock()->now()->format('Y-m-d H:i:s'), 'New', $sender);
    }

    /**
     * The recipient's Task for the referral, as TaskService returns it, with its outputs: the
     * latest of the referral's own Tasks (ownTasks()) that the recipient owns and that still
     * counts as theirs (countsForRecipient()). ReferralService::taskStatusSql() reads the
     * referral's status from the same Task, and both createForReferral() (does Refer To have one?)
     * and recordReplyLocked() (which one does the reply complete?) ask this, so the three agree.
     *
     * @return ?array<string, mixed>
     */
    private function recipientTask(string $referralUuid, string $ownerUuid): ?array
    {
        $latest = null;
        $latestId = 0;
        foreach ($this->ownTasks($referralUuid, $ownerUuid) as $task) {
            $id = is_numeric($task['id'] ?? null) ? (int)$task['id'] : 0;
            if (self::countsForRecipient($task) && ($latest === null || $id > $latestId)) {
                $latest = $task;
                $latestId = $id;
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
     * Whether one of the referral's own Tasks still counts as its recipient's. One this service set
     * aside doesn't: a status in SET_ASIDE_STATUSES with a reason in OWN_REASONS, matched exactly
     * (cancelled because the referral went elsewhere, lost its recipient or was deleted, or
     * entered-in-error because the reply that completed it was cleared). Every other Task they own
     * does, whatever its status: one they finished, rejected or cancelled themselves, or one whose
     * reason only looks like the service's. ReferralService::taskStatusSql() leaves out the same
     * Tasks.
     *
     * @param array<mixed> $task
     */
    private static function countsForRecipient(array $task): bool
    {
        return !(in_array($task['status'] ?? null, self::SET_ASIDE_STATUSES, true) && in_array($task['status_reason'] ?? null, self::OWN_REASONS, true));
    }

    /**
     * Undoes a reply the referral no longer records. createForReferral() calls it only when the
     * saving form showed a Reply Date, posted the field and saved it cleared, as when one saved by
     * mistake is cleared. The reply it showed was the reply of the recipient it showed (its Refer To
     * as loaded, whatever this save made of Refer To), so the latest of that recipient's Tasks the
     * reply completed (replyCompletedTask()) is marked entered-in-error with REASON_REPLY_CLEARED.
     * Another recipient's answer is never touched, even when the Reply Date shown had been written
     * over for someone else. The undone Task stops counting as its owner's, so they get a new Task
     * while the referral is theirs, or when it comes back to them. The reply stays in the patient's
     * documents and on the Task's outputs, as a record. The update names the version it read, as the
     * other writes do: a recipient's change in between is read again, and a Task it left with another
     * status or reason stays as it is.
     *
     * The reply's completion is known by its reason alone, as the service's cancellations are: a
     * Task the recipient completed has none of the service's, a recipient's write that replaces the
     * statusReason takes the Task out of the undo, and an API client that completes a Task with the
     * service's exact reason text would have it undone. The Task's owner is found from the Refer To
     * the form showed, so nothing is undone when the form showed none, or when that address book
     * entry or user has since been deleted; each is logged as a warning. So is a recipient with Tasks
     * of the referral none of which the reply completed. A recipient with none at all (one FHIR
     * doesn't serve, or a reply recorded while the global was off) is logged at info.
     *
     * It isn't retried: the Reply Date is already deleted, so later forms show none. When an undo
     * is skipped (each case above, the lock missed, a failure, or the Task changing on every pass),
     * the log names the transaction; re-entering the Reply Date and clearing it again undoes it. A
     * recipient's change that lands between the read and the write stands, as their answer, and
     * isn't logged.
     *
     * @param ?int $referToLoaded the Refer To the form showed (a users.id), or null when it showed none
     */
    private function undoClearedReply(int $transactionId, string $referralUuid, ?int $referToLoaded, ?int $userId): void
    {
        if ($referToLoaded === null) {
            $this->getLogger()->warning('A referral\'s Reply Date was cleared but its form showed no Refer To, so no reply\'s Task was undone', [
                'transaction_id' => $transactionId,
            ]);
            return;
        }
        $ownerUuid = self::userUuid($referToLoaded);
        if ($ownerUuid === null && !self::userExists($referToLoaded)) {
            $this->getLogger()->warning('A referral\'s Reply Date was cleared but the Refer To its form showed no longer exists, so its reply\'s Task was not undone', [
                'transaction_id' => $transactionId,
                'refer_to' => $referToLoaded,
            ]);
            return;
        }
        $taskUuid = null;
        for ($pass = 0; $pass < self::WRITE_PASSES; $pass++) {
            // A user without a uuid owns no Task: a Task names its owner by uuid.
            $tasks = $ownerUuid === null ? [] : $this->ownTasks($referralUuid, $ownerUuid);
            $task = self::replyCompletedTask($tasks);
            $taskUuid = $task['uuid'] ?? null;
            $version = $task['version'] ?? null;
            if (!is_string($taskUuid) || !is_numeric($version)) {
                if ($pass === 0 && $tasks === []) {
                    $this->getLogger()->info('A referral\'s Reply Date was cleared, and the recipient its form showed has no Task of the referral\'s, so there was nothing to undo', [
                        'transaction_id' => $transactionId,
                    ]);
                } elseif ($pass === 0) {
                    $this->getLogger()->warning('A referral\'s Reply Date was cleared but none of the Tasks of the recipient its form showed was completed by the reply, so nothing was undone', [
                        'transaction_id' => $transactionId,
                    ]);
                }
                return;
            }
            $undone = ['status' => 'entered-in-error', 'status_reason' => self::REASON_REPLY_CLEARED];
            if ($this->getTaskService()->update($taskUuid, $undone, $userId, (int)$version)) {
                return;
            }
        }
        $this->getLogger()->warning('A referral\'s Reply Date was cleared but the Task its reply completed kept changing, so it was not undone, nor will it be', [
            'transaction_id' => $transactionId,
            'task_uuid' => $taskUuid,
        ]);
    }

    /**
     * The latest of these Tasks (one owner's, from ownTasks()) that a reply completed: completed,
     * with REASON_REPLY_RECORDED as its reason.
     *
     * @param list<array<mixed>> $tasks
     * @return ?array<mixed>
     */
    private static function replyCompletedTask(array $tasks): ?array
    {
        $latest = null;
        $latestId = 0;
        foreach ($tasks as $task) {
            $id = is_numeric($task['id'] ?? null) ? (int)$task['id'] : 0;
            if (
                ($task['status'] ?? null) === 'completed' && ($task['status_reason'] ?? null) === self::REASON_REPLY_RECORDED
                && ($latest === null || $id > $latestId)
            ) {
                $latest = $task;
                $latestId = $id;
            }
        }
        return $latest;
    }

    /**
     * The uuid FHIR serves an address book entry or user by, as a Task's owner names it (Refer To's
     * performer_uuid), for a users.id as a referral stores it; null when there's no such row, or it
     * has no uuid, which no Task can name.
     */
    private static function userUuid(int $userId): ?string
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM users WHERE id = ?', 'uuid', [$userId]);
        return is_string($uuid) && $uuid !== '' ? UuidRegistry::uuidToString($uuid) : null;
    }

    /**
     * Whether an address book entry or user with this id still exists.
     */
    private static function userExists(int $userId): bool
    {
        $found = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM users WHERE id = ?', 'n', [$userId]);
        return is_numeric($found) && (int)$found > 0;
    }

    /**
     * The referral's stored Reply Date, or null when it has none (one indexed read).
     */
    private static function storedReplyDate(int $transactionId): ?string
    {
        $replyDate = QueryUtils::fetchSingleValue("SELECT field_value FROM lbt_data WHERE form_id = ? AND field_id = 'reply_date'", 'field_value', [$transactionId]);
        return is_string($replyDate) && trim($replyDate) !== '' ? $replyDate : null;
    }

    /**
     * The Counter-Referral section as the text fileReply() files: the heading "Counter-Referral",
     * the Referral Date and Refer To, then each field of the reply that is in use (uor above 0) and
     * has a value, in the form's order. On a layout a site can rearrange, only the field ids tell a
     * reply field from a referral field, so the reply's fields are those whose id starts with
     * reply_, wherever the layout puts them. A field a site adds to the section is in the reply when
     * its id starts with reply_; otherwise it stays on the screen and in the referral but isn't
     * filed with the reply. Nothing of the referral itself goes in. Titles are the layout's as
     * stored, not translated, and values are as stored (a date stays YYYY-MM-DD, a list field keeps
     * its option id), so the text reads the same whoever saves the referral.
     *
     * @param array<string, string> $values the referral's stored values (storedValues())
     * @param array<string, mixed> $referral
     */
    private static function replyText(array $values, array $referral): string
    {
        // The form's order: a group's fields, then its subgroups', each by seq, with the field id
        // breaking a tie so the order never varies.
        $layout = QueryUtils::fetchRecords('SELECT field_id, title, uor FROM layout_options WHERE form_id = ? ORDER BY group_id, seq, field_id', [ReferralService::FORM_ID]);
        $titles = [];
        foreach ($layout as $row) {
            $field = $row['field_id'] ?? null;
            $title = $row['title'] ?? null;
            if (is_string($field)) {
                // A field without a title goes by its id.
                $titles[$field] ??= is_string($title) && trim($title) !== '' ? trim($title) : $field;
            }
        }
        $lines = [
            'Counter-Referral',
            ($titles['refer_date'] ?? 'refer_date') . ': ' . ($values['refer_date'] ?? ''),
            ($titles['refer_to'] ?? 'refer_to') . ': ' . (self::text($referral, 'performer_name') ?? ''),
            '',
        ];
        $listed = [];
        foreach ($layout as $row) {
            $field = $row['field_id'] ?? null;
            if (
                is_string($field) && str_starts_with($field, 'reply_') && isset($values[$field]) && !isset($listed[$field])
                && is_numeric($row['uor'] ?? null) && (int)$row['uor'] > 0
            ) {
                $listed[$field] = true;
                $lines[] = $titles[$field] . ': ' . $values[$field];
            }
        }
        return implode("\n", $lines) . "\n";
    }

    /**
     * The reply's text as a document in the patient's Medical Record, linked to the referral.
     * Returns its uuid; false when an offsite store took the file (Document::createDocument() then
     * answers with a message and writes no documents row); null when there's no Medical Record
     * category or the document couldn't be saved.
     */
    private function fileReply(int $transactionId, int $patientId, string $name, string $text, ?int $userId): string|false|null
    {
        $category = QueryUtils::fetchSingleValue("SELECT id FROM categories WHERE name = 'Medical Record' ORDER BY id LIMIT 1", 'id', []);
        if (!is_numeric($category)) {
            return null;
        }
        // Filed under the user who saved the referral, else the system user; without either it isn't
        // filed, rather than falling to whoever's session createDocument() finds.
        $owner = $userId ?? $this->systemUserId();
        if ($owner === null) {
            $this->getLogger()->warning('A referral\'s reply was not filed: there is no user to file it under', [
                'transaction_id' => $transactionId,
            ]);
            return null;
        }
        $document = $this->newDocument();
        $error = $document->createDocument(
            (string)$patientId,
            (int)$category,
            $name,
            'text/plain',
            $text,
            '',
            1,
            $owner,
            null,
            null,
            (string)$transactionId,
            ReferralService::TABLE_NAME
        );
        if ($error !== '') {
            return self::storedRemotely() ? false : null;
        }
        $id = $document->get_id();
        if (!is_numeric($id)) {
            return null;
        }
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM documents WHERE id = ?', 'uuid', [(int)$id]);
        return is_string($uuid) ? UuidRegistry::uuidToString($uuid) : null;
    }

    /**
     * Whether an offsite store took the document just made, read as Document::createDocument() reads
     * it: any value PHP takes as true in documentStoredRemotely, not only a boolean.
     */
    private static function storedRemotely(): bool
    {
        return !in_array(OEGlobalsBag::getInstance()->get('documentStoredRemotely'), [null, false, 0, 0.0, '', '0', []], true);
    }

    /**
     * The system user's id (UserService::getSystemUser()), which files a reply no signed-in user
     * saved, or null when there's no system user or its id isn't a positive whole number.
     */
    private function systemUserId(): ?int
    {
        $user = $this->userService->getSystemUser();
        $id = filter_var(is_array($user) ? $user['id'] : null, FILTER_VALIDATE_INT);
        return is_int($id) && $id > 0 ? $id : null;
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
            if (is_array($task) && self::isOwnTask($task)) {
                $own[] = $task;
            }
        }
        return $own;
    }

    /**
     * Whether a Task is one this service makes: code fulfill and not part of another Task.
     *
     * @param array<mixed> $task
     */
    private static function isOwnTask(array $task): bool
    {
        return ($task['code'] ?? null) === 'fulfill' && in_array($task['part_of_uuid'] ?? null, [null, ''], true);
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

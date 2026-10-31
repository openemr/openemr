<?php

/**
 * The FHIR Task that tracks a referral (#14710). When the fhir_referral_tasks global is on
 * and a referral (the Transactions screen's LBTref form) is saved, its recipient gets a
 * Task: status requested, intent order, code fulfill, focus the referral's ServiceRequest,
 * for its patient, owned by the one it is sent to (Refer To) and requested by the one who
 * made it (Refer By).
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

    /**
     * After a transaction is saved: a Task for its referral's recipient, unless they have one.
     * One Task per referral and recipient, so saving the referral again adds none. When Refer To
     * changes, or is cleared, the open Tasks of anyone else are cancelled with the reason, so they
     * leave that recipient's queue and stay as a record; a recipient the referral returns to gets a
     * new Task (a cancelled one doesn't count). Nothing when the global is off, the transaction isn't
     * a referral, or the recipient isn't a Practitioner or Organization in FHIR, since a Task's owner
     * has to be one.
     *
     * @return ?string the new Task's uuid
     */
    public function createForReferral(int $transactionId, ?int $userId): ?string
    {
        if (!OEGlobalsBag::getInstance()->getBoolean(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value)) {
            return null;
        }
        self::createMissingUuids($transactionId);
        $referral = $this->getReferralService()->getReferral($transactionId);
        if ($referral === null) {
            return null;
        }
        $referralUuid = self::text($referral, 'uuid');
        $patientId = $referral['patient_id'] ?? null;
        $owner = self::party($referral, 'performer');
        if ($referralUuid === null || !is_numeric($patientId)) {
            return null;
        }
        // Two saves of the referral at once would each find no Task and each make one, and the task
        // table doesn't prevent it: a lock on the referral is held across the check and the insert,
        // so the second save finds the first one's Task.
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
            $this->cancelOpenTasks(
                $referralUuid,
                $owner[1] ?? null,
                self::text($referral, 'performer_uuid') === null ? 'The referral no longer names a recipient.' : 'The referral was sent to another recipient.',
                $userId
            );
            if ($owner === null || $this->hasTask($referralUuid, $owner[1])) {
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
        return $this->createAfterSave((int)$transactionId, is_numeric($userId) ? (int)$userId : null);
    }

    /**
     * createForReferral() for a caller that has already saved the referral: a failure is logged,
     * not thrown, since the referral is saved either way and its next save makes the Task this one
     * missed (a recipient that has a Task is skipped). Thrown, it would answer a REST client's
     * successful save with an error, and a retry would save the referral twice. It catches every
     * exception a library throws (RuntimeException, LogicException, the audit log's DBAL ones) but
     * not \Error, which the project's ForbiddenCatchTypeRule keeps visible: a TypeError is a bug.
     *
     * @return ?string the new Task's uuid
     */
    public function createAfterSave(int $transactionId, ?int $userId): ?string
    {
        try {
            return $this->createForReferral($transactionId, $userId);
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            $this->getLogger()->error('A referral was saved but its FHIR Task was not created', [
                'transaction_id' => $transactionId,
                'exception' => $e,
            ]);
            return null;
        }
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
        $uuid = QueryUtils::fetchSingleValue(
            'SELECT uuid FROM transactions WHERE id = ? AND title = ?',
            'uuid',
            [$transactionId, ReferralService::FORM_ID]
        );
        if (!is_string($uuid) || $uuid === '') {
            return 0;
        }
        return $this->cancelOpenTasks(UuidRegistry::uuidToString($uuid), null, 'The referral was deleted.', $userId);
    }

    /**
     * Cancels the referral's open Tasks, all of them or all but one owner's, with the reason. Each
     * update names the version the search read, so a recipient's change in between (a PUT to
     * completed, say) isn't written over: that Task just isn't counted.
     *
     * @return int how many Tasks were cancelled
     */
    private function cancelOpenTasks(string $referralUuid, ?string $keepOwnerUuid, string $reason, ?int $userId): int
    {
        $tasks = $this->getTaskService()->search([
            'focus_uuid' => new TokenSearchField('focus_uuid', [$referralUuid], true),
        ])->getData();
        $cancelled = 0;
        foreach (is_array($tasks) ? $tasks : [] as $task) {
            if (!is_array($task)) {
                continue;
            }
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
            }
        }
        return $cancelled;
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
     * Whether the recipient has a Task for the referral that wasn't cancelled: one they finished
     * stays theirs, but a cancelled one (the referral went elsewhere, then came back) doesn't count.
     */
    private function hasTask(string $referralUuid, string $ownerUuid): bool
    {
        $tasks = $this->getTaskService()->search([
            'focus_uuid' => new TokenSearchField('focus_uuid', [$referralUuid], true),
            'owner_uuid' => new TokenSearchField('owner_uuid', [$ownerUuid], true),
        ])->getData();
        foreach (is_array($tasks) ? $tasks : [] as $task) {
            if (is_array($task) && ($task['status'] ?? null) !== 'cancelled') {
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

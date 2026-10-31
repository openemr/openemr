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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\Search\TokenSearchField;

class ReferralTaskService
{
    /** FHIR Task statuses a Task doesn't move on from. */
    private const FINISHED_STATUSES = ['completed', 'cancelled', 'entered-in-error', 'failed', 'rejected'];

    private ?ReferralService $referralService = null;

    private ?TaskService $taskService = null;

    private function getReferralService(): ReferralService
    {
        return $this->referralService ??= new ReferralService(createMissingUuids: false);
    }

    public function setReferralService(ReferralService $referralService): void
    {
        $this->referralService = $referralService;
    }

    private function getTaskService(): TaskService
    {
        return $this->taskService ??= new TaskService();
    }

    public function setTaskService(TaskService $taskService): void
    {
        $this->taskService = $taskService;
    }

    /**
     * After a transaction is saved: a Task for its referral's recipient, unless they have one.
     * One Task per referral and recipient, so saving the referral again adds none, and a new
     * Refer To adds one for the new recipient while the earlier Task stays as it is. Nothing
     * when the global is off, the transaction isn't a referral, or the recipient isn't a
     * Practitioner or Organization in FHIR, since a Task's owner has to be one.
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
        if ($referralUuid === null || !is_numeric($patientId) || $owner === null || $this->hasTask($referralUuid, $owner[1])) {
            return null;
        }
        $requester = self::party($referral, 'requester');
        return $this->getTaskService()->insert([
            'status' => 'requested',
            'intent' => 'order',
            'code' => 'fulfill',
            'focus_type' => 'ServiceRequest',
            'focus_uuid' => $referralUuid,
            'owner_type' => $owner[0],
            'owner_uuid' => $owner[1],
            'requester_type' => $requester[0] ?? null,
            'requester_uuid' => $requester[1] ?? null,
            'authored_on' => date('Y-m-d H:i:s'),
        ], (int)$patientId, $userId);
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
     * createForReferral() for a caller that has already saved the referral: a database failure is
     * logged, not thrown, since the referral is saved either way and its next save makes the Task this one
     * missed (a recipient that has a Task is skipped). Thrown, it would answer a REST client's
     * successful save with an error, and a retry would save the referral twice.
     *
     * @return ?string the new Task's uuid
     */
    public function createAfterSave(int $transactionId, ?int $userId): ?string
    {
        try {
            return $this->createForReferral($transactionId, $userId);
        } catch (SqlQueryException $e) {
            ServiceContainer::getLogger()->error('A referral was saved but its FHIR Task was not created', [
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
        $tasks = $this->getTaskService()->search([
            'focus_uuid' => new TokenSearchField('focus_uuid', [UuidRegistry::uuidToString($uuid)], true),
        ])->getData();
        $cancelled = 0;
        foreach (is_array($tasks) ? $tasks : [] as $task) {
            $taskUuid = is_array($task) ? ($task['uuid'] ?? null) : null;
            $status = is_array($task) ? ($task['status'] ?? null) : null;
            if (!is_string($taskUuid) || in_array($status, self::FINISHED_STATUSES, true)) {
                continue;
            }
            if ($this->getTaskService()->update($taskUuid, ['status' => 'cancelled', 'status_reason' => 'The referral was deleted.'], $userId)) {
                $cancelled++;
            }
        }
        return $cancelled;
    }

    private function hasTask(string $referralUuid, string $ownerUuid): bool
    {
        $tasks = $this->getTaskService()->search([
            'focus_uuid' => new TokenSearchField('focus_uuid', [$referralUuid], true),
            'owner_uuid' => new TokenSearchField('owner_uuid', [$ownerUuid], true),
        ])->getData();
        return is_array($tasks) && $tasks !== [];
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

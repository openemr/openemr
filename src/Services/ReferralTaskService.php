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

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\Search\TokenSearchField;

class ReferralTaskService
{
    private ?ReferralService $referralService = null;

    private ?TaskService $taskService = null;

    private function getReferralService(): ReferralService
    {
        return $this->referralService ??= new ReferralService();
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

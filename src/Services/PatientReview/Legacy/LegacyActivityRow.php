<?php

/**
 * A review request in the shape of an onsite_portal_activity row.
 *
 * The staff review list, the portal document screen and the payment page are JavaScript and
 * templates written against onsite_portal_activity's columns (status 'waiting', pending_action
 * 'review', table_args, ...). They keep working unchanged by reading this view of the request.
 * It is read-only: nothing writes these values back, and the card details that used to sit in
 * the checksum column are never exposed here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Legacy;

use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class LegacyActivityRow
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param string|array<array-key, mixed> $tableArgs
     */
    public function __construct(
        public int $id,
        public string $date,
        public int $patientId,
        public string $activity,
        public int $requireAudit,
        public string $pendingAction,
        public string $actionTaken,
        public string $status,
        public string $narrative,
        public string $tableAction,
        public string|array $tableArgs,
        public int $actionUser,
        public ?string $actionTakenTime,
    ) {
    }

    public static function fromRequest(ReviewRequest $request): self
    {
        $closed = !$request->status->isOpen();

        return new self(
            id: $request->id,
            date: $request->createdAt->format(self::DATETIME_FORMAT),
            patientId: $request->pid,
            activity: $request->type->value,
            requireAudit: $request->type === ReviewType::Invoice ? 0 : 1,
            pendingAction: self::pendingAction($request),
            actionTaken: $closed ? ($request->reviewNote ?? '') : '',
            status: self::status($request->status),
            narrative: $request->summary,
            tableAction: self::tableAction($request),
            tableArgs: self::tableArgs($request),
            actionUser: $request->reviewedBy ?? 0,
            actionTakenTime: $request->reviewedAt?->format(self::DATETIME_FORMAT),
        );
    }

    /**
     * The legacy status string for a review status.
     */
    public static function status(ReviewStatus $status): string
    {
        return match ($status) {
            ReviewStatus::Pending => 'waiting',
            ReviewStatus::AwaitingPayment => 'waiting transaction',
            ReviewStatus::Approved => 'approved',
            ReviewStatus::Denied => 'denied',
            ReviewStatus::Cancelled => 'deleted',
            ReviewStatus::Completed => 'closed',
        };
    }

    /**
     * Keyed by onsite_portal_activity column name.
     *
     * @return array{id: int, date: string, patient_id: int, activity: string, require_audit: int, pending_action: string, action_taken: string, status: string, narrative: string, table_action: string, table_args: string|array<array-key, mixed>, action_user: int, action_taken_time: ?string, checksum: string}
     */
    public function toColumns(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'patient_id' => $this->patientId,
            'activity' => $this->activity,
            'require_audit' => $this->requireAudit,
            'pending_action' => $this->pendingAction,
            'action_taken' => $this->actionTaken,
            'status' => $this->status,
            'narrative' => $this->narrative,
            'table_action' => $this->tableAction,
            'table_args' => $this->tableArgs,
            'action_user' => $this->actionUser,
            'action_taken_time' => $this->actionTakenTime,
            'checksum' => '',
        ];
    }

    /**
     * Keyed the way the portal's page scripts name the fields (camelCase).
     *
     * @return array{id: int, date: string, patientId: int, activity: string, requireAudit: int, pendingAction: string, actionTaken: string, status: string, narrative: string, tableAction: string, tableArgs: string, actionUser: int, actionTakenTime: ?string, checksum: string}
     */
    public function toModel(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'patientId' => $this->patientId,
            'activity' => $this->activity,
            'requireAudit' => $this->requireAudit,
            'pendingAction' => $this->pendingAction,
            'actionTaken' => $this->actionTaken,
            'status' => $this->status,
            'narrative' => $this->narrative,
            'tableAction' => $this->tableAction,
            'tableArgs' => is_string($this->tableArgs) ? $this->tableArgs : json_encode($this->tableArgs, JSON_THROW_ON_ERROR),
            'actionUser' => $this->actionUser,
            'actionTakenTime' => $this->actionTakenTime,
            'checksum' => '',
        ];
    }

    private static function pendingAction(ReviewRequest $request): string
    {
        return match ($request->status) {
            ReviewStatus::Pending, ReviewStatus::Approved => 'review',
            ReviewStatus::AwaitingPayment => 'payment',
            ReviewStatus::Completed => $request->type === ReviewType::Payment ? 'notify patient' : 'completed',
            ReviewStatus::Denied, ReviewStatus::Cancelled => 'none',
        };
    }

    private static function tableAction(ReviewRequest $request): string
    {
        return match ($request->status) {
            ReviewStatus::Completed => 'update',
            ReviewStatus::Cancelled => $request->type === ReviewType::Document ? 'delete' : '',
            ReviewStatus::Pending, ReviewStatus::AwaitingPayment, ReviewStatus::Approved, ReviewStatus::Denied => '',
        };
    }

    /**
     * What onsite_portal_activity.table_args held for each type: the edited record for a
     * profile change, the onsite_documents id for a document, the posted invoice text for a
     * payment, the encounters (JSON) for an invoice.
     *
     * @return string|array<array-key, mixed>
     */
    private static function tableArgs(ReviewRequest $request): string|array
    {
        return match ($request->type) {
            ReviewType::Profile, ReviewType::FhirWrite => $request->payload,
            ReviewType::Document => $request->targetId ?? '',
            ReviewType::Payment => self::text($request->payload, PortalReviewWorkflows::PAYMENT_INVOICE_VALUES),
            ReviewType::Invoice => self::invoiceText($request->payload),
        };
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private static function invoiceText(array $payload): string
    {
        $encounters = $payload[PortalReviewWorkflows::INVOICE_ENCOUNTERS] ?? null;
        if (is_array($encounters)) {
            return json_encode($encounters, JSON_THROW_ON_ERROR);
        }

        // Rows migrated from onsite_portal_activity keep the JSON text as it was stored.
        return self::text($payload, 'value');
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private static function text(array $payload, string $key): string
    {
        $value = $payload[$key] ?? '';

        return is_string($value) ? $value : '';
    }
}

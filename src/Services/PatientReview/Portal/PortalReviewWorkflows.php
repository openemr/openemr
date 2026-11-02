<?php

/**
 * The patient portal's review workflows, on the review queue.
 *
 * Each method is one thing the portal or a staff screen does today against
 * onsite_portal_activity: a patient submits a profile change or a payment, staff close it, a
 * biller asks a patient to pay, a portal document goes into review and is charted or deleted.
 * Keeping them here, rather than in the legacy entry points, gives each one a single
 * definition and a test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewActorType;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class PortalReviewWorkflows
{
    public const SUMMARY_PROFILE = 'Patient request changes to demographics.';
    public const SUMMARY_PAYMENT = 'Authorize online payment.';
    public const SUMMARY_INVOICE = 'Request patient online payment.';

    /** Closing notes, kept from the legacy action_taken values so the history reads the same. */
    public const NOTE_PROFILE_ACCEPTED = 'accept';
    public const NOTE_PAYMENT_POSTED = 'payment posted';

    public const DOCUMENT_TABLE = 'onsite_documents';

    /** Payload key holding the invoice selection the payment page posts, as it posts it. */
    public const PAYMENT_INVOICE_VALUES = 'value';
    /** Payload key holding the encounters a biller asked the patient to pay. */
    public const INVOICE_ENCOUNTERS = 'invoices';

    public function __construct(private ReviewQueueService $queue)
    {
    }

    /**
     * A patient's demographics edit. A second edit before staff review replaces the first.
     *
     * @param array<array-key, mixed> $changes the edited patient record
     */
    public function submitProfileChange(int $pid, array $changes, ReviewActor $patient): ReviewRequest
    {
        return $this->queue->replacePending(
            new ReviewSubmission(
                $pid,
                ReviewType::Profile,
                self::SUMMARY_PROFILE,
                $changes,
                targetTable: 'patient_data',
                targetId: (string) $pid,
            ),
            $patient,
        );
    }

    public function pendingProfileChange(int $pid): ?ReviewRequest
    {
        return $this->queue->openFor($pid, ReviewType::Profile);
    }

    /**
     * Staff committed the change to the chart (the profile review screen saves the patient
     * record itself, then closes the request).
     *
     * @return ?ReviewRequest the closed request, or null when none was open
     */
    public function completeProfileChange(int $pid, ReviewActor $staff): ?ReviewRequest
    {
        self::assertStaff($staff);
        $open = $this->pendingProfileChange($pid);

        return $open === null ? null : $this->queue->complete($open->id, $staff, self::NOTE_PROFILE_ACCEPTED);
    }

    /**
     * A patient's online payment, held for staff to post. The card details (the gateway's
     * response: card type, masked number, auth code) are kept encrypted and deleted when the
     * payment is posted.
     *
     * @param string $invoiceValues the invoice selection, as the payment page posts it
     * @param string $cardDetails JSON from the payment page; empty when there are none
     */
    public function submitPayment(int $pid, string $invoiceValues, string $cardDetails, ReviewActor $actor): ReviewRequest
    {
        $secrets = $cardDetails === '' ? [] : [ReviewQueueService::SECRET_PAYMENT_CARD => $cardDetails];

        return $this->queue->replacePending(
            new ReviewSubmission(
                $pid,
                ReviewType::Payment,
                self::SUMMARY_PAYMENT,
                [self::PAYMENT_INVOICE_VALUES => $invoiceValues],
            ),
            $actor,
            $secrets,
        );
    }

    public function pendingPayment(int $pid): ?ReviewRequest
    {
        return $this->queue->openFor($pid, ReviewType::Payment);
    }

    /**
     * The payment with this id, if it is one and (when $pid is given) belongs to that patient.
     */
    public function payment(int $id, ?int $pid = null): ?ReviewRequest
    {
        $request = $this->queue->find($id);
        if ($request === null || $request->type !== ReviewType::Payment) {
            return null;
        }

        return $pid === null || $request->pid === $pid ? $request : null;
    }

    /**
     * The invoice selection of a payment, as the payment page posted it.
     */
    public function paymentInvoiceValues(ReviewRequest $payment): string
    {
        $values = $payment->payload[self::PAYMENT_INVOICE_VALUES] ?? '';

        return is_string($values) ? $values : '';
    }

    /**
     * The card details of a payment still waiting to be posted.
     *
     * @return array<array-key, mixed> empty once the payment is posted or when there were none
     */
    public function paymentCardDetails(ReviewRequest $payment): array
    {
        $json = $this->queue->readSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD);
        if ($json === null || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Staff posted the payment.
     *
     * @return ?ReviewRequest the closed request, or null when none was open
     */
    public function completePayment(int $pid, ReviewActor $staff): ?ReviewRequest
    {
        self::assertStaff($staff);
        $open = $this->pendingPayment($pid);

        return $open === null ? null : $this->queue->complete($open->id, $staff, self::NOTE_PAYMENT_POSTED);
    }

    /**
     * Staff posted this payment (the one they opened). Unlike completePayment(), a payment the
     * patient has since replaced is not closed, and neither is the newer one.
     *
     * @return ?ReviewRequest the closed request, or null when it is not this patient's open payment
     */
    public function completePaymentRequest(int $id, int $pid, ReviewActor $staff): ?ReviewRequest
    {
        self::assertStaff($staff);
        $payment = $this->payment($id, $pid);
        if ($payment === null || !$payment->status->isOpen()) {
            return null;
        }

        return $this->queue->complete($payment->id, $staff, self::NOTE_PAYMENT_POSTED);
    }

    /**
     * A biller asks the patient to pay these encounters online. It waits on the patient, not on
     * staff, so it is never counted as pending review.
     *
     * @param array<array-key, mixed> $encounters
     */
    public function requestInvoicePayment(int $pid, array $encounters, ReviewActor $staff): ReviewRequest
    {
        self::assertStaff($staff);
        return $this->queue->replacePending(
            new ReviewSubmission($pid, ReviewType::Invoice, self::SUMMARY_INVOICE, [self::INVOICE_ENCOUNTERS => $encounters]),
            $staff,
        );
    }

    /**
     * The patient sent a portal document for review (or saved it while it is in review). One
     * request per document: an open one is kept, not replaced.
     */
    public function submitDocument(int $pid, int $documentId, string $documentType, ReviewActor $actor): ReviewRequest
    {
        return $this->queue->submitUnlessOpen(
            new ReviewSubmission(
                $pid,
                ReviewType::Document,
                $documentType,
                ['doc_type' => $documentType],
                targetTable: self::DOCUMENT_TABLE,
                targetId: (string) $documentId,
            ),
            $actor,
        );
    }

    /**
     * The document review request with this id, if it is one.
     */
    public function document(int $id): ?ReviewRequest
    {
        $request = $this->queue->find($id);

        return $request !== null && $request->type === ReviewType::Document ? $request : null;
    }

    public function pendingDocument(int $pid, int $documentId): ?ReviewRequest
    {
        return $this->queue->openForTarget($pid, ReviewType::Document, self::DOCUMENT_TABLE, (string) $documentId);
    }

    /**
     * The document's most recent review request, open or closed.
     */
    public function latestDocument(int $pid, int $documentId): ?ReviewRequest
    {
        return $this->queue->latestForTarget($pid, ReviewType::Document, self::DOCUMENT_TABLE, (string) $documentId);
    }

    /**
     * Staff charted the document.
     *
     * @return ?ReviewRequest the closed request, or null when none was open
     */
    public function completeDocument(int $pid, int $documentId, ReviewActor $staff): ?ReviewRequest
    {
        self::assertStaff($staff);
        $open = $this->pendingDocument($pid, $documentId);

        return $open === null ? null : $this->queue->complete($open->id, $staff, null);
    }

    /**
     * The document was deleted before it was charted.
     *
     * @return ?ReviewRequest the cancelled request, or null when none was open
     */
    public function cancelDocument(int $pid, int $documentId, ReviewActor $actor, string $note): ?ReviewRequest
    {
        $open = $this->pendingDocument($pid, $documentId);

        return $open === null ? null : $this->queue->cancel($open->id, $actor, $note);
    }

    /**
     * Staff removed a request from the review list. A document request also takes its
     * un-charted document with it, in the same transaction. Other types only cancel the request.
     *
     * @throws \DomainException when the actor is not a staff user
     * @throws InvalidReviewTransitionException when the request is no longer open
     */
    public function discard(ReviewRequest $request, ReviewActor $staff, string $note): ReviewRequest
    {
        self::assertStaff($staff);
        if ($request->type !== ReviewType::Document) {
            return $this->queue->cancel($request->id, $staff, $note);
        }

        return $this->queue->cancelAlongside(
            $request->id,
            $staff,
            $note,
            static function (ReviewRequest $cancelled): void {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `' . self::DOCUMENT_TABLE . '` WHERE `id` = ? AND `pid` = ?',
                    [$cancelled->targetId, $cancelled->pid]
                );
            },
        );
    }

    /**
     * The numbers behind the staff portal alert badge.
     *
     * @return array{audits: int, payments: int} audits includes payments
     */
    public function alertCounts(): array
    {
        return [
            // Only the types the portal review list shows; other sources (FHIR writes) have
            // their own screens.
            'audits' => $this->queue->countPending(ReviewType::Profile)
                + $this->queue->countPending(ReviewType::Payment)
                + $this->queue->countPending(ReviewType::Document),
            'payments' => $this->queue->countPending(ReviewType::Payment),
        ];
    }

    /**
     * Closing a review, or asking a patient to pay, is a staff decision. A portal session that
     * reaches one of these (a forged document status, say) is refused.
     *
     * @throws \DomainException when the actor is not a staff user
     */
    private static function assertStaff(ReviewActor $actor): void
    {
        if ($actor->type !== ReviewActorType::User) {
            throw new \DomainException('Only staff can take this review action');
        }
    }
}

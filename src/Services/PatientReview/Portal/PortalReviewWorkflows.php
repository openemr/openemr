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

use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewActorType;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;
use Psr\Log\LoggerInterface;

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
    /** Payload key: whether the gateway had already charged the card when the payment was recorded. */
    public const PAYMENT_CHARGED = 'charged';
    /**
     * The card fields staff need to charge an uncharged payment, as the payment page's card
     * form posts them (and requires them before submitting).
     */
    public const CARD_FIELDS = ['cardHolderName', 'cardNumber', 'month', 'year', 'cardCode'];
    /** Payload key holding the encounters a biller asked the patient to pay. */
    public const INVOICE_ENCOUNTERS = 'invoices';

    /** The request types the portal review list shows, and the order it counts them in. */
    public const LIST_TYPES = [ReviewType::Profile, ReviewType::Payment, ReviewType::Document, ReviewType::Invoice];

    /** The portal request types that wait on staff review (an invoice waits on the patient). */
    public const REVIEW_TYPES = [ReviewType::Profile, ReviewType::Payment, ReviewType::Document];

    public function __construct(private ReviewQueueService $queue, private LoggerInterface $logger)
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
     * A charged payment (the gateway took the money) is its own pending request and never
     * replaces another: each is real money staff must post. A payment left for staff to charge
     * replaces only another uncharged one, which it supersedes. A request with no charged flag
     * (from before it was recorded) is treated as charged, so it is never cancelled.
     *
     * @param string $invoiceValues the invoice selection, as the payment page posts it
     * @param string $cardDetails JSON from the payment page; empty when there are none
     * @param bool $charged whether the gateway has already charged the card
     * @throws \InvalidArgumentException when an uncharged payment has no usable card details
     */
    public function submitPayment(int $pid, string $invoiceValues, string $cardDetails, bool $charged, ReviewActor $actor): ReviewRequest
    {
        self::assertChargeable($cardDetails, $charged);
        $submission = self::paymentSubmission($pid, $invoiceValues, $charged);
        if ($charged) {
            return $this->queue->submit($submission, $actor, self::paymentSecrets($cardDetails));
        }

        return $this->queue->replacePending(
            $submission,
            $actor,
            self::paymentSecrets($cardDetails),
            static fn(ReviewRequest $open): bool => !self::isChargedPayment($open),
        );
    }

    /**
     * Whether the gateway had already charged the card when the payment was recorded. A
     * request with no charged flag (from before it was recorded) counts as charged.
     */
    public static function isChargedPayment(ReviewRequest $payment): bool
    {
        return ($payment->payload[self::PAYMENT_CHARGED] ?? true) !== false;
    }

    /**
     * Whether card details hold every field staff need to charge the card (CARD_FIELDS), each a
     * non-empty string.
     *
     * @param array<array-key, mixed> $cardDetails
     */
    public static function isUsableCard(array $cardDetails): bool
    {
        foreach (self::CARD_FIELDS as $field) {
            $value = $cardDetails[$field] ?? null;
            if (!is_string($value) || trim($value) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * An uncharged payment is charged by staff from its card details, so it is refused without
     * usable ones. A charged payment's details only describe the gateway's transaction.
     *
     * @throws \InvalidArgumentException
     */
    private static function assertChargeable(string $cardDetails, bool $charged): void
    {
        if ($charged) {
            return;
        }
        $decoded = json_decode($cardDetails, true);
        if (!is_array($decoded) || !self::isUsableCard($decoded)) {
            throw new \InvalidArgumentException('An uncharged payment needs the card details to charge');
        }
    }

    /**
     * Why staff cannot post this open payment, or null when they can. An uncharged payment
     * (staff charge the card) needs card details they can read, with every field to charge it;
     * the patient submits it again otherwise. A charged one can always be posted: the gateway
     * holds the transaction.
     *
     * @param ?array<array-key, mixed> $cardDetails the payment's card details, as
     *                                              paymentCardDetails() returns them
     */
    public static function postingBlock(ReviewRequest $payment, ?array $cardDetails): ?PaymentPostingBlock
    {
        if (self::isChargedPayment($payment)) {
            return null;
        }
        if ($cardDetails === null) {
            return PaymentPostingBlock::CardUnreadable;
        }
        if ($cardDetails === []) {
            return PaymentPostingBlock::NoCardDetails;
        }

        return self::isUsableCard($cardDetails) ? null : PaymentPostingBlock::IncompleteCardDetails;
    }

    /**
     * A payment recorded alongside the pending ones rather than replacing any, for when staff
     * may be posting one right now (the posting lock could not be had).
     */
    public function submitAdditionalPayment(int $pid, string $invoiceValues, string $cardDetails, bool $charged, ReviewActor $actor): ReviewRequest
    {
        self::assertChargeable($cardDetails, $charged);
        return $this->queue->submit(self::paymentSubmission($pid, $invoiceValues, $charged), $actor, self::paymentSecrets($cardDetails));
    }

    private static function paymentSubmission(int $pid, string $invoiceValues, bool $charged): ReviewSubmission
    {
        return new ReviewSubmission(
            $pid,
            ReviewType::Payment,
            self::SUMMARY_PAYMENT,
            [self::PAYMENT_INVOICE_VALUES => $invoiceValues, self::PAYMENT_CHARGED => $charged],
        );
    }

    /**
     * @return array<string, string>
     */
    private static function paymentSecrets(string $cardDetails): array
    {
        return $cardDetails === '' ? [] : [ReviewQueueService::SECRET_PAYMENT_CARD => $cardDetails];
    }

    public function pendingPayment(int $pid): ?ReviewRequest
    {
        return $this->queue->openFor($pid, ReviewType::Payment);
    }

    /**
     * Whether any open payment of the patient's was already charged by the gateway, not only the
     * newest one: while one is, the patient is not offered another payment.
     */
    public function hasOpenChargedPayment(int $pid): bool
    {
        foreach ($this->queue->allOpenFor($pid, ReviewType::Payment) as $payment) {
            if (self::isChargedPayment($payment)) {
                return true;
            }
        }

        return false;
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
     * Card details that cannot be decrypted (the site's keys were replaced after the payment
     * was submitted) come back as null rather than throwing, so the payment page still opens:
     * the patient can submit the payment again and staff can see it needs that.
     *
     * @return ?array<array-key, mixed> empty once the payment is posted or when there were none;
     *                                  null when they are stored but cannot be read
     */
    public function paymentCardDetails(ReviewRequest $payment): ?array
    {
        try {
            $json = $this->queue->readSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD);
        } catch (CryptoGenException $e) {
            $this->logger->error('Card details of a portal payment could not be decrypted', [
                'request_id' => $payment->id,
                'exception' => $e,
            ]);
            return null;
        }
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
            'audits' => array_sum(array_map($this->queue->countPending(...), self::REVIEW_TYPES)),
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

<?php

/**
 * The portal workflows (profile, payment, invoice, document, alert counts) against an
 * in-memory queue. These pin the behavior the legacy onsite_portal_activity callers had.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use DateTimeImmutable;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidFactory;

final class PortalReviewWorkflowsIsolatedTest extends TestCase
{
    private const PID = 7;
    private const CARD = '{"cardHolderName":"Ann Lee","cc_type":"Visa","cardNumber":"******** 1111","authCode":"A1","transId":"T1","zip":"12345"}';

    private InMemoryReviewRequestStore $store;
    private ReviewQueueService $queue;
    private PortalReviewWorkflows $workflows;

    protected function setUp(): void
    {
        $this->store = new InMemoryReviewRequestStore();
        $clock = new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable('2026-10-01 09:30:00');
            }
        };
        $crypto = $this->createMock(CryptoInterface::class);
        $crypto->method('encryptForDatabase')->willReturnCallback(static fn(?string $value): string => 'enc(' . $value . ')');
        $crypto->method('decryptFromDatabase')->willReturnCallback(static fn(?string $value): string => substr((string) $value, 4, -1));

        $this->queue = new ReviewQueueService($this->store, new RecordingReviewAuditTrail(), $clock, new UuidFactory(), $crypto, new NullLogger());
        $this->workflows = new PortalReviewWorkflows($this->queue);
    }

    private function patient(): ReviewActor
    {
        return ReviewActor::patient(self::PID, 'ann7');
    }

    private function staff(): ReviewActor
    {
        return ReviewActor::user(3, 'drsmith');
    }

    public function testAProfileEditReplacesTheOnePendingEdit(): void
    {
        $first = $this->workflows->submitProfileChange(self::PID, ['fname' => 'Ann'], $this->patient());
        $second = $this->workflows->submitProfileChange(self::PID, ['fname' => 'Anne'], $this->patient());

        $pending = $this->workflows->pendingProfileChange(self::PID);
        $this->assertNotNull($pending);
        $this->assertSame($second->id, $pending->id);
        $this->assertSame(['fname' => 'Anne'], $pending->payload);
        $this->assertSame(ReviewStatus::Cancelled, $this->queue->get($first->id)->status);
        $this->assertSame('patient_data', $second->targetTable);
        $this->assertSame((string) self::PID, $second->targetId);
    }

    public function testStaffCommittingAProfileEditClosesIt(): void
    {
        $nothingOpen = $this->workflows->completeProfileChange(self::PID, $this->staff());
        $this->assertNull($nothingOpen, 'nothing to close');

        $this->workflows->submitProfileChange(self::PID, ['fname' => 'Ann'], $this->patient());
        $closed = $this->workflows->completeProfileChange(self::PID, $this->staff());

        $this->assertSame(ReviewStatus::Completed, $closed?->status);
        $this->assertSame(3, $closed->reviewedBy);
        $this->assertSame(PortalReviewWorkflows::NOTE_PROFILE_ACCEPTED, $closed->reviewNote);
        $this->assertNull($this->workflows->pendingProfileChange(self::PID));
    }

    public function testAPaymentKeepsItsCardDetailsEncryptedUntilPosted(): void
    {
        $payment = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, $this->patient());

        $this->assertSame(['value' => '{"1001":"10.00"}'], $payment->payload, 'no card data in the payload');
        $this->assertSame('{"1001":"10.00"}', $this->workflows->paymentInvoiceValues($payment));
        $this->assertSame('enc(' . self::CARD . ')', $this->store->getSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertSame('Ann Lee', $this->workflows->paymentCardDetails($payment)['cardHolderName'] ?? null);

        $posted = $this->workflows->completePayment(self::PID, $this->staff());

        $this->assertSame(ReviewStatus::Completed, $posted?->status);
        $this->assertSame(PortalReviewWorkflows::NOTE_PAYMENT_POSTED, $posted->reviewNote);
        $this->assertNull($this->store->getSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD), 'card details are deleted on posting');
        $this->assertSame([], $this->workflows->paymentCardDetails($posted));
    }

    public function testAPaymentWithoutCardDetailsStoresNoSecret(): void
    {
        $payment = $this->workflows->submitPayment(self::PID, '{}', '', $this->patient());

        $this->assertNull($this->store->getSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertSame([], $this->workflows->paymentCardDetails($payment));
    }

    public function testASecondPaymentReplacesTheFirstAndDropsItsCard(): void
    {
        $first = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, $this->patient());
        $second = $this->workflows->submitPayment(self::PID, '{"1001":"12.00"}', self::CARD, $this->patient());

        $this->assertSame(ReviewStatus::Cancelled, $this->queue->get($first->id)->status);
        $this->assertNull($this->store->getSecret($first->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertSame($second->id, $this->workflows->pendingPayment(self::PID)?->id);
    }

    public function testClosingTheOpenedPaymentLeavesAReplacementAlone(): void
    {
        $opened = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, $this->patient());
        $replacement = $this->workflows->submitPayment(self::PID, '{"1001":"12.00"}', self::CARD, $this->patient());

        $staleClose = $this->workflows->completePaymentRequest($opened->id, self::PID, $this->staff());
        $wrongPatient = $this->workflows->completePaymentRequest($replacement->id, 8, $this->staff());
        $this->assertNull($staleClose, 'a payment the patient replaced is not posted');
        $this->assertNull($wrongPatient, "another patient's id does not close it");
        $this->assertSame(ReviewStatus::Pending, $this->queue->get($replacement->id)->status, 'the newer payment stays pending');

        $closed = $this->workflows->completePaymentRequest($replacement->id, self::PID, $this->staff());
        $this->assertNotNull($closed);
        $this->assertSame(ReviewStatus::Completed, $closed->status);
        $this->assertSame(PortalReviewWorkflows::NOTE_PAYMENT_POSTED, $closed->reviewNote);
    }

        public function testPaymentLookupChecksTypeAndPatient(): void
    {
        $payment = $this->workflows->submitPayment(self::PID, '{}', '', $this->patient());
        $profile = $this->workflows->submitProfileChange(self::PID, ['fname' => 'Ann'], $this->patient());

        $this->assertSame($payment->id, $this->workflows->payment($payment->id)?->id);
        $this->assertSame($payment->id, $this->workflows->payment($payment->id, self::PID)?->id);
        $this->assertNull($this->workflows->payment($payment->id, 8), 'another patient cannot open it');
        $this->assertNull($this->workflows->payment($profile->id), 'not a payment');
        $this->assertNull($this->workflows->payment(999));
    }

    public function testAnInvoiceWaitsOnThePatientAndIsNotCounted(): void
    {
        $invoice = $this->workflows->requestInvoicePayment(self::PID, [['encounter' => 5, 'amount' => '20.00']], $this->staff());

        $this->assertSame(ReviewType::Invoice, $invoice->type);
        $this->assertSame(ReviewStatus::AwaitingPayment, $invoice->status);
        $this->assertSame(['audits' => 0, 'payments' => 0], $this->workflows->alertCounts());
    }

    public function testADocumentHasOneOpenRequestPerDocument(): void
    {
        $consent = $this->workflows->submitDocument(self::PID, 11, 'Consent', $this->patient());
        $again = $this->workflows->submitDocument(self::PID, 11, 'Consent', $this->patient());
        $history = $this->workflows->submitDocument(self::PID, 12, 'History', $this->patient());

        $this->assertSame($consent->id, $again->id, 'saving a document in review keeps its request');
        $this->assertNotSame($consent->id, $history->id);
        $this->assertSame('11', $consent->targetId);
        $this->assertSame(PortalReviewWorkflows::DOCUMENT_TABLE, $consent->targetTable);
        $this->assertSame(2, $this->queue->countPending(ReviewType::Document));
    }

    public function testChartingOrDeletingADocumentClosesItsRequest(): void
    {
        $nothingToChart = $this->workflows->completeDocument(self::PID, 11, $this->staff());
        $nothingToCancel = $this->workflows->cancelDocument(self::PID, 11, $this->patient(), 'deleted');
        $this->assertNull($nothingToChart);
        $this->assertNull($nothingToCancel);

        $this->workflows->submitDocument(self::PID, 11, 'Consent', $this->patient());
        $this->workflows->submitDocument(self::PID, 12, 'History', $this->patient());

        $charted = $this->workflows->completeDocument(self::PID, 11, $this->staff());
        $deleted = $this->workflows->cancelDocument(self::PID, 12, $this->patient(), 'Patient deleted un-charted template');

        $this->assertNotNull($charted);
        $this->assertNotNull($deleted);
        $this->assertSame(ReviewStatus::Completed, $charted->status);
        $this->assertSame(ReviewStatus::Cancelled, $deleted->status);
        $this->assertSame('Patient deleted un-charted template', $this->queue->history($deleted->id)[1]->note);
        $this->assertSame(ReviewStatus::Completed, $this->workflows->latestDocument(self::PID, 11)?->status);
        $this->assertNull($this->workflows->pendingDocument(self::PID, 11));
    }

    public function testAlertCountsIncludePaymentsInAudits(): void
    {
        $this->workflows->submitProfileChange(self::PID, ['fname' => 'Ann'], $this->patient());
        $this->workflows->submitPayment(self::PID, '{}', '', $this->patient());
        $this->workflows->submitDocument(self::PID, 11, 'Consent', $this->patient());
        $this->workflows->submitPayment(8, '{}', '', ReviewActor::patient(8, 'bob8'));

        $this->assertSame(['audits' => 4, 'payments' => 2], $this->workflows->alertCounts());
    }
}

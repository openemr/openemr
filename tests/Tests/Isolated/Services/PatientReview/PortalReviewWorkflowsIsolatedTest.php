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
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\FhirResourceType;
use OpenEMR\Services\PatientReview\PayloadFormat;
use OpenEMR\Services\PatientReview\Portal\PaymentPostingBlock;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidFactory;

final class PortalReviewWorkflowsIsolatedTest extends TestCase
{
    private const PID = 7;
    private const OLD_KEY_SECRET = 'written under replaced keys';
    private const CARD = '{"cardHolderName":"Ann Lee","cc_type":"Visa","cardNumber":"4111111111111111","month":"12","year":"2030","cardCode":"123","zip":"12345"}';

    private InMemoryReviewRequestStore $store;
    private ReviewQueueService $queue;
    private PortalReviewWorkflows $workflows;
    private RecordingLogger $logger;

    /**
     * A fresh in-memory queue, with a crypto stub that fails on the old key ciphertext.
     */
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
        $crypto->method('decryptFromDatabase')->willReturnCallback(static function (?string $value): string {
            // A secret written under keys the site no longer has.
            if ($value === self::OLD_KEY_SECRET) {
                throw new CryptoGenException('Decryption failed');
            }
            return substr((string) $value, 4, -1);
        });
        $this->logger = new RecordingLogger();

        $this->queue = new ReviewQueueService($this->store, new RecordingReviewAuditTrail(), $clock, new UuidFactory(), $crypto, new NullLogger());
        $this->workflows = new PortalReviewWorkflows($this->queue, $this->logger);
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
        $payment = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());

        $this->assertSame(['value' => '{"1001":"10.00"}', 'charged' => false], $payment->payload, 'no card data in the payload');
        $this->assertSame('{"1001":"10.00"}', $this->workflows->paymentInvoiceValues($payment));
        $this->assertSame('enc(' . self::CARD . ')', $this->store->getSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertSame('Ann Lee', $this->workflows->paymentCardDetails($payment)['cardHolderName'] ?? null);

        $posted = $this->workflows->completePayment(self::PID, $this->staff());

        $this->assertSame(ReviewStatus::Completed, $posted?->status);
        $this->assertSame(PortalReviewWorkflows::NOTE_PAYMENT_POSTED, $posted->reviewNote);
        $this->assertNull($this->store->getSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD), 'card details are deleted on posting');
        $this->assertSame([], $this->workflows->paymentCardDetails($posted));
    }

    /**
     * Undecryptable card details are logged and come back as null, and the patient can submit again.
     */
    public function testCardDetailsThatCannotBeDecryptedAreReportedNotThrown(): void
    {
        $payment = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        $this->store->putSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD, self::OLD_KEY_SECRET, new DateTimeImmutable('2026-10-01 09:30:00'));

        $this->assertNull($this->workflows->paymentCardDetails($payment));
        $this->assertCount(1, $this->logger->records);
        $this->assertSame($payment->id, $this->logger->records[0][2]['request_id'] ?? null);

        // The patient can still replace it, and the new payment's card details read normally.
        $again = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        $this->assertSame(ReviewStatus::Cancelled, $this->queue->get($payment->id)->status);
        $this->assertSame('Ann Lee', $this->workflows->paymentCardDetails($again)['cardHolderName'] ?? null);
    }

    private function postingBlock(ReviewRequest $payment): ?PaymentPostingBlock
    {
        return PortalReviewWorkflows::postingBlock($payment, $this->workflows->paymentCardDetails($payment));
    }

    public function testAnUnreadableUnchargedPaymentCannotBePosted(): void
    {
        $uncharged = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        $this->assertNull($this->postingBlock($uncharged));

        $this->store->putSecret($uncharged->id, ReviewQueueService::SECRET_PAYMENT_CARD, self::OLD_KEY_SECRET, new DateTimeImmutable('2026-10-01 09:30:00'));
        $this->assertFalse(PortalReviewWorkflows::isChargedPayment($uncharged));
        $this->assertSame(PaymentPostingBlock::CardUnreadable, $this->postingBlock($uncharged), 'staff cannot charge a card they cannot read');
    }

    /**
     * Stored card details that are empty (a payment from before the rule) block posting.
     */
    public function testAnUnchargedPaymentWithNoCardDetailsCannotBePosted(): void
    {
        $uncharged = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        $this->store->putSecret($uncharged->id, ReviewQueueService::SECRET_PAYMENT_CARD, 'enc()', new DateTimeImmutable('2026-10-01 09:30:00'));

        $this->assertSame(PaymentPostingBlock::NoCardDetails, $this->postingBlock($uncharged), 'there is no card to charge');
    }

    /**
     * Readable card details that lack a field needed to charge the card block posting.
     */
    public function testAnUnchargedPaymentWithIncompleteCardDetailsCannotBePosted(): void
    {
        $uncharged = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        $this->store->putSecret($uncharged->id, ReviewQueueService::SECRET_PAYMENT_CARD, 'enc({"cardHolderName":"Ann Lee","note":"x"})', new DateTimeImmutable('2026-10-01 09:30:00'));

        $this->assertSame(PaymentPostingBlock::IncompleteCardDetails, $this->postingBlock($uncharged));
    }

    /**
     * An uncharged payment is refused at submission unless every card field is there.
     */
    public function testAnUnchargedPaymentNeedsUsableCardDetails(): void
    {
        $incomplete = json_decode(self::CARD, true, flags: JSON_THROW_ON_ERROR);
        $incomplete['cardCode'] = ' ';
        foreach (['', 'not json', '{"note":"x"}', json_encode($incomplete, JSON_THROW_ON_ERROR)] as $card) {
            try {
                $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', $card, false, $this->patient());
                $this->fail('accepted ' . $card);
            } catch (\InvalidArgumentException) {
                $this->assertNull($this->workflows->pendingPayment(self::PID), 'nothing recorded for ' . $card);
            }
        }
        $this->assertTrue(PortalReviewWorkflows::isUsableCard(json_decode(self::CARD, true, flags: JSON_THROW_ON_ERROR)));
        $charged = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', '', true, $this->patient());
        $this->assertSame($charged->id, $this->workflows->pendingPayment(self::PID)?->id, 'a charged payment needs none');
    }

    public function testAChargedPaymentCanBePostedWithUnreadableOrNoCardDetails(): void
    {
        $charged = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, true, $this->patient());
        $this->store->putSecret($charged->id, ReviewQueueService::SECRET_PAYMENT_CARD, self::OLD_KEY_SECRET, new DateTimeImmutable('2026-10-01 09:30:00'));

        $this->assertTrue(PortalReviewWorkflows::isChargedPayment($charged));
        $this->assertNull($this->postingBlock($charged), 'the gateway holds the transaction');

        $noCard = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', '', true, $this->patient());
        $this->assertNull($this->postingBlock($noCard));
    }

    /**
     * Every posting block tells staff something.
     */
    public function testEachPostingBlockHasAMessage(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', true);
        foreach (PaymentPostingBlock::cases() as $block) {
            $this->assertNotSame('', $block->message());
        }
    }

    public function testAnOlderChargedPaymentStillCountsAsOpen(): void
    {
        $this->assertFalse($this->workflows->hasOpenChargedPayment(self::PID));

        $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, true, $this->patient());
        // A later uncharged payment is the newest open one, but the charged one is still open.
        $newest = $this->workflows->submitPayment(self::PID, '{"1001":"5.00"}', self::CARD, false, $this->patient());

        $this->assertSame($newest->id, $this->workflows->pendingPayment(self::PID)?->id);
        $this->assertFalse(PortalReviewWorkflows::isChargedPayment($newest));
        $this->assertTrue($this->workflows->hasOpenChargedPayment(self::PID));
        $this->assertFalse($this->workflows->hasOpenChargedPayment(self::PID + 1), 'another patient');
    }

    public function testAPaymentWithNoChargedFlagCountsAsCharged(): void
    {
        $payment = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        // A payment migrated from onsite_portal_activity has no charged flag.
        $migrated = new ReviewRequest(
            $payment->id,
            $payment->uuid,
            $payment->pid,
            $payment->type,
            null,
            $payment->source,
            null,
            $payment->status,
            $payment->summary,
            ['value' => '{"1001":"10.00"}'],
            PayloadFormat::Text,
            null,
            null,
            $payment->createdAt,
            $payment->updatedAt,
            null,
            null,
            null,
        );

        $this->assertFalse(PortalReviewWorkflows::isChargedPayment($payment));
        $this->assertTrue(PortalReviewWorkflows::isChargedPayment($migrated));
    }

    public function testAPaymentWithoutCardDetailsStoresNoSecret(): void
    {
        $payment = $this->workflows->submitPayment(self::PID, '{}', '', true, $this->patient());

        $this->assertNull($this->store->getSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertSame([], $this->workflows->paymentCardDetails($payment));
    }

    public function testASecondPaymentReplacesTheFirstAndDropsItsCard(): void
    {
        $first = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        $second = $this->workflows->submitPayment(self::PID, '{"1001":"12.00"}', self::CARD, false, $this->patient());

        $this->assertSame(ReviewStatus::Cancelled, $this->queue->get($first->id)->status);
        $this->assertNull($this->store->getSecret($first->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertSame($second->id, $this->workflows->pendingPayment(self::PID)?->id);
    }

    public function testAChargedPaymentNeitherReplacesNorIsReplaced(): void
    {
        $uncharged = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        $charged = $this->workflows->submitPayment(self::PID, '{"1001":"12.00"}', self::CARD, true, $this->patient());
        $resubmitted = $this->workflows->submitPayment(self::PID, '{"1001":"15.00"}', self::CARD, false, $this->patient());

        $this->assertSame(ReviewStatus::Cancelled, $this->queue->get($uncharged->id)->status, 'an uncharged payment is still superseded');
        $this->assertSame(ReviewStatus::Pending, $this->queue->get($charged->id)->status, 'a charged payment is never cancelled');
        $this->assertNotNull($this->store->getSecret($charged->id, ReviewQueueService::SECRET_PAYMENT_CARD), 'and keeps its card details');
        $this->assertSame(ReviewStatus::Pending, $this->queue->get($resubmitted->id)->status);
    }

    public function testClosingTheOpenedPaymentLeavesAReplacementAlone(): void
    {
        $opened = $this->workflows->submitPayment(self::PID, '{"1001":"10.00"}', self::CARD, false, $this->patient());
        $replacement = $this->workflows->submitPayment(self::PID, '{"1001":"12.00"}', self::CARD, false, $this->patient());

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
        $payment = $this->workflows->submitPayment(self::PID, '{}', '', true, $this->patient());
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

    public function testAPatientCannotCloseTheirOwnDocumentReview(): void
    {
        $submitted = $this->workflows->submitDocument(self::PID, 11, 'Consent', $this->patient());

        $this->expectException(\DomainException::class);
        try {
            $this->workflows->completeDocument(self::PID, 11, $this->patient());
        } finally {
            $this->assertSame(ReviewStatus::Pending, $this->queue->get($submitted->id)->status);
        }
    }

    public function testAPatientCannotAcceptTheirOwnProfileChange(): void
    {
        $submitted = $this->workflows->submitProfileChange(self::PID, ['fname' => 'Ann'], $this->patient());

        $this->expectException(\DomainException::class);
        try {
            $this->workflows->completeProfileChange(self::PID, $this->patient());
        } finally {
            $this->assertSame(ReviewStatus::Pending, $this->queue->get($submitted->id)->status);
        }
    }

    public function testDiscardingAPaymentCancelsItAndDropsTheCard(): void
    {
        $payment = $this->workflows->submitPayment(self::PID, '{"form_paytotal":"20.00"}', self::CARD, false, $this->patient());

        $discarded = $this->workflows->discard($payment, $this->staff(), 'User deleted audit');

        $this->assertSame(ReviewStatus::Cancelled, $discarded->status);
        $this->assertNull($this->store->getSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertNull($this->workflows->pendingPayment(self::PID));
    }

    public function testDiscardRefusesARequestAlreadyClosed(): void
    {
        $payment = $this->workflows->submitPayment(self::PID, '{}', '', true, $this->patient());
        $this->workflows->completePayment(self::PID, $this->staff());

        $this->expectException(InvalidReviewTransitionException::class);
        $this->workflows->discard($payment, $this->staff(), 'stale row');
    }

    public function testAlertCountsLeaveOutNonPortalRequests(): void
    {
        $this->workflows->submitDocument(self::PID, 11, 'Consent', $this->patient());
        $this->queue->submit(
            new ReviewSubmission(
                self::PID,
                ReviewType::FhirWrite,
                'Patient app Condition write',
                ['resourceType' => 'Condition'],
                ReviewSource::Api,
                'client-abc',
                resourceType: new FhirResourceType('Condition'),
            ),
            $this->patient(),
        );

        $this->assertSame(['audits' => 1, 'payments' => 0], $this->workflows->alertCounts());
    }

    public function testAlertCountsIncludePaymentsInAudits(): void
    {
        $this->workflows->submitProfileChange(self::PID, ['fname' => 'Ann'], $this->patient());
        $this->workflows->submitPayment(self::PID, '{}', '', true, $this->patient());
        $this->workflows->submitDocument(self::PID, 11, 'Consent', $this->patient());
        $this->workflows->submitPayment(8, '{}', '', true, ReviewActor::patient(8, 'bob8'));

        $this->assertSame(['audits' => 4, 'payments' => 2], $this->workflows->alertCounts());
    }
}

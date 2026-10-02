<?php

/**
 * Isolated tests for the review queue rules, against an in-memory store.
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
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\Exception\ReviewHandlerMissingException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestNotFoundException;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewActorType;
use OpenEMR\Services\PatientReview\ReviewHandlerInterface;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidFactory;

class ReviewQueueServiceIsolatedTest extends TestCase
{
    private const PID = 7;

    private InMemoryReviewRequestStore $store;
    private RecordingReviewAuditTrail $trail;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->store = new InMemoryReviewRequestStore();
        $this->trail = new RecordingReviewAuditTrail();
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @param list<ReviewHandlerInterface> $handlers
     */
    private function service(array $handlers = []): ReviewQueueService
    {
        $clock = new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable('2026-10-01 09:30:00');
            }
        };
        $crypto = $this->createMock(CryptoInterface::class);
        $crypto->method('encryptForDatabase')->willReturnCallback(static fn(?string $value): string => 'enc(' . $value . ')');
        $crypto->method('decryptFromDatabase')->willReturnCallback(static fn(?string $value): string => substr((string) $value, 4, -1));

        return new ReviewQueueService($this->store, $this->trail, $clock, new UuidFactory(), $crypto, $this->logger, $handlers);
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private function profile(array $payload = ['fname' => 'Ann']): ReviewSubmission
    {
        return new ReviewSubmission(self::PID, ReviewType::Profile, 'Patient request changes to demographics.', $payload);
    }

    private function patient(): ReviewActor
    {
        return ReviewActor::patient(self::PID, 'ann7');
    }

    private function staff(): ReviewActor
    {
        return ReviewActor::user(3, 'drsmith');
    }

    public function testSubmitCreatesAPendingRequestWithItsFirstEvent(): void
    {
        $request = $this->service()->submit($this->profile(), $this->patient());

        $this->assertSame(ReviewStatus::Pending, $request->status);
        $this->assertSame(self::PID, $request->pid);
        $this->assertSame(['fname' => 'Ann'], $request->payload);
        $this->assertSame(ReviewSource::Portal, $request->source);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $request->uuid);

        $history = $this->service()->history($request->id);
        $this->assertCount(1, $history);
        $this->assertNull($history[0]->from);
        $this->assertSame(ReviewStatus::Pending, $history[0]->to);
        $this->assertSame(ReviewActorType::Patient, $history[0]->actorType);
        $this->assertSame(self::PID, $history[0]->actorId);

        $this->assertSame([['id' => $request->id, 'from' => null, 'to' => ReviewStatus::Pending, 'actor' => 'ann7', 'note' => null]], $this->trail->entries);
    }

    public function testAnInvoiceWaitsOnThePatientNotStaff(): void
    {
        $service = $this->service();
        $invoice = $service->submit(
            new ReviewSubmission(self::PID, ReviewType::Invoice, 'Request patient online payment.', ['invoices' => [12]]),
            $this->staff()
        );

        $this->assertSame(ReviewStatus::AwaitingPayment, $invoice->status);
        $this->assertSame(0, $service->countPending());
        $this->assertSame($invoice->id, $service->openFor(self::PID, ReviewType::Invoice)?->id);
    }

    public function testAnApiSubmissionRecordsItsClient(): void
    {
        $request = $this->service()->submit(
            new ReviewSubmission(self::PID, ReviewType::Document, 'Uploaded document', ['name' => 'a.pdf'], ReviewSource::Api, 'client-abc'),
            $this->patient()
        );

        $this->assertSame(ReviewSource::Api, $request->source);
        $this->assertSame('client-abc', $request->clientId);
    }

    public function testReplacePendingKeepsOneOpenRequestAndTheSupersededOne(): void
    {
        $service = $this->service();
        $first = $service->replacePending($this->profile(['fname' => 'Ann']), $this->patient());
        $second = $service->replacePending($this->profile(['fname' => 'Anne']), $this->patient());

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame($second->id, $service->openFor(self::PID, ReviewType::Profile)?->id);
        $this->assertSame(1, $service->countPending(ReviewType::Profile));

        $superseded = $service->get($first->id);
        $this->assertSame(ReviewStatus::Cancelled, $superseded->status);
        $this->assertSame(['fname' => 'Ann'], $superseded->payload, 'the earlier submission is kept as submitted');
        $this->assertSame(ReviewQueueService::NOTE_SUPERSEDED, $service->history($first->id)[1]->note);
    }

    public function testReplacePendingLeavesOtherTypesAndPatientsAlone(): void
    {
        $service = $this->service();
        $payment = $service->submit(new ReviewSubmission(self::PID, ReviewType::Payment, 'Authorize online payment.', ['amount' => '10.00']), $this->patient());
        $other = $service->submit(new ReviewSubmission(8, ReviewType::Profile, 'Other patient', []), ReviewActor::patient(8, 'bob8'));
        $service->replacePending($this->profile(), $this->patient());

        $this->assertSame(ReviewStatus::Pending, $service->get($payment->id)->status);
        $this->assertSame(ReviewStatus::Pending, $service->get($other->id)->status);
        $this->assertSame(3, $service->countPending());
    }

    public function testApproveAppliesThroughTheHandlerAndCompletes(): void
    {
        $handler = new RecordingReviewHandler(ReviewType::Profile);
        $service = $this->service([$handler]);
        $request = $service->submit($this->profile(), $this->patient());

        $done = $service->approve($request->id, $this->staff(), 'looks right');

        $this->assertSame(ReviewStatus::Completed, $done->status);
        $this->assertSame(3, $done->reviewedBy);
        $this->assertSame([$request->id], $handler->applied);
        $this->assertSame(
            [[null, 'pending'], ['pending', 'approved'], ['approved', 'completed']],
            array_map(static fn($e): array => [$e->from?->value, $e->to->value], $service->history($request->id))
        );
        $this->assertSame(0, $service->countPending());
    }

    public function testApproveRollsBackWhenTheHandlerFails(): void
    {
        $service = $this->service([new RecordingReviewHandler(ReviewType::Profile, new \RuntimeException('chart write failed'))]);
        $request = $service->submit($this->profile(), $this->patient());

        try {
            $service->approve($request->id, $this->staff());
            $this->fail('expected the handler failure to propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('chart write failed', $e->getMessage());
        }

        $this->assertSame(ReviewStatus::Pending, $service->get($request->id)->status);
        $this->assertCount(1, $service->history($request->id), 'no approval event survives the rollback');
        $this->assertCount(1, $this->trail->entries, 'the audit log must not record an approval that was rolled back');
    }

    public function testApproveNeedsAHandlerForTheType(): void
    {
        $service = $this->service([new RecordingReviewHandler(ReviewType::Payment)]);
        $request = $service->submit($this->profile(), $this->patient());

        $this->expectException(ReviewHandlerMissingException::class);
        $service->approve($request->id, $this->staff());
    }

    public function testDenyRecordsTheReviewerAndNote(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());

        $denied = $service->deny($request->id, $this->staff(), 'wrong patient');

        $this->assertSame(ReviewStatus::Denied, $denied->status);
        $this->assertSame(3, $denied->reviewedBy);
        $this->assertSame('wrong patient', $denied->reviewNote);
        $this->assertNotNull($denied->reviewedAt);
    }

    public function testCompleteClosesInOneStepForLegacyCallers(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());

        $done = $service->complete($request->id, $this->staff(), 'accept');

        $this->assertSame(ReviewStatus::Completed, $done->status);
        $this->assertSame('accept', $service->history($request->id)[1]->note);
    }

    public function testAPatientCancellingDoesNotCountAsAReview(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());

        $cancelled = $service->cancel($request->id, $this->patient());

        $this->assertSame(ReviewStatus::Cancelled, $cancelled->status);
        $this->assertNull($cancelled->reviewedBy);
        $this->assertNull($cancelled->reviewedAt);
    }

    public function testAClosedRequestCannotChangeAgain(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());
        $service->deny($request->id, $this->staff());

        $this->expectException(InvalidReviewTransitionException::class);
        $service->complete($request->id, $this->staff());
    }

    public function testTwoReviewersCannotBothDecide(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());
        $this->store->loseNextUpdate = true;

        try {
            $service->deny($request->id, $this->staff());
            $this->fail('expected the losing reviewer to be refused');
        } catch (InvalidReviewTransitionException $e) {
            $this->assertStringContainsString('no longer pending', $e->getMessage());
        }
        $this->assertCount(1, $service->history($request->id), 'the losing change leaves no event');
        $this->assertCount(1, $this->trail->entries, 'the losing change is not audited');
    }

    public function testUnknownRequest(): void
    {
        $this->assertNull($this->service()->find(999));
        $this->expectException(ReviewRequestNotFoundException::class);
        $this->service()->deny(999, $this->staff());
    }

    public function testSecretIsEncryptedReadableWhileOpenAndDeletedOnClose(): void
    {
        $service = $this->service();
        $payment = $service->submit(new ReviewSubmission(self::PID, ReviewType::Payment, 'Authorize online payment.', ['amount' => '10.00']), $this->patient());

        $service->attachSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD, '4111111111111111');

        $this->assertSame('enc(4111111111111111)', $this->store->getSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertSame('4111111111111111', $service->readSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
        $this->assertStringNotContainsString('4111', json_encode($service->get($payment->id)->payload, JSON_THROW_ON_ERROR));

        $service->complete($payment->id, $this->staff(), 'payment posted');

        $this->assertSame(0, $this->store->secretCount());
        $this->assertNull($service->readSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
    }

    public function testSupersededPaymentLosesItsSecret(): void
    {
        $service = $this->service();
        $submission = new ReviewSubmission(self::PID, ReviewType::Payment, 'Authorize online payment.', ['amount' => '10.00']);
        $first = $service->replacePending($submission, $this->patient());
        $service->attachSecret($first->id, ReviewQueueService::SECRET_PAYMENT_CARD, '4111111111111111');

        $service->replacePending($submission, $this->patient());

        $this->assertSame(0, $this->store->secretCount());
    }

    public function testAClosedRequestCannotTakeASecret(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());
        $service->cancel($request->id, $this->patient());

        $this->expectException(\DomainException::class);
        $service->attachSecret($request->id, ReviewQueueService::SECRET_PAYMENT_CARD, 'x');
    }

    public function testFindPendingIsOldestFirstAndFiltersByType(): void
    {
        $service = $this->service();
        $a = $service->submit($this->profile(), $this->patient());
        $b = $service->submit(new ReviewSubmission(self::PID, ReviewType::Payment, 'Authorize online payment.', []), $this->patient());
        $c = $service->submit(new ReviewSubmission(8, ReviewType::Profile, 'Other patient', []), ReviewActor::patient(8, 'bob8'));

        $this->assertSame([$a->id, $b->id, $c->id], array_map(static fn(ReviewRequest $r): int => $r->id, $service->findPending()));
        $this->assertSame([$a->id, $c->id], array_map(static fn(ReviewRequest $r): int => $r->id, $service->findPending(ReviewType::Profile)));
        $this->assertSame(1, $service->countPending(ReviewType::Payment));
    }

    public function testEveryStateChangeIsAudited(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());
        $service->deny($request->id, $this->staff(), 'wrong patient');

        $this->assertCount(2, $this->trail->entries);
        $this->assertSame(
            ['id' => $request->id, 'from' => ReviewStatus::Pending, 'to' => ReviewStatus::Denied, 'actor' => 'drsmith', 'note' => 'wrong patient'],
            $this->trail->entries[1]
        );
    }

    /**
     * The audit log is written after the change commits. If it cannot be written the change still
     * happened, so the caller must not be told it failed: a retry would submit a duplicate.
     */
    public function testAnAuditLogFailureDoesNotFailTheCommittedChange(): void
    {
        $this->trail->failure = new \RuntimeException('audit database unavailable');
        $this->logger->expects($this->once())->method('error')->with(
            $this->stringContains('audit log entry could not be written'),
            $this->callback(static fn(array $context): bool => ($context['to'] ?? null) === 'pending' && ($context['exception'] ?? null) instanceof \RuntimeException)
        );

        $request = $this->service()->submit($this->profile(), $this->patient());

        $this->assertSame(ReviewStatus::Pending, $request->status);
        $this->assertCount(1, $this->service()->history($request->id), 'the change is still on record in its history');
        $this->assertSame(1, $this->service()->countPending());
    }

    public function testAttachSecretToAnUnknownRequest(): void
    {
        $this->expectException(ReviewRequestNotFoundException::class);
        $this->service()->attachSecret(999, ReviewQueueService::SECRET_PAYMENT_CARD, 'x');
    }

    /**
     * @return array<string, array{array<array-key, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function cardBearingPayloadProvider(): array
    {
        return [
            'card number' => [['amount' => '10.00', 'account' => '4111111111111111']],
            'formatted card number' => [['note' => '4111 1111 1111 1111']],
            'nested card number' => [['payment' => ['details' => ['5555-5555-5555-4444']]]],
            'integer card number' => [['n' => 4111111111111111]],
            'card field' => [['card_number' => 'tok_abc']],
            'security code field' => [['amount' => '10.00', 'cvv' => '123']],
            'cc field' => [['cc' => ['anything']]],
        ];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('cardBearingPayloadProvider')]
    public function testAPaymentPayloadCarryingCardDataIsRefused(array $payload): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('attached as a secret');
        new ReviewSubmission(self::PID, ReviewType::Payment, 'Authorize online payment.', $payload);
    }

    public function testOrdinaryPaymentAndNonPaymentPayloadsAreAccepted(): void
    {
        $payment = new ReviewSubmission(self::PID, ReviewType::Payment, 'Authorize online payment.', [
            'amount' => '125.00',
            'invoices' => [1024, 1025],
            'card_type' => 'visa',
            'last4' => '1111',
            'cvv' => '',
            'reference' => '1234567890123456',
        ]);
        // A long number in a profile change (an insurance policy number, say) is not card data.
        $profile = new ReviewSubmission(self::PID, ReviewType::Profile, 'Patient request changes to demographics.', [
            'policy_number' => '4111111111111111',
        ]);

        $this->assertSame('1111', $payment->payload['last4']);
        $this->assertSame('4111111111111111', $profile->payload['policy_number']);
    }

    public function testSubmissionAndActorGuards(): void
    {
        $guards = [
            static fn(): ReviewSubmission => new ReviewSubmission(0, ReviewType::Profile, '', []),
            static fn(): ReviewSubmission => new ReviewSubmission(self::PID, ReviewType::Profile, '', [], ReviewSource::Api),
            static fn(): ReviewSubmission => new ReviewSubmission(self::PID, ReviewType::Profile, '', [], ReviewSource::Portal, 'client-abc'),
            static fn(): ReviewActor => ReviewActor::patient(0, 'x'),
            static fn(): ReviewActor => ReviewActor::user(-1, 'x'),
        ];
        foreach ($guards as $index => $guard) {
            try {
                $guard();
                $this->fail('guard ' . $index . ' should have thrown');
            } catch (\DomainException) {
                $this->addToAssertionCount(1);
            }
        }
        $this->assertSame(ReviewActorType::System, ReviewActor::system()->type);
        $this->assertNull(ReviewActor::system()->id);
    }
}

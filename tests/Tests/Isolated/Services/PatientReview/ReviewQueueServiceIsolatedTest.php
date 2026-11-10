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
use OpenEMR\Services\PatientReview\Exception\ReviewLimitReachedException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestChangedException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestNotFoundException;
use OpenEMR\Services\PatientReview\FhirResourceType;
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
            new ReviewSubmission(self::PID, ReviewType::Document, 'Uploaded document', ['name' => 'a.pdf'], ReviewSource::Api, 'client-abc', 'onsite_documents', '41'),
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

    private function fhirWrite(string $resourceType): ReviewSubmission
    {
        return new ReviewSubmission(
            self::PID,
            ReviewType::FhirWrite,
            'Patient app ' . $resourceType . ' write',
            ['resourceType' => $resourceType],
            ReviewSource::Api,
            'client-abc',
            resourceType: new FhirResourceType($resourceType),
        );
    }

    public function testAFhirWriteRecordsItsResourceType(): void
    {
        $request = $this->service()->submit($this->fhirWrite('Condition'), $this->patient());

        $this->assertSame(ReviewType::FhirWrite, $request->type);
        $this->assertSame('Condition', $request->resourceType?->value);
        $this->assertSame(ReviewStatus::Pending, $request->status);
    }

    public function testSubmitWithinLimitKeepsEveryEarlierRequestOpen(): void
    {
        $service = $this->service();
        $first = $service->submitWithinLimit($this->fhirWrite('Observation'), $this->patient(), 2);
        $second = $service->submitWithinLimit($this->fhirWrite('Observation'), $this->patient(), 2);

        $this->assertSame(ReviewStatus::Pending, $service->get($first->id)->status, 'a second reading must not cancel the first');
        $this->assertSame(ReviewStatus::Pending, $second->status);
        $this->assertSame(['patient_data|' . self::PID, 'patient_data|' . self::PID], $this->store->locks);
    }

    /**
     * At the limit nothing is added or audited, whatever the resource type.
     */
    public function testSubmitWithinLimitRefusesAtTheLimitAndAddsNothing(): void
    {
        $service = $this->service();
        $service->submitWithinLimit($this->fhirWrite('Observation'), $this->patient(), 2);
        $service->submitWithinLimit($this->fhirWrite('Condition'), $this->patient(), 2);
        $auditedBefore = count($this->trail->entries);

        try {
            $service->submitWithinLimit($this->fhirWrite('Observation'), $this->patient(), 2);
            $this->fail('A third open request should be refused');
        } catch (ReviewLimitReachedException) {
            // expected: the limit counts every resource type the client wrote
        }

        $this->assertSame(2, $service->countPending());
        $this->assertCount($auditedBefore, $this->trail->entries);
    }

    public function testSubmitWithinLimitCountsOnlyThisClientsOpenRequests(): void
    {
        $service = $this->service();
        $first = $service->submitWithinLimit($this->fhirWrite('Observation'), $this->patient(), 1);
        $other = new ReviewSubmission(
            self::PID,
            ReviewType::FhirWrite,
            'Patient app Observation write',
            ['resourceType' => 'Observation'],
            ReviewSource::Api,
            'client-other',
            resourceType: new FhirResourceType('Observation'),
        );
        $service->submitWithinLimit($other, $this->patient(), 1);

        $service->deny($first->id, $this->staff(), 'not a vital sign');
        $again = $service->submitWithinLimit($this->fhirWrite('Observation'), $this->patient(), 1);

        $this->assertSame(ReviewStatus::Pending, $again->status, 'a decided request no longer counts');
    }

    /**
     * A portal submission or a limit below 1 is a programming error.
     */
    public function testSubmitWithinLimitNeedsAClientAndALimitOfAtLeastOne(): void
    {
        $service = $this->service();
        try {
            $service->submitWithinLimit($this->profile(), $this->patient(), 5);
            $this->fail('A portal submission has no client to limit');
        } catch (\DomainException) {
            // expected
        }

        $this->expectException(\DomainException::class);
        $service->submitWithinLimit($this->fhirWrite('Observation'), $this->patient(), 0);
    }

    public function testReplacePendingSupersedesFhirWritesOnlyOfTheSameResourceType(): void
    {
        $service = $this->service();
        $allergy = $service->replacePending($this->fhirWrite('AllergyIntolerance'), $this->patient());
        $firstCondition = $service->replacePending($this->fhirWrite('Condition'), $this->patient());
        $secondCondition = $service->replacePending($this->fhirWrite('Condition'), $this->patient());

        $this->assertSame(ReviewStatus::Pending, $service->get($allergy->id)->status);
        $this->assertSame(ReviewStatus::Cancelled, $service->get($firstCondition->id)->status);
        $this->assertSame(
            $secondCondition->id,
            $service->openFor(self::PID, ReviewType::FhirWrite, new FhirResourceType('Condition'))?->id
        );
        $this->assertSame(
            $allergy->id,
            $service->openFor(self::PID, ReviewType::FhirWrite, new FhirResourceType('AllergyIntolerance'))?->id
        );
        $this->assertNull($service->openFor(self::PID, ReviewType::FhirWrite));
        $this->assertSame(2, $service->countPending(ReviewType::FhirWrite));
    }

    private function document(string $documentId): ReviewSubmission
    {
        return new ReviewSubmission(self::PID, ReviewType::Document, 'Consent form', [], targetTable: 'onsite_documents', targetId: $documentId);
    }

    public function testDocumentsAreSupersededPerDocumentNotPerPatient(): void
    {
        $service = $this->service();
        $consent = $service->replacePending($this->document('11'), $this->patient());
        $history = $service->replacePending($this->document('12'), $this->patient());
        $consentAgain = $service->replacePending($this->document('11'), $this->patient());

        $this->assertSame(ReviewStatus::Cancelled, $service->get($consent->id)->status);
        $this->assertSame(ReviewStatus::Pending, $service->get($history->id)->status);
        $this->assertSame($consentAgain->id, $service->openForTarget(self::PID, ReviewType::Document, 'onsite_documents', '11')?->id);
        $this->assertSame($history->id, $service->openForTarget(self::PID, ReviewType::Document, 'onsite_documents', '12')?->id);
        $this->assertSame(2, $service->countPending(ReviewType::Document));
    }

    public function testSubmitUnlessOpenLocksTheTargetAndKeepsOneOpenRequest(): void
    {
        $service = $this->service();
        $first = $service->submitUnlessOpen($this->document('11'), $this->patient());
        $again = $service->submitUnlessOpen($this->document('11'), $this->patient());
        $other = $service->submitUnlessOpen($this->document('12'), $this->patient());

        $this->assertSame($first->id, $again->id);
        $this->assertNotSame($first->id, $other->id);
        $this->assertSame(['onsite_documents|11', 'onsite_documents|11', 'onsite_documents|12'], $this->store->locks);
        $this->assertSame(2, $service->countPending(ReviewType::Document));
        $this->assertCount(1, $service->history($first->id), 'the second submission wrote no event');
    }

    public function testSubmitUnlessOpenNeedsATargetKeyedType(): void
    {
        $this->expectException(\DomainException::class);
        $this->service()->submitUnlessOpen($this->profile(), $this->patient());
    }

    public function testLatestForTargetIncludesClosedRequests(): void
    {
        $service = $this->service();
        $before = $service->latestForTarget(self::PID, ReviewType::Document, 'onsite_documents', '11');
        $this->assertNull($before);

        $request = $service->submit($this->document('11'), $this->patient());
        $service->complete($request->id, $this->staff());

        $open = $service->openForTarget(self::PID, ReviewType::Document, 'onsite_documents', '11');
        $latest = $service->latestForTarget(self::PID, ReviewType::Document, 'onsite_documents', '11');
        $this->assertNull($open);
        $this->assertSame(ReviewStatus::Completed, $latest?->status);
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

    public function testAPaymentHandlerCanReadTheCardWhileApplyingAndItIsDeletedAfter(): void
    {
        $handler = new class implements ReviewHandlerInterface {
            public ?ReviewQueueService $service = null;
            public ?string $cardSeen = null;
            public ?ReviewStatus $statusSeen = null;

            public function type(): ReviewType
            {
                return ReviewType::Payment;
            }

            public function apply(ReviewRequest $request, ReviewActor $reviewer): void
            {
                $this->statusSeen = $request->status;
                $this->cardSeen = $this->service?->readSecret($request->id, ReviewQueueService::SECRET_PAYMENT_CARD);
            }
        };
        $service = $this->service([$handler]);
        $handler->service = $service;
        $payment = $service->submit(new ReviewSubmission(self::PID, ReviewType::Payment, 'Authorize online payment.', ['amount' => '10.00']), $this->patient());
        $service->attachSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD, '4111111111111111');

        $completed = $service->approve($payment->id, $this->staff());

        $this->assertSame(ReviewStatus::Approved, $handler->statusSeen);
        $this->assertSame('4111111111111111', $handler->cardSeen, 'the handler can charge the card');
        $this->assertSame(ReviewStatus::Completed, $completed->status);
        $this->assertSame(0, $this->store->secretCount(), 'the card is gone once the payment is completed');
        $this->assertNull($service->readSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
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

    public function testCancelAlongsideRunsTheSideEffectWithTheCancel(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());
        $seen = null;

        $cancelled = $service->cancelAlongside($request->id, $this->staff(), 'removed', function (ReviewRequest $r) use (&$seen): void {
            $seen = $r;
        });

        $this->assertSame(ReviewStatus::Cancelled, $cancelled->status);
        $this->assertInstanceOf(ReviewRequest::class, $seen);
        $this->assertSame(ReviewStatus::Cancelled, $seen->status, 'the side effect sees the request already cancelled');
    }

    public function testCancelAlongsideLeavesTheRequestOpenWhenTheSideEffectFails(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());

        try {
            $service->cancelAlongside($request->id, $this->staff(), 'removed', static function (): void {
                throw new \RuntimeException('delete failed');
            });
            $this->fail('The side effect failure should propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('delete failed', $e->getMessage());
        }

        $this->assertSame(ReviewStatus::Pending, $service->get($request->id)->status);
        $this->assertCount(1, $service->history($request->id), 'no cancel event is kept');
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

    /**
     * A revision keeps the request's id and uuid, and its history keeps what it replaced.
     */
    public function testReviseReplacesThePayloadAndKeepsThePreviousOneInTheHistory(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());

        $revised = $service->revise($request->id, $this->patient(), 'Patient corrected the demographics change.', ['fname' => 'Anne']);

        $this->assertSame($request->id, $revised->id);
        $this->assertSame($request->uuid, $revised->uuid);
        $this->assertSame(ReviewStatus::Pending, $revised->status);
        $this->assertSame(['fname' => 'Anne'], $revised->payload);
        $this->assertSame('Patient corrected the demographics change.', $revised->summary);

        $history = $service->history($request->id);
        $this->assertCount(2, $history);
        $this->assertNull($history[0]->previousPayload, 'a status change keeps no payload');
        $this->assertSame(ReviewStatus::Pending, $history[1]->from);
        $this->assertSame(ReviewStatus::Pending, $history[1]->to);
        $this->assertSame(ReviewQueueService::NOTE_REVISED, $history[1]->note);
        $this->assertSame(['fname' => 'Ann'], $history[1]->previousPayload);
        $this->assertSame(
            ['id' => $request->id, 'from' => ReviewStatus::Pending, 'to' => ReviewStatus::Pending, 'actor' => 'ann7', 'note' => ReviewQueueService::NOTE_REVISED],
            $this->trail->entries[1]
        );
    }

    /**
     * A decided request can't be revised, and an unknown id is not found; neither changes anything.
     */
    public function testOnlyAPendingRequestCanBeRevised(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());
        $service->deny($request->id, $this->staff(), 'no');

        try {
            $service->revise($request->id, $this->patient(), 'late', ['fname' => 'Anne']);
            $this->fail('A denied request cannot be revised');
        } catch (InvalidReviewTransitionException) {
            $this->assertSame(['fname' => 'Ann'], $service->get($request->id)->payload);
            $this->assertCount(2, $service->history($request->id));
        }

        $this->expectException(ReviewRequestNotFoundException::class);
        $service->revise(999, $this->patient(), 'none', []);
    }

    /**
     * The revision names the content: it changes when the payload is replaced, not otherwise.
     */
    public function testTheRevisionChangesOnlyWithThePayload(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());

        $this->assertSame($request->revision(), $service->get($request->id)->revision());
        $revised = $service->revise($request->id, $this->patient(), 'corrected', ['fname' => 'Anne']);
        $this->assertNotSame($request->revision(), $revised->revision());
        $back = $service->revise($request->id, $this->patient(), 'corrected back', ['fname' => 'Ann']);
        $this->assertSame($request->revision(), $back->revision(), 'the same content is the same revision');
    }

    /**
     * Staff decide the version they were shown: a request replaced since is neither approved
     * nor denied, and stays pending with the replacement.
     */
    public function testADecisionOnAReplacedVersionIsRefused(): void
    {
        $handler = new RecordingReviewHandler(ReviewType::Profile);
        $service = $this->service([$handler]);
        $request = $service->submit($this->profile(), $this->patient());
        $shown = $request->revision();
        $service->revise($request->id, $this->patient(), 'corrected', ['fname' => 'Anne']);

        foreach (
            [
                'approve' => fn(): ReviewRequest => $service->approve($request->id, $this->staff(), null, $shown),
                'deny' => fn(): ReviewRequest => $service->deny($request->id, $this->staff(), 'no', $shown),
            ] as $decision => $decide
        ) {
            try {
                $decide();
                $this->fail('A ' . $decision . ' of a replaced version should be refused');
            } catch (ReviewRequestChangedException) {
                $now = $service->get($request->id);
                $this->assertSame(ReviewStatus::Pending, $now->status, $decision);
                $this->assertSame(['fname' => 'Anne'], $now->payload, $decision);
            }
        }
        $this->assertSame([], $handler->applied, 'nothing was applied');
        $this->assertCount(2, $service->history($request->id), 'submitted and revised only');

        $approved = $service->approve($request->id, $this->staff(), null, $service->get($request->id)->revision());
        $this->assertSame(ReviewStatus::Completed, $approved->status);
        $this->assertSame([$request->id], $handler->applied);
    }

    /**
     * A request already decided is refused as decided, whatever revision the reviewer saw.
     */
    public function testADecisionOnADecidedRequestIsRefusedAsDecided(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());
        $service->deny($request->id, $this->staff(), 'no', $request->revision());

        $this->expectException(InvalidReviewTransitionException::class);
        $service->deny($request->id, $this->staff(), 'again', 'a revision from an older page');
    }

    /**
     * An id from a URL can be any text; only a uuid that the queue issued finds a request.
     */
    public function testFindByUuidFindsOnlyAnIssuedUuid(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());

        $this->assertSame($request->id, $service->findByUuid($request->uuid)?->id);
        $this->assertNull($service->findByUuid('00000000-0000-4000-8000-000000000000'));
        $this->assertNull($service->findByUuid('not-a-uuid'));
        $this->assertNull($service->findByUuid(''));
    }

    public function testReadsAreAuditedOnceForTheRequestsShown(): void
    {
        $service = $this->service();
        $first = $service->submit($this->profile(), $this->patient());
        $second = $service->submit($this->profile(['fname' => 'Bo']), $this->patient());

        $service->recordAccess([$first, $second], $this->patient());
        $service->recordAccess([], $this->patient());

        $this->assertSame([['ids' => [$first->id, $second->id], 'actor' => 'ann7']], $this->trail->reads);
    }

    /**
     * A failure to write the read's audit entry is logged with the request ids, not thrown.
     */
    public function testAReadAuditFailureDoesNotFailTheRead(): void
    {
        $service = $this->service();
        $request = $service->submit($this->profile(), $this->patient());
        $this->trail->failure = new \RuntimeException('audit database unavailable');
        $this->logger->expects($this->once())->method('error')->with(
            $this->stringContains('read but the audit log entry could not be written'),
            $this->callback(static fn(array $context): bool => ($context['request_ids'] ?? null) === [$request->id])
        );

        $service->recordAccess([$request], $this->patient());
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
            static fn(): ReviewSubmission => new ReviewSubmission(self::PID, ReviewType::FhirWrite, '', [], ReviewSource::Api, 'client-abc'),
            static fn(): ReviewSubmission => new ReviewSubmission(self::PID, ReviewType::Document, '', []),
            static fn(): ReviewSubmission => new ReviewSubmission(self::PID, ReviewType::Document, '', [], targetTable: 'onsite_documents', targetId: ''),
            static fn(): ReviewSubmission => new ReviewSubmission(self::PID, ReviewType::Profile, '', [], resourceType: new FhirResourceType('Patient')),
            static fn(): FhirResourceType => new FhirResourceType('condition'),
            static fn(): FhirResourceType => new FhirResourceType(''),
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

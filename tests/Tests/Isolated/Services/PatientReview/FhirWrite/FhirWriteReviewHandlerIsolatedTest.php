<?php

/**
 * Isolated tests for the fhir-write review handler's dispatch to per-type appliers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview\FhirWrite;

use DateTimeImmutable;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\PatientReview\FhirResourceType;
use OpenEMR\Services\PatientReview\FhirWrite\FhirWriteReviewHandler;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteApplierInterface;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;
use OpenEMR\Tests\Isolated\Services\PatientReview\InMemoryReviewRequestStore;
use OpenEMR\Tests\Isolated\Services\PatientReview\RecordingReviewAuditTrail;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidFactory;

class FhirWriteReviewHandlerIsolatedTest extends TestCase
{
    private const PID = 7;

    /** @var \ArrayObject<int, string> uuid of each request an applier wrote */
    private \ArrayObject $applied;

    protected function setUp(): void
    {
        $this->applied = new \ArrayObject();
    }

    /**
     * An applier that records the uuid it wrote, or throws when told to fail; it reports every
     * resource writable unless told otherwise.
     */
    private function applier(PatientWritableType $type, bool $fails = false, bool $writable = true): PatientWriteApplierInterface
    {
        return new class ($type, $fails, $this->applied, $writable) implements PatientWriteApplierInterface {
            /**
             * @param \ArrayObject<int, string> $applied
             */
            public function __construct(
                private readonly PatientWritableType $type,
                private readonly bool $fails,
                private readonly \ArrayObject $applied,
                private readonly bool $writable,
            ) {
            }

            public function canWrite(array $resource): bool
            {
                return $this->writable;
            }

            public function type(): PatientWritableType
            {
                return $this->type;
            }

            /**
             * Records the request, or throws to stand in for a failed chart write.
             */
            public function apply(ReviewRequest $request): void
            {
                if ($this->fails) {
                    throw new \RuntimeException('could not write');
                }
                $this->applied[] = $request->uuid;
            }
        };
    }

    private function queue(FhirWriteReviewHandler $handler): ReviewQueueService
    {
        $clock = new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable('2026-11-07 09:30:00');
            }
        };

        return new ReviewQueueService(
            new InMemoryReviewRequestStore(),
            new RecordingReviewAuditTrail(),
            $clock,
            new UuidFactory(),
            $this->createStub(CryptoInterface::class),
            new NullLogger(),
            [$handler],
        );
    }

    private function held(ReviewQueueService $queue, string $resourceType): ReviewRequest
    {
        return $queue->submit(
            new ReviewSubmission(
                self::PID,
                ReviewType::FhirWrite,
                'Patient app write',
                ['resourceType' => $resourceType],
                ReviewSource::Api,
                'client-abc',
                resourceType: new FhirResourceType($resourceType),
            ),
            ReviewActor::patient(self::PID, 'ann7')
        );
    }

    public function testApprovalRunsTheApplierForTheResourceType(): void
    {
        $handler = new FhirWriteReviewHandler([
            $this->applier(PatientWritableType::QuestionnaireResponse),
            $this->applier(PatientWritableType::VitalSignObservation),
        ]);
        $queue = $this->queue($handler);
        $vital = $this->held($queue, 'Observation');

        $this->assertTrue($handler->canApply($vital));
        $done = $queue->approve($vital->id, ReviewActor::user(3, 'drsmith'));

        $this->assertSame(ReviewStatus::Completed, $done->status);
        $this->assertSame([$vital->uuid], $this->applied->getArrayCopy(), 'written under the id the app was given');
    }

    /**
     * A type without an applier is refused and nothing is written.
     */
    public function testATypeWithNoApplierCannotBeApprovedAndStaysPending(): void
    {
        $handler = new FhirWriteReviewHandler([$this->applier(PatientWritableType::QuestionnaireResponse)]);
        $queue = $this->queue($handler);
        $condition = $this->held($queue, 'Condition');
        $notWritable = $this->held($queue, 'MedicationRequest');

        $this->assertFalse($handler->canApply($condition));
        $this->assertFalse($handler->canApply($notWritable));
        try {
            $queue->approve($condition->id, ReviewActor::user(3, 'drsmith'));
            $this->fail('A Condition has no applier');
        } catch (\LogicException) {
            // expected
        }

        $this->assertSame(ReviewStatus::Pending, $queue->get($condition->id)->status);
        $this->assertSame([], $this->applied->getArrayCopy());
    }

    /**
     * An applier that throws rolls the approval back.
     */
    public function testAFailedWriteLeavesTheRequestPending(): void
    {
        $handler = new FhirWriteReviewHandler([$this->applier(PatientWritableType::QuestionnaireResponse, fails: true)]);
        $queue = $this->queue($handler);
        $response = $this->held($queue, 'QuestionnaireResponse');

        try {
            $queue->approve($response->id, ReviewActor::user(3, 'drsmith'));
            $this->fail('The applier failed');
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertSame(ReviewStatus::Pending, $queue->get($response->id)->status);
        $this->assertCount(1, $queue->history($response->id), 'no approval is recorded');
    }

    /**
     * Approve is offered only when what is held could be written; a type with no applier yet
     * can still be held, for staff to deny.
     */
    public function testApproveIsOfferedOnlyWhenTheApplierCouldWriteIt(): void
    {
        $handler = new FhirWriteReviewHandler([$this->applier(PatientWritableType::QuestionnaireResponse, writable: false)]);
        $response = $this->held($this->queue($handler), 'QuestionnaireResponse');

        $this->assertFalse($handler->canApply($response));
        $this->assertFalse($handler->couldWrite(PatientWritableType::QuestionnaireResponse, $response->payload));
        $this->assertTrue($handler->couldWrite(PatientWritableType::Condition, ['resourceType' => 'Condition']));
    }

    public function testOnlyFhirWriteRequestsHaveAnApplier(): void
    {
        $handler = new FhirWriteReviewHandler([$this->applier(PatientWritableType::QuestionnaireResponse)]);
        $queue = $this->queue($handler);
        $profile = $queue->submit(
            new ReviewSubmission(self::PID, ReviewType::Profile, 'Profile change', ['fname' => 'Ann']),
            ReviewActor::patient(self::PID, 'ann7')
        );

        $this->assertSame(ReviewType::FhirWrite, $handler->type());
        $this->assertFalse($handler->canApply($profile));
    }
}

<?php

/**
 * Database tests for the review queue: the repository's SQL, and ReviewQueueService wired the
 * way production wires it (ReviewQueueFactory).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\PatientReview;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Services\PatientReview\EventAuditReviewTrail;
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewActorType;
use OpenEMR\Services\PatientReview\ReviewHandlerInterface;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewRequestRepository;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\TestCase;

class ReviewQueueDatabaseTest extends TestCase
{
    /** Patient ids no fixture uses, so the rows these tests create are easy to find and remove. */
    private const PID = 990071;
    private const OTHER_PID = 990072;

    protected function setUp(): void
    {
        $this->removeTestRows();
    }

    protected function tearDown(): void
    {
        $this->removeTestRows();
    }

    private function removeTestRows(): void
    {
        $pids = [self::PID, self::OTHER_PID];
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `patient_review_secret` WHERE `request_id` IN (SELECT `id` FROM `patient_review_request` WHERE `pid` IN (?, ?))',
            $pids
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `patient_review_event` WHERE `request_id` IN (SELECT `id` FROM `patient_review_request` WHERE `pid` IN (?, ?))',
            $pids
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `patient_review_request` WHERE `pid` IN (?, ?)', $pids);
    }

    private function patient(): ReviewActor
    {
        return ReviewActor::patient(self::PID, 'review-test-patient');
    }

    private function staff(): ReviewActor
    {
        return ReviewActor::user(1, 'admin');
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private function profile(array $payload = ['fname' => 'Ann', 'street' => "12 O'Neil Rd"]): ReviewSubmission
    {
        return new ReviewSubmission(
            self::PID,
            ReviewType::Profile,
            'Patient request changes to demographics.',
            $payload,
            targetTable: 'patient_data',
            targetId: (string) self::PID,
        );
    }

    private function countRows(string $table, int $requestId): int
    {
        $row = QueryUtils::querySingleRow(
            'SELECT COUNT(*) AS `total` FROM ' . QueryUtils::escapeTableName($table) . ' WHERE `request_id` = ?',
            [$requestId]
        );
        $this->assertIsArray($row);
        $this->assertIsNumeric($row['total']);
        return (int) $row['total'];
    }

    /**
     * Ids of the requests these tests created, in the order given.
     *
     * @param list<ReviewRequest> $requests
     * @return list<int>
     */
    private function ownIds(array $requests): array
    {
        $ids = [];
        foreach ($requests as $request) {
            if (in_array($request->pid, [self::PID, self::OTHER_PID], true)) {
                $ids[] = $request->id;
            }
        }
        return $ids;
    }

    public function testSubmitStoresAndReadsBackARequest(): void
    {
        $service = ReviewQueueFactory::create();
        $submitted = $service->submit($this->profile(), $this->patient());

        $found = $service->get($submitted->id);
        $this->assertSame(ReviewStatus::Pending, $found->status);
        $this->assertSame(self::PID, $found->pid);
        $this->assertSame(ReviewType::Profile, $found->type);
        $this->assertSame(ReviewSource::Portal, $found->source);
        $this->assertNull($found->clientId);
        $this->assertSame(['fname' => 'Ann', 'street' => "12 O'Neil Rd"], $found->payload);
        $this->assertSame('patient_data', $found->targetTable);
        $this->assertSame((string) self::PID, $found->targetId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $found->uuid);
        $this->assertNull($found->reviewedBy);

        $history = $service->history($submitted->id);
        $this->assertCount(1, $history);
        $this->assertNull($history[0]->from);
        $this->assertSame(ReviewActorType::Patient, $history[0]->actorType);
        $this->assertSame(self::PID, $history[0]->actorId);
    }

    public function testApiSubmissionStoresItsClient(): void
    {
        $service = ReviewQueueFactory::create();
        $submitted = $service->submit(
            new ReviewSubmission(self::PID, ReviewType::Document, 'Uploaded document', ['name' => 'a.pdf'], ReviewSource::Api, 'review-test-client'),
            $this->patient()
        );

        $found = $service->get($submitted->id);
        $this->assertSame(ReviewSource::Api, $found->source);
        $this->assertSame('review-test-client', $found->clientId);
    }

    public function testReplacePendingCancelsTheEarlierRequest(): void
    {
        $service = ReviewQueueFactory::create();
        $first = $service->replacePending($this->profile(['fname' => 'Ann']), $this->patient());
        $second = $service->replacePending($this->profile(['fname' => 'Anne']), $this->patient());

        $this->assertSame(ReviewStatus::Cancelled, $service->get($first->id)->status);
        $this->assertSame($second->id, $service->openFor(self::PID, ReviewType::Profile)?->id);
        $this->assertSame(['fname' => 'Ann'], $service->get($first->id)->payload);
    }

    public function testDenyWritesReviewerHistoryAndCountsFall(): void
    {
        $service = ReviewQueueFactory::create();
        $before = $service->countPending(ReviewType::Profile);
        $request = $service->submit($this->profile(), $this->patient());
        $this->assertSame($before + 1, $service->countPending(ReviewType::Profile));

        $denied = $service->deny($request->id, $this->staff(), 'wrong patient');

        $this->assertSame(ReviewStatus::Denied, $denied->status);
        $this->assertSame(1, $denied->reviewedBy);
        $this->assertSame('wrong patient', $denied->reviewNote);
        $this->assertNotNull($denied->reviewedAt);
        $this->assertSame($before, $service->countPending(ReviewType::Profile));
        $this->assertSame(2, $this->countRows('patient_review_event', $request->id));
    }

    public function testUpdateStatusOnlySucceedsFromTheExpectedStatus(): void
    {
        $service = ReviewQueueFactory::create();
        $repository = new ReviewRequestRepository(new ReviewRequestMapper());
        $request = $service->submit($this->profile(), $this->patient());
        $now = new \DateTimeImmutable('2026-10-01 10:00:00');

        $this->assertFalse(
            $repository->updateStatus($request->id, ReviewStatus::Approved, ReviewStatus::Completed, $now, null, null),
            'a request that is pending cannot be moved as if it were approved'
        );
        $this->assertTrue($repository->updateStatus($request->id, ReviewStatus::Pending, ReviewStatus::Cancelled, $now, null, null));
        $this->assertFalse(
            $repository->updateStatus($request->id, ReviewStatus::Pending, ReviewStatus::Denied, $now, 1, 'late'),
            'the second reviewer loses'
        );
        $this->assertSame(ReviewStatus::Cancelled, $service->get($request->id)->status);
    }

    public function testAClosedRequestCannotBeReopenedOrReclosed(): void
    {
        $service = ReviewQueueFactory::create();
        $request = $service->submit($this->profile(), $this->patient());
        $service->complete($request->id, $this->staff(), 'accept');

        $this->expectException(InvalidReviewTransitionException::class);
        $service->deny($request->id, $this->staff());
    }

    public function testApproveRollsBackInTheDatabaseWhenTheHandlerFails(): void
    {
        $handler = new class implements ReviewHandlerInterface {
            public function type(): ReviewType
            {
                return ReviewType::Profile;
            }

            public function apply(ReviewRequest $request, ReviewActor $reviewer): void
            {
                throw new \RuntimeException('chart write failed');
            }
        };
        $service = ReviewQueueFactory::create([$handler]);
        $request = $service->submit($this->profile(), $this->patient());

        try {
            $service->approve($request->id, $this->staff());
            $this->fail('expected the handler failure to propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('chart write failed', $e->getMessage());
        }

        $this->assertSame(ReviewStatus::Pending, $service->get($request->id)->status);
        $this->assertSame(1, $this->countRows('patient_review_event', $request->id));
    }

    public function testSecretIsStoredEncryptedAndDeletedWhenTheRequestCloses(): void
    {
        // A stand-in cipher, so the test does not depend on the site's database_encryption
        // setting: what matters here is that the service stores what the cipher returns, never
        // the plain value, and deletes it on close.
        $crypto = $this->createMock(CryptoInterface::class);
        $crypto->method('encryptForDatabase')->willReturnCallback(static fn(?string $value): string => base64_encode(strrev((string) $value)));
        $crypto->method('decryptFromDatabase')->willReturnCallback(static fn(?string $value): string => strrev((string) base64_decode((string) $value, true)));
        $service = new ReviewQueueService(
            new ReviewRequestRepository(new ReviewRequestMapper()),
            new EventAuditReviewTrail(EventAuditLogger::getInstance(), 'Default'),
            ServiceContainer::getClock(),
            ServiceContainer::getUuidFactory(),
            $crypto,
            ServiceContainer::getLogger(),
        );
        $payment = $service->submit(
            new ReviewSubmission(self::PID, ReviewType::Payment, 'Authorize online payment.', ['amount' => '10.00']),
            $this->patient()
        );
        $service->attachSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD, '4111111111111111');
        // attaching again replaces, it does not add a second row
        $service->attachSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD, '4111111111111111');

        $this->assertSame(1, $this->countRows('patient_review_secret', $payment->id));
        $stored = QueryUtils::querySingleRow('SELECT `ciphertext` FROM `patient_review_secret` WHERE `request_id` = ?', [$payment->id]);
        $this->assertIsArray($stored);
        $this->assertIsString($stored['ciphertext']);
        $this->assertStringNotContainsString('4111111111111111', $stored['ciphertext']);
        $this->assertSame('4111111111111111', $service->readSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));

        $service->complete($payment->id, $this->staff(), 'payment posted');

        $this->assertSame(0, $this->countRows('patient_review_secret', $payment->id));
        $this->assertNull($service->readSecret($payment->id, ReviewQueueService::SECRET_PAYMENT_CARD));
    }

    public function testFindForUpdateReadsTheRequestInsideATransaction(): void
    {
        $service = ReviewQueueFactory::create();
        $repository = new ReviewRequestRepository(new ReviewRequestMapper());
        $request = $service->submit($this->profile(), $this->patient());

        $locked = $repository->transactional(fn(): ?ReviewRequest => $repository->findForUpdate($request->id));

        $this->assertNotNull($locked);
        $this->assertSame($request->id, $locked->id);
        $this->assertSame(ReviewStatus::Pending, $locked->status);
        $this->assertNull($repository->transactional(fn(): ?ReviewRequest => $repository->findForUpdate(0)));
    }

    public function testFindPendingIsOldestFirstAndScopedByType(): void
    {
        $service = ReviewQueueFactory::create();
        $profile = $service->submit($this->profile(), $this->patient());
        $payment = $service->submit(
            new ReviewSubmission(self::OTHER_PID, ReviewType::Payment, 'Authorize online payment.', []),
            ReviewActor::patient(self::OTHER_PID, 'review-test-other')
        );
        $invoice = $service->submit(
            new ReviewSubmission(self::PID, ReviewType::Invoice, 'Request patient online payment.', ['invoices' => [1]]),
            $this->staff()
        );

        $this->assertSame([$profile->id, $payment->id], $this->ownIds($service->findPending(limit: 1000)));
        $this->assertSame([$profile->id], $this->ownIds($service->findPending(ReviewType::Profile, 1000)));
        $this->assertSame(ReviewStatus::AwaitingPayment, $service->get($invoice->id)->status);
        $this->assertSame($invoice->id, $service->openFor(self::PID, ReviewType::Invoice)?->id);
    }
}

<?php

/**
 * The patient review queue: data a patient submits that staff review before it reaches the chart.
 *
 * Replaces the onsite_portal_activity audit workflow. Three rules that workflow did not have:
 *
 *  - A request is never edited in place. A newer submission supersedes the open one, which is
 *    cancelled and kept (replacePending).
 *  - Every state change is checked against ReviewStatus, written as a patient_review_event row
 *    and recorded in the audit log.
 *  - Sensitive data (payment card details) lives apart from the payload, encrypted, and is
 *    deleted as soon as the request is no longer open.
 *
 * The queue is for patient-originated data: the portal today, patient-facing API apps next
 * (ReviewSource::Api with the OAuth2 client id). Writes a signed-in clinician makes are not
 * queued.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\Exception\ReviewHandlerMissingException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestNotFoundException;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidFactoryInterface;

final class ReviewQueueService
{
    /** The patient_review_secret kind for payment card details. */
    public const SECRET_PAYMENT_CARD = 'payment-card';

    /** Note written on a request that a newer submission replaced. */
    public const NOTE_SUPERSEDED = 'superseded by a newer submission';

    /** @var array<string, ReviewHandlerInterface> keyed by ReviewType value */
    private array $handlers = [];

    /**
     * Audit entries for the transaction in progress. The audit log writes on its own database
     * connection, so an entry written mid-transaction would survive a rollback and describe a
     * change that never happened. They are written once the transaction commits.
     *
     * @var list<array{request: ReviewRequest, from: ?ReviewStatus, actor: ReviewActor, note: ?string}>
     */
    private array $pendingAudit = [];

    /**
     * @param iterable<ReviewHandlerInterface> $handlers
     */
    public function __construct(
        private readonly ReviewRequestStoreInterface $store,
        private readonly ReviewAuditTrailInterface $auditTrail,
        private readonly ClockInterface $clock,
        private readonly UuidFactoryInterface $uuidFactory,
        private readonly CryptoInterface $crypto,
        private readonly LoggerInterface $logger,
        iterable $handlers = [],
    ) {
        foreach ($handlers as $handler) {
            $this->handlers[$handler->type()->value] = $handler;
        }
    }

    /**
     * Adds a request to the queue. Any request already open for the patient and type is left
     * alone; use replacePending() for the "one pending item" workflows.
     */
    public function submit(ReviewSubmission $submission, ReviewActor $actor): ReviewRequest
    {
        return $this->atomically(fn(): ReviewRequest => $this->create($submission, $actor));
    }

    /**
     * Adds a request and cancels the ones already open for the same patient and type.
     *
     * This is what the portal means by "pending": a patient who edits their profile twice
     * before staff look has one pending change, the later one. The earlier request is kept as
     * cancelled, so what was submitted and when is not lost.
     */
    public function replacePending(ReviewSubmission $submission, ReviewActor $actor): ReviewRequest
    {
        return $this->atomically(function () use ($submission, $actor): ReviewRequest {
            foreach ($this->store->findOpen($submission->pid, $submission->type) as $open) {
                $this->transition($open, ReviewStatus::Cancelled, $actor, self::NOTE_SUPERSEDED);
            }
            return $this->create($submission, $actor);
        });
    }

    /**
     * Approves a pending request and applies it to the chart through the handler for its type.
     * If the handler throws, nothing changes and the request stays pending.
     *
     * @throws ReviewHandlerMissingException when no handler is registered for the type
     */
    public function approve(int $id, ReviewActor $reviewer, ?string $note = null): ReviewRequest
    {
        return $this->atomically(function () use ($id, $reviewer, $note): ReviewRequest {
            $request = $this->get($id);
            $handler = $this->handlers[$request->type->value]
                ?? throw ReviewHandlerMissingException::forType($request->type);

            $approved = $this->transition($request, ReviewStatus::Approved, $reviewer, $note);
            $handler->apply($approved, $reviewer);

            return $this->transition($approved, ReviewStatus::Completed, $reviewer, null);
        });
    }

    public function deny(int $id, ReviewActor $reviewer, ?string $note = null): ReviewRequest
    {
        return $this->change($id, ReviewStatus::Denied, $reviewer, $note);
    }

    public function cancel(int $id, ReviewActor $actor, ?string $note = null): ReviewRequest
    {
        return $this->change($id, ReviewStatus::Cancelled, $actor, $note);
    }

    /**
     * Closes a request whose change the caller has already applied. The legacy portal workflows
     * apply the change themselves and then close the request in one step; new code should
     * register a handler and call approve().
     */
    public function complete(int $id, ReviewActor $actor, ?string $note = null): ReviewRequest
    {
        return $this->change($id, ReviewStatus::Completed, $actor, $note);
    }

    public function find(int $id): ?ReviewRequest
    {
        return $this->store->find($id);
    }

    /**
     * @throws ReviewRequestNotFoundException
     */
    public function get(int $id): ReviewRequest
    {
        return $this->store->find($id) ?? throw ReviewRequestNotFoundException::forId($id);
    }

    /**
     * The request the patient and staff currently see as open for this type, if any.
     */
    public function openFor(int $pid, ReviewType $type): ?ReviewRequest
    {
        return $this->store->findOpen($pid, $type)[0] ?? null;
    }

    /**
     * Requests waiting for staff, oldest first.
     *
     * @return list<ReviewRequest>
     */
    public function findPending(?ReviewType $type = null, int $limit = 100, int $offset = 0): array
    {
        return $this->store->findByStatus(ReviewStatus::Pending, $type, $limit, $offset);
    }

    /**
     * How many requests are waiting for staff; the number behind the portal alert badge.
     */
    public function countPending(?ReviewType $type = null): int
    {
        return $this->store->countByStatus(ReviewStatus::Pending, $type);
    }

    /**
     * @return list<ReviewEvent>
     */
    public function history(int $id): array
    {
        return $this->store->events($id);
    }

    /**
     * Stores sensitive data for an open request, encrypted the way the site encrypts database
     * values (CryptoInterface::encryptForDatabase(): on by default; a site whose database server
     * is itself encrypted may turn it off). It is deleted when the request leaves the open
     * states.
     *
     * @throws \DomainException when the request is not open
     * @throws ReviewRequestNotFoundException
     */
    public function attachSecret(int $id, string $kind, string $plaintext): void
    {
        // The status check and the write share one transaction and hold the request row, so a
        // concurrent close (which deletes secrets) cannot slip between them and leave a secret
        // on a closed request.
        $this->store->transactional(function () use ($id, $kind, $plaintext): void {
            $request = $this->store->findForUpdate($id) ?? throw ReviewRequestNotFoundException::forId($id);
            if (!$request->status->isOpen()) {
                throw new \DomainException('Review request ' . $id . ' is not open; it cannot hold a secret');
            }
            $this->store->putSecret($id, $kind, $this->crypto->encryptForDatabase($plaintext), $this->clock->now());
        });
    }

    /**
     * The decrypted secret, or null when the request is no longer open or has none.
     */
    public function readSecret(int $id, string $kind): ?string
    {
        $request = $this->store->find($id);
        if ($request === null || !$request->status->isOpen()) {
            return null;
        }
        $ciphertext = $this->store->getSecret($id, $kind);

        return $ciphertext === null ? null : $this->crypto->decryptFromDatabase($ciphertext);
    }

    private function create(ReviewSubmission $submission, ReviewActor $actor): ReviewRequest
    {
        $now = $this->clock->now();
        $status = $submission->type->initialStatus();
        $id = $this->store->insert($submission, $status, $this->uuidFactory->uuid4()->getBytes(), $now);
        $this->store->appendEvent($id, null, $status, $actor, $now, null);

        $request = $this->get($id);
        $this->pendingAudit[] = ['request' => $request, 'from' => null, 'actor' => $actor, 'note' => null];

        return $request;
    }

    /**
     * Runs $action in one database transaction, then writes the audit entries it produced. If
     * it throws, the transaction rolls back and nothing is audited.
     *
     * @param callable(): ReviewRequest $action
     */
    private function atomically(callable $action): ReviewRequest
    {
        $this->drainAudit();
        try {
            $result = $this->store->transactional($action);
        } finally {
            $entries = $this->drainAudit();
        }
        foreach ($entries as $entry) {
            $this->audit($entry['request'], $entry['from'], $entry['actor'], $entry['note']);
        }

        return $result;
    }

    /**
     * Writes one audit log entry for a change that has already committed.
     *
     * A failure here must not surface as a failed operation: the request did change, and a
     * caller told otherwise would retry and submit a duplicate. The change is not lost to the
     * record either, because its patient_review_event row was written in the same transaction
     * as the change. So the failure is logged for the operator and the operation succeeds.
     */
    private function audit(ReviewRequest $request, ?ReviewStatus $from, ReviewActor $actor, ?string $note): void
    {
        try {
            $this->auditTrail->record($request, $from, $actor, $note);
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            $this->logger->error('Review queue change committed but its audit log entry could not be written', [
                'request_id' => $request->id,
                'from' => $from?->value,
                'to' => $request->status->value,
                'exception' => $e,
            ]);
        }
    }

    /**
     * @return list<array{request: ReviewRequest, from: ?ReviewStatus, actor: ReviewActor, note: ?string}>
     */
    private function drainAudit(): array
    {
        $entries = $this->pendingAudit;
        $this->pendingAudit = [];

        return $entries;
    }

    private function change(int $id, ReviewStatus $to, ReviewActor $actor, ?string $note): ReviewRequest
    {
        return $this->atomically(
            fn(): ReviewRequest => $this->transition($this->get($id), $to, $actor, $note)
        );
    }

    /**
     * The one place a request changes state.
     */
    private function transition(ReviewRequest $request, ReviewStatus $to, ReviewActor $actor, ?string $note): ReviewRequest
    {
        $from = $request->status;
        if (!$from->canTransitionTo($to)) {
            throw InvalidReviewTransitionException::notAllowed($request->id, $from, $to);
        }

        $now = $this->clock->now();
        $reviewedBy = $actor->type === ReviewActorType::User ? $actor->id : null;
        if (!$this->store->updateStatus($request->id, $from, $to, $now, $reviewedBy, $note)) {
            throw InvalidReviewTransitionException::changedConcurrently($request->id, $from);
        }
        $this->store->appendEvent($request->id, $from, $to, $actor, $now, $note);
        if (!$to->isOpen()) {
            $this->store->deleteSecrets($request->id);
        }

        $updated = $this->get($request->id);
        $this->pendingAudit[] = ['request' => $updated, 'from' => $from, 'actor' => $actor, 'note' => $note];

        return $updated;
    }
}

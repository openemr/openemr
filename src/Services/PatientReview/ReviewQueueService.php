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
 *    deleted as soon as the request is decided (reaches a terminal status). An approved
 *    request keeps it until its handler has run, so a payment handler can charge the card.
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
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\Exception\ReviewHandlerMissingException;
use OpenEMR\Services\PatientReview\Exception\ReviewLimitReachedException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestChangedException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestNotFoundException;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\UuidFactoryInterface;

final class ReviewQueueService
{
    /** The patient_review_secret kind for payment card details. */
    public const SECRET_PAYMENT_CARD = 'payment-card';

    /** Note written on a request that a newer submission replaced. */
    public const NOTE_SUPERSEDED = 'superseded by a newer submission';

    /** Note written when the submitter replaced what a pending request holds. */
    public const NOTE_REVISED = 'replaced by the submitter';

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
     *
     * @param array<string, string> $secrets plaintext keyed by secret kind (e.g.
     *                                       SECRET_PAYMENT_CARD), stored encrypted with the request
     *                                       in the same transaction
     */
    public function submit(ReviewSubmission $submission, ReviewActor $actor, array $secrets = []): ReviewRequest
    {
        return $this->atomically(fn(): ReviewRequest => $this->create($submission, $actor, $secrets));
    }

    /**
     * Adds a request unless the submitting API client already has $maxOpenForClient open
     * requests of this type for the patient. Like submit(), it leaves the open ones alone: a
     * patient app's second reading is a second request, not a replacement for the first.
     *
     * The patient row is held for the count, so concurrent submissions for the patient take
     * turns and cannot both pass it.
     *
     * @throws \DomainException when the submission has no client, or the limit is below 1
     * @throws \RuntimeException when the patient does not exist (nothing is created)
     * @throws ReviewLimitReachedException when the client is at the limit (nothing is created)
     */
    public function submitWithinLimit(ReviewSubmission $submission, ReviewActor $actor, int $maxOpenForClient): ReviewRequest
    {
        $clientId = $submission->clientId;
        if ($clientId === null) {
            throw new \DomainException('Only a submission from an API client has a per-client limit');
        }
        if ($maxOpenForClient < 1) {
            throw new \DomainException('The open request limit must be at least 1');
        }

        return $this->atomically(function () use ($submission, $actor, $clientId, $maxOpenForClient): ReviewRequest {
            $this->store->lockTarget('patient_data', (string) $submission->pid);
            if ($this->store->countOpenForClient($submission->pid, $submission->type, $clientId) >= $maxOpenForClient) {
                throw ReviewLimitReachedException::forClient($maxOpenForClient);
            }

            return $this->create($submission, $actor, []);
        });
    }

    /**
     * Adds a request for a target row unless one is already open for it, and returns the open
     * one. The target row is locked for the check, so concurrent submissions for the same row
     * end with one open request.
     *
     * @throws \DomainException when the type is not keyed by target
     * @throws \RuntimeException when the target row does not exist (nothing is created)
     */
    public function submitUnlessOpen(ReviewSubmission $submission, ReviewActor $actor): ReviewRequest
    {
        $targetTable = $submission->targetTable;
        $targetId = $submission->targetId;
        if (!$submission->type->isKeyedByTarget() || $targetTable === null || $targetId === null) {
            throw new \DomainException('Only a request keyed by its target row can be submitted once per target');
        }

        return $this->atomically(function () use ($submission, $actor, $targetTable, $targetId): ReviewRequest {
            $this->store->lockTarget($targetTable, $targetId);

            return $this->store->findOpenForTarget($submission->pid, $submission->type, $targetTable, $targetId)[0]
                ?? $this->create($submission, $actor, []);
        });
    }

    /**
     * Adds a request and cancels the ones already open for the same patient and type.
     *
     * This is what the portal means by "pending": a patient who edits their profile twice
     * before staff look has one pending change, the later one. The earlier request is kept as
     * cancelled, so what was submitted and when is not lost. FHIR writes are matched on their
     * resource type too: a pending Condition does not replace a pending AllergyIntolerance; and
     * types keyed by target (documents) are matched on the target row.
     */
    /**
     * @param array<string, string> $secrets as for submit()
     * @param ?\Closure(ReviewRequest): bool $supersedes which open requests the new one replaces;
     *                                              all of them when null
     */
    public function replacePending(
        ReviewSubmission $submission,
        ReviewActor $actor,
        array $secrets = [],
        ?\Closure $supersedes = null,
    ): ReviewRequest {
        return $this->atomically(function () use ($submission, $actor, $secrets, $supersedes): ReviewRequest {
            foreach ($this->openMatching($submission) as $open) {
                if ($supersedes !== null && $supersedes($open) !== true) {
                    continue;
                }
                $this->transition($open, ReviewStatus::Cancelled, $actor, self::NOTE_SUPERSEDED);
            }
            return $this->create($submission, $actor, $secrets);
        });
    }

    /**
     * Replaces what a pending request holds, for a submitter correcting it before it is decided.
     * The request keeps its id and uuid, and the payload it replaced is kept in its history.
     *
     * The request row is held while its status is checked, so a reviewer deciding it at the same
     * moment either sees the replacement or the replacement fails.
     *
     * Each replacement keeps the payload it overwrote, so $maxRevisions bounds how much history
     * one request can grow while it waits; null leaves it unbounded.
     *
     * @param array<array-key, mixed> $payload
     * @throws ReviewRequestNotFoundException
     * @throws InvalidReviewTransitionException when the request is no longer pending
     * @throws ReviewLimitReachedException when the request has been replaced $maxRevisions times (nothing changes)
     */
    public function revise(int $id, ReviewActor $actor, string $summary, array $payload, ?int $maxRevisions = null): ReviewRequest
    {
        return $this->atomically(function () use ($id, $actor, $summary, $payload, $maxRevisions): ReviewRequest {
            $request = $this->store->findForUpdate($id) ?? throw ReviewRequestNotFoundException::forId($id);
            if ($request->status !== ReviewStatus::Pending) {
                throw InvalidReviewTransitionException::changedConcurrently($id, ReviewStatus::Pending);
            }

            if ($maxRevisions !== null && $this->store->countRevisions($id) >= $maxRevisions) {
                throw ReviewLimitReachedException::forRevisions($maxRevisions);
            }

            $now = $this->clock->now();
            $this->store->replacePayload($id, $summary, $payload, $now);
            $this->store->appendEvent($id, ReviewStatus::Pending, ReviewStatus::Pending, $actor, $now, self::NOTE_REVISED, $request->payload);
            $revised = $this->get($id);
            $this->pendingAudit[] = ['request' => $revised, 'from' => ReviewStatus::Pending, 'actor' => $actor, 'note' => self::NOTE_REVISED];

            return $revised;
        });
    }

    /**
     * Records in the audit log that open requests were shown outside staff review: a patient app
     * reading back what it submitted. Nothing is recorded for an empty list.
     *
     * As with the audit of a change, a failure to write the entry is logged for the operator
     * rather than failing the read.
     *
     * @param list<ReviewRequest> $requests
     */
    public function recordAccess(array $requests, ReviewActor $actor): void
    {
        if ($requests === []) {
            return;
        }
        try {
            $this->auditTrail->recordAccess($requests, $actor);
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            $this->logger->error('Review requests were read but the audit log entry could not be written', [
                'request_ids' => array_map(static fn(ReviewRequest $request): int => $request->id, $requests),
                'exception' => $e,
            ]);
        }
    }

    /**
     * Approves a pending request and applies it to the chart through the handler for its type.
     * If the handler throws, nothing changes and the request stays pending.
     *
     * @param ?string $reviewedRevision the revision() the reviewer was shown; required for a
     *                                  request its submitter can replace (see revise())
     * @throws ReviewHandlerMissingException when no handler is registered for the type
     * @throws ReviewRequestChangedException when the request was replaced since that revision
     */
    public function approve(int $id, ReviewActor $reviewer, ?string $note = null, ?string $reviewedRevision = null): ReviewRequest
    {
        return $this->atomically(function () use ($id, $reviewer, $note, $reviewedRevision): ReviewRequest {
            $request = $this->forDecision($id, $reviewedRevision);
            $handler = $this->handlers[$request->type->value]
                ?? throw ReviewHandlerMissingException::forType($request->type);

            $approved = $this->transition($request, ReviewStatus::Approved, $reviewer, $note);
            $handler->apply($approved, $reviewer);

            return $this->transition($approved, ReviewStatus::Completed, $reviewer, null);
        });
    }

    /**
     * @param ?string $reviewedRevision as for approve()
     * @throws ReviewRequestChangedException when the request was replaced since that revision
     */
    public function deny(int $id, ReviewActor $reviewer, ?string $note = null, ?string $reviewedRevision = null): ReviewRequest
    {
        return $this->atomically(
            fn(): ReviewRequest => $this->transition($this->forDecision($id, $reviewedRevision), ReviewStatus::Denied, $reviewer, $note)
        );
    }

    public function cancel(int $id, ReviewActor $actor, ?string $note = null): ReviewRequest
    {
        return $this->change($id, ReviewStatus::Cancelled, $actor, $note);
    }

    /**
     * Cancels the request and runs $alongside in the same transaction, so if either fails
     * neither happens. $alongside must not open a transaction of its own.
     *
     * @param callable(ReviewRequest): void $alongside receives the cancelled request
     */
    public function cancelAlongside(int $id, ReviewActor $actor, ?string $note, callable $alongside): ReviewRequest
    {
        return $this->atomically(function () use ($id, $actor, $note, $alongside): ReviewRequest {
            $cancelled = $this->transition($this->get($id), ReviewStatus::Cancelled, $actor, $note);
            $alongside($cancelled);

            return $cancelled;
        });
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
     * The request with this uuid, in canonical text form. Null when there is none, or when the
     * text is not a uuid (an id from a URL can be anything).
     */
    public function findByUuid(string $uuid): ?ReviewRequest
    {
        try {
            $bytes = $this->uuidFactory->fromString($uuid)->getBytes();
        } catch (InvalidUuidStringException) {
            return null;
        }

        return $this->store->findByUuid($bytes);
    }

    /**
     * The open requests one API client submitted for the patient, of one type and resource type,
     * oldest first.
     *
     * @return list<ReviewRequest>
     */
    public function allOpenForClient(int $pid, ReviewType $type, FhirResourceType $resourceType, string $clientId): array
    {
        return $this->store->findOpenForClient($pid, $type, $resourceType, $clientId);
    }

    /**
     * The request the patient and staff currently see as open for this type (and, for FHIR
     * writes, resource type), if any.
     */
    public function openFor(int $pid, ReviewType $type, ?FhirResourceType $resourceType = null): ?ReviewRequest
    {
        return $this->store->findOpen($pid, $type, $resourceType)[0] ?? null;
    }

    /**
     * Every open request of this type (and, for FHIR writes, resource type) for the patient,
     * newest first. Usually one, but some types (a charged payment) never replace an open
     * request.
     *
     * @return list<ReviewRequest>
     */
    public function allOpenFor(int $pid, ReviewType $type, ?FhirResourceType $resourceType = null): array
    {
        return $this->store->findOpen($pid, $type, $resourceType);
    }

    /**
     * The open request for one target row (a portal document), if any.
     */
    public function openForTarget(int $pid, ReviewType $type, string $targetTable, string $targetId): ?ReviewRequest
    {
        return $this->store->findOpenForTarget($pid, $type, $targetTable, $targetId)[0] ?? null;
    }

    /**
     * The most recent request for one target row, open or closed.
     */
    public function latestForTarget(int $pid, ReviewType $type, string $targetTable, string $targetId): ?ReviewRequest
    {
        return $this->store->findLatestForTarget($pid, $type, $targetTable, $targetId);
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
     * The decrypted secret, or null when the request has been decided or has none. It stays
     * readable while the request is approved, so the handler applying it can use it.
     *
     * @throws CryptoGenException when the stored secret cannot be decrypted
     */
    public function readSecret(int $id, string $kind): ?string
    {
        $request = $this->store->find($id);
        if ($request === null || $request->status->isTerminal()) {
            return null;
        }
        $ciphertext = $this->store->getSecret($id, $kind);

        return $ciphertext === null ? null : $this->crypto->decryptFromDatabase($ciphertext);
    }

    /**
     * The open requests a new submission would supersede.
     *
     * @return list<ReviewRequest>
     */
    private function openMatching(ReviewSubmission $submission): array
    {
        if ($submission->type->isKeyedByTarget() && $submission->targetTable !== null && $submission->targetId !== null) {
            return $this->store->findOpenForTarget($submission->pid, $submission->type, $submission->targetTable, $submission->targetId);
        }

        return $this->store->findOpen($submission->pid, $submission->type, $submission->resourceType);
    }

    /**
     * @param array<string, string> $secrets
     */
    private function create(ReviewSubmission $submission, ReviewActor $actor, array $secrets): ReviewRequest
    {
        $now = $this->clock->now();
        $status = $submission->type->initialStatus();
        $id = $this->store->insert($submission, $status, $this->uuidFactory->uuid4()->getBytes(), $now);
        $this->store->appendEvent($id, null, $status, $actor, $now, null);
        foreach ($secrets as $kind => $plaintext) {
            $this->store->putSecret($id, $kind, $this->crypto->encryptForDatabase($plaintext), $now);
        }

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

    /**
     * The request a reviewer is deciding. Given the revision they were shown, the row is held
     * until the decision commits, so revise() cannot replace the content between this check and
     * the change of status; a pending request no longer at that revision is refused. One that
     * is no longer pending is left to transition() to refuse.
     *
     * Call inside atomically().
     *
     * @throws ReviewRequestNotFoundException
     * @throws ReviewRequestChangedException
     */
    private function forDecision(int $id, ?string $reviewedRevision): ReviewRequest
    {
        if ($reviewedRevision === null) {
            return $this->get($id);
        }
        $request = $this->store->findForUpdate($id) ?? throw ReviewRequestNotFoundException::forId($id);
        if ($request->status === ReviewStatus::Pending && !hash_equals($request->revision(), $reviewedRevision)) {
            throw ReviewRequestChangedException::sinceReviewed($id);
        }

        return $request;
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
        // Not on approved: approve() runs the handler after this, and the handler may need the
        // secret. approve() moves on to completed in the same transaction, which deletes it.
        if ($to->isTerminal()) {
            $this->store->deleteSecrets($request->id);
        }

        $updated = $this->get($request->id);
        $this->pendingAudit[] = ['request' => $updated, 'from' => $from, 'actor' => $actor, 'note' => $note];

        return $updated;
    }
}

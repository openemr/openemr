<?php

/**
 * Holds a patient app's FHIR write in the review queue instead of writing it to the chart, and
 * lets the app change or withdraw it while it waits.
 *
 * Every patient app write goes through here, in every mode. In order:
 *
 *  1. The practice's policy must allow this kind of client to write this type (403).
 *  2. The server-set fields are forced (PatientWriteFields: 400 or 403).
 *  3. The resource, as the queue stores it, must be within the size limit (413).
 *  4. It must refer only to what this server has, as approval will need (400): for a
 *     QuestionnaireResponse, a local Questionnaire at its current version.
 *  5. The app must have fewer than the limit of writes waiting for this patient (429).
 *
 * Then it is a pending fhir-write request. Its uuid is the id the app is given in Location;
 * approval writes the record under the same id.
 *
 * An update (replace()) goes through the same policy, fields, size and reference checks, and may only name
 * the app's own write that is still waiting. It keeps the id, and the version it replaces stays
 * in the request's history.
 *
 * The request body is expected to have passed FHIR validation already, as the REST controller
 * does for every write.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\Exception\ReviewLimitReachedException;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class PatientWriteHold
{
    /** The largest resource held, in bytes of the JSON the queue stores: 256 KB. */
    public const MAX_PAYLOAD_BYTES = 262144;

    /** Writes one app may have waiting for review for one patient. */
    public const MAX_OPEN_PER_CLIENT = 50;

    /**
     * Times an app may replace one write while it waits. Each replacement keeps what it
     * overwrote in the write's history, so this bounds how much one write can grow.
     */
    public const MAX_REVISIONS = 20;

    /** Note on a request the app withdrew by marking it entered-in-error. */
    public const NOTE_WITHDRAWN = 'withdrawn by the app (entered-in-error)';

    public function __construct(
        private ReviewQueueService $queue,
        private PatientWritePolicy $policy,
        private FhirWriteReviewHandler $handler,
        private PatientWriteFields $fields = new PatientWriteFields(),
        private ReviewRequestMapper $mapper = new ReviewRequestMapper(),
    ) {
    }

    /**
     * @param array<array-key, mixed> $resource decoded FHIR JSON, already validated
     * @throws PatientWriteRefusal when the write is not held; nothing is added
     */
    public function hold(PatientWriteContext $context, PatientWritableType $type, array $resource): ReviewRequest
    {
        $this->refuseUnlessAllowed($context, $type);
        $held = $this->prepare($context, $type, $resource);

        $submission = new ReviewSubmission(
            $context->pid(),
            ReviewType::FhirWrite,
            PatientWriteSummary::of($type, $held),
            $held,
            ReviewSource::Api,
            $context->clientId,
            resourceType: $type->fhirResourceType(),
        );
        try {
            return $this->queue->submitWithinLimit($submission, $context->actor, self::MAX_OPEN_PER_CLIENT);
        } catch (ReviewLimitReachedException $e) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::TooManyOpen, $e);
        }
    }

    /**
     * A FHIR update of the app's own write that is still waiting for review: replaces what is
     * held, or withdraws it (cancels the request) when the update marks it entered-in-error.
     *
     * Anything else the id could name gets the same 403, so an app learns nothing about chart
     * records or other apps' writes: a chart record (an approved write included), another app's
     * or patient's write, or nothing at all. The app's own denied or withdrawn write is 410.
     *
     * @param string $id the id the update was sent to
     * @param array<array-key, mixed> $resource decoded FHIR JSON, already validated
     * @return ReviewRequest the request as replaced, or as cancelled
     * @throws PatientWriteRefusal when nothing is changed, 429 once the write has been replaced MAX_REVISIONS times
     */
    public function replace(PatientWriteContext $context, PatientWritableType $type, string $id, array $resource): ReviewRequest
    {
        $this->refuseUnlessAllowed($context, $type);
        if (($resource['id'] ?? null) !== $id) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::IdMismatch);
        }
        $request = $this->queue->findByUuid($id);
        if ($request === null || !$context->wrote($request, $type)) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::NotHeldForApp);
        }
        self::refuseUnlessPending($request);

        try {
            if ($this->fields->withdraws($type, $resource)) {
                return $this->queue->cancel($request->id, $context->actor, self::NOTE_WITHDRAWN);
            }
            $held = $this->prepare($context, $type, $resource);

            return $this->queue->revise($request->id, $context->actor, PatientWriteSummary::of($type, $held), $held, self::MAX_REVISIONS);
        } catch (ReviewLimitReachedException $e) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::TooManyRevisions, $e);
        } catch (InvalidReviewTransitionException $e) {
            // Staff decided it between the lookup and the change.
            self::refuseUnlessPending($this->queue->find($request->id) ?? $request, $e);
            throw $e;
        }
    }

    /**
     * @throws PatientWriteRefusal when the practice's policy doesn't allow this client to write this type
     */
    private function refuseUnlessAllowed(PatientWriteContext $context, PatientWritableType $type): void
    {
        if (!$this->policy->allowsWrite($context->clientType, $type)) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::NotAllowed);
        }
    }

    /**
     * The resource as it is held: server-set fields forced, within the size limit, and referring
     * only to what this server has.
     *
     * @param array<array-key, mixed> $resource
     * @return array<array-key, mixed>
     * @throws PatientWriteRefusal
     */
    private function prepare(PatientWriteContext $context, PatientWritableType $type, array $resource): array
    {
        $held = $this->fields->force($type, $resource, $context->patientUuid);
        // Measured as the queue stores it, not as the app sent it: the stored JSON escapes
        // slashes and non-ASCII text, so the same resource can take several times the bytes.
        try {
            $stored = $this->mapper->encodePayload($held);
        } catch (\JsonException $e) {
            // Decoded request JSON always re-encodes; anything else is not a resource.
            throw new PatientWriteRefusal(PatientWriteRefusalReason::WrongType, $e);
        }
        if (strlen($stored) > self::MAX_PAYLOAD_BYTES) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::TooLarge);
        }
        // Otherwise the app is given an id for a record that can never exist.
        if (!$this->handler->couldWrite($type, $held)) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::NotWritable);
        }

        return $held;
    }

    /**
     * @throws PatientWriteRefusal 410 when the write was denied or withdrawn; 403 when it is
     *                             in the chart, or being put there
     */
    private static function refuseUnlessPending(ReviewRequest $request, ?\Throwable $previous = null): void
    {
        if ($request->status === ReviewStatus::Pending) {
            return;
        }
        $reason = $request->status === ReviewStatus::Denied || $request->status === ReviewStatus::Cancelled
            ? PatientWriteRefusalReason::Gone
            : PatientWriteRefusalReason::NotHeldForApp;

        throw new PatientWriteRefusal($reason, $previous);
    }
}

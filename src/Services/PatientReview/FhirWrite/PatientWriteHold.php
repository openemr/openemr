<?php

/**
 * Holds a patient app's FHIR write in the review queue instead of writing it to the chart.
 *
 * Every patient app write goes through here, in every mode. In order:
 *
 *  1. The practice's policy must allow this kind of client to write this type (403).
 *  2. The resource must be within the size limit (413).
 *  3. The server-set fields are forced (PatientWriteFields: 400 or 403).
 *  4. The app must have fewer than the limit of writes waiting for this patient (429).
 *
 * Then it is a pending fhir-write request. Its uuid is the id the app is given in Location;
 * approval writes the record under the same id.
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

use OpenEMR\Services\PatientReview\Exception\ReviewLimitReachedException;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class PatientWriteHold
{
    /** The largest resource held, as JSON: 256 KB. */
    public const MAX_PAYLOAD_BYTES = 262144;

    /** Writes one app may have waiting for review for one patient. */
    public const MAX_OPEN_PER_CLIENT = 50;

    public function __construct(
        private ReviewQueueService $queue,
        private PatientWritePolicy $policy,
        private PatientWriteFields $fields = new PatientWriteFields(),
    ) {
    }

    /**
     * @param array<array-key, mixed> $resource decoded FHIR JSON, already validated
     * @throws PatientWriteRefusal when the write is not held; nothing is added
     */
    public function hold(PatientWriteContext $context, PatientWritableType $type, array $resource): ReviewRequest
    {
        if (!$this->policy->allowsWrite($context->clientType, $type)) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::NotAllowed);
        }
        $encoded = json_encode($resource, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            // Decoded request JSON always re-encodes; anything else is not a resource.
            throw new PatientWriteRefusal(PatientWriteRefusalReason::WrongType);
        }
        if (strlen($encoded) > self::MAX_PAYLOAD_BYTES) {
            throw new PatientWriteRefusal(PatientWriteRefusalReason::TooLarge);
        }
        $held = $this->fields->force($type, $resource, $context->patientUuid);

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
}

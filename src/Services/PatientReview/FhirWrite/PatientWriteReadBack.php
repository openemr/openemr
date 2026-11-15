<?php

/**
 * A patient app reading back the writes it sent that are waiting for review.
 *
 * Only the app that wrote an item, for the token's patient, can see it; to every other reader
 * (another app, bulk export, C-CDA, decision support) a held write does not exist. What the app
 * sees is the resource as held, under the id it was given, tagged as held for review. The
 * server-set fields already say it is unverified: an Observation is preliminary, and an
 * AllergyIntolerance or Condition is unconfirmed.
 *
 * Once staff decide, the queue no longer answers for it: an approved write is a chart record
 * read like any other, and a denied or withdrawn one is gone (410).
 *
 * Every read that shows a held write is recorded in the audit log.
 *
 * Search returns every held write of the type; the REST layer applies the search parameters.
 * Whether the token has a read scope is the REST layer's check too.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class PatientWriteReadBack
{
    /** meta.tag system marking a resource the app wrote that staff have not yet reviewed. */
    public const HELD_TAG_SYSTEM = 'http://open-emr.org/fhir/CodeSystem/patient-write';
    public const HELD_TAG_CODE = 'held-for-review';

    public function __construct(private ReviewQueueService $queue)
    {
    }

    /**
     * @param string $id the id the app read
     */
    public function read(PatientWriteContext $context, PatientWritableType $type, string $id): PatientWriteRead
    {
        $request = $this->queue->findByUuid($id);
        if ($request === null || !$context->wrote($request, $type)) {
            return PatientWriteRead::notHeld();
        }

        return match ($request->status) {
            ReviewStatus::Pending => $this->shown($context, $type, $request),
            ReviewStatus::Denied, ReviewStatus::Cancelled => PatientWriteRead::gone(),
            ReviewStatus::AwaitingPayment, ReviewStatus::Approved, ReviewStatus::Completed => PatientWriteRead::notHeld(),
        };
    }

    /**
     * The app's writes of this type waiting for review for the token's patient, oldest first.
     *
     * @return list<array<array-key, mixed>>
     */
    public function search(PatientWriteContext $context, PatientWritableType $type): array
    {
        $held = array_values(array_filter(
            $this->queue->allOpenForClient($context->pid(), ReviewType::FhirWrite, $type->fhirResourceType(), $context->clientId),
            static fn(ReviewRequest $request): bool => $request->status === ReviewStatus::Pending,
        ));
        $this->queue->recordAccess($held, $context->actor);

        return array_map(static fn(ReviewRequest $request): array => self::resource($type, $request), $held);
    }

    /**
     * The held resource as the app sees it: under the id it was given, last updated when it was
     * held or replaced, and tagged as held for review.
     *
     * @return array<array-key, mixed>
     */
    public static function resource(PatientWritableType $type, ReviewRequest $request): array
    {
        return [
            'resourceType' => $type->fhirResourceType()->value,
            'id' => $request->uuid,
            'meta' => [
                'lastUpdated' => $request->updatedAt->format(DATE_ATOM),
                'tag' => [[
                    'system' => self::HELD_TAG_SYSTEM,
                    'code' => self::HELD_TAG_CODE,
                    'display' => 'Held for staff review; not yet in the chart',
                ]],
            ],
        ] + $request->payload;
    }

    private function shown(PatientWriteContext $context, PatientWritableType $type, ReviewRequest $request): PatientWriteRead
    {
        $this->queue->recordAccess([$request], $context->actor);

        return PatientWriteRead::held(self::resource($type, $request));
    }
}

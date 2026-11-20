<?php

/**
 * Applies an approved fhir-write request: the one ReviewType::FhirWrite handler, dispatching on
 * the request's resource type to its applier.
 *
 * A resource type with no applier is refused, so the request stays pending rather than being
 * marked done with nothing written. Each type's applier arrives with the PR that opens the
 * type to patient apps.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewHandlerInterface;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewType;

final class FhirWriteReviewHandler implements ReviewHandlerInterface
{
    /** @var array<string, PatientWriteApplierInterface> keyed by PatientWritableType name */
    private array $appliers = [];

    /**
     * @param iterable<PatientWriteApplierInterface> $appliers
     */
    public function __construct(iterable $appliers)
    {
        foreach ($appliers as $applier) {
            $this->appliers[$applier->type()->name] = $applier;
        }
    }

    public function type(): ReviewType
    {
        return ReviewType::FhirWrite;
    }

    /**
     * Whether staff can approve this request: its resource type has an applier, and what it
     * holds could be written now.
     */
    public function canApply(ReviewRequest $request): bool
    {
        return $this->applierFor($request)?->canWrite($request->payload) === true;
    }

    /**
     * Whether a resource being held could be written on approval. True when the type has no
     * applier yet: nothing can be checked, and staff can still deny it.
     *
     * @param array<array-key, mixed> $resource the resource as it would be held
     */
    public function couldWrite(PatientWritableType $type, array $resource): bool
    {
        $applier = $this->appliers[$type->name] ?? null;

        return $applier === null || $applier->canWrite($resource);
    }

    /**
     * Writes the approved request through its type's applier; a type without one is refused.
     */
    public function apply(ReviewRequest $request, ReviewActor $reviewer): void
    {
        $applier = $this->applierFor($request)
            ?? throw new \LogicException('Patient app writes of this resource type cannot be approved yet');
        $applier->apply($request);
    }

    /**
     * The applier for a fhir-write request's resource type, if there is one.
     */
    private function applierFor(ReviewRequest $request): ?PatientWriteApplierInterface
    {
        if ($request->type !== ReviewType::FhirWrite || $request->resourceType === null) {
            return null;
        }
        $type = PatientWritableType::forResourceType($request->resourceType);

        return $type === null ? null : ($this->appliers[$type->name] ?? null);
    }
}

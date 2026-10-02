<?php

/**
 * A ReviewHandlerInterface that records what it applied, or fails on demand.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewHandlerInterface;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewType;

final class RecordingReviewHandler implements ReviewHandlerInterface
{
    /** @var list<int> ids of the requests applied */
    public array $applied = [];

    public function __construct(
        private readonly ReviewType $type,
        private readonly ?\RuntimeException $failure = null,
    ) {
    }

    public function type(): ReviewType
    {
        return $this->type;
    }

    public function apply(ReviewRequest $request, ReviewActor $reviewer): void
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }
        $this->applied[] = $request->id;
    }
}

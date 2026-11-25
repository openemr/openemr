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
use OpenEMR\Services\PatientReview\ReviewApprovalOptions;
use OpenEMR\Services\PatientReview\ReviewHandlerInterface;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewType;

final class RecordingReviewHandler implements ReviewHandlerInterface
{
    /** @var list<int> ids of the requests applied */
    public array $applied = [];

    /** @var list<?ReviewApprovalOptions> the options each apply() was given */
    public array $options = [];

    /**
     * @param ?string $note what apply() returns, for the entry that completes the request
     */
    public function __construct(
        private readonly ReviewType $type,
        private readonly ?\RuntimeException $failure = null,
        private readonly ?string $note = null,
    ) {
    }

    public function type(): ReviewType
    {
        return $this->type;
    }

    public function apply(ReviewRequest $request, ReviewActor $reviewer, ?ReviewApprovalOptions $options = null): ?string
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }
        $this->applied[] = $request->id;
        $this->options[] = $options;

        return $this->note;
    }
}

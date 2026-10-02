<?php

/**
 * The requested state change is not allowed from the request's current state, or the request
 * changed state while the change was being made.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Exception;

use OpenEMR\Services\PatientReview\ReviewStatus;

final class InvalidReviewTransitionException extends \DomainException
{
    public static function notAllowed(int $id, ReviewStatus $from, ReviewStatus $to): self
    {
        return new self('Review request ' . $id . ' cannot go from ' . $from->value . ' to ' . $to->value);
    }

    public static function changedConcurrently(int $id, ReviewStatus $expected): self
    {
        return new self('Review request ' . $id . ' is no longer ' . $expected->value);
    }
}

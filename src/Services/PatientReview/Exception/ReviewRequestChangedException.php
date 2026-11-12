<?php

/**
 * The request's content was replaced after the reviewer was shown it, so their decision would
 * apply to a version they did not see.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Exception;

final class ReviewRequestChangedException extends \DomainException
{
    public static function sinceReviewed(int $id): self
    {
        return new self('Review request ' . $id . ' was replaced after it was reviewed');
    }
}

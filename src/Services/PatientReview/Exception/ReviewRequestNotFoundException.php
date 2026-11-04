<?php

/**
 * No review request has the given id.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Exception;

final class ReviewRequestNotFoundException extends \RuntimeException
{
    public static function forId(int $id): self
    {
        return new self('Review request ' . $id . ' was not found');
    }
}

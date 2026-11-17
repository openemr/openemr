<?php

/**
 * No handler is registered to apply an approved request of this type.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Exception;

use OpenEMR\Services\PatientReview\ReviewType;

final class ReviewHandlerMissingException extends \LogicException
{
    public static function forType(ReviewType $type): self
    {
        return new self('No review handler is registered for ' . $type->value . ' requests');
    }
}

<?php

/**
 * A client already has as many open requests for the patient as it is allowed; nothing was
 * added.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Exception;

final class ReviewLimitReachedException extends \RuntimeException
{
    public static function forClient(int $limit): self
    {
        return new self('The client already has ' . $limit . ' open review requests for this patient');
    }

    public static function forRevisions(int $limit): self
    {
        return new self('The request has already been replaced ' . $limit . ' times');
    }
}

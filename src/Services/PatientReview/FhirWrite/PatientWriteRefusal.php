<?php

/**
 * A patient app write that was not held. Nothing was added to the queue.
 *
 * The message is safe to send to the app; it is the reason's diagnostics text.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

final class PatientWriteRefusal extends \RuntimeException
{
    public function __construct(public readonly PatientWriteRefusalReason $reason, ?\Throwable $previous = null)
    {
        parent::__construct($reason->diagnostics(), 0, $previous);
    }
}

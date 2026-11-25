<?php

/**
 * What the reviewer chose when approving a request, beyond approving it: for example where in
 * the chart it goes. Each ReviewType's handler takes its own kind and ignores none it can't use.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

interface ReviewApprovalOptions
{
}

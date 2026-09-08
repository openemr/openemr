<?php

/**
 * Lifecycle state of a pull request as reported by GitHub.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

enum PullRequestState: string
{
    case Open = 'OPEN';
    case Closed = 'CLOSED';
    case Merged = 'MERGED';
}

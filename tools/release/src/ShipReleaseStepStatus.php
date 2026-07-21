<?php

/**
 * Outcome of one step (one PR) in the ship-release orchestration.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

enum ShipReleaseStepStatus: string
{
    case SKIPPED_ALREADY_MERGED = 'skipped_already_merged';
    case MERGED = 'merged';
    case BLOCKED = 'blocked';
    case WOULD_MERGE = 'would_merge';
    case NOT_REACHED = 'not_reached';
}

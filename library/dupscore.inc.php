<?php

/**
 * Note this may be included by CLI scripts, so don't do anything web-specific here!
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\Patient\DuplicatePatientService;

/**
 * The SQL returned by this function is an expression that computes the duplication
 * score between two patient_data table rows p1 and p2.
 *
 * Prefer DuplicatePatientService::dupScoreSql() in new code; this remains for CLI scripts and
 * other legacy callers.
 */
function getDupScoreSQL(): string
{
    return DuplicatePatientService::dupScoreSql();
}

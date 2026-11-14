<?php

/**
 * Patient questionnaire assessment launcher.
 *
 * Entry point only. The page is AssessmentEntryPoint::dashboard(), with its markup in
 * templates/patient/assessment/.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once(__DIR__ . '/../../globals.php');

use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentEntryPoint;

(new AssessmentEntryPoint())->dashboard()->send();

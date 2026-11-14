<?php

/**
 * Save a patient-context FHIR QuestionnaireResponse from the native Questionnaire runtime.
 *
 * Entry point only. The save is AssessmentEntryPoint::save(), which checks the ACL (write or
 * addonly for a new response, write to amend one) and hands off to AssessmentSaveController.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once(__DIR__ . '/../../globals.php');

use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentEntryPoint;

(new AssessmentEntryPoint())->save(CurrentRequest::get())->send();

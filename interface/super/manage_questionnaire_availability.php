<?php

/**
 * Questionnaire availability management.
 *
 * Entry point only: the URL the Availability and Import tabs under Questionnaires load. The
 * pages are QuestionnaireAvailabilityController and QuestionnaireImportController, with their
 * markup in templates/super/.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once('../globals.php');

use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAdminEntryPoint;

(new QuestionnaireAdminEntryPoint())->handle(CurrentRequest::get())->send();

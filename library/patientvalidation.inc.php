<?php

/**
 * Thin delegator kept for the existing call sites of library/patientvalidation.inc.php.
 * The body lives in PatientValidationService; see the migration tracker, openemr/openemr#11674.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dror Golan <drorgo@matrix.co.il>
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2016 Matrix Israel
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\PatientValidationService;

/**
 * Whether the Patientvalidation module is registered and active in the modules table.
 */
function checkIfPatientValidationHookIsActive(): bool
{
    return PatientValidationService::checkIfPatientValidationHookIsActive();
}

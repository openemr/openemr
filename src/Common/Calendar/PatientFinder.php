<?php

/**
 * Patient finder presentation rules.
 *
 * @package OpenEMR
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Calendar;

final class PatientFinder
{
    public static function canAddPatient(bool $patientSelectionOnly, bool $aclAllowsAdd): bool
    {
        return !$patientSelectionOnly && $aclAllowsAdd;
    }
}

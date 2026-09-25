<?php

/**
 * View values used by the calendar patient finder.
 *
 * @package OpenEMR
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Calendar;

final class PatientFinderView
{
    public static function scriptUrl(string $webRoot): string
    {
        return $webRoot . '/library/js/calendar-patient-finder.js';
    }

    public static function addPatientUrl(string $webRoot): string
    {
        return $webRoot . '/interface/new/new.php';
    }

    /**
     * Return the complete optional style attribute for the Add Patient link.
     *
     * The callback keeps the ACL dependency outside this isolated view helper.
     * Its short-circuiting also preserves the existing behavior of not querying
     * the ACL when pflag has already hidden the link.
     *
     * @param callable(): bool $canAddPatient
     */
    public static function addPatientVisibilityStyle(bool $pflag, callable $canAddPatient): string
    {
        if ($pflag || !$canAddPatient()) {
            return ' style="display: none;"';
        }

        return '';
    }
}

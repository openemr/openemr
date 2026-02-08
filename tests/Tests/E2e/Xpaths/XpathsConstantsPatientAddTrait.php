<?php

/**
 * XpathsConstantsPatientAddTrait class
 *
 *
 * TODO when no longer supporting PHP 8.1
 * THIS class is needed since unable to add constants directly to the PatientAddTrait in PHP 8.1
 *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Xpaths;

class XpathsConstantsPatientAddTrait
{
    public const CREATE_PATIENT_FORM_PATIENTADD_TRAIT = "//form[@id='DEM']";

    public const CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT = "//*[@id='create']";

    public const NEW_PATIENT_IFRAME_PATIENTADD_TRAIT = "//iframe[@id='modalframe']";

    public const CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT = "//*[@id='confirmCreate']";

    public const NEW_PATIENT_FORM_FNAME_FIELD = "//input[@type='text' and @name='form_fname']";
}

<?php

/**
 * XpathsConstantsPatientOpenTrait class
 *
 *
 * TODO when no longer supporting PHP 8.1
 * THIS class is needed since unable to add constants directly to the PatientOpenTrait in PHP 8.1
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

class XpathsConstantsPatientOpenTrait
{
    public const ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT = "//form[@name='frm_search_globals']";

    public const ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT = "//button[@id='search_globals']";
}
